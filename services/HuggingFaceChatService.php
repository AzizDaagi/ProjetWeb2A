<?php

class HuggingFaceChatService
{
    private const API_URL = 'https://router.huggingface.co/hf-inference/models/TinyLlama/TinyLlama-1.1B-Chat-v1.0';
    private const CONNECT_TIMEOUT = 4;
    private const TIMEOUT = 10;

    /**
     * @return array{reply: string|null, debug: array}
     */
    public function generateReply(string $message): array
    {
        $message = trim($message);
        $debug   = ['model_url' => self::API_URL];

        if ($message === '') {
            $debug['skip_reason'] = 'empty_message';
            return ['reply' => null, 'debug' => $debug];
        }

        $this->loadEnv();
        $token = trim((string) ($_ENV['HF_API_TOKEN'] ?? getenv('HF_API_TOKEN') ?? ''));
        $debug['token_present'] = ($token !== '' && $token !== 'YOUR_HF_API_TOKEN_HERE' && $token !== 'YOUR_HUGGINGFACE_TOKEN');

        if (!$debug['token_present'] || !function_exists('curl_init')) {
            $debug['skip_reason'] = !$debug['token_present'] ? 'token_missing_or_placeholder' : 'curl_unavailable';
            return ['reply' => null, 'debug' => $debug];
        }

        $payload = json_encode([
            'inputs' => $this->buildPrompt($message),
            'parameters' => [
                'max_new_tokens' => 180,
                'temperature' => 0.4,
            ],
            'options' => [
                'wait_for_model' => false,
                'use_cache' => true,
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($payload === false) {
            $debug['skip_reason'] = 'json_encode_failed';
            return ['reply' => null, 'debug' => $debug];
        }

        $curl = curl_init(self::API_URL);

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
            CURLOPT_TIMEOUT => self::TIMEOUT,
        ]);

        $rawResponse = curl_exec($curl);
        $httpCode    = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError   = curl_error($curl);
        curl_close($curl);

        $debug['http_code']         = $httpCode;
        $debug['curl_error']        = $curlError !== '' ? $curlError : null;
        $debug['response_preview']  = is_string($rawResponse)
            ? mb_substr($rawResponse, 0, 200, 'UTF-8')
            : null;

        if ($rawResponse === false || $curlError !== '' || $httpCode < 200 || $httpCode >= 300) {
            $debug['skip_reason'] = 'http_or_curl_error';
            return ['reply' => null, 'debug' => $debug];
        }

        $decoded = json_decode($rawResponse, true);

        if (!is_array($decoded) || !empty($decoded['error'])) {
            $debug['skip_reason'] = 'invalid_json_or_api_error';
            return ['reply' => null, 'debug' => $debug];
        }

        if (!empty($decoded[0]['generated_text'])) {
            return ['reply' => $this->sanitizeReply($decoded[0]['generated_text']), 'debug' => $debug];
        }

        if (!empty($decoded['generated_text'])) {
            return ['reply' => $this->sanitizeReply($decoded['generated_text']), 'debug' => $debug];
        }

        $debug['skip_reason']       = 'generated_text_not_found_in_response';
        return ['reply' => null, 'debug' => $debug];
    }

    private function buildPrompt(string $message): string
    {
        // flan-t5 is a text2text model — direct question format works best
        return 'Answer in French as a nutrition assistant. '
            . 'Give 2 to 4 sentences maximum. '
            . 'Only answer about nutrition, hydration, food goals, or meal tracking. '
            . 'Question: ' . $message;
    }

    private function sanitizeReply(string $reply): ?string
    {
        $reply = trim(preg_replace('/\s+/u', ' ', $reply) ?? '');

        if ($reply === '') {
            return null;
        }

        return mb_substr($reply, 0, 500, 'UTF-8');
    }

    private function loadEnv(): void
    {
        $envPath = dirname(__DIR__) . '/env.php';

        if (is_file($envPath)) {
            require_once $envPath;
        }
    }
}
