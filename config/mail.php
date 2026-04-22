<?php

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
