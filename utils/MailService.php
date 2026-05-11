<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/phpmailer/Exception.php';
require_once __DIR__ . '/phpmailer/PHPMailer.php';
require_once __DIR__ . '/phpmailer/SMTP.php';
require_once __DIR__ . '/Env.php';

class MailService
{
    private const DEFAULT_SMTP_HOST = 'smtp-relay.brevo.com';
    private const DEFAULT_SMTP_PORT = 587;
    private const DEFAULT_FROM_NAME = 'Smart Nutrition';

    private static $lastResult = [
        'success' => false,
        'transport' => null,
        'message_id' => null,
        'event' => null,
        'error' => null,
    ];

    private static function getBaseUrl()
    {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/';
        $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
        return 'http://' . $host . $basePath;
    }

    private static function formatGoal($goal)
    {
        $map = [
            'lose weight' => 'Perdre du poids',
            'gain muscle' => 'Prendre de la masse musculaire',
            'maintain weight' => 'Maintenir le poids',
        ];

        return $map[$goal] ?? 'Objectif personnalise';
    }

    public static function getLastResult()
    {
        return self::$lastResult;
    }

    public static function sendThankYouEmail($toEmail, $userName, $goal = '', $weight = null, $height = null)
    {
        Env::load(dirname(__DIR__) . '/.env');

        $brevoApiKey = Env::get('BREVO_API_KEY', '');
        $smtpHost = Env::get('BREVO_SMTP_HOST', self::DEFAULT_SMTP_HOST);
        $smtpPort = (int) Env::get('BREVO_SMTP_PORT', (string) self::DEFAULT_SMTP_PORT);
        $smtpUser = Env::get('BREVO_SMTP_USER', '');
        $smtpPass = Env::get('BREVO_SMTP_PASS', '');
        $fromEmail = Env::get('BREVO_FROM_EMAIL', $smtpUser);
        $fromName = Env::get('BREVO_FROM_NAME', self::DEFAULT_FROM_NAME);

        $subject = 'Merci pour votre confiance - Votre bilan Smart Nutrition est en cours';
        $htmlBody = self::getHtmlTemplate($userName, $goal, $weight, $height);
        $textBody = self::getTextTemplate($userName, $goal, $weight, $height);

        self::$lastResult = [
            'success' => false,
            'transport' => null,
            'message_id' => null,
            'event' => null,
            'error' => null,
        ];

        if ($brevoApiKey !== '') {
            $senderCheck = self::checkBrevoSenderValidation($brevoApiKey, $fromEmail);
            if ($senderCheck['checked'] === true && $senderCheck['valid'] === false) {
                self::$lastResult = [
                    'success' => false,
                    'transport' => 'brevo_api',
                    'message_id' => null,
                    'event' => 'error',
                    'error' => $senderCheck['reason'],
                ];
                error_log('Mail Error: ' . $senderCheck['reason']);
                return false;
            }
        }

        // First choice: Brevo API (gives back a messageId that we can show to the user).
        if ($brevoApiKey !== '') {
            $apiResult = self::sendViaBrevoApi(
                $brevoApiKey,
                $fromEmail,
                $fromName,
                $toEmail,
                $userName,
                $subject,
                $htmlBody,
                $textBody
            );

            self::$lastResult = $apiResult;
            if ($apiResult['success'] === true) {
                return true;
            }
        }

        // Fallback: SMTP
        if ($smtpUser === '' || $smtpPass === '' || $fromEmail === '') {
            self::$lastResult['error'] = self::$lastResult['error'] ?? 'Missing Brevo SMTP configuration in .env';
            error_log('Mail Error: ' . self::$lastResult['error']);
            return false;
        }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $smtpUser;
            $mail->Password = $smtpPass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $smtpPort;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($toEmail, $userName);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = $textBody;

            $mail->send();

            self::$lastResult = [
                'success' => true,
                'transport' => 'smtp',
                'message_id' => null,
                'event' => 'accepted',
                'error' => null,
            ];
            return true;
        } catch (Exception $e) {
            self::$lastResult = [
                'success' => false,
                'transport' => 'smtp',
                'message_id' => null,
                'event' => 'error',
                'error' => $mail->ErrorInfo ?: $e->getMessage(),
            ];
            error_log('Mail Error: ' . self::$lastResult['error']);
            return false;
        }
    }

    private static function checkBrevoSenderValidation($apiKey, $fromEmail)
    {
        if ($fromEmail === '') {
            return ['checked' => true, 'valid' => false, 'reason' => 'BREVO_FROM_EMAIL is empty in .env'];
        }

        $url = 'https://api.brevo.com/v3/senders';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'accept: application/json',
            'api-key: ' . $apiKey,
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            return ['checked' => false, 'valid' => true, 'reason' => ''];
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded) || !isset($decoded['senders']) || !is_array($decoded['senders'])) {
            return ['checked' => false, 'valid' => true, 'reason' => ''];
        }

        foreach ($decoded['senders'] as $sender) {
            if (!is_array($sender) || !isset($sender['email'])) {
                continue;
            }

            if (strcasecmp((string) $sender['email'], (string) $fromEmail) === 0) {
                $isActive = (bool) ($sender['active'] ?? false);
                if ($isActive) {
                    return ['checked' => true, 'valid' => true, 'reason' => ''];
                }
                return [
                    'checked' => true,
                    'valid' => false,
                    'reason' => 'Sender found in Brevo but not active. Validate sender email/domain in Brevo first.',
                ];
            }
        }

        return [
            'checked' => true,
            'valid' => false,
            'reason' => 'Sender email not found in Brevo account. Create/validate this sender in Brevo (Settings > Senders).',
        ];
    }

    private static function sendViaBrevoApi($apiKey, $fromEmail, $fromName, $toEmail, $toName, $subject, $htmlBody, $textBody)
    {
        $payload = [
            'sender' => [
                'email' => $fromEmail,
                'name' => $fromName,
            ],
            'to' => [
                [
                    'email' => $toEmail,
                    'name' => $toName,
                ],
            ],
            'subject' => $subject,
            'htmlContent' => $htmlBody,
            'textContent' => $textBody,
        ];

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'accept: application/json',
            'content-type: application/json',
            'api-key: ' . $apiKey,
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            return [
                'success' => false,
                'transport' => 'brevo_api',
                'message_id' => null,
                'error' => $curlError !== '' ? $curlError : 'Brevo API request failed',
            ];
        }

        $decoded = json_decode($response, true);
        if ($httpCode === 201 && is_array($decoded) && isset($decoded['messageId'])) {
            $messageId = (string) $decoded['messageId'];
            $eventCheck = self::checkBrevoEventByMessageId($apiKey, $messageId);

            if ($eventCheck['event'] === 'error') {
                return [
                    'success' => false,
                    'transport' => 'brevo_api',
                    'message_id' => $messageId,
                    'event' => 'error',
                    'error' => $eventCheck['reason'] !== '' ? $eventCheck['reason'] : 'Brevo reported an error for this message',
                ];
            }

            return [
                'success' => true,
                'transport' => 'brevo_api',
                'message_id' => $messageId,
                'event' => $eventCheck['event'] !== '' ? $eventCheck['event'] : 'requests',
                'error' => null,
            ];
        }

        $errorMessage = 'Brevo API HTTP ' . $httpCode;
        if (is_array($decoded) && isset($decoded['message'])) {
            $errorMessage .= ': ' . $decoded['message'];
        }

        return [
            'success' => false,
            'transport' => 'brevo_api',
            'message_id' => null,
            'event' => 'error',
            'error' => $errorMessage,
        ];
    }

    private static function checkBrevoEventByMessageId($apiKey, $messageId)
    {
        if ($messageId === '') {
            return ['event' => '', 'reason' => ''];
        }

        $lastEvent = '';
        for ($attempt = 0; $attempt < 4; $attempt++) {
            $events = self::fetchBrevoEventsByMessageId($apiKey, $messageId);
            if ($events === null) {
                if ($attempt < 3) {
                    sleep(1);
                }
                continue;
            }

            foreach ($events as $evt) {
                if (!is_array($evt)) {
                    continue;
                }

                if (isset($evt['event']) && is_string($evt['event'])) {
                    $lastEvent = $evt['event'];
                }
                if (($evt['event'] ?? '') === 'error') {
                    $errorReason = isset($evt['reason']) ? (string) $evt['reason'] : '';
                    return ['event' => 'error', 'reason' => $errorReason];
                }
            }

            // `requests` means queued; wait a bit to see if an immediate rejection appears.
            if ($attempt < 3 && ($lastEvent === '' || $lastEvent === 'requests')) {
                sleep(1);
                continue;
            }

            break;
        }

        return ['event' => $lastEvent, 'reason' => ''];
    }

    private static function fetchBrevoEventsByMessageId($apiKey, $messageId)
    {
        $url = 'https://api.brevo.com/v3/smtp/statistics/events?limit=20&offset=0&messageId=' . rawurlencode($messageId);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'accept: application/json',
            'api-key: ' . $apiKey,
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            return null;
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded) || !isset($decoded['events']) || !is_array($decoded['events'])) {
            return null;
        }

        return $decoded['events'];
    }

    private static function getHtmlTemplate($userName, $goal, $weight, $height)
    {
        $baseUrl = self::getBaseUrl();
        $goalLabel = self::formatGoal((string) $goal);
        $safeName = htmlspecialchars((string) $userName);
        $safeGoal = htmlspecialchars($goalLabel);
        $safeWeight = is_numeric($weight) ? number_format((float) $weight, 1, '.', '') . ' kg' : 'Non specifie';
        $safeHeight = is_numeric($height) ? number_format((float) $height, 0, '.', '') . ' cm' : 'Non specifiee';

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f0f4f8; margin: 0; padding: 0; }
                .wrapper { width: 100%; padding: 40px 0; }
                .container { max-width: 620px; margin: 0 auto; background-color: #ffffff; border-radius: 14px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.08); }
                .header { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 36px; text-align: center; }
                .logo { font-size: 30px; font-weight: 800; color: #10b981; letter-spacing: -1px; }
                .logo span { color: #ffffff; }
                .content { padding: 40px 34px; color: #334155; line-height: 1.8; }
                .h1 { font-size: 24px; font-weight: 700; color: #0f172a; margin-bottom: 20px; }
                .highlight { color: #10b981; font-weight: 700; }
                .snapshot { margin: 24px 0; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; }
                .snapshot-title { margin: 0; padding: 12px 16px; background-color: #f8fafc; color: #0f172a; font-weight: 700; font-size: 14px; }
                .snapshot-grid { display: table; width: 100%; border-collapse: collapse; }
                .snapshot-row { display: table-row; }
                .snapshot-label, .snapshot-value { display: table-cell; padding: 10px 16px; border-top: 1px solid #e2e8f0; font-size: 14px; }
                .snapshot-label { width: 42%; color: #475569; }
                .snapshot-value { color: #0f172a; font-weight: 700; }
                .quote-card { background-color: #f8fafc; border-radius: 8px; padding: 22px; margin: 28px 0; border-left: 5px solid #10b981; }
                .quote-text { margin: 0; font-style: italic; color: #475569; font-size: 16px; }
                .btn-container { text-align: center; margin-top: 32px; }
                .btn { display: inline-block; padding: 14px 28px; background-color: #10b981; color: #ffffff !important; text-decoration: none; border-radius: 40px; font-weight: 700; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); }
                .footer { background-color: #f1f5f9; padding: 24px; text-align: center; font-size: 13px; color: #64748b; }
            </style>
        </head>
        <body>
            <div class='wrapper'>
                <div class='container'>
                    <div class='header'>
                        <div class='logo'>SMART <span>NUTRITION</span></div>
                    </div>
                    <div class='content'>
                        <div class='h1'>Bonjour <span class='highlight'>" . $safeName . "</span>,</div>
                        <p>Merci pour votre confiance. Votre demande de programme nutritionnel a bien ete recue et notre equipe est deja en train de preparer vos recommandations.</p>
                        <p>Vous venez de poser une base solide. Chaque petit choix intelligent repete dans le temps fait une grande difference.</p>

                        <div class='snapshot'>
                            <p class='snapshot-title'>Resume de votre bilan</p>
                            <div class='snapshot-grid'>
                                <div class='snapshot-row'>
                                    <div class='snapshot-label'>Objectif</div>
                                    <div class='snapshot-value'>" . $safeGoal . "</div>
                                </div>
                                <div class='snapshot-row'>
                                    <div class='snapshot-label'>Poids actuel</div>
                                    <div class='snapshot-value'>" . $safeWeight . "</div>
                                </div>
                                <div class='snapshot-row'>
                                    <div class='snapshot-label'>Taille</div>
                                    <div class='snapshot-value'>" . $safeHeight . "</div>
                                </div>
                            </div>
                        </div>

                        <div class='quote-card'>
                            <p class='quote-text'>\"La discipline n'est pas une punition, c'est une promesse que l'on se fait a soi-meme.\"</p>
                        </div>

                        <p>Retrouvez votre suivi et vos demandes directement dans votre espace personnel.</p>

                        <div class='btn-container'>
                            <a href='" . $baseUrl . "/index.php?action=my_nutrition_requests' class='btn'>Voir mes demandes nutritionnelles</a>
                        </div>
                    </div>
                    <div class='footer'>
                        &copy; " . date('Y') . " Smart Nutrition<br>
                        Votre sante, notre priorite.<br>
                        <span style='font-size: 11px; margin-top: 8px; display: block;'>Email automatique envoye apres votre soumission de bilan nutritionnel.</span>
                    </div>
                </div>
            </div>
        </body>
        </html>";
    }

    private static function getTextTemplate($userName, $goal, $weight, $height)
    {
        $goalLabel = self::formatGoal((string) $goal);
        $weightText = is_numeric($weight) ? number_format((float) $weight, 1, '.', '') . ' kg' : 'Non specifie';
        $heightText = is_numeric($height) ? number_format((float) $height, 0, '.', '') . ' cm' : 'Non specifiee';
        $baseUrl = self::getBaseUrl();

        return "Bonjour " . $userName . ",\n\n"
            . "Merci pour votre confiance. Votre demande de programme nutritionnel a bien ete recue.\n\n"
            . "Resume de votre bilan:\n"
            . "- Objectif: " . $goalLabel . "\n"
            . "- Poids actuel: " . $weightText . "\n"
            . "- Taille: " . $heightText . "\n\n"
            . "Suivez vos demandes ici: " . $baseUrl . "/index.php?action=my_nutrition_requests\n\n"
            . "Smart Nutrition";
    }
}
