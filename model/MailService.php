<?php

class MailService
{
    private $fromEmail;
    private $fromName;
    private $apiUrl;
    private $apiKey;

    public function __construct()
    {
        if (class_exists('config')) {
            config::loadEnv();
        }

        $this->fromEmail = getenv('MAIL_FROM_EMAIL') ?: 'no-reply@smartnutrition.local';
        $this->fromName = getenv('MAIL_FROM_NAME') ?: 'Smart Nutrition';
        $this->apiUrl = getenv('MAIL_API_URL') ?: '';
        $this->apiKey = getenv('MAIL_API_KEY') ?: '';
    }

    public function sendNotificationEmail($toEmail, $subject, $message, $linkUrl = null)
    {
        $toEmail = trim((string) $toEmail);
        if ($toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Aucun email destinataire valide n est configure'
            ];
        }

        $subject = trim((string) $subject);
        $message = trim((string) $message);
        $html = $this->buildNotificationHtml($subject, $message, $linkUrl);

        if ($this->apiUrl !== '' && $this->apiKey !== '') {
            return $this->sendViaApi($toEmail, $subject, $html);
        }

        return $this->sendViaPhpMail($toEmail, $subject, $html);
    }

    private function sendViaApi($toEmail, $subject, $html)
    {
        if (stripos($this->apiUrl, 'brevo.com') !== false || stripos($this->apiUrl, 'sendinblue.com') !== false) {
            return $this->sendViaBrevo($toEmail, $subject, $html);
        }

        $payload = json_encode([
            'from' => [
                'email' => $this->fromEmail,
                'name' => $this->fromName
            ],
            'to' => [
                [
                    'email' => $toEmail
                ]
            ],
            'subject' => $subject,
            'html' => $html
        ]);

        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 12,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ]
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $status < 200 || $status >= 300) {
            return [
                'success' => false,
                'message' => $error ?: 'L API mail a renvoye HTTP ' . $status
            ];
        }

        return [
            'success' => true,
            'message' => 'Email envoye via l API mail'
        ];
    }

    private function sendViaBrevo($toEmail, $subject, $html)
    {
        $payload = json_encode([
            'sender' => [
                'email' => $this->fromEmail,
                'name' => $this->fromName
            ],
            'to' => [
                [
                    'email' => $toEmail
                ]
            ],
            'subject' => $subject,
            'htmlContent' => $html
        ]);

        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 12,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'api-key: ' . $this->apiKey
            ]
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $status < 200 || $status >= 300) {
            $message = $error ?: 'L API Brevo a renvoye HTTP ' . $status;
            if ($response) {
                $message .= ': ' . substr($response, 0, 180);
            }

            return [
                'success' => false,
                'message' => $message
            ];
        }

        return [
            'success' => true,
            'message' => 'Email envoye via Brevo'
        ];
    }

    private function sendViaPhpMail($toEmail, $subject, $html)
    {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $this->fromName . ' <' . $this->fromEmail . '>'
        ];

        $sent = @mail($toEmail, $subject, $html, implode("\r\n", $headers));

        return [
            'success' => $sent,
            'message' => $sent ? 'Email envoye via PHP mail' : 'PHP mail a echoue ou n est pas configure'
        ];
    }

    private function buildNotificationHtml($subject, $message, $linkUrl)
    {
        $safeSubject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
        $safeMessage = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
        $safeLink = $linkUrl ? htmlspecialchars($linkUrl, ENT_QUOTES, 'UTF-8') : '';

        $button = $safeLink !== ''
            ? '<p><a href="' . $safeLink . '" style="display:inline-block;padding:10px 14px;background:#2ecc71;color:#ffffff;text-decoration:none;border-radius:8px;">Ouvrir la notification</a></p>'
            : '';

        return '<div style="font-family:Arial,sans-serif;line-height:1.6;color:#1f2a36;">'
            . '<h2>' . $safeSubject . '</h2>'
            . '<p>' . $safeMessage . '</p>'
            . $button
            . '<p style="color:#6b7280;font-size:13px;">Communaute Smart Nutrition</p>'
            . '</div>';
    }
}

?>
