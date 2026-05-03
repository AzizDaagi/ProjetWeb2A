<?php

return [
    'app_url' => getenv('APP_URL') ?: 'http://localhost/smart_nutrition',
    // Brevo / SMTP settings are intentionally not stored in the repository.
    // Use local environment variables instead:
    // BREVO_API_KEY, BREVO_SMTP_HOST, BREVO_SMTP_PORT, BREVO_SMTP_USERNAME,
    // BREVO_SMTP_PASSWORD, BREVO_SMTP_SECURE, BREVO_FROM_EMAIL, BREVO_FROM_NAME
];
