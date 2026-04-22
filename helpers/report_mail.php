<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/../vendor/autoload.php';

function sendWeeklyReport($stats, $to = null)
{
    $config = require __DIR__ . '/../config/mail.php';
    $to = $to ?: $config['to_email'];
    $smtpPassword = preg_replace('/\s+/', '', (string) ($config['password'] ?? ''));

    if (
        empty($config['username']) ||
        empty($smtpPassword) ||
        str_contains($config['username'], 'yourgmail@gmail.com') ||
        str_contains((string) ($config['password'] ?? ''), 'your-16-char-app-password') ||
        str_contains($to, 'yourgmail@gmail.com')
    ) {
        return [
            'success' => false,
            'error' => "Configure Gmail SMTP dans config/mail.php avant l'envoi."
        ];
    }

    if (
        str_contains((string) $config['host'], 'gmail.com') &&
        strlen($smtpPassword) < 16
    ) {
        return [
            'success' => false,
            'error' => "Gmail demande un mot de passe d'application de 16 caracteres, pas le mot de passe normal du compte."
        ];
    }

    $subject = "Rapport nutrition hebdomadaire";
    $htmlBody = "
    <h2>Resume de la semaine</h2>
    <p>Moyenne calories : {$stats['average']} kcal</p>
    <p>Objectif atteint : {$stats['success']} / 7 jours</p>
    <p>Aliment le plus consomme : {$stats['top_aliment']}</p>
    ";
    $textBody =
        "Resume de la semaine\n" .
        "Moyenne calories : {$stats['average']} kcal\n" .
        "Objectif atteint : {$stats['success']} / 7 jours\n" .
        "Aliment le plus consomme : {$stats['top_aliment']}";

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $smtpPassword;
        $mail->Port = (int) $config['port'];
        $mail->CharSet = 'UTF-8';

        if (($config['encryption'] ?? 'tls') === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }

        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $textBody;

        $mail->send();

        return [
            'success' => true,
            'error' => null
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $mail->ErrorInfo ?: $e->getMessage()
        ];
    }
}
