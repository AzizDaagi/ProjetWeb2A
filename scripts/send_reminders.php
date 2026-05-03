<?php

require_once __DIR__ . '/../models/ReminderMailer.php';

// 🔥 LOG pour vérifier exécution
file_put_contents(__DIR__ . "/test_log.txt", date("Y-m-d H:i:s") . " RUN\n", FILE_APPEND);

$mailer = new ReminderMailer();

// 🔥 USER FIXE (pas de DB)
$user = [
    'id' => 1,
    'email' => 'melikkrbb@gmail.com',
    'nom' => 'Yassine'
];

// 🔥 envoi
$sent = $mailer->sendReminder($user);

if ($sent) {
    echo "MAIL SENT\n";
} else {
    echo "MAIL FAILED\n";
}