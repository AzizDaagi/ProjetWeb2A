<?php

return [
    'app_url' => getenv('APP_URL') ?: 'http://localhost/smart_nutrition',
    // Brevo / SMTP settings are intentionally not stored in the repository.
    // Use local environment variables instead:
    // BREVO_API_KEY, BREVO_FROM_EMAIL, BREVO_FROM_NAME
];
