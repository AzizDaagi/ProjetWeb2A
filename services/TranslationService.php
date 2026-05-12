<?php

class TranslationService
{
    private $apiUrl = 'https://api.mymemory.translated.net/get';
    private $connectTimeoutSeconds = 5;
    private $timeoutSeconds = 8;

    public function translateFrToEn(string $text): array
    {
        $text = trim($text);

        if ($text === '') {
            return [
                'translated_text' => $text,
                'source' => 'fallback_original',
            ];
        }

        if (!function_exists('curl_init')) {
            return [
                'translated_text' => $text,
                'source' => 'fallback_original',
            ];
        }

        $url = $this->apiUrl . '?q=' . urlencode($text) . '&langpair=fr|en';
        $curl = curl_init($url);

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeoutSeconds,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
            ],
        ]);

        $responseBody = curl_exec($curl);

        if ($responseBody === false) {
            curl_close($curl);
            return [
                'translated_text' => $text,
                'source' => 'fallback_original',
            ];
        }

        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode < 200 || $httpCode >= 300) {
            return [
                'translated_text' => $text,
                'source' => 'fallback_original',
            ];
        }

        $decoded = json_decode($responseBody, true);
        $translatedText = trim((string) (($decoded['responseData']['translatedText'] ?? '') ?: ''));

        if ($translatedText === '') {
            return [
                'translated_text' => $text,
                'source' => 'fallback_original',
            ];
        }

        return [
            'translated_text' => $translatedText,
            'source' => 'mymemory',
        ];
    }
}
