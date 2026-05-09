<?php

require_once __DIR__ . '/TranslationService.php';

class UsdaNutritionService
{
    private $apiUrl = 'https://api.nal.usda.gov/fdc/v1/foods/search';
    private $connectTimeoutSeconds = 5;
    private $timeoutSeconds = 15;
    private $excludedTerms = ['babyfood', 'infant', 'formula', 'toddler', 'junior', 'branded', 'fast food', 'soup', 'canned', 'prepared', 'frozen meal'];
    private $translationService;

    public function __construct()
    {
        $this->translationService = new TranslationService();
    }

    public function lookup($query)
    {
        $query = trim((string) $query);

        if ($query === '') {
            return $this->error("La recherche est obligatoire.");
        }

        $originalQuery = $query;
        $normalizedQuery = $this->normalizeFoodQuery($query);
        $translationSource = 'dictionary';

        if ($normalizedQuery === $query) {
            $translation = $this->translationService->translateFrToEn($query);
            $normalizedQuery = trim((string) ($translation['translated_text'] ?? $query));
            $translationSource = (string) ($translation['source'] ?? 'fallback_original');
        }

        $this->loadEnv();
        $apiKey = $this->resolveApiKey();

        if ($apiKey === '' || $apiKey === 'YOUR_USDA_API_KEY_HERE') {
            return $this->error("La cle USDA est absente.", [
                'query' => $originalQuery,
                'normalized_query' => $normalizedQuery,
                'token_present' => false,
                'http_code' => null,
                'url_preview' => $this->buildUrlPreview($normalizedQuery),
                'response_preview' => null,
            ]);
        }

        if (!function_exists('curl_init')) {
            return $this->error("cURL n'est pas disponible sur ce serveur.");
        }

        $params = [
            'query' => $normalizedQuery,
            'api_key' => $apiKey,
            'pageSize' => 10,
            'pageNumber' => 1,
        ];
        $url = $this->apiUrl . '?' . http_build_query($params);
        $curl = curl_init($url);

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeoutSeconds,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
            ],
        ]);

        $responseBody = curl_exec($curl);

        if ($responseBody === false) {
            $curlErrorCode = curl_errno($curl);
            $errorMessage = curl_error($curl) ?: "Impossible de contacter l'API USDA.";
            curl_close($curl);

            if ($curlErrorCode === CURLE_OPERATION_TIMEDOUT) {
                return $this->error("USDA API timeout. Reessayez avec un nom plus precis.", [
                    'query' => $originalQuery,
                    'normalized_query' => $normalizedQuery,
                    'token_present' => true,
                    'http_code' => null,
                    'url_preview' => $this->buildUrlPreview($normalizedQuery),
                    'response_preview' => null,
                ]);
            }

            return $this->error($errorMessage, [
                'query' => $originalQuery,
                'normalized_query' => $normalizedQuery,
                'token_present' => true,
                'http_code' => null,
                'url_preview' => $this->buildUrlPreview($normalizedQuery),
                'response_preview' => null,
            ]);
        }

        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode < 200 || $httpCode >= 300) {
            return $this->error("Erreur API USDA (HTTP {$httpCode}).", [
                'query' => $originalQuery,
                'normalized_query' => $normalizedQuery,
                'token_present' => true,
                'http_code' => $httpCode,
                'url_preview' => $this->buildUrlPreview($normalizedQuery),
                'response_preview' => mb_substr(trim((string) $responseBody), 0, 200),
            ]);
        }

        $decoded = json_decode($responseBody, true);

        if (!is_array($decoded)) {
            return $this->error("Reponse API USDA invalide.", [
                'query' => $originalQuery,
                'normalized_query' => $normalizedQuery,
                'token_present' => true,
                'http_code' => $httpCode,
                'url_preview' => $this->buildUrlPreview($normalizedQuery),
                'response_preview' => mb_substr(trim((string) $responseBody), 0, 200),
            ]);
        }

        $foods = $decoded['foods'] ?? [];
        $item = $this->selectBestFood(is_array($foods) ? $foods : [], $normalizedQuery);

        if ($item === null) {
            return $this->success(null);
        }

        $item['original_query'] = $originalQuery;
        $item['normalized_query'] = $normalizedQuery;
        $item['translation_source'] = $translationSource;

        return $this->success($item);
    }

    private function selectBestFood(array $foods, $query)
    {
        $bestCandidate = null;

        foreach ($foods as $food) {
            if (!is_array($food)) {
                continue;
            }

            $candidate = $this->buildCandidate($food, $query);

            if ($candidate === null) {
                continue;
            }

            if ($bestCandidate === null || $candidate['score'] > $bestCandidate['score']) {
                $bestCandidate = $candidate;
            }
        }

        return $bestCandidate['data'] ?? null;
    }

    private function buildCandidate(array $item, $query)
    {
        $nutrients = is_array($item['foodNutrients'] ?? null) ? $item['foodNutrients'] : [];
        $name = trim((string) ($item['description'] ?? $query));
        $dataType = trim((string) ($item['dataType'] ?? ''));
        $calories = $this->findCalories($nutrients);
        $protein = $this->findNutrientValue($nutrients, ['203'], ['Protein']);
        $carbohydrates = $this->findNutrientValue($nutrients, ['205'], ['Carbohydrate']);
        $fat = $this->findNutrientValue($nutrients, ['204'], ['Total lipid']);
        $sugar = $this->findNutrientValue($nutrients, ['269'], ['Sugars']);
        $score = $this->getDataTypePriority($dataType);
        $warning = null;

        if ($calories !== null && $protein !== null && $carbohydrates !== null && $fat !== null) {
            $score += 100;
        } else {
            $score += $this->countPresentValues([$calories, $protein, $carbohydrates, $fat]) * 20;
            $warning = 'Resultat partiel ou approximatif.';
        }

        if ($this->containsExcludedTerm($name)) {
            $score -= 1000;
        }

        return [
            'score' => $score,
            'data' => [
                'name' => $name,
                'fdcId' => isset($item['fdcId']) ? (int) $item['fdcId'] : null,
                'dataType' => $dataType,
                'calories' => $calories,
                'protein_g' => $protein,
                'carbohydrates_total_g' => $carbohydrates,
                'fat_total_g' => $fat,
                'sugar_g' => $sugar,
                'source' => 'usda_fdc',
                'warning' => $warning,
            ],
        ];
    }

    private function getDataTypePriority($dataType)
    {
        $normalized = strtolower(trim((string) $dataType));

        if ($normalized === 'foundation') {
            return 400;
        }

        if ($normalized === 'sr legacy') {
            return 300;
        }

        if (strpos($normalized, 'survey') !== false) {
            return 200;
        }

        if (strpos($normalized, 'branded') !== false) {
            return 100;
        }

        return 0;
    }

    private function containsExcludedTerm($name)
    {
        $normalized = strtolower(trim((string) $name));

        foreach ($this->excludedTerms as $term) {
            if ($term !== '' && strpos($normalized, $term) !== false) {
                return true;
            }
        }

        return false;
    }

    private function countPresentValues(array $values)
    {
        $count = 0;

        foreach ($values as $value) {
            if ($value !== null) {
                $count++;
            }
        }

        return $count;
    }

    private function normalizeFoodQuery($query)
    {
        $query = trim((string) $query);
        $normalized = $this->stripAccents(strtolower($query));
        $dictionary = [
            // Viandes / volailles
            'blanc de poulet' => 'chicken breast raw',
            'filet de poulet' => 'chicken breast raw',
            'poulet' => 'chicken breast raw',
            'dinde' => 'turkey breast raw',
            'jambon' => 'ham',
            'boeuf' => 'beef raw',
            'bÅ“uf' => 'beef raw',
            'steak' => 'beef steak raw',
            'viande hachÃ©e' => 'ground beef raw',
            'agneau' => 'lamb raw',
            'porc' => 'pork raw',

            // Poissons / fruits de mer
            'poisson' => 'fish raw',
            'saumon' => 'salmon raw',
            'thon' => 'tuna raw',
            'sardine' => 'sardines',
            'crevette' => 'shrimp raw',
            'cabillaud' => 'cod raw',
            'colin' => 'pollock raw',

            // Å’ufs / produits laitiers
            'oeuf' => 'egg raw',
            'Å“uf' => 'egg raw',
            'oeufs' => 'eggs raw',
            'Å“ufs' => 'eggs raw',
            'lait' => 'milk whole',
            'lait Ã©crÃ©mÃ©' => 'milk skim',
            'yaourt' => 'yogurt plain',
            'yaourt nature' => 'yogurt plain',
            'fromage blanc' => 'cottage cheese',
            'fromage' => 'cheese cheddar',
            'mozzarella' => 'mozzarella cheese',
            'beurre' => 'butter',

            // FÃ©culents / cÃ©rÃ©ales
            'riz' => 'white rice cooked',
            'riz blanc' => 'white rice cooked',
            'riz complet' => 'brown rice cooked',
            'riz cuit' => 'white rice cooked',
            'pates' => 'pasta cooked',
            'pÃ¢tes' => 'pasta cooked',
            'spaghetti' => 'spaghetti cooked',
            'pain' => 'bread white',
            'pain complet' => 'whole wheat bread',
            'avoine' => 'oats',
            'flocons dâ€™avoine' => 'oats',
            'flocons d\'avoine' => 'oats',
            'semoule' => 'couscous cooked',
            'couscous' => 'couscous cooked',
            'quinoa' => 'quinoa cooked',
            'boulgour' => 'bulgur cooked',

            // LÃ©gumineuses
            'lentilles' => 'lentils cooked',
            'lentille' => 'lentils cooked',
            'pois chiches' => 'chickpeas cooked',
            'haricots rouges' => 'kidney beans cooked',
            'haricots blancs' => 'white beans cooked',
            'petits pois' => 'green peas cooked',

            // LÃ©gumes
            'tomate' => 'tomato raw',
            'tomates' => 'tomato raw',
            'carotte' => 'carrot raw',
            'carottes' => 'carrot raw',
            'salade' => 'lettuce raw',
            'laitue' => 'lettuce raw',
            'concombre' => 'cucumber raw',
            'courgette' => 'zucchini raw',
            'aubergine' => 'eggplant raw',
            'brocoli' => 'broccoli raw',
            'Ã©pinards' => 'spinach raw',
            'epinards' => 'spinach raw',
            'oignon' => 'onion raw',
            'poivron' => 'bell pepper raw',
            'champignon' => 'mushrooms raw',
            'pomme de terre' => 'potato boiled',
            'patate' => 'potato boiled',
            'patate douce' => 'sweet potato raw',

            // Fruits
            'banane' => 'banana raw',
            'pomme' => 'apple raw',
            'orange' => 'orange raw',
            'fraise' => 'strawberries raw',
            'fraises' => 'strawberries raw',
            'raisin' => 'grapes raw',
            'mangue' => 'mango raw',
            'ananas' => 'pineapple raw',
            'kiwi' => 'kiwifruit raw',
            'poire' => 'pear raw',
            'pÃªche' => 'peach raw',
            'peche' => 'peach raw',
            'citron' => 'lemon raw',
            'avocat' => 'avocado raw',
            'datte' => 'dates',

            // Fruits secs / olÃ©agineux
            'amandes' => 'almonds',
            'amande' => 'almonds',
            'noix' => 'walnuts',
            'noix de cajou' => 'cashews',
            'cacahuÃ¨tes' => 'peanuts',
            'cacahuetes' => 'peanuts',
            'pistaches' => 'pistachios',
            'raisins secs' => 'raisins',

            // Huiles / sauces simples
            'huile dâ€™olive' => 'olive oil',
            'huile d\'olive' => 'olive oil',
            'huile olive' => 'olive oil',
            'huile de tournesol' => 'sunflower oil',
            'mayonnaise' => 'mayonnaise',
            'ketchup' => 'ketchup',
            'moutarde' => 'mustard',

            // Produits courants / petit-dÃ©jeuner
            'miel' => 'honey',
            'confiture' => 'jam',
            'sucre' => 'sugar',
            'chocolat' => 'chocolate',
            'cafÃ©' => 'coffee',
            'cafe' => 'coffee',
            'thÃ©' => 'tea',
            'the' => 'tea',
            'jus dâ€™orange' => 'orange juice',
            'jus d\'orange' => 'orange juice',
        ];
        uksort($dictionary, fn($a, $b) => mb_strlen($b) <=> mb_strlen($a));
        foreach ($dictionary as $term => $mappedQuery) {
            if (strpos($normalized, $this->stripAccents($term)) !== false) {
                return $mappedQuery;
            }
        }

        return $query;
    }

    private function stripAccents($text)
    {
        $search = ['Ã ', 'Ã¡', 'Ã¢', 'Ã¤', 'Ã£', 'Ã¥', 'Ã§', 'Ã¨', 'Ã©', 'Ãª', 'Ã«', 'Ã¬', 'Ã­', 'Ã®', 'Ã¯', 'Ã±', 'Ã²', 'Ã³', 'Ã´', 'Ã¶', 'Ãµ', 'Ã¹', 'Ãº', 'Ã»', 'Ã¼', 'Ã½', 'Ã¿', 'Å“'];
        $replace = ['a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'oe'];

        return str_replace($search, $replace, $text);
    }

    private function findNutrientValue(array $nutrients, array $numbers, array $names)
    {
        foreach ($nutrients as $nutrient) {
            if (!is_array($nutrient)) {
                continue;
            }

            $number = trim((string) ($nutrient['nutrientNumber'] ?? ''));
            $name = trim((string) ($nutrient['nutrientName'] ?? ''));
            $matchesName = false;

            foreach ($names as $expectedName) {
                if ($expectedName !== '' && stripos($name, $expectedName) !== false) {
                    $matchesName = true;
                    break;
                }
            }

            if (in_array($number, $numbers, true) || $matchesName) {
                $value = $nutrient['value'] ?? null;
                return is_numeric($value) ? (float) $value : null;
            }
        }

        return null;
    }

    private function findCalories(array $nutrients)
    {
        $kjValue = null;

        foreach ($nutrients as $nutrient) {
            if (!is_array($nutrient)) {
                continue;
            }

            $number = trim((string) ($nutrient['nutrientNumber'] ?? ''));
            $name = trim((string) ($nutrient['nutrientName'] ?? ''));
            $unit = strtoupper(trim((string) ($nutrient['unitName'] ?? '')));
            $value = $nutrient['value'] ?? null;

            if (!is_numeric($value)) {
                continue;
            }

            $numericValue = (float) $value;
            $isEnergy = $number === '208' || stripos($name, 'Energy') !== false;

            if (!$isEnergy) {
                continue;
            }

            if ($number === '208' || $unit === 'KCAL') {
                return $numericValue;
            }

            if ($unit === 'KJ') {
                $kjValue = $numericValue;
            }
        }

        return $kjValue !== null ? round($kjValue / 4.184, 1) : null;
    }

    private function loadEnv()
    {
        $envPath = dirname(__DIR__) . '/env.php';

        if (is_file($envPath)) {
            require_once $envPath;
        }
    }

    private function resolveApiKey()
    {
        $candidates = [
            $_ENV['USDA_API_KEY'] ?? null,
            getenv('USDA_API_KEY') ?: null,
            defined('USDA_API_KEY') ? constant('USDA_API_KEY') : null,
        ];

        foreach ($candidates as $candidate) {
            $value = trim((string) $candidate);

            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function buildUrlPreview($query)
    {
        return $this->apiUrl
            . '?query=' . rawurlencode((string) $query)
            . '&pageSize=10&pageNumber=1&api_key=***';
    }

    private function success($data)
    {
        return [
            'data' => $data,
            'error' => null,
            'cached' => false,
        ];
    }

    private function error($message, array $debug = [])
    {
        return [
            'data' => null,
            'error' => (string) $message,
            'cached' => false,
            'debug_usda' => !empty($debug) ? $debug : null,
        ];
    }
}
