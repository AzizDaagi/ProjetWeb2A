<?php

class HuggingFaceClient
{
    private string $token;
    private int $timeoutSeconds;

    public function __construct(?string $token = null, int $timeoutSeconds = 20)
    {
        $this->loadEnvFile(__DIR__ . '/../.env');
        $this->token = trim((string) ($token ?? getenv('HF_TOKEN') ?: ''));
        $this->timeoutSeconds = $timeoutSeconds;
    }

    public function isConfigured(): bool
    {
        return $this->token !== '';
    }

    public function textClassification(string $model, string $text): array
    {
        return $this->requestModel($model, [
            'inputs' => $text,
        ]);
    }

    public function zeroShotClassification(string $model, string $text, array $candidateLabels): array
    {
        return $this->requestModel($model, [
            'inputs' => $text,
            'parameters' => [
                'candidate_labels' => array_values($candidateLabels),
            ],
        ]);
    }

    public function featureExtraction(string $model, string $text): array
    {
        return $this->requestModel($model, [
            'inputs' => $text,
        ]);
    }

    public function imageClassification(string $model, string $imagePath, string $mimeType): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('Hugging Face token is not configured.');
        }

        if (!is_file($imagePath) || !is_readable($imagePath)) {
            throw new RuntimeException('Image file is not readable.');
        }

        $imageBytes = file_get_contents($imagePath);
        if ($imageBytes === false) {
            throw new RuntimeException('Could not read image file.');
        }

        return $this->requestBinaryModel($model, $imageBytes, $mimeType);
    }

    private function requestModel(string $model, array $payload): array
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('Hugging Face token is not configured.');
        }

        if (!function_exists('curl_init')) {
            throw new RuntimeException('PHP cURL extension is required for Hugging Face requests.');
        }

        $encodedModel = implode('/', array_map('rawurlencode', explode('/', $model)));
        $url = 'https://router.huggingface.co/hf-inference/models/' . $encodedModel;
        $jsonPayload = json_encode($payload);

        if ($jsonPayload === false) {
            throw new RuntimeException('Could not encode Hugging Face request payload.');
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->token,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => $jsonPayload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
        ]);

        $responseBody = curl_exec($ch);
        $curlError = curl_error($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($responseBody === false) {
            throw new RuntimeException('Hugging Face request failed: ' . $curlError);
        }

        $decoded = json_decode($responseBody, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Hugging Face returned an invalid JSON response.');
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            $message = isset($decoded['error']) ? (string) $decoded['error'] : 'HTTP ' . $statusCode;
            throw new RuntimeException('Hugging Face request failed: ' . $message);
        }

        return $decoded;
    }

    private function requestBinaryModel(string $model, string $payload, string $contentType): array
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('PHP cURL extension is required for Hugging Face requests.');
        }

        $encodedModel = implode('/', array_map('rawurlencode', explode('/', $model)));
        $url = 'https://router.huggingface.co/hf-inference/models/' . $encodedModel;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->token,
                'Content-Type: ' . $contentType,
            ],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
        ]);

        $responseBody = curl_exec($ch);
        $curlError = curl_error($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($responseBody === false) {
            throw new RuntimeException('Hugging Face request failed: ' . $curlError);
        }

        $decoded = json_decode($responseBody, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Hugging Face returned an invalid JSON response.');
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            $message = isset($decoded['error']) ? (string) $decoded['error'] : 'HTTP ' . $statusCode;
            throw new RuntimeException('Hugging Face request failed: ' . $message);
        }

        return $decoded;
    }

    private function loadEnvFile(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");

            if ($key !== '' && getenv($key) === false) {
                putenv($key . '=' . $value);
                $_ENV[$key] = $value;
            }
        }
    }
}
