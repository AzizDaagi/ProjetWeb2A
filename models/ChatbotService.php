<?php

require_once __DIR__ . '/../services/HuggingFaceChatService.php';

class ChatbotService
{
    private $pdo;
    private $huggingFaceChatService;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->huggingFaceChatService = new HuggingFaceChatService();
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

        $hfResult    = $this->huggingFaceChatService->generateReply($message);
        $apiResponse = $hfResult['reply'];
        $hfDebug     = $hfResult['debug'];

        if ($apiResponse !== null) {
            return [
                'response' => $apiResponse,
                'source'   => 'huggingface',
            ];
        }

        return [
            'response' => $this->buildDefaultFallbackResponse(),
            'source'   => 'fallback',
            'debug_hf' => $hfDebug,
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

        if (preg_match('/(glucides?|carb)/ui', $message)) {
            return "Les glucides servent surtout a fournir de l'energie. Essaie de privilegier des sources simples a suivre comme riz, flocons d avoine, pommes de terre, fruits ou pain complet selon ton objectif.";
        }

        if (preg_match('/(lipides?|gras|mati[eè]res?\s+grasses?)/ui', $message)) {
            return "Les lipides sont utiles pour l energie et l equilibre hormonal. Garde surtout des sources de bonne qualite comme huile d olive, oeufs, avocats, noix et poissons gras.";
        }

        if (preg_match('/(eau|hydrat)/ui', $message)) {
            return "Pour rester bien hydrate, buvez regulierement dans la journee et augmentez un peu l'apport s'il fait chaud ou si vous vous entrainez. Un repere simple est d'anticiper la soif plutot que d'attendre d'avoir tres soif.";
        }

        if (preg_match('/(objectif|cible)/ui', $message)) {
            return $this->buildCaloriesResponse($userId);
        }

        if (preg_match('/(repas|manger|mange|collation)/ui', $message)) {
            return "Essaie de structurer tes repas autour d une source de proteines, un glucide utile et un peu de fibres. Meme un suivi simple de 2 a 3 prises par jour aide deja beaucoup a garder un bon rythme.";
        }

        if (preg_match('/(chrono|horaire|mange tard|manger tard|soir)/ui', $message)) {
            return "Si tu manges tard, essaie de garder un repas plus simple et digeste le soir, avec une portion de proteines et un glucide modere. Le plus utile reste de garder des horaires assez reguliers sur plusieurs jours.";
        }

        if (preg_match('/(sucre|sucres|dessert|boisson sucree)/ui', $message)) {
            return "Pour reduire le sucre, surveille d abord les boissons sucrees, desserts frequents et grignotages. Une bonne strategie est de remplacer une seule source sucree recurrente par jour plutot que de tout changer d un coup.";
        }

        if (preg_match('/(projection|prediction|pr[eé]vision|date d[\' ]atteinte)/ui', $message)) {
            return "La projection nutritionnelle s appuie surtout sur tes repas enregistres recents et ton objectif courant. Elle reste indicative et devient plus utile quand ton suivi est regulier sur plusieurs jours.";
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

    private function buildDefaultFallbackResponse()
    {
        return "Je n’ai pas pu générer une réponse avancée pour le moment, mais je peux t’aider sur les calories, protéines, hydratation, objectifs, sucre ou projection nutritionnelle.";
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

    private function formatFrenchDate($date)
    {
        $timestamp = strtotime((string) $date);

        if ($timestamp === false) {
            return "aujourd'hui";
        }

        return date('d/m/Y', $timestamp);
    }
}
