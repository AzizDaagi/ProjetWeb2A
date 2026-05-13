<?php

require_once dirname(__DIR__) . '/env.php';

if (!function_exists('app_env_value')) {
    function app_env_value(string $key, string $default = ''): string
    {
        $value = $_ENV[$key] ?? getenv($key);
        if ($value === false || $value === null) {
            return $default;
        }

        return trim((string) $value);
    }
}

return [
    'app_url' => app_env_value('APP_URL', 'http://localhost/projet-web-25-26'),
    'firebase_web_api_key' => app_env_value('FIREBASE_WEB_API_KEY'),
    'firebase_project_id' => app_env_value('FIREBASE_PROJECT_ID'),
    'firebase_auth_domain' => app_env_value('FIREBASE_AUTH_DOMAIN'),
    'firebase_storage_bucket' => app_env_value('FIREBASE_STORAGE_BUCKET'),
    'firebase_messaging_sender_id' => app_env_value('FIREBASE_MESSAGING_SENDER_ID'),
    'firebase_app_id' => app_env_value('FIREBASE_APP_ID'),
    'firebase_measurement_id' => app_env_value('FIREBASE_MEASUREMENT_ID'),
    'weather_default_lat' => (float) app_env_value('WEATHER_DEFAULT_LAT', '36.8065'),
    'weather_default_lon' => (float) app_env_value('WEATHER_DEFAULT_LON', '10.1815'),
    'weather_api_timeout' => (int) app_env_value('WEATHER_API_TIMEOUT', '10'),
    // Brevo / SMTP settings are intentionally not stored in the repository.
    // Use local environment variables instead:
    // BREVO_API_KEY, BREVO_SMTP_HOST, BREVO_SMTP_PORT, BREVO_SMTP_USERNAME,
    // BREVO_SMTP_PASSWORD, BREVO_SMTP_SECURE, BREVO_FROM_EMAIL, BREVO_FROM_NAME
    // Firebase:
    // FIREBASE_WEB_API_KEY, FIREBASE_PROJECT_ID
    // OpenWeather:
    // OPENWEATHER_API_KEY, WEATHER_DEFAULT_LAT, WEATHER_DEFAULT_LON, WEATHER_API_TIMEOUT
];
