<?php

class ProfanityFilter {
    private static $apiKey = 'sXexCYbiAwQJraoAxzMjzPHmKzbE1Tgaxrs4uM9q';

    /**
     * Checks if a single string contains profanity.
     *
     * @param string $text
     * @return bool True if bad words found, false otherwise
     */
    public static function check($text) {
        if (empty(trim($text))) {
            return false;
        }

        $url = 'https://api.api-ninjas.com/v1/profanityfilter?text=' . urlencode($text);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Api-Key: ' . self::$apiKey
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            if (isset($data['has_profanity']) && $data['has_profanity'] === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks an array of strings for profanity.
     *
     * @param array $texts
     * @return bool True if any string contains bad words
     */
    public static function checkArray(array $texts) {
        foreach ($texts as $text) {
            if (self::check($text)) {
                return true;
            }
        }
        return false;
    }
}
?>
