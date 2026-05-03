<?php

return [
    'app_url' => getenv('APP_URL') ?: 'http://localhost/smart_nutrition',
    'weather_default_lat' => (float) (getenv('WEATHER_DEFAULT_LAT') ?: 36.8065),
    'weather_default_lon' => (float) (getenv('WEATHER_DEFAULT_LON') ?: 10.1815),
    'weather_api_timeout' => (int) (getenv('WEATHER_API_TIMEOUT') ?: 10),
    // Brevo / SMTP settings are intentionally not stored in the repository.
    // Use local environment variables instead:
    // BREVO_API_KEY, BREVO_SMTP_HOST, BREVO_SMTP_PORT, BREVO_SMTP_USERNAME,
    // BREVO_SMTP_PASSWORD, BREVO_SMTP_SECURE, BREVO_FROM_EMAIL, BREVO_FROM_NAME
    // OpenWeather:
    // OPENWEATHER_API_KEY, WEATHER_DEFAULT_LAT, WEATHER_DEFAULT_LON, WEATHER_API_TIMEOUT
];
