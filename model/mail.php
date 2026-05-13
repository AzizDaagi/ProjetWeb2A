<?php

require_once dirname(__DIR__) . '/env.php';

$envValue = static function (string $key, string $default = ''): string {
    $value = $_ENV[$key] ?? getenv($key);
    if ($value === false || $value === null) {
        return $default;
    }

    return trim((string) $value);
};

return [
    'enabled' => true,
    'host' => $envValue('BREVO_SMTP_HOST', 'smtp-relay.brevo.com'),
    'port' => (int) $envValue('BREVO_SMTP_PORT', '587'),
    'username' => $envValue('BREVO_SMTP_USERNAME', $envValue('BREVO_FROM_EMAIL', '')),
    'password' => $envValue('BREVO_SMTP_PASSWORD'),
    'from_email' => $envValue('BREVO_FROM_EMAIL', 'no-reply@localhost'),
    'from_name' => $envValue('BREVO_FROM_NAME', 'Smart Nutrition'),
    'secure' => $envValue('BREVO_SMTP_SECURE', 'tls'),
    'timeout' => (int) $envValue('BREVO_SMTP_TIMEOUT', '30'),
];
