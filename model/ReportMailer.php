<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

class ReportMailer
{
    public function sendWeeklyReport(array $stats, ?string $to = null): array
    {
        if (!class_exists(PHPMailer::class)) {
            return [
                'success' => false,
                'error' => "PHPMailer n'est pas disponible. Verifiez vendor/autoload.php."
            ];
        }

        $config = $this->getMailConfig();
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
                'error' => "Configure Gmail SMTP dans le modele ReportMailer avant l'envoi."
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

        $average = (int) round((float) ($stats['average'] ?? 0));
        $success = (int) ($stats['success'] ?? 0);
        $topAliment = trim((string) ($stats['top_aliment'] ?? 'Aucun'));
        $topAlimentHtml = htmlspecialchars($topAliment, ENT_QUOTES, 'UTF-8');
        $appUrl = htmlspecialchars($this->getAppUrl(), ENT_QUOTES, 'UTF-8');
        // ---------------------------------------------------------------
        // LOGO: replace this URL with your real publicly hosted logo.
        // Upload logo.png to Imgur / your server and paste the direct link.
        // ---------------------------------------------------------------
        $logoUrl = 'https://i.postimg.cc/g2WrxLyR/2-removebg-preview.png'; // diagnostic test
        $logoImg = '<img src="' . $logoUrl . '" width="120" alt="Smart Nutrition" style="display:block;margin:auto;height:auto;">';

        // Define missing variables
        $subject = "📊 Rapport nutrition hebdomadaire";
        $htmlBody = "
        <table width=\"100%\" bgcolor=\"#0f172a\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:#0f172a;\">
          <tr>
            <td align=\"center\" style=\"padding:24px 12px;\">
              <table width=\"600\" cellpadding=\"0\" cellspacing=\"0\" style=\"width:600px; max-width:600px; font-family:Arial, sans-serif; color:#ffffff;\">
                <tr>
                  <td align=\"center\" style=\"padding:20px;\">
                    " . $logoImg . "
                  </td>
                </tr>
                <tr>
                  <td style=\"padding:20px 20px 14px 20px;\">
                    <h2 style=\"margin:0; color:#22c55e; font-size:26px; line-height:1.2;\">📊 Rapport nutrition hebdomadaire</h2>
                  </td>
                </tr>
                <tr>
                  <td style=\"padding:0 20px;\">
                    <table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:#1e293b; padding:20px; border-radius:10px;\">
                      <tr>
                        <td style=\"padding-bottom:10px; font-size:15px; line-height:1.5;\">
                           <b>Moyenne calorique :</b> {$average} kcal
                        </td>
                      </tr>
                      <tr>
                        <td style=\"padding-bottom:10px; font-size:15px; line-height:1.5;\">
                           <b>Objectif atteint :</b> {$success} / 7 jours
                        </td>
                      </tr>
                      <tr>
                        <td style=\"font-size:15px; line-height:1.5;\">
                           <b>Aliment principal :</b> {$topAlimentHtml}
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <tr>
                  <td style=\"padding:20px; font-size:15px; line-height:1.6;\">
                    💡 Continuez vos efforts ! Vous pouvez encore améliorer votre régularité cette semaine 💪
                  </td>
                </tr>
                <tr>
                  <td align=\"center\" style=\"padding:0 20px 24px 20px;\">
                    <a href=\"{$appUrl}\" style=\"display:inline-block; background:#22c55e; color:#ffffff; padding:12px 20px; border-radius:8px; text-decoration:none; font-weight:700;\">
                      Voir mon suivi
                    </a>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
        ";

        $textBody =
            "Resume de la semaine\n" .
            "Moyenne calories : {$average} kcal\n" .
            "Objectif atteint : {$success} / 7 jours\n" .
            "Aliment le plus consomme : {$topAliment}\n" .
            "Continuez vos efforts ! Vous pouvez encore ameliorer votre regularite cette semaine.\n" .
            "Voir mon suivi : " . $this->getAppUrl();

        // Prevent empty body error
        if (empty($htmlBody)) {
            throw new Exception("Email body is empty");
        }

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
    }

    private function getMailConfig(): array
    {
        $username = getenv('MAIL_USERNAME') ?: 'melikk.rb@gmail.com';
        $password = getenv('MAIL_PASSWORD') ?: 'ranl wibw lsxo zbex';

        return [
            'host' => getenv('MAIL_HOST') ?: 'smtp.gmail.com',
            'port' => (int) (getenv('MAIL_PORT') ?: 587),
            'encryption' => getenv('MAIL_ENCRYPTION') ?: 'tls',
            'username' => $username,
            'password' => $password,
            'from_email' => getenv('MAIL_FROM_EMAIL') ?: $username,
            'from_name' => getenv('MAIL_FROM_NAME') ?: 'Smart Nutrition',
            'to_email' => getenv('MAIL_TO_EMAIL') ?: 'melikkrbb@gmail.com',
        ];
    }

    private function getAppUrl(): string
    {
        $configuredUrl = trim((string) getenv('APP_URL'));

        if ($configuredUrl !== '') {
            return $configuredUrl;
        }

        $projectName = basename(dirname(__DIR__));

        return "http://localhost/{$projectName}/index.php?controller=objectif&action=index";
    }
}
