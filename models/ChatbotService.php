<?php

class ChatbotService
{
    private $pdo;
    private $config;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->config = $this->loadConfig();
    }

    public function generateResponse($message, $userId = null)
    {
        $message = trim((string) $message);

        if ($message === '') {
            return [
                'response' => "Veuillez saisir un message avant l'envoi.",
                'source' => 'local',
            ];
        }

        $localResponse = $this->resolveKeywordResponse($message, $userId);

        if ($localResponse !== null) {
            return [
                'response' => $localResponse,
                'source' => 'local',
            ];
        }

        $apiResponse = $this->requestHuggingFace($message);

        if ($apiResponse !== null) {
            return [
                'response' => $apiResponse,
                'source' => 'api',
            ];
        }

        return [
            'response' => $this->buildDefaultFallbackResponse(),
            'source' => 'local',
        ];
    }

    private function resolveKeywordResponse($message, $userId)
    {
        if (preg_match('/\b(calorie|calories|kcal)\b/ui', $message)) {
            return $this->buildCaloriesResponse($userId);
        }

        if (preg_match('/(protéine|proteine|protein)/ui', $message)) {
            return "Les proteines aident surtout a maintenir la masse musculaire et a recuperer apres l'effort. Pensez a en mettre dans chaque repas via oeufs, poisson, viande maigre, yaourt grec ou legumes secs.";
        }

        if (preg_match('/(eau|hydrat)/ui', $message)) {
            return "Pour rester bien hydrate, buvez regulierement dans la journee et augmentez un peu l'apport s'il fait chaud ou si vous vous entrainez. Un repere simple est d'anticiper la soif plutot que d'attendre d'avoir tres soif.";
        }

        return null;
    }

    private function buildCaloriesResponse($userId)
    {
        $todayObjectif = $this->fetchTodayObjectif();

        if ($todayObjectif !== null) {
            $calories = (int) round((float) $todayObjectif['calories_cible']);
            $objectifDate = $this->formatFrenchDate($todayObjectif['date_creation'] ?? date('Y-m-d'));

            return "Votre objectif calorique pour {$objectifDate} est de {$calories} kcal.";
        }

        $userObjectif = $this->fetchUserObjective($userId);

        if ($userObjectif !== null) {
            $calories = (int) round($userObjectif);

            return "Je n'ai pas trouve d'objectif du jour, mais votre objectif enregistre est de {$calories} kcal.";
        }

        return "Je n'ai pas trouve d'objectif calorique pour aujourd'hui. Vous pouvez en creer un depuis la page Objectif.";
    }

    private function fetchTodayObjectif()
    {
        $stmt = $this->pdo->prepare("
            SELECT calories_cible, date_creation
            FROM objectif
            WHERE DATE(date_creation) = CURDATE()
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    private function fetchUserObjective($userId)
    {
        $userId = (int) $userId;

        if ($userId <= 0) {
            return null;
        }

        $stmt = $this->pdo->prepare("
            SELECT objectif_calories
            FROM utilisateur
            WHERE id = ?
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        $value = $stmt->fetchColumn();

        if ($value === false || $value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    private function requestHuggingFace($message)
    {
        $apiKey = trim((string) ($this->config['huggingface_api_key'] ?? ''));
        $model = trim((string) ($this->config['huggingface_model'] ?? ''));
        $timeout = max(1, (int) ($this->config['huggingface_timeout'] ?? 5));

        if ($apiKey === '' || $model === '' || !function_exists('curl_init')) {
            return null;
        }

        $endpoint = 'https://api-inference.huggingface.co/models/' . $this->encodeModelId($model);
        $prompt = "You are a nutrition assistant. Answer briefly in French.\n\nQuestion: " . $message;
        $payload = json_encode([
            'inputs' => $prompt,
            'parameters' => [
                'max_new_tokens' => 140,
                'temperature' => 0.4,
                'return_full_text' => false,
            ],
            'options' => [
                'wait_for_model' => false,
                'use_cache' => true,
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($payload === false) {
            return null;
        }

        $curl = curl_init($endpoint);

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_CONNECTTIMEOUT => min(3, $timeout),
            CURLOPT_TIMEOUT => $timeout,
        ]);

        $rawResponse = curl_exec($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        if ($rawResponse === false || $curlError !== '' || $httpCode >= 400) {
            return null;
        }

        $decoded = json_decode($rawResponse, true);

        if (!is_array($decoded)) {
            return null;
        }

        if (!empty($decoded['error'])) {
            return null;
        }

        if (!empty($decoded[0]['generated_text'])) {
            return $this->sanitizeApiText($decoded[0]['generated_text']);
        }

        if (!empty($decoded['generated_text'])) {
            return $this->sanitizeApiText($decoded['generated_text']);
        }

        return null;
    }

    private function sanitizeApiText($text)
    {
        $text = trim((string) $text);

        if ($text === '') {
            return null;
        }

        $text = preg_replace('/\s+/u', ' ', $text);

        if ($text === null) {
            return null;
        }

        return mb_substr(trim($text), 0, 500, 'UTF-8');
    }

    private function buildDefaultFallbackResponse()
    {
        return "Je peux deja vous aider rapidement sur les calories, les proteines et l'hydratation. Reformulez votre question nutritionnelle en une phrase simple si vous voulez une reponse plus precise.";
    }

    private function loadConfig()
    {
        $configPath = __DIR__ . '/../config/env.php';

        if (!is_file($configPath)) {
            return [];
        }

        $config = require $configPath;

        return is_array($config) ? $config : [];
    }

    private function encodeModelId($modelId)
    {
        $segments = array_map('rawurlencode', explode('/', $modelId));

        return implode('/', $segments);
    }

    private function formatFrenchDate($date)
    {
        $timestamp = strtotime((string) $date);

        if ($timestamp === false) {
            return "aujourd'hui";
        }

        return date('d/m/Y', $timestamp);
    }
}
