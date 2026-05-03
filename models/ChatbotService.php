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

        $apiResponse = $this->requestHuggingFace($message, $userId);

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
        if (preg_match('/(progression|statut|ou\s+j[\'e ]?en\s+suis)/ui', $message)) {
            return $this->buildProgressionResponse($userId);
        }

        if (preg_match('/(conseil|que\s+faire)/ui', $message)) {
            return $this->buildAdviceResponse($userId);
        }

        if (preg_match('/(bilan|semaine)/ui', $message)) {
            return $this->buildWeekSummaryResponse($userId);
        }

        if (preg_match('/\b(calorie|calories|kcal)\b/ui', $message)) {
            return $this->buildCaloriesResponse($userId);
        }

        if (preg_match('/(proteines?|protein)/ui', $message)) {
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
            $responses = [
                "Votre objectif calorique pour {$objectifDate} est de {$calories} kcal.",
                "Pour {$objectifDate}, votre cible est fixee a {$calories} kcal.",
                "Aujourd'hui, votre objectif nutritionnel est de {$calories} kcal.",
            ];

            return $this->pickVariation($responses);
        }

        $userObjectif = $this->fetchUserObjective($userId);

        if ($userObjectif !== null) {
            $calories = (int) round($userObjectif);
            $responses = [
                "Je n'ai pas trouve d'objectif du jour, mais votre objectif enregistre est de {$calories} kcal.",
                "Aucun objectif du jour n'est defini, mais votre repere utilisateur reste {$calories} kcal.",
                "Je ne vois pas d'objectif actif aujourd'hui. Votre objectif enregistre est de {$calories} kcal.",
            ];

            return $this->pickVariation($responses);
        }

        return "Je n'ai pas trouve d'objectif calorique pour aujourd'hui. Vous pouvez en creer un depuis la page Objectif.";
    }

    private function buildProgressionResponse($userId)
    {
        $progress = $this->fetchTodayProgressData($userId);

        if ($progress['objectif'] === null) {
            return "Je n'ai pas encore d'objectif du jour pour mesurer votre progression. Creez d'abord un objectif nutritionnel.";
        }

        $consumed = (int) round((float) $progress['consomme']);
        $target = (int) round((float) $progress['objectif']);
        $status = $progress['statut'];

        if ($status === 'depasse') {
            $responses = [
                "Vous avez consomme {$consumed} kcal sur {$target} kcal aujourd'hui. Vous etes au-dessus de votre objectif.",
                "A ce stade, vous avez atteint {$consumed} kcal pour un objectif de {$target} kcal. Votre objectif est depasse.",
                "Votre progression du jour est de {$consumed} kcal sur {$target} kcal, donc vous avez depasse la cible.",
            ];

            return $this->pickVariation($responses);
        }

        if ($status === 'ok') {
            $responses = [
                "Vous avez consomme {$consumed} kcal sur {$target} kcal aujourd'hui. Vous etes pile dans votre objectif.",
                "Belle regularite : {$consumed} kcal sur {$target} kcal aujourd'hui, c'est exactement la cible.",
                "Vous en etes a {$consumed} kcal pour {$target} kcal aujourd'hui. Votre objectif est atteint.",
            ];

            return $this->pickVariation($responses);
        }

        if ($consumed <= 0) {
            $responses = [
                "Pour l'instant, vous etes a {$consumed} kcal sur {$target} kcal aujourd'hui. Vous etes encore en dessous de votre objectif.",
                "Aucune calorie n'a encore ete enregistree aujourd'hui sur un objectif de {$target} kcal.",
                "Vous demarrez la journee : {$consumed} kcal consommee(s) sur {$target} kcal visees.",
            ];

            return $this->pickVariation($responses);
        }

        $responses = [
            "Vous avez consomme {$consumed} kcal sur {$target} kcal aujourd'hui. Vous etes encore en dessous de votre objectif.",
            "Pour le moment, vous en etes a {$consumed} kcal sur {$target} kcal. Il vous reste de la marge.",
            "Votre progression du jour est de {$consumed} kcal pour un objectif de {$target} kcal, vous etes encore sous la cible.",
        ];

        return $this->pickVariation($responses);
    }

    private function buildAdviceResponse($userId)
    {
        $progress = $this->fetchTodayProgressData($userId);

        if ($progress['objectif'] === null) {
            return "Je peux vous donner un conseil plus precis des qu'un objectif du jour est defini. Commencez par generer votre objectif nutritionnel.";
        }

        $consumed = (int) round((float) $progress['consomme']);
        $target = (int) round((float) $progress['objectif']);
        $remaining = max(0, $target - $consumed);
        $status = $progress['statut'];

        if ($status === 'depasse') {
            $responses = [
                "Vous avez depasse votre objectif aujourd'hui. Pour equilibrer la suite, privilegiez un repas plus leger, riche en legumes et en proteines maigres.",
                "Comme vous etes au-dessus de votre cible, misez maintenant sur des aliments rassasiants mais moins denses en calories.",
                "Votre objectif est depasse pour aujourd'hui. Essayez de finir la journee avec une option plus simple : legumes, yaourt nature ou proteine maigre.",
            ];

            return $this->pickVariation($responses);
        }

        if ($status === 'ok') {
            $responses = [
                "Vous etes dans votre objectif aujourd'hui. Continuez avec le meme equilibre, sans surcompenser.",
                "Votre journee est bien calibree. Gardez ce rythme et restez attentif a l'hydratation.",
                "Bon equilibre aujourd'hui. Le meilleur conseil est de rester regulier jusqu'au prochain repas.",
            ];

            return $this->pickVariation($responses);
        }

        if ($consumed <= 0) {
            $responses = [
                "Vous pouvez commencer avec un repas simple et structure pour lancer la journee : une source de proteines, un glucide rassasiant et un peu de fibres.",
                "Comme rien n'est encore enregistre aujourd'hui, commencez par un repas complet plutot que de grignoter au hasard.",
                "Pour bien demarrer, visez un premier repas equilibre avec proteines, glucides et hydratation.",
            ];

            return $this->pickVariation($responses);
        }

        $responses = [
            "Vous etes encore sous votre objectif aujourd'hui. Vous pouvez ajouter environ {$remaining} kcal avec un repas complet ou une collation utile.",
            "Il vous reste de la marge aujourd'hui. Ajoutez plutot des calories de qualite : feculents, produits laitiers, fruits secs ou proteines.",
            "Comme vous etes encore sous la cible, pensez a completer avec un repas nourrissant plutot qu'avec des calories vides.",
        ];

        return $this->pickVariation($responses);
    }

    private function buildWeekSummaryResponse($userId)
    {
        $summary = $this->fetchWeeklySummaryData($userId);

        if ($summary['total_days'] === 0) {
            return "Je n'ai pas encore assez de donnees pour etablir un bilan cette semaine.";
        }

        $responses = [
            "Cette semaine : {$summary['depasse']} jour(s) depasse(s), {$summary['ok']} jour(s) correct(s), {$summary['sous']} jour(s) sous l'objectif et {$summary['aucune']} jour(s) sans consommation enregistree.",
            "Sur votre semaine en cours, je vois {$summary['ok']} jour(s) bien equilibres, {$summary['depasse']} depassement(s), {$summary['sous']} jour(s) en dessous et {$summary['aucune']} jour(s) vides.",
            "Bilan de la semaine : {$summary['depasse']} jour(s) au-dessus, {$summary['ok']} jour(s) dans la cible, {$summary['sous']} jour(s) sous la cible et {$summary['aucune']} jour(s) sans donnees de repas.",
        ];

        return $this->pickVariation($responses);
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

    private function requestHuggingFace($message, $userId = null)
    {
        $apiKey = trim((string) ($this->config['huggingface_api_key'] ?? ''));
        $model = trim((string) ($this->config['huggingface_model'] ?? ''));
        $timeout = max(1, (int) ($this->config['huggingface_timeout'] ?? 5));

        if ($apiKey === '' || $model === '' || !function_exists('curl_init')) {
            return null;
        }

        $endpoint = 'https://api-inference.huggingface.co/models/' . $this->encodeModelId($model);
        $progress = $this->fetchTodayProgressData($userId);
        $objectifText = $progress['objectif'] !== null
            ? (string) ((int) round((float) $progress['objectif'])) . ' kcal'
            : 'non disponible';
        $consumedText = (string) ((int) round((float) ($progress['consomme'] ?? 0))) . ' kcal';
        $statusText = $this->mapStatusToFrench($progress['statut'] ?? 'aucune');
        $prompt = "You are a nutrition assistant.\n\n"
            . "User context:\n"
            . "- objectif: {$objectifText}\n"
            . "- consomme: {$consumedText}\n"
            . "- statut: {$statusText}\n\n"
            . "Answer briefly in French.\n\nQuestion: "
            . $message;
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
        return "Je peux analyser votre progression, votre objectif ou vous donner un conseil personnalise. Essayez par exemple : ou j'en suis ?";
    }

    private function fetchTodayProgressData($userId = null)
    {
        $todayObjectif = $this->fetchTodayObjectif();
        $objectifCalories = $todayObjectif !== null
            ? (float) ($todayObjectif['calories_cible'] ?? 0)
            : $this->fetchUserObjective($userId);
        $consumedCalories = $this->fetchTodayConsumedCalories();
        $status = 'aucune';

        if ($objectifCalories !== null) {
            if ($consumedCalories > $objectifCalories) {
                $status = 'depasse';
            } elseif ($consumedCalories < $objectifCalories) {
                $status = 'sous';
            } else {
                $status = 'ok';
            }
        }

        return [
            'objectif' => $objectifCalories !== null ? (float) $objectifCalories : null,
            'consomme' => (float) $consumedCalories,
            'statut' => $status,
        ];
    }

    private function fetchTodayConsumedCalories()
    {
        $stmt = $this->pdo->query("
            SELECT COALESCE(SUM(calories_calculees), 0)
            FROM repas_consomme
            WHERE DATE(date_consommation) = CURDATE()
        ");

        return (float) $stmt->fetchColumn();
    }

    private function fetchWeeklySummaryData($userId = null)
    {
        $stmt = $this->pdo->query("
            SELECT
                CASE
                    WHEN COUNT(r.id) = 0 THEN 'aucune'
                    WHEN COALESCE(SUM(r.calories_calculees), 0) > o.calories_cible THEN 'depasse'
                    WHEN COALESCE(SUM(r.calories_calculees), 0) < o.calories_cible THEN 'sous'
                    ELSE 'ok'
                END AS statut
            FROM objectif o
            LEFT JOIN repas_consomme r ON r.objectif_id = o.id
            WHERE DATE(o.date_creation) <= CURDATE()
              AND DATE(o.date_creation) BETWEEN (
                    SELECT DATE_SUB(MAX(DATE(date_creation)), INTERVAL 6 DAY)
                    FROM objectif
                ) AND CURDATE()
            GROUP BY o.id, DATE(o.date_creation), o.calories_cible
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows) && $this->fetchUserObjective($userId) !== null) {
            return [
                'depasse' => 0,
                'ok' => 0,
                'sous' => 0,
                'aucune' => 0,
                'total_days' => 0,
            ];
        }

        $summary = [
            'depasse' => 0,
            'ok' => 0,
            'sous' => 0,
            'aucune' => 0,
            'total_days' => count($rows),
        ];

        foreach ($rows as $row) {
            $status = (string) ($row['statut'] ?? 'aucune');

            if (!array_key_exists($status, $summary)) {
                continue;
            }

            $summary[$status]++;
        }

        return $summary;
    }

    private function mapStatusToFrench($status)
    {
        $labels = [
            'depasse' => 'depasse',
            'sous' => 'sous l objectif',
            'ok' => 'dans l objectif',
            'aucune' => 'aucune consommation',
        ];

        return $labels[$status] ?? 'non disponible';
    }

    private function pickVariation(array $responses)
    {
        if (empty($responses)) {
            return '';
        }

        return $responses[array_rand($responses)];
    }

    private function loadConfig()
    {
        $configPath = __DIR__ . '/chatbot_env.php';

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
