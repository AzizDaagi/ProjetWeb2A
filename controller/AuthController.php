<?php

require_once __DIR__ . '/../model/Database.php';
require_once __DIR__ . '/../model/User.php';

class AuthController
{
    private const FACE_DESCRIPTOR_SIZE = 128;
    private const FACE_DISTANCE_THRESHOLD = 0.52;

    private $userModel;
    private $appConfig;
    private $mailConfig;

    public function __construct()
    {
        $pdo = Database::getConnection();
        $this->userModel = new User($pdo);
        $configFile = __DIR__ . '/../model/config.php';
        $this->appConfig = file_exists($configFile) ? include $configFile : [];
        $mailConfigFile = __DIR__ . '/../model/mail.php';
        $this->mailConfig = file_exists($mailConfigFile) ? include $mailConfigFile : [];
    }

    private function redirect($action)
    {
        header('Location: /projet-web-25-26/index.php?action=' . $action);
        exit;
    }

    private function buildActionUrl($action)
    {
        return '/projet-web-25-26/index.php?action=' . $action;
    }

    private function respondJson($payload, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload);
        exit;
    }

    private function getRequestPayload()
    {
        $contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));

        if (strpos($contentType, 'application/json') !== false) {
            $rawInput = file_get_contents('php://input');
            $decoded = json_decode($rawInput, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return $_POST;
    }

    private function postJsonRequest($url, array $payload)
    {
        if (!function_exists('curl_init')) {
            return [
                'success' => false,
                'status' => 0,
                'body' => null,
                'error' => 'cURL indisponible sur le serveur.',
            ];
        }

        $jsonPayload = json_encode($payload);
        if ($jsonPayload === false) {
            return [
                'success' => false,
                'status' => 0,
                'body' => null,
                'error' => 'Erreur de serialisation JSON.',
            ];
        }

        $ch = curl_init($url);
        if ($ch === false) {
            return [
                'success' => false,
                'status' => 0,
                'body' => null,
                'error' => 'Impossible d\'initialiser cURL.',
            ];
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        $rawResponse = curl_exec($ch);
        $curlError = curl_error($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($rawResponse === false || $curlError !== '') {
            return [
                'success' => false,
                'status' => $statusCode,
                'body' => null,
                'error' => $curlError !== '' ? $curlError : 'Erreur reseau.',
            ];
        }

        $decoded = json_decode((string) $rawResponse, true);

        return [
            'success' => $statusCode >= 200 && $statusCode < 300,
            'status' => $statusCode,
            'body' => is_array($decoded) ? $decoded : null,
            'error' => '',
        ];
    }

    private function extractGoogleNames($displayName)
    {
        $name = trim((string) $displayName);
        if ($name === '') {
            return ['prenom' => 'Google', 'nom' => 'User'];
        }

        $parts = preg_split('/\s+/', $name);
        $parts = is_array($parts) ? array_values(array_filter($parts, function ($part) {
            return trim((string) $part) !== '';
        })) : [];

        if (count($parts) === 0) {
            return ['prenom' => 'Google', 'nom' => 'User'];
        }

        if (count($parts) === 1) {
            return ['prenom' => $parts[0], 'nom' => 'User'];
        }

        $prenom = array_shift($parts);
        $nom = implode(' ', $parts);

        return [
            'prenom' => $prenom,
            'nom' => $nom,
        ];
    }

    private function verifyFirebaseIdToken($idToken)
    {
        $apiKey = trim((string) ($this->appConfig['firebase_web_api_key'] ?? ''));
        if ($apiKey === '') {
            return [
                'success' => false,
                'error' => 'Configuration Firebase manquante (FIREBASE_WEB_API_KEY).',
                'user' => null,
            ];
        }

        $verifyUrl = 'https://identitytoolkit.googleapis.com/v1/accounts:lookup?key=' . urlencode($apiKey);
        $result = $this->postJsonRequest($verifyUrl, [
            'idToken' => (string) $idToken,
        ]);

        if (!$result['success']) {
            return [
                'success' => false,
                'error' => 'Verification Firebase echouee.',
                'user' => null,
            ];
        }

        $users = $result['body']['users'] ?? null;
        if (!is_array($users) || empty($users[0]) || !is_array($users[0])) {
            return [
                'success' => false,
                'error' => 'Utilisateur Firebase introuvable.',
                'user' => null,
            ];
        }

        $firebaseUser = $users[0];
        $email = trim((string) ($firebaseUser['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'error' => 'E-mail Firebase invalide.',
                'user' => null,
            ];
        }

        return [
            'success' => true,
            'error' => '',
            'user' => $firebaseUser,
        ];
    }

    private function sanitizeFaceDescriptor($rawDescriptor)
    {
        if (!is_array($rawDescriptor) || count($rawDescriptor) !== self::FACE_DESCRIPTOR_SIZE) {
            return null;
        }

        $descriptor = [];
        foreach ($rawDescriptor as $value) {
            if (!is_numeric($value)) return null;

            $floatValue = (float) $value;
            if (!is_finite($floatValue)) return null;

            $descriptor[] = $floatValue;
        }

        return $descriptor;
    }

    private function computeFaceDistance($knownDescriptor, $candidateDescriptor)
    {
        if (!is_array($knownDescriptor) || !is_array($candidateDescriptor)) return INF;
        if (count($knownDescriptor) !== count($candidateDescriptor)) return INF;

        $sum = 0.0;
        foreach ($knownDescriptor as $i => $value) {
            $delta = $value - $candidateDescriptor[$i];
            $sum += $delta * $delta;
        }

        return sqrt($sum);
    }

    private function generateResetCode($length = 6)
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $max = strlen($alphabet) - 1;
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= $alphabet[random_int(0, $max)];
        }

        return $code;
    }

    private function sendBrevoEmail($to, $subject, $htmlContent)
    {
        $fromEmail = trim((string) ($this->mailConfig['from_email'] ?? getenv('BREVO_FROM_EMAIL')));
        $fromName = trim((string) ($this->mailConfig['from_name'] ?? getenv('BREVO_FROM_NAME')));
        if ($fromName === '') {
            $fromName = 'Smart Nutrition';
        }

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Email destinataire invalide.'];
        }

        if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Email expediteur invalide. Configurez BREVO_FROM_EMAIL avec une adresse verifiee dans Brevo.'];
        }

        if (!$this->isSmtpConfigured()) {
            return [
                'success' => false,
                'error' => 'SMTP non configure en local. Le lien de reinitialisation est affiche en mode developpement.',
                'local_fallback' => true,
            ];
        }

        $smtpPassword = trim((string) ($this->mailConfig['password'] ?? ''));
        if ($smtpPassword !== '') {
            return $this->sendBrevoEmailViaSmtp($to, $subject, $htmlContent, $fromEmail, $fromName);
        }

        $apiKey = trim((string) getenv('BREVO_API_KEY'));
        if ($apiKey === '') {
            return ['success' => false, 'error' => 'Aucune configuration d\'envoi Brevo active.'];
        }

        return $this->sendBrevoEmailViaApi($to, $subject, $htmlContent, $fromEmail, $fromName, $apiKey);
    }

    private function isSmtpConfigured()
    {
        $username = trim((string) ($this->mailConfig['username'] ?? getenv('BREVO_SMTP_USERNAME') ?? ''));
        $password = trim((string) ($this->mailConfig['password'] ?? getenv('BREVO_SMTP_PASSWORD') ?? ''));

        if ($username === '' || $password === '') {
            return false;
        }

        $placeholders = [
            'YOUR_BREVO_SMTP_USERNAME_HERE',
            'YOUR_BREVO_SMTP_PASSWORD_HERE',
            'your_brevo_smtp_username_here',
            'your_brevo_smtp_password_here',
            'smtp_username',
            'smtp_password',
        ];

        return !in_array($username, $placeholders, true) && !in_array($password, $placeholders, true);
    }

    private function isLocalEnvironment()
    {
        $appEnv = strtolower(trim((string) (getenv('APP_ENV') ?: '')));
        $appUrl = strtolower(trim((string) ($this->appConfig['app_url'] ?? '')));
        $serverName = strtolower(trim((string) ($_SERVER['SERVER_NAME'] ?? '')));

        if ($appEnv === 'production') {
            return false;
        }

        if (in_array($appEnv, ['local', 'development', 'dev'], true)) {
            return true;
        }

        return strpos($appUrl, 'localhost') !== false
            || strpos($appUrl, '127.0.0.1') !== false
            || in_array($serverName, ['localhost', '127.0.0.1'], true);
    }

    private function buildLocalResetUrl($email, $code)
    {
        return $this->buildActionUrl('reset-password')
            . '&email=' . urlencode((string) $email)
            . '&code=' . urlencode((string) $code);
    }

    private function sendBrevoEmailViaApi($to, $subject, $htmlContent, $fromEmail, $fromName, $apiKey)
    {
        if (!function_exists('curl_init')) {
            return ['success' => false, 'error' => 'cURL indisponible.'];
        }

        $data = [
            'sender' => [
                'name' => $fromName,
                'email' => $fromEmail,
            ],
            'to' => [
                ['email' => $to],
            ],
            'subject' => $subject,
            'htmlContent' => $htmlContent,
        ];

        $payload = json_encode($data);
        if ($payload === false) {
            return ['success' => false, 'error' => 'Erreur JSON.'];
        }

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        if ($ch === false) {
            return ['success' => false, 'error' => 'Impossible d\'initialiser cURL.'];
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'accept: application/json',
            'api-key: ' . $apiKey,
            'content-type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlError !== '') {
            return ['success' => false, 'error' => 'Erreur cURL: ' . $curlError];
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true, 'error' => ''];
        }

        return ['success' => false, 'error' => 'API Brevo ' . $httpCode . ': ' . (string) $response];
    }

    private function sendBrevoEmailViaSmtp($to, $subject, $htmlContent, $fromEmail, $fromName)
    {
        $host = trim((string) ($this->mailConfig['host'] ?? 'smtp-relay.brevo.com'));
        $port = (int) ($this->mailConfig['port'] ?? 587);
        $username = trim((string) ($this->mailConfig['username'] ?? ''));
        $password = trim((string) ($this->mailConfig['password'] ?? ''));
        $secure = strtolower(trim((string) ($this->mailConfig['secure'] ?? 'tls')));
        $timeout = (int) ($this->mailConfig['timeout'] ?? 30);

        if ($timeout < 1) {
            $timeout = 30;
        }

        if ($username === '') {
            $username = $fromEmail;
        }

        if ($host === '' || $port < 1 || $username === '' || $password === '') {
            return ['success' => false, 'error' => 'Configuration SMTP Brevo incomplete.'];
        }

        $transport = ($secure === 'ssl' || $port === 465) ? 'ssl' : 'tcp';
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $socket = @stream_socket_client(
            $transport . '://' . $host . ':' . $port,
            $errorNumber,
            $errorMessage,
            $timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!$socket) {
            return ['success' => false, 'error' => 'Connexion SMTP impossible: ' . $errorMessage . ' (' . $errorNumber . ')'];
        }

        stream_set_timeout($socket, $timeout);

        $welcome = $this->readSmtpResponse($socket);
        if (!$this->isExpectedSmtpResponse($welcome, [220])) {
            fclose($socket);
            return ['success' => false, 'error' => 'SMTP accueil inattendu: ' . trim((string) $welcome)];
        }

        $helloHost = parse_url($this->appConfig['app_url'] ?? '', PHP_URL_HOST) ?: 'localhost';

        $response = $this->sendSmtpCommand($socket, 'EHLO ' . $helloHost, [250]);
        if (!$this->isExpectedSmtpResponse($response, [250])) {
            fclose($socket);
            return ['success' => false, 'error' => 'EHLO refuse: ' . trim((string) $response)];
        }

        if ($secure === 'tls' && $transport === 'tcp') {
            $response = $this->sendSmtpCommand($socket, 'STARTTLS', [220]);
            if (!$this->isExpectedSmtpResponse($response, [220])) {
                fclose($socket);
                return ['success' => false, 'error' => 'STARTTLS refuse: ' . trim((string) $response)];
            }

            $cryptoEnabled = @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if ($cryptoEnabled !== true) {
                fclose($socket);
                return ['success' => false, 'error' => 'Activation TLS impossible sur la connexion SMTP.'];
            }

            $response = $this->sendSmtpCommand($socket, 'EHLO ' . $helloHost, [250]);
            if (!$this->isExpectedSmtpResponse($response, [250])) {
                fclose($socket);
                return ['success' => false, 'error' => 'EHLO apres TLS refuse: ' . trim((string) $response)];
            }
        }

        $response = $this->sendSmtpCommand($socket, 'AUTH LOGIN', [334]);
        if (!$this->isExpectedSmtpResponse($response, [334])) {
            fclose($socket);
            return ['success' => false, 'error' => 'AUTH LOGIN refuse: ' . trim((string) $response)];
        }

        $response = $this->sendSmtpCommand($socket, base64_encode($username), [334]);
        if (!$this->isExpectedSmtpResponse($response, [334])) {
            fclose($socket);
            return ['success' => false, 'error' => 'Identifiant SMTP refuse. Utilisez le SMTP login affiche dans Brevo > Settings > SMTP & API: ' . trim((string) $response)];
        }

        $response = $this->sendSmtpCommand($socket, base64_encode($password), [235]);
        if (!$this->isExpectedSmtpResponse($response, [235])) {
            fclose($socket);
            return ['success' => false, 'error' => 'Mot de passe SMTP refuse. Verifiez la cle SMTP et le SMTP login Brevo configure dans BREVO_SMTP_USERNAME: ' . trim((string) $response)];
        }

        $response = $this->sendSmtpCommand($socket, 'MAIL FROM:<' . $fromEmail . '>', [250]);
        if (!$this->isExpectedSmtpResponse($response, [250])) {
            fclose($socket);
            return ['success' => false, 'error' => 'Expediteur refuse: ' . trim((string) $response)];
        }

        $response = $this->sendSmtpCommand($socket, 'RCPT TO:<' . $to . '>', [250, 251]);
        if (!$this->isExpectedSmtpResponse($response, [250, 251])) {
            fclose($socket);
            return ['success' => false, 'error' => 'Destinataire refuse: ' . trim((string) $response)];
        }

        $response = $this->sendSmtpCommand($socket, 'DATA', [354]);
        if (!$this->isExpectedSmtpResponse($response, [354])) {
            fclose($socket);
            return ['success' => false, 'error' => 'SMTP DATA refuse: ' . trim((string) $response)];
        }

        $message = $this->buildMimeMessage($to, $subject, $htmlContent, $fromEmail, $fromName);
        fwrite($socket, $message . "\r\n.\r\n");
        $response = $this->readSmtpResponse($socket);
        if (!$this->isExpectedSmtpResponse($response, [250])) {
            fclose($socket);
            return ['success' => false, 'error' => 'Envoi du message refuse: ' . trim((string) $response)];
        }

        $this->sendSmtpCommand($socket, 'QUIT', [221]);
        fclose($socket);

        return ['success' => true, 'error' => ''];
    }

    private function buildMimeMessage($to, $subject, $htmlContent, $fromEmail, $fromName)
    {
        $boundary = 'smart-nutrition-' . bin2hex(random_bytes(12));
        $plainText = $this->htmlToText($htmlContent);
        $encodedHtml = chunk_split(base64_encode($htmlContent), 76, "\r\n");
        $encodedText = chunk_split(base64_encode($plainText), 76, "\r\n");

        $headers = [
            'Date: ' . date(DATE_RFC2822),
            'To: <' . $to . '>',
            'From: ' . $this->encodeMimeHeader($fromName) . ' <' . $fromEmail . '>',
            'Reply-To: <' . $fromEmail . '>',
            'Subject: ' . $this->encodeMimeHeader($subject),
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        ];

        $body = [
            '--' . $boundary,
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
            '',
            $encodedText,
            '--' . $boundary,
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
            '',
            $encodedHtml,
            '--' . $boundary . '--',
        ];

        return str_replace("\r\n.", "\r\n..", implode("\r\n", $headers) . "\r\n\r\n" . implode("\r\n", $body));
    }

    private function htmlToText($htmlContent)
    {
        $text = preg_replace('/<br\s*\/?>/i', "\n", $htmlContent);
        $text = preg_replace('/<\/p>/i', "\n\n", (string) $text);
        $text = strip_tags((string) $text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/\n{3,}/", "\n\n", $text);

        return trim((string) $text);
    }

    private function encodeMimeHeader($value)
    {
        return '=?UTF-8?B?' . base64_encode((string) $value) . '?=';
    }

    private function sendSmtpCommand($socket, $command, array $expectedCodes)
    {
        fwrite($socket, $command . "\r\n");
        $response = $this->readSmtpResponse($socket);

        if (!$this->isExpectedSmtpResponse($response, $expectedCodes)) {
            return $response;
        }

        return $response;
    }

    private function readSmtpResponse($socket)
    {
        $response = '';

        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;

            if (preg_match('/^\d{3}\s/', $line) === 1) {
                break;
            }
        }

        return $response;
    }

    private function isExpectedSmtpResponse($response, array $expectedCodes)
    {
        if (!is_string($response) || strlen($response) < 3) {
            return false;
        }

        $code = (int) substr($response, 0, 3);
        return in_array($code, $expectedCodes, true);
    }

    private function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    private function normalizeRole($role)
    {
        return $role === 'admin' ? 'admin' : 'user';
    }

    private function isProfileIncomplete($user)
    {
        $role = $this->normalizeRole($user['role'] ?? 'user');

        $requiredFields = $role === 'admin'
            ? ['date_naissance']
            : ['date_naissance', 'sexe', 'age', 'poids', 'objectif'];

        foreach ($requiredFields as $field) {
            if (empty($user[$field])) return true;
        }

        return false;
    }

    private function redirectBySessionRole()
    {
        $role = $this->normalizeRole($_SESSION['user_role'] ?? 'user');
        $this->redirect($role === 'admin' ? 'admin-dashboard' : 'home');
    }

    private function hydrateSessionAndResolveNextAction($user, $bypassProfileCheck = false)
    {
        $role = $this->normalizeRole($user['role'] ?? 'user');

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = trim($user['prenom'] . ' ' . $user['nom']);
        $_SESSION['user_role'] = $role;

        if ($role !== 'admin' && !$bypassProfileCheck && $this->isProfileIncomplete($user)) {
            $_SESSION['flash_error'] = 'Completez vos informations.';
            return 'profile';
        }

        return $role === 'admin' ? 'admin-dashboard' : 'home';
    }

    // ================= REGISTER =================

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('register');
        }

        $data = [
            'nom' => trim($_POST['nom'] ?? ''),
            'prenom' => trim($_POST['prenom'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
        ];

        $errors = [];

        if ($data['nom'] === '') $errors[] = 'Nom requis';
        if ($data['prenom'] === '') $errors[] = 'Prenom requis';
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide';
        if (strlen($data['password']) < 6) $errors[] = 'Mot de passe trop court';

        if ($this->userModel->emailExists($data['email'])) {
            $errors[] = 'Email deja utilise';
        }

        if (!empty($errors)) {
            print_r($errors);
            return;
        }

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        if ($this->userModel->create($data)) {
            $_SESSION['success'] = 'Compte cree';
            $this->redirect('login');
        }
    }

    // ================= LOGIN =================

    public function login()
    {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            $this->showLogin('E-mail ou mot de passe invalide.');
            return;
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            $this->showLogin('E-mail ou mot de passe incorrect.');
            return;
        }

        $storedPassword = (string) ($user['password'] ?? '');
        $passwordIsValid = password_verify($password, $storedPassword);

        // Backward compatibility for legacy accounts that still store plain-text passwords.
        if (!$passwordIsValid && hash_equals($storedPassword, $password)) {
            $passwordIsValid = true;

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            if ($hashedPassword !== false) {
                $this->userModel->updatePasswordById($user['id'], $hashedPassword);
                $user['password'] = $hashedPassword;
            }
        }

        if (!$passwordIsValid) {
            $this->showLogin('E-mail ou mot de passe incorrect.');
            return;
        }

        $this->redirect($this->hydrateSessionAndResolveNextAction($user));
    }

    // ================= GOOGLE LOGIN =================

    public function loginWithGoogle()
    {
        $payload = $this->getRequestPayload();
        $idToken = trim((string) ($payload['idToken'] ?? ''));

        if ($idToken === '') {
            $this->respondJson([
                'success' => false,
                'message' => 'Jeton Google manquant.',
            ], 400);
        }

        $verification = $this->verifyFirebaseIdToken($idToken);
        if (empty($verification['success'])) {
            $this->respondJson([
                'success' => false,
                'message' => $verification['error'] ?? 'Verification Google impossible.',
            ], 401);
        }

        $firebaseUser = $verification['user'];
        $email = trim((string) ($firebaseUser['email'] ?? ''));
        $displayName = trim((string) ($firebaseUser['displayName'] ?? ''));

        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            $names = $this->extractGoogleNames($displayName);

            $created = $this->userModel->create([
                'nom' => $names['nom'],
                'prenom' => $names['prenom'],
                'email' => $email,
                // Local password placeholder for Google-created accounts.
                'password' => password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT),
                'role' => 'user',
            ]);

            if (!$created) {
                $this->respondJson([
                    'success' => false,
                    'message' => 'Impossible de creer le compte Google local.',
                ], 500);
            }

            $user = $this->userModel->findByEmail($email);
        }

        if (!$user) {
            $this->respondJson([
                'success' => false,
                'message' => 'Compte utilisateur introuvable.',
            ], 500);
        }

        $next = $this->hydrateSessionAndResolveNextAction($user);

        $this->respondJson([
            'success' => true,
            'redirect' => $this->buildActionUrl($next),
        ]);
    }

    // ================= FACE LOGIN =================

    public function loginWithFace()
    {
        $payload = $this->getRequestPayload();

        $descriptor = $this->sanitizeFaceDescriptor($payload['descriptor'] ?? null);

        if (!$descriptor) {
            $this->respondJson(['success' => false]);
        }

        $users = $this->userModel->getAllWithFaceDescriptors();

        $bestUser = null;
        $bestDistance = INF;

        foreach ($users as $u) {
            $stored = json_decode($u['face_descriptor'], true);
            $dist = $this->computeFaceDistance($stored, $descriptor);

            if ($dist < $bestDistance) {
                $bestDistance = $dist;
                $bestUser = $u;
            }
        }

        if ($bestDistance > self::FACE_DISTANCE_THRESHOLD) {
            $this->respondJson(['success' => false]);
        }

        $next = $this->hydrateSessionAndResolveNextAction($bestUser, true);

        $this->respondJson([
            'success' => true,
            'redirect' => $this->buildActionUrl($next)
        ]);
    }

    // ================= FORGOT PASSWORD =================

    public function forgotPassword()
    {
        $email = trim($_POST['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'Email invalide';
            $this->redirect('forgot');
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            $_SESSION['success'] = 'Si le compte existe, un code est genere.';
            $this->redirect('login');
        }

        $code = $this->generateResetCode();
        $expires = date('Y-m-d H:i:s', time() + 3600);

        $this->userModel->setPasswordResetTokenByEmail($email, $code, $expires);

        $htmlContent = '<p>Voici votre code de reinitialisation :</p>'
            . '<h2>' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '</h2>'
            . '<p>Ce code expire dans 1 heure.</p>';

        $result = $this->sendBrevoEmail($email, 'Code de reinitialisation', $htmlContent);
        if (empty($result['success'])) {
            if ($this->isLocalEnvironment()) {
                $resetUrl = $this->buildLocalResetUrl($email, $code);
                $_SESSION['success'] = 'Mode developpement : l e-mail n a pas pu etre envoye. Utilisez ce code ou ce lien de verification.';
                $_SESSION['dev_reset_link'] = $resetUrl;
                $_SESSION['dev_reset_code'] = $code;
                error_log('[RESET EMAIL FALLBACK] ' . ($result['error'] ?? 'unknown_error'));
                error_log('[DEV RESET LINK] ' . $resetUrl);
                header('Location: /projet-web-25-26/index.php?action=reset-password&email=' . urlencode($email) . '&code=' . urlencode($code));
                exit;
            }

            error_log('[RESET EMAIL ERROR] ' . ($result['error'] ?? 'unknown_error'));
            $_SESSION['flash_error'] = 'Impossible d envoyer l e-mail de reinitialisation pour le moment.';
            $this->redirect('forgot');
        }

        $_SESSION['success'] = 'Un code a ete envoye par e-mail.';
        header('Location: /projet-web-25-26/index.php?action=reset-password&email=' . urlencode($email));
        exit;
    }

    // ================= RESET PASSWORD =================

    public function performReset()
    {
        $email = trim($_POST['email'] ?? '');
        $code = trim((string) ($_POST['code'] ?? ''));
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        $errors = [];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email invalide';
        }
        if ($code === '') {
            $errors[] = 'Code invalide';
        }
        if (strlen($password) < 6) {
            $errors[] = 'Mot de passe trop court';
        }
        if ($password !== $passwordConfirm) {
            $errors[] = 'Les mots de passe ne correspondent pas';
        }

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode('. ', $errors);
            $this->redirect('reset-password');
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user || trim((string) $user['password_reset_token']) !== $code) {
            $_SESSION['flash_error'] = 'Code invalide';
            $this->redirect('reset-password');
        }

        if (empty($user['password_reset_expires']) || strtotime($user['password_reset_expires']) < time()) {
            $this->userModel->clearResetTokenById($user['id']);
            $_SESSION['flash_error'] = 'Code expire';
            $this->redirect('reset-password');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $this->userModel->updatePasswordById($user['id'], $hash);

        $_SESSION['success'] = 'Mot de passe modifie';
        $this->redirect('login');
    }

    // ================= LOGOUT =================

    public function logout()
    {
        session_destroy();
        $this->redirect('login');
    }

    public function showLogin($error = '')
{
    if ($this->isLoggedIn()) {
        $this->redirectBySessionRole();
    }

    $success = $_SESSION['success'] ?? '';
    unset($_SESSION['success']);

    $firebaseConfig = [
        'apiKey' => trim((string) (getenv('FIREBASE_WEB_API_KEY') ?: ($this->appConfig['firebase_web_api_key'] ?? ''))),
        'authDomain' => trim((string) (getenv('FIREBASE_AUTH_DOMAIN') ?: 'smartnutrition-7f619.firebaseapp.com')),
        'projectId' => trim((string) (getenv('FIREBASE_PROJECT_ID') ?: ($this->appConfig['firebase_project_id'] ?? 'smartnutrition-7f619'))),
        'storageBucket' => trim((string) (getenv('FIREBASE_STORAGE_BUCKET') ?: 'smartnutrition-7f619.firebasestorage.app')),
        'messagingSenderId' => trim((string) (getenv('FIREBASE_MESSAGING_SENDER_ID') ?: '584688286786')),
        'appId' => trim((string) (getenv('FIREBASE_APP_ID') ?: '1:584688286786:web:abae17dce0c7979d61c1db')),
        'measurementId' => trim((string) (getenv('FIREBASE_MEASUREMENT_ID') ?: 'G-BJTYY669Y6')),
    ];

    $firebaseGoogleEnabled = $firebaseConfig['apiKey'] !== '';

    $pageTitle = 'Connexion';

    include __DIR__ . '/../view/layouts/header.php';
    include __DIR__ . '/../view/front/auth/login.php';
    include __DIR__ . '/../view/layouts/footer.php';
}
public function showRegister($errors = [], $old = [])
{
    if ($this->isLoggedIn()) {
        $this->redirectBySessionRole();
    }

    $pageTitle = 'Inscription';

    include __DIR__ . '/../view/layouts/header.php';
    include __DIR__ . '/../view/front/auth/register.php';
    include __DIR__ . '/../view/layouts/footer.php';
}
 
public function showForgotPassword($errors = [])
{
    $pageTitle = 'Mot de passe oubliÃƒÆ’Ã‚Â©';

    include __DIR__ . '/../view/layouts/header.php';
    include __DIR__ . '/../view/front/auth/forgot.php';
    include __DIR__ . '/../view/layouts/footer.php';
}
public function showResetForm()
{
    if (!empty($_GET['code'])) {
        $_SESSION['dev_reset_code'] = trim((string) $_GET['code']);
    }

    $pageTitle = 'RÃƒÆ’Ã‚Â©initialiser mot de passe';

    include __DIR__ . '/../view/layouts/header.php';
    include __DIR__ . '/../view/front/auth/reset.php';
    include __DIR__ . '/../view/layouts/footer.php';
}
  
    
}
