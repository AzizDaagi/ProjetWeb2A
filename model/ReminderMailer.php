<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

class  ReminderMailer  {

    public function sendReminder($user)
{
    if (!class_exists(PHPMailer::class)) {
        error_log("ReminderMailer: PHPMailer indisponible, vendor/autoload.php introuvable ou incomplet.");
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        $config = $this->getMailConfig();

        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = $config['encryption'];
        $mail->Port = $config['port'];

        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($user['email'], $user['nom']);
        $baseUrl = getenv('APP_BASE_URL') ?: 'http://localhost/Web';
        $trackingUrl = $baseUrl . '/index.php?controller=suivi&action=index';

        $mail->isHTML(true);
        $mail->Subject = "Rappel nutrition";
        $logoUrl = 'https://i.postimg.cc/g2WrxLyR/2-removebg-preview.png'; // diagnostic test
        $logoImg = '<img src="' . $logoUrl . '" width="120" alt="Smart Nutrition" style="display:block;margin:auto;height:auto;">';

        
        $mail->Body = "
<table width='600' align='center' style='background:#0f172a; color:white; font-family:Arial; border-radius:10px; padding:20px;'>

  <tr>
                  <td align=\"center\" style=\"padding:20px;\">
                    " . $logoImg . "
                  </td>
                </tr>

  <tr>
    <td>
      <h2 style='color:#22c55e;'> Rappel nutrition</h2>
    </td>
  </tr>

  <tr>
    <td style='background:#1e293b; padding:15px; border-radius:10px;'>
      <p>Bonjour {$user['nom']} </p>
      <p>Vous n'avez pas encore enregistrÃƒÂ© votre consommation aujourd'hui.</p>
      <p>Prenez 1 minute pour complÃƒÂ©ter votre suivi Ã°Å¸â€˜â€¡</p>
    </td>
  </tr>

  <tr>
    <td style='text-align:center; padding-top:20px;'>
      <a href='" . htmlspecialchars($trackingUrl, ENT_QUOTES, 'UTF-8') . "'
         style='background:#22c55e; color:white; padding:10px 20px; text-decoration:none; border-radius:8px;'>
         Ajouter mon repas
      </a>
    </td>
  </tr>

</table>
";

        $mail->send();

        return true;

    } catch (Exception $e) {
        error_log("ReminderMailer error: " . ($mail->ErrorInfo ?: $e->getMessage()));
        return false;
    }
}
private function getMailConfig(): array {
    return [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'encryption' => 'tls',
        'username' => 'melikk.rb@gmail.com',
        'password' => 'ranlwibwlsxozbex',
        'from_email' => 'melikk.rb@gmail.com',
        'from_name' => 'Smart Nutrition'
    ];
}
}
