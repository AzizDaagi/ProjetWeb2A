<?php

class InputValidator
{
    public static function cleanText($value): string
    {
        return trim(preg_replace('/\s+/', ' ', (string) $value));
    }

    public static function cleanMultiline($value): string
    {
        $value = str_replace(["\r\n", "\r"], "\n", (string) $value);
        $value = preg_replace("/[ \t]+/", ' ', $value);
        $value = preg_replace("/\n{3,}/", "\n\n", $value);
        return trim($value);
    }

    public static function validateId($value, string $fieldName = 'ID'): ?string
    {
        return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
            ? null
            : $fieldName . ' invalide.';
    }

    public static function validatePostTitle($value): ?string
    {
        $value = self::cleanText($value);
        $length = mb_strlen($value);
        $securityError = self::validateSafeText($value, 'Le titre');
        if ($securityError) {
            return $securityError;
        }

        if ($length < 3) {
            return 'Le titre doit contenir au moins 3 caracteres.';
        }
        if ($length > 120) {
            return 'Le titre ne doit pas depasser 120 caracteres.';
        }
        if (!preg_match('/[\p{L}\p{N}]/u', $value)) {
            return 'Le titre doit contenir au moins une lettre ou un chiffre.';
        }

        return null;
    }

    public static function validatePostContent($value): ?string
    {
        $value = self::cleanMultiline($value);
        $length = mb_strlen($value);
        $securityError = self::validateSafeText($value, 'Le contenu');
        if ($securityError) {
            return $securityError;
        }

        if ($length < 10) {
            return 'Le contenu doit contenir au moins 10 caracteres.';
        }
        if ($length > 3000) {
            return 'Le contenu ne doit pas depasser 3000 caracteres.';
        }
        if (!preg_match('/[\p{L}\p{N}]/u', $value)) {
            return 'Le contenu doit contenir au moins une lettre ou un chiffre.';
        }

        return null;
    }

    public static function validatePostCategory($value): ?string
    {
        $allowed = ['question', 'recipe', 'progress', 'advice', 'product_review'];
        return in_array((string) $value, $allowed, true)
            ? null
            : 'Categorie de publication invalide.';
    }

    public static function validateComment($value): ?string
    {
        $value = self::cleanMultiline($value);
        $length = mb_strlen($value);
        $securityError = self::validateSafeText($value, 'Le commentaire');
        if ($securityError) {
            return $securityError;
        }

        if ($length < 2) {
            return 'Le commentaire doit contenir au moins 2 caracteres.';
        }
        if ($length > 1000) {
            return 'Le commentaire ne doit pas depasser 1000 caracteres.';
        }
        if (!preg_match('/[\p{L}\p{N}]/u', $value)) {
            return 'Le commentaire doit contenir au moins une lettre ou un chiffre.';
        }

        return null;
    }

    public static function validateReportDetails($value): ?string
    {
        $value = self::cleanMultiline($value);
        $securityError = self::validateSafeText($value, 'Les details du signalement');
        if ($securityError) {
            return $securityError;
        }
        if ($value !== '' && mb_strlen($value) > 800) {
            return 'Les details du signalement ne doivent pas depasser 800 caracteres.';
        }
        return null;
    }

    public static function validateReviewNote($value): ?string
    {
        $value = self::cleanMultiline($value);
        $securityError = self::validateSafeText($value, 'La note de revision');
        if ($securityError) {
            return $securityError;
        }
        if ($value !== '' && mb_strlen($value) > 1000) {
            return 'La note de revision ne doit pas depasser 1000 caracteres.';
        }
        return null;
    }

    public static function validateNewsTitle($value): ?string
    {
        $value = self::cleanText($value);
        $length = mb_strlen($value);
        $securityError = self::validateSafeText($value, 'Le titre de l article');
        if ($securityError) {
            return $securityError;
        }
        if ($length < 5) {
            return 'Le titre de l article doit contenir au moins 5 caracteres.';
        }
        if ($length > 180) {
            return 'Le titre de l article ne doit pas depasser 180 caracteres.';
        }
        return null;
    }

    public static function validateNewsContent($value): ?string
    {
        $value = self::cleanMultiline($value);
        $length = mb_strlen($value);
        $securityError = self::validateSafeText($value, 'Le contenu de l article');
        if ($securityError) {
            return $securityError;
        }
        if ($length < 30) {
            return 'Le contenu de l article doit contenir au moins 30 caracteres.';
        }
        if ($length > 10000) {
            return 'Le contenu de l article ne doit pas depasser 10000 caracteres.';
        }
        return null;
    }

    public static function validateUrl($value, string $fieldName = 'URL'): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            return $fieldName . ' invalide.';
        }

        $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));
        if (!in_array($scheme, ['http', 'https'], true)) {
            return $fieldName . ' doit commencer par http ou https.';
        }

        return null;
    }

    public static function validateSafeText($value, string $fieldName = 'Le champ'): ?string
    {
        $value = (string) $value;
        if ($value === '') {
            return null;
        }

        if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value)) {
            return $fieldName . ' contient des caracteres non autorises.';
        }

        $patterns = [
            '/<\s*script\b/i',
            '/<\s*iframe\b/i',
            '/<\s*object\b/i',
            '/<\s*embed\b/i',
            '/<\s*link\b/i',
            '/<\s*meta\b/i',
            '/<\s*form\b/i',
            '/<\s*input\b/i',
            '/on\w+\s*=/i',
            '/javascript\s*:/i',
            '/data\s*:\s*text\/html/i',
            '/vbscript\s*:/i',
            '/expression\s*\(/i',
            '/(?:union\s+select|select\s+.+\s+from|insert\s+into|update\s+\w+\s+set|delete\s+from|drop\s+table|alter\s+table|truncate\s+table)/i',
            '/(?:--|#|\/\*|\*\/)\s*$/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return $fieldName . ' contient du contenu potentiellement dangereux.';
            }
        }

        return null;
    }

    public static function firstError(array $errors): ?string
    {
        foreach ($errors as $error) {
            if ($error !== null) {
                return $error;
            }
        }
        return null;
    }
}

?>
