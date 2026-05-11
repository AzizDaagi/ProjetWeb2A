<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/phpmailer/Exception.php';
require_once __DIR__ . '/phpmailer/PHPMailer.php';
require_once __DIR__ . '/phpmailer/SMTP.php';

class MailService {
    // SMTP Credentials
    private const SMTP_HOST = 'smtp-relay.brevo.com';
    private const SMTP_PORT = 587;
    private const SMTP_USER = 'aa7ace001@smtp-brevo.com';
    private const SMTP_PASS = 'xsmtpsib-93083b5ba2bf5a1833f9a80d2c82fc84a293eb157ba5f2463ed61512c87fbc41-QZAuSuP5YUIoeJ9M';
    private const FROM_EMAIL = 'aa7ace001@smtp-brevo.com';
    private const FROM_NAME = 'Smart Nutrition';

    private static function getBaseUrl() {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/';
        $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
        return 'http://' . $host . $basePath;
    }

    public static function sendThankYouEmail($toEmail, $userName) {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = self::SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = self::SMTP_USER;
            $mail->Password   = self::SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = self::SMTP_PORT;
            $mail->CharSet    = 'UTF-8';

            // Recipients
            $mail->setFrom(self::FROM_EMAIL, self::FROM_NAME);
            $mail->addAddress($toEmail, $userName);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Merci pour votre bilan nutritionnel - Smart Nutrition';
            
            // Email Body (HTML)
            $mail->Body = self::getHtmlTemplate($userName);
            $mail->AltBody = "Bonjour " . $userName . ",\n\nMerci pour votre bilan nutritionnel. Vos recommandations sont prêtes sur votre tableau de bord.\n\nSmart Nutrition Team";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mail Error: " . $mail->ErrorInfo);
            return false;
        }
    }

    private static function getHtmlTemplate($userName) {
        $baseUrl = self::getBaseUrl();
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f0f4f8; margin: 0; padding: 0; }
                .wrapper { width: 100%; padding: 40px 0; }
                .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
                .header { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 40px; text-align: center; }
                .logo { font-size: 32px; font-weight: 800; color: #10b981; letter-spacing: -1px; }
                .logo span { color: #ffffff; }
                .content { padding: 50px 40px; color: #334155; line-height: 1.8; }
                .h1 { font-size: 24px; font-weight: 700; color: #0f172a; margin-bottom: 24px; }
                .highlight { color: #10b981; font-weight: 600; }
                .quote-card { background-color: #f8fafc; border-radius: 8px; padding: 25px; margin: 35px 0; border-left: 5px solid #10b981; }
                .quote-text { font-style: italic; color: #475569; font-size: 16px; margin: 0; }
                .btn-container { text-align: center; margin-top: 40px; }
                .btn { display: inline-block; padding: 16px 32px; background-color: #10b981; color: #ffffff !important; text-decoration: none; border-radius: 50px; font-weight: 700; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); }
                .footer { background-color: #f1f5f9; padding: 30px; text-align: center; font-size: 13px; color: #64748b; }
            </style>
        </head>
        <body>
            <div class='wrapper'>
                <div class='container'>
                    <div class='header'>
                        <div class='logo'>SMART <span>NUTRITION</span></div>
                    </div>
                    <div class='content'>
                        <div class='h1'>Bonjour <span class='highlight'>" . htmlspecialchars($userName) . "</span>,</div>
                        <p>Félicitations pour avoir franchi cette étape vers une vie plus saine ! Nous avons bien reçu votre bilan nutritionnel.</p>
                        <p>Nos experts et nos algorithmes ont analysé vos données pour vous proposer un programme sur mesure qui correspond à votre objectif.</p>
                        
                        <div class='quote-card'>
                            <p class='quote-text'>\"Prendre soin de son corps est le plus beau cadeau que l'on puisse se faire. Chaque petit changement aujourd'hui construit le vous de demain.\u201d</p>
                        </div>
                        
                        <p>Vous pouvez consulter vos recommandations détaillées et commencer votre transformation dès maintenant en accédant à votre espace personnel.</p>
                        
                        <div class='btn-container'>
                            <a href='" . $baseUrl . "/index.php?action=my_nutrition_requests' class='btn'>Accéder à mon Bilan</a>
                        </div>
                    </div>
                    <div class='footer'>
                        &copy; " . date('Y') . " Smart Nutrition Platform<br>
                        Votre santé, notre priorité.<br>
                        <span style='font-size: 11px; margin-top: 10px; display: block;'>Cet email a été envoyé automatiquement suite à votre demande sur notre site.</span>
                    </div>
                </div>
            </div>
        </body>
        </html>";
    }
}
