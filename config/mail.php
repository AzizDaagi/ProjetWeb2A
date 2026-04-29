<?php

return [
    'enabled' => true,
    'host' => getenv('BREVO_SMTP_HOST') ?: 'smtp-relay.brevo.com',
    'port' => (int) (getenv('BREVO_SMTP_PORT') ?: 587),
    'username' => getenv('BREVO_SMTP_USERNAME') ?: 'apikey',
    'password' => getenv('BREVO_SMTP_PASSWORD') ?: '',
    'from_email' => getenv('BREVO_FROM_EMAIL') ?: 'no-reply@localhost',
    'from_name' => getenv('BREVO_FROM_NAME') ?: 'Smart Nutrition',
    'secure' => getenv('BREVO_SMTP_SECURE') ?: 'tls',
    'timeout' => (int) (getenv('BREVO_SMTP_TIMEOUT') ?: 30),
];
