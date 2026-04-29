<?php

require_once __DIR__ . '/../model/Database.php';
require_once __DIR__ . '/../model/User.php';

class UserController
{
    private const FACE_DESCRIPTOR_SIZE = 128;

    private $userModel;

    public function __construct()
    {
        $pdo = Database::getConnection();
        $this->userModel = new User($pdo);
    }

    private function redirect($action)
    {
        header('Location: /smart_nutrition/index.php?action=' . $action);
        exit;
    }

    private function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    private function isAdmin()
    {
        return ($_SESSION['user_role'] ?? 'user') === 'admin';
    }

    private function setFlash($type, $message)
    {
        $_SESSION['flash_' . $type] = $message;
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
            if (is_string($rawInput) && trim($rawInput) !== '') {
                $decoded = json_decode($rawInput, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        return $_POST;
    }

    private function sanitizeFaceDescriptor($rawDescriptor)
    {
        if (!is_array($rawDescriptor) || count($rawDescriptor) !== self::FACE_DESCRIPTOR_SIZE) {
            return null;
        }

        $descriptor = [];
        foreach ($rawDescriptor as $value) {
            if (!is_numeric($value)) {
                return null;
            }

            $floatValue = (float) $value;
            if (!is_finite($floatValue)) {
                return null;
            }

            $descriptor[] = $floatValue;
        }

        return $descriptor;
    }

    private function validateUtilisateurData($data, $excludeUserId = null, $isAdmin = false): array
    {
        $errors = [];

        if ($data['nom'] === '') {
            $errors[] = 'Le nom est obligatoire.';
        }

        if ($data['prenom'] === '') {
            $errors[] = 'Le prenom est obligatoire.';
        }

        if ($data['date_naissance'] === '') {
            $errors[] = 'La date de naissance est obligatoire.';
        } else {
            $date = DateTime::createFromFormat('Y-m-d', $data['date_naissance']);
            if (!$date || $date->format('Y-m-d') !== $data['date_naissance']) {
                $errors[] = 'La date de naissance est invalide.';
            } else {
                $today = new DateTime('today');
                $minDate = new DateTime('1950-01-01');
                $maxDate = (clone $today)->modify('-13 years');

                if ($date < $minDate || $date > $maxDate) {
                    $errors[] = 'La date de naissance est invalide.';
                }
            }
        }

        if (!$isAdmin) {
            $sexeValue = trim((string) ($data['sexe'] ?? ''));
            if ($sexeValue === '') {
                $errors[] = 'Le sexe est obligatoire.';
            } elseif (!in_array($sexeValue, ['homme', 'femme'], true)) {
                $errors[] = 'Le sexe est invalide.';
            }

            if ($data['age'] === '') {
                $errors[] = 'L\'age est obligatoire et doit etre un entier.';
            } else {
                $ageValue = filter_var($data['age'], FILTER_VALIDATE_INT);
                if ($ageValue === false) {
                    $errors[] = 'L\'age doit etre un entier.';
                } elseif ($ageValue < 1 || $ageValue > 120) {
                    $errors[] = 'L\'age doit etre compris entre 1 et 120.';
                }
            }

            if ($data['poids'] === '') {
                $errors[] = 'Le poids est obligatoire et doit etre numerique.';
            } else {
                if (!is_numeric($data['poids'])) {
                    $errors[] = 'Le poids doit etre numerique.';
                } else {
                    $poids = (float) $data['poids'];
                    if ($poids < 30 || $poids > 250) {
                        $errors[] = 'Le poids doit etre compris entre 30 et 250 kg.';
                    }
                }
            }

            if ($data['taille'] !== '' && !is_numeric($data['taille'])) {
                $errors[] = 'La taille doit etre numerique.';
            } elseif ($data['taille'] !== '') {
                $taille = (float) $data['taille'];
                if ($taille <= 0 || $taille > 300) {
                    $errors[] = 'La taille doit etre comprise entre 0 et 300 cm.';
                }
            }

            if ($data['objectif'] === '') {
                $errors[] = 'L\'objectif est obligatoire.';
            } elseif (mb_strlen($data['objectif']) < 3 || mb_strlen($data['objectif']) > 255) {
                $errors[] = 'L\'objectif doit contenir entre 3 et 255 caracteres.';
            }
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Adresse e-mail invalide.';
        } elseif ($excludeUserId !== null && $this->userModel->emailExistsForAnother($data['email'], $excludeUserId)) {
            $errors[] = 'Cet e-mail est deja utilise par un autre utilisateur.';
        }

        return $errors;
    }

    private function calculateAgeFromDateNaissance($dateNaissance)
    {
        if (!is_string($dateNaissance) || trim($dateNaissance) === '') {
            return null;
        }

        $birthDate = DateTime::createFromFormat('Y-m-d', $dateNaissance);
        if (!$birthDate || $birthDate->format('Y-m-d') !== $dateNaissance) {
            return null;
        }

        $today = new DateTime('today');
        if ($birthDate > $today) {
            return null;
        }

        return (int) $today->diff($birthDate)->y;
    }

    private function getShortMonthLabel($date)
    {
        $monthMap = [
            '01' => 'Jan',
            '02' => 'Fev',
            '03' => 'Mar',
            '04' => 'Avr',
            '05' => 'Mai',
            '06' => 'Juin',
            '07' => 'Juil',
            '08' => 'Aou',
            '09' => 'Sep',
            '10' => 'Oct',
            '11' => 'Nov',
            '12' => 'Dec',
        ];

        $month = $date->format('m');
        return $monthMap[$month] ?? $date->format('M');
    }

    private function normalizeSearchTerm($value)
    {
        $normalized = trim((string) $value);
        if ($normalized === '') {
            return '';
        }

        if (function_exists('mb_strtolower')) {
            return mb_strtolower($normalized, 'UTF-8');
        }

        return strtolower($normalized);
    }

    private function filterUsersBySearch(array $users, $searchTerm)
    {
        $normalizedSearch = $this->normalizeSearchTerm($searchTerm);
        if ($normalizedSearch === '') {
            return $users;
        }

        $filteredUsers = [];
        foreach ($users as $user) {
            $haystack = implode(' ', [
                (string) ($user['nom'] ?? ''),
                (string) ($user['prenom'] ?? ''),
                (string) ($user['date_naissance'] ?? ''),
                (string) ($user['sexe'] ?? ''),
                (string) ($user['age'] ?? ''),
                (string) ($user['poids'] ?? ''),
                (string) ($user['taille'] ?? ''),
                (string) ($user['objectif'] ?? ''),
                (string) ($user['email'] ?? ''),
                (string) ($user['role'] ?? ''),
            ]);

            if (function_exists('mb_strtolower')) {
                $haystack = mb_strtolower($haystack, 'UTF-8');
            } else {
                $haystack = strtolower($haystack);
            }

            if (strpos($haystack, $normalizedSearch) !== false) {
                $filteredUsers[] = $user;
            }
        }

        return $filteredUsers;
    }

    private function buildUsersReportSummary(array $users)
    {
        $summary = [
            'total' => count($users),
            'with_face' => 0,
            'with_birthdate' => 0,
            'with_email' => 0,
        ];

        foreach ($users as $user) {
            $faceDescriptor = trim((string) ($user['face_descriptor'] ?? ''));
            if ($faceDescriptor !== '') {
                $summary['with_face']++;
            }

            if (trim((string) ($user['date_naissance'] ?? '')) !== '') {
                $summary['with_birthdate']++;
            }

            if (trim((string) ($user['email'] ?? '')) !== '') {
                $summary['with_email']++;
            }
        }

        return $summary;
    }

    private function hasUsersColumn($pdo, $columnName)
    {
        try {
            $stmt = $pdo->prepare('SHOW COLUMNS FROM users LIKE :column_name');
            $stmt->execute(['column_name' => $columnName]);
            return (bool) $stmt->fetch();
        } catch (Throwable $e) {
            return false;
        }
    }

    private function buildUsersEvolutionData($pdo)
    {
        $months = 6;
        $start = (new DateTime('first day of this month'))->modify('-' . ($months - 1) . ' months');

        $monthKeys = [];
        $labels = [];
        for ($i = 0; $i < $months; $i++) {
            $date = (clone $start)->modify('+' . $i . ' months');
            $monthKeys[] = $date->format('Y-m');
            $labels[] = $this->getShortMonthLabel($date);
        }

        $hommes = array_fill(0, $months, 0);
        $femmes = array_fill(0, $months, 0);

        $hasCreatedAt = $this->hasUsersColumn($pdo, 'created_at');

        if ($hasCreatedAt) {
            $hommesMap = array_fill_keys($monthKeys, 0);
            $femmesMap = array_fill_keys($monthKeys, 0);

            $sexStmt = $pdo->prepare(
                'SELECT DATE_FORMAT(created_at, "%Y-%m") AS month_key,
                        LOWER(TRIM(sexe)) AS sexe_key,
                        COUNT(*) AS total
                 FROM users
                 WHERE COALESCE(NULLIF(`role`, ""), "user") <> "admin"
                   AND created_at IS NOT NULL
                   AND created_at >= :start_date
                   AND NULLIF(TRIM(sexe), "") IS NOT NULL
                 GROUP BY month_key, sexe_key
                 ORDER BY month_key ASC'
            );
            $sexStmt->execute(['start_date' => $start->format('Y-m-01 00:00:00')]);

            foreach ($sexStmt->fetchAll() as $row) {
                $key = (string) ($row['month_key'] ?? '');
                $sexeKey = (string) ($row['sexe_key'] ?? '');
                $total = (int) ($row['total'] ?? 0);

                if ($sexeKey === 'homme' && array_key_exists($key, $hommesMap)) {
                    $hommesMap[$key] += $total;
                }

                if ($sexeKey === 'femme' && array_key_exists($key, $femmesMap)) {
                    $femmesMap[$key] += $total;
                }
            }

            foreach ($monthKeys as $index => $key) {
                $hommes[$index] = (int) ($hommesMap[$key] ?? 0);
                $femmes[$index] = (int) ($femmesMap[$key] ?? 0);
            }

            return [
                'labels' => $labels,
                'hommes' => $hommes,
                'femmes' => $femmes,
            ];
        }

        $fallbackRows = $pdo->query(
              'SELECT id, date_naissance, sexe, age, poids, objectif
             FROM users
             WHERE COALESCE(NULLIF(`role`, ""), "user") <> "admin"
             ORDER BY id ASC'
        )->fetchAll();

        $totalRows = count($fallbackRows);
        if ($totalRows === 0) {
            return [
                'labels' => $labels,
                'hommes' => $hommes,
                'femmes' => $femmes,
            ];
        }

        foreach ($fallbackRows as $index => $row) {
            $bucket = (int) floor(($index * $months) / $totalRows);
            if ($bucket < 0) {
                $bucket = 0;
            }
            if ($bucket >= $months) {
                $bucket = $months - 1;
            }

            $sexe = strtolower(trim((string) ($row['sexe'] ?? '')));
            if ($sexe === 'homme') {
                $hommes[$bucket]++;
            } elseif ($sexe === 'femme') {
                $femmes[$bucket]++;
            }
        }

        return [
            'labels' => $labels,
            'hommes' => $hommes,
            'femmes' => $femmes,
        ];
    }

    public function profile($errors = [], $user = null)
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('login');
        }

        if ($user === null) {
            $user = $this->userModel->findById($_SESSION['user_id']);
        }

        if (!$user) {
            $this->logout();
        }

        $flashSuccess = $_SESSION['flash_success'] ?? '';
        $flashError = $_SESSION['flash_error'] ?? '';
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $pageTitle = 'Mon profil';
        include __DIR__ . '/../view/layouts/header.php';
        include __DIR__ . '/../view/front/profile.php';
        include __DIR__ . '/../view/layouts/footer.php';
    }

    public function updateProfile()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('login');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('profile');
        }

        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            $this->logout();
        }

        $currentUser = $this->userModel->findById($userId);
        if (!$currentUser) {
            $this->logout();
        }

        $data = [
            'nom' => trim($_POST['nom'] ?? ''),
            'prenom' => trim($_POST['prenom'] ?? ''),
            'date_naissance' => trim($_POST['date_naissance'] ?? ''),
            'sexe' => trim((string) ($_POST['sexe'] ?? '')),
            'age' => trim((string) ($_POST['age'] ?? '')),
            'poids' => trim((string) ($_POST['poids'] ?? '')),
            'taille' => trim((string) ($_POST['taille'] ?? '')),
            'objectif' => trim($_POST['objectif'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
        ];

        $isAdminUser = (($currentUser['role'] ?? ($_SESSION['user_role'] ?? 'user')) === 'admin');
        if ($isAdminUser) {
            // Admin accounts do not use these profile metrics.
            $data['age'] = '';
            $data['poids'] = '';
            $data['taille'] = '';
            $data['objectif'] = '';
            $data['sexe'] = '';
        } else {
            $calculatedAge = $this->calculateAgeFromDateNaissance($data['date_naissance']);
            $data['age'] = $calculatedAge === null ? '' : (string) $calculatedAge;
        }

        $errors = $this->validateUtilisateurData($data, $userId, $isAdminUser);

        if (!empty($errors)) {
            $userForView = array_merge($currentUser, $data, ['id' => $userId]);
            $this->profile($errors, $userForView);
            return;
        }

        if ($isAdminUser) {
            $data['age'] = null;
            $data['poids'] = null;
            $data['taille'] = null;
            $data['objectif'] = null;
            $data['sexe'] = null;
        } else {
            $data['age'] = $data['age'] === '' ? null : (int) $data['age'];
            $data['sexe'] = $data['sexe'] === '' ? null : $data['sexe'];
            $data['poids'] = $data['poids'] === '' ? null : round((float) $data['poids'], 2);
            $data['taille'] = $data['taille'] === '' ? null : round((float) $data['taille'], 2);
            $data['objectif'] = $data['objectif'] === '' ? null : $data['objectif'];
        }

        $updated = $this->userModel->updateById($userId, $data);

        if ($updated) {
            $_SESSION['user_name'] = trim($data['prenom'] . ' ' . $data['nom']);
            $this->setFlash('success', 'Vos donnees ont ete mises a jour avec succes.');
        } else {
            $this->setFlash('error', 'Impossible de mettre a jour vos donnees.');
        }

        $this->redirect($isAdminUser ? 'admin-dashboard' : 'profile');
    }

    public function saveFaceDescriptor()
    {
        if (!$this->isLoggedIn()) {
            $this->respondJson([
                'success' => false,
                'message' => 'Session invalide.',
            ], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respondJson([
                'success' => false,
                'message' => 'Methode non autorisee.',
            ], 405);
        }

        $payload = $this->getRequestPayload();
        $descriptor = $this->sanitizeFaceDescriptor($payload['descriptor'] ?? null);

        if ($descriptor === null) {
            $this->respondJson([
                'success' => false,
                'message' => 'Empreinte faciale invalide.',
            ], 422);
        }

        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            $this->respondJson([
                'success' => false,
                'message' => 'Session invalide.',
            ], 401);
        }

        $serializedDescriptor = json_encode(array_values($descriptor));
        if ($serializedDescriptor === false) {
            $this->respondJson([
                'success' => false,
                'message' => 'Impossible de serialiser l\'empreinte faciale.',
            ], 500);
        }

        $saved = $this->userModel->updateFaceDescriptorById($userId, $serializedDescriptor);

        if (!$saved) {
            $this->respondJson([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement de l\'empreinte.',
            ], 500);
        }

        $this->respondJson([
            'success' => true,
            'message' => 'Empreinte faciale enregistree avec succes.',
        ]);
    }

    public function clearFaceDescriptor()
    {
        if (!$this->isLoggedIn()) {
            $this->respondJson([
                'success' => false,
                'message' => 'Session invalide.',
            ], 401);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respondJson([
                'success' => false,
                'message' => 'Methode non autorisee.',
            ], 405);
        }

        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            $this->respondJson([
                'success' => false,
                'message' => 'Session invalide.',
            ], 401);
        }

        $cleared = $this->userModel->clearFaceDescriptorById($userId);
        if (!$cleared) {
            $this->respondJson([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'empreinte.',
            ], 500);
        }

        $this->respondJson([
            'success' => true,
            'message' => 'Empreinte faciale supprimee.',
        ]);
    }

    public function usersList()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('login');
        }

        if (!$this->isAdmin()) {
            $this->redirect('home');
        }

        $users = $this->userModel->getAllWithRole();
        $usersCount = count($users);
        $flashSuccess = $_SESSION['flash_success'] ?? '';
        $flashError = $_SESSION['flash_error'] ?? '';
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $pageTitle = 'Utilisateurs';
        $isAdminTemplate = true;
        include __DIR__ . '/../view/layouts/header.php';
        include __DIR__ . '/../view/back/users/list.php';
        include __DIR__ . '/../view/layouts/footer.php';
    }

    public function usersReport()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('login');
        }

        if (!$this->isAdmin()) {
            $this->redirect('home');
        }

        $searchTerm = trim((string) ($_GET['search'] ?? ''));
        $users = $this->filterUsersBySearch($this->userModel->getAllWithRole(), $searchTerm);
        $summary = $this->buildUsersReportSummary($users);
        $generatedAt = new DateTimeImmutable('now');

        $pageTitle = 'Rapport utilisateurs PDF';
        $showNav = false;
        $isAdminTemplate = false;
        $bodyClass = 'report-page';

        include __DIR__ . '/../view/layouts/header.php';
        include __DIR__ . '/../view/back/users/report.php';
        include __DIR__ . '/../view/layouts/footer.php';
    }

    public function createUser($errors = [], $user = null)
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('login');
        }

        if (!$this->isAdmin()) {
            $this->redirect('home');
        }

        if (!is_array($user)) {
            $user = [];
        }

        $pageTitle = 'Ajouter un utilisateur';
        $isAdminTemplate = true;
        include __DIR__ . '/../view/layouts/header.php';
        include __DIR__ . '/../view/back/users/create.php';
        include __DIR__ . '/../view/layouts/footer.php';
    }

    public function storeUser()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('login');
        }

        if (!$this->isAdmin()) {
            $this->redirect('home');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('create-user');
        }

        $data = [
            'nom' => trim($_POST['nom'] ?? ''),
            'prenom' => trim($_POST['prenom'] ?? ''),
            'date_naissance' => trim($_POST['date_naissance'] ?? ''),
            'sexe' => trim((string) ($_POST['sexe'] ?? '')),
            'age' => '',
            'poids' => trim((string) ($_POST['poids'] ?? '')),
            'taille' => trim((string) ($_POST['taille'] ?? '')),
            'objectif' => trim($_POST['objectif'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => (string) ($_POST['password'] ?? ''),
        ];

        $calculatedAge = $this->calculateAgeFromDateNaissance($data['date_naissance']);
        $data['age'] = $calculatedAge === null ? '' : (string) $calculatedAge;

        $errors = $this->validateUtilisateurData($data, null, false);

        if (filter_var($data['email'], FILTER_VALIDATE_EMAIL) && $this->userModel->emailExists($data['email'])) {
            $errors[] = 'Cet e-mail est deja utilise par un autre utilisateur.';
        }

        if (strlen($data['password']) < 6) {
            $errors[] = 'Le mot de passe doit contenir au moins 6 caracteres.';
        }

        if (!empty($errors)) {
            $userForView = $data;
            unset($userForView['password']);
            $this->createUser($errors, $userForView);
            return;
        }

        $payload = [
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'date_naissance' => $data['date_naissance'] === '' ? null : $data['date_naissance'],
            'sexe' => $data['sexe'] === '' ? null : $data['sexe'],
            'age' => $data['age'] === '' ? null : (int) $data['age'],
            'poids' => $data['poids'] === '' ? null : round((float) $data['poids'], 2),
            'taille' => $data['taille'] === '' ? null : round((float) $data['taille'], 2),
            'objectif' => $data['objectif'] === '' ? null : $data['objectif'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => 'user',
        ];

        $created = $this->userModel->create($payload);
        if ($created) {
            $this->setFlash('success', 'Utilisateur ajoute avec succes.');
            $this->redirect('users-list');
        }

        $errors[] = 'Impossible de creer cet utilisateur pour le moment.';
        $userForView = $data;
        unset($userForView['password']);
        $this->createUser($errors, $userForView);
    }

    public function editUser($errors = [], $user = null)
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('login');
        }

        if (!$this->isAdmin()) {
            $this->redirect('home');
        }

        if ($user === null) {
            $userId = (int) ($_GET['id'] ?? 0);
            if ($userId <= 0) {
                $this->redirect('users-list');
            }

            $user = $this->userModel->findById($userId);
            if (!$user) {
                $this->setFlash('error', 'Utilisateur introuvable.');
                $this->redirect('users-list');
            }

            if ((($user['role'] ?? 'user') === 'admin')) {
                $this->setFlash('error', 'Les donnees admin ne sont pas affichables dans le backoffice.');
                $this->redirect('users-list');
            }
        }

        $pageTitle = 'Modifier un utilisateur';
        $showNav = true;
    $isAdminTemplate = true;
        include __DIR__ . '/../view/layouts/header.php';
        include __DIR__ . '/../view/back/users/edit.php';
        include __DIR__ . '/../view/layouts/footer.php';
    }

    public function updateUser()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('login');
        }

        if (!$this->isAdmin()) {
            $this->redirect('home');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('users-list');
        }

        $userId = (int) ($_POST['id'] ?? 0);
        if ($userId <= 0) {
            $this->setFlash('error', 'Identifiant utilisateur invalide.');
            $this->redirect('users-list');
        }

        $currentUser = $this->userModel->findById($userId);
        if (!$currentUser) {
            $this->setFlash('error', 'Utilisateur introuvable.');
            $this->redirect('users-list');
        }

        if ((($currentUser['role'] ?? 'user') === 'admin')) {
            $this->setFlash('error', 'Les donnees admin ne sont pas modifiables depuis le backoffice.');
            $this->redirect('users-list');
        }

        $data = [
            'nom' => trim($_POST['nom'] ?? ''),
            'prenom' => trim($_POST['prenom'] ?? ''),
            'date_naissance' => trim($_POST['date_naissance'] ?? ''),
            'sexe' => trim((string) ($_POST['sexe'] ?? '')),
            'age' => trim((string) ($_POST['age'] ?? '')),
            'poids' => trim((string) ($_POST['poids'] ?? '')),
            'taille' => trim((string) ($_POST['taille'] ?? '')),
            'objectif' => trim($_POST['objectif'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
        ];

        $calculatedAge = $this->calculateAgeFromDateNaissance($data['date_naissance']);
        $data['age'] = $calculatedAge === null ? '' : (string) $calculatedAge;

        $errors = $this->validateUtilisateurData($data, $userId, false);

        if (!empty($errors)) {
            $userForView = array_merge($currentUser, $data, ['id' => $userId]);
            $this->editUser($errors, $userForView);
            return;
        }

        $data['age'] = $data['age'] === '' ? null : (int) $data['age'];
        $data['sexe'] = $data['sexe'] === '' ? null : $data['sexe'];
        $data['poids'] = $data['poids'] === '' ? null : round((float) $data['poids'], 2);
        $data['taille'] = $data['taille'] === '' ? null : round((float) $data['taille'], 2);
        $data['objectif'] = $data['objectif'] === '' ? null : $data['objectif'];

        $updated = $this->userModel->updateById($userId, $data);

        if ($updated) {
            $this->setFlash('success', 'Utilisateur mis a jour avec succes.');
        } else {
            $this->setFlash('error', 'Impossible de mettre a jour cet utilisateur.');
        }

        $this->redirect('users-list');
    }

    public function deleteUser()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('login');
        }

        if (!$this->isAdmin()) {
            $this->redirect('home');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('users-list');
        }

        $userId = (int) ($_POST['id'] ?? 0);
        if ($userId <= 0) {
            $this->setFlash('error', 'Identifiant utilisateur invalide.');
            $this->redirect('users-list');
        }

        if ((int) ($_SESSION['user_id'] ?? 0) === $userId) {
            $this->setFlash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            $this->redirect('users-list');
        }

        $targetUser = $this->userModel->findById($userId);
        if (!$targetUser) {
            $this->setFlash('error', 'Utilisateur introuvable.');
            $this->redirect('users-list');
        }

        if ((($targetUser['role'] ?? 'user') === 'admin')) {
            $this->setFlash('error', 'Les comptes admin ne peuvent pas etre geres depuis le backoffice.');
            $this->redirect('users-list');
        }

        $deleted = $this->userModel->deleteById($userId);

        if ($deleted) {
            $this->setFlash('success', 'Utilisateur supprime avec succes.');
        } else {
            $this->setFlash('error', 'Impossible de supprimer cet utilisateur.');
        }

        $this->redirect('users-list');
    }

    public function adminDashboard()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('login');
        }

        if (!$this->isAdmin()) {
            $this->redirect('home');
        }

        $pdo = Database::getConnection();

        $statsStmt = $pdo->query(
            'SELECT
                COUNT(*) AS total_users,
                SUM(
                    CASE
                        WHEN date_naissance IS NOT NULL
                         AND NULLIF(TRIM(sexe), "") IS NOT NULL
                         AND age IS NOT NULL
                         AND poids IS NOT NULL
                         AND NULLIF(TRIM(objectif), "") IS NOT NULL
                        THEN 1 ELSE 0
                    END
                ) AS completed_profiles,
                SUM(
                    CASE
                        WHEN (
                            date_naissance IS NOT NULL
                            OR NULLIF(TRIM(sexe), "") IS NOT NULL
                            OR age IS NOT NULL
                            OR poids IS NOT NULL
                            OR taille IS NOT NULL
                            OR NULLIF(TRIM(objectif), "") IS NOT NULL
                        )
                        AND NOT (
                            date_naissance IS NOT NULL
                            AND NULLIF(TRIM(sexe), "") IS NOT NULL
                            AND age IS NOT NULL
                            AND poids IS NOT NULL
                            AND NULLIF(TRIM(objectif), "") IS NOT NULL
                        )
                        THEN 1 ELSE 0
                    END
                ) AS partial_profiles,
                SUM(
                    CASE WHEN LOWER(TRIM(sexe)) = "homme" THEN 1 ELSE 0 END
                ) AS male_users,
                SUM(
                    CASE WHEN LOWER(TRIM(sexe)) = "femme" THEN 1 ELSE 0 END
                ) AS female_users
             FROM users
             WHERE COALESCE(NULLIF(`role`, ""), "user") <> "admin"'
        );
        $statsRow = $statsStmt->fetch() ?: [];

        $totalUsers = (int) ($statsRow['total_users'] ?? 0);
        $completedProfiles = (int) ($statsRow['completed_profiles'] ?? 0);
        $partialProfiles = (int) ($statsRow['partial_profiles'] ?? 0);
        $emptyProfiles = max(0, $totalUsers - $completedProfiles - $partialProfiles);
        $incompleteProfiles = max(0, $totalUsers - $completedProfiles);
        $completionRate = $totalUsers > 0 ? (int) round(($completedProfiles * 100) / $totalUsers) : 0;

        $maleUsers = (int) ($statsRow['male_users'] ?? 0);
        $femaleUsers = (int) ($statsRow['female_users'] ?? 0);

        $pieSegments = [
            [
                'label' => 'Hommes',
                'count' => $maleUsers,
                'dotClass' => 'legend-blue',
            ],
            [
                'label' => 'Femmes',
                'count' => $femaleUsers,
                'dotClass' => 'legend-orange',
            ],
        ];

        if ($totalUsers > 0) {
            $maleStop = round(($maleUsers * 100) / $totalUsers, 2);
            $femaleStop = 100.00;
            $pieGradient = sprintf(
                'conic-gradient(#3498db 0 %.2f%%, #ff5f45 %.2f%% 100%%)',
                $maleStop,
                $femaleStop
            );
        } else {
            $pieGradient = 'conic-gradient(#95a5a6 0 100%)';
        }

        $donutSegments = [
            [
                'label' => 'Profils completes',
                'count' => $completedProfiles,
                'dotClass' => 'legend-green',
            ],
            [
                'label' => 'Profils partiels',
                'count' => $partialProfiles,
                'dotClass' => 'legend-blue',
            ],
            [
                'label' => 'Sans profil',
                'count' => $emptyProfiles,
                'dotClass' => 'legend-orange',
            ],
        ];

        if ($totalUsers > 0) {
            $greenStop = round(($completedProfiles * 100) / $totalUsers, 2);
            $blueStop = round((($completedProfiles + $partialProfiles) * 100) / $totalUsers, 2);
            $donutGradient = sprintf(
                'conic-gradient(#2ecc71 0 %.2f%%, #3498db %.2f%% %.2f%%, #f39c12 %.2f%% 100%%)',
                $greenStop,
                $greenStop,
                $blueStop,
                $blueStop
            );
        } else {
            $donutGradient = 'conic-gradient(#95a5a6 0 100%)';
        }

        $recentStmt = $pdo->query(
            'SELECT id, nom, prenom, email
             FROM users
             WHERE COALESCE(NULLIF(`role`, ""), "user") <> "admin"
             ORDER BY id DESC
             LIMIT 5'
        );
        $recentUsers = $recentStmt->fetchAll();

        $kpiCards = [
            [
                'label' => 'Total utilisateurs',
                'value' => (string) $totalUsers,
                'icon' => 'fa-solid fa-users',
            ],
            [
                'label' => 'Taux profils completes',
                'value' => $completionRate . '%',
                'icon' => 'fa-solid fa-circle-check',
            ],
            [
                'label' => 'Profils a completer',
                'value' => (string) $incompleteProfiles,
                'icon' => 'fa-solid fa-user-pen',
            ],
        ];

        $pageTitle = 'Tableau de bord Admin';
        $isAdminTemplate = true;
        include __DIR__ . '/../view/layouts/header.php';
        include __DIR__ . '/../view/back/dashboard.php';
        include __DIR__ . '/../view/layouts/footer.php';
    }

    public function logout()
    {
        session_unset();
        session_destroy();

        header('Location: /smart_nutrition/index.php?action=login');
        exit;
    }
}
