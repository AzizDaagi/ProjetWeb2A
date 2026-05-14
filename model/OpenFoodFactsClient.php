<?php

class OpenFoodFactsClient
{
    private const USER_AGENT = 'SmartNutritionStudentProject/1.0 (student project)';
    private const TIMEOUT_SECONDS = 8;
    private const MAX_RETRIES = 2;
    private const SEARCH_PAGE_SIZE = 12;
    private const TUNISIA_HINTS = [
        'tunisia',
        'tunisie',
        'تونس',
        'tn',
        'tunis',
        'sfax',
        'sousse',
        'nabeul',
        'ben arous',
        'bizerte',
        'monastir',
        'mahdia',
        'kairouan',
        'gabes',
        'gafsa'
    ];
    private const TUNISIAN_BRANDS = [
        'delice',
        'délice',
        'vitalait',
        'jadida',
        'sicam',
        'randa',
        'boga',
        'apla',
        'sabrine',
        'safia',
        'saida',
        'saïda',
        'stifen',
        'tom',
        'moulin d or',
        'moulin dor',
        'moulin d\'or',
        'caprice',
        'warda',
        'lilas',
        'cristal',
        'hamadi abid'
    ];
    private const SEARCH_ALIASES = [
        'boga' => ['boga lim', 'boga cidre'],
        'delice' => ['delice tunisie', 'delice lait', 'delice eau'],
        'délice' => ['delice tunisie', 'delice lait', 'delice eau'],
        'saida' => ['saida biscuit', 'saida eau', 'saïda biscuit'],
        'saïda' => ['saida biscuit', 'saida eau', 'saïda biscuit'],
        'caprice' => ['caprice tunisie chocolat', 'caprice biscuit'],
        'stifen' => ['stifen tunisie'],
        'randa' => ['randa couscous', 'randa pates'],
        'sicam' => ['harissa sicam', 'tomate sicam']
    ];

    public function analyze(string $query): array
    {
        $query = trim($query);
        if ($query === '') {
            return [
                'success' => false,
                'message' => 'Veuillez saisir un nom de produit ou un code-barres.'
            ];
        }

        $product = $this->looksLikeBarcode($query)
            ? $this->fetchByBarcode($query)
            : $this->searchByName($query);

        if (!$product) {
            return [
                'success' => false,
                'message' => 'Produit non trouve dans Open Food Facts.'
            ];
        }

        return [
            'success' => true,
            'product' => $this->normalizeProduct($product)
        ];
    }

    private function fetchByBarcode(string $barcode): ?array
    {
        $url = 'https://world.openfoodfacts.org/api/v2/product/' . rawurlencode($barcode) . '.json';
        $data = $this->requestJson($url);

        if (!$data || (int) ($data['status'] ?? 0) !== 1 || empty($data['product'])) {
            return null;
        }

        return $data['product'];
    }

    private function searchByName(string $name): ?array
    {
        $queries = $this->buildSearchQueries($name);
        $candidates = [];

        foreach ($queries as $query) {
            $candidates = array_merge($candidates, $this->searchProducts([
                'search_terms' => $query,
                'countries_tags' => 'en:tunisia',
                'search_simple' => 1,
                'action' => 'process',
                'json' => 1,
                'page_size' => self::SEARCH_PAGE_SIZE,
                'fields' => $this->productFields()
            ]));
        }

        foreach ($queries as $query) {
            $candidates = array_merge($candidates, $this->searchProducts([
                'search_terms' => $query,
                'search_simple' => 1,
                'action' => 'process',
                'json' => 1,
                'page_size' => self::SEARCH_PAGE_SIZE,
                'fields' => $this->productFields()
            ]));
        }

        return $this->pickBestCandidate($name, $candidates);
    }

    private function searchProducts(array $params): array
    {
        $params = http_build_query($params);
        $url = 'https://world.openfoodfacts.org/cgi/search.pl?' . $params;
        $data = $this->requestJson($url);
        $products = $data['products'] ?? [];

        return is_array($products) ? array_values(array_filter($products, 'is_array')) : [];
    }

    private function buildSearchQueries(string $name): array
    {
        $clean = $this->cleanSearchText($name);
        $withoutTunisia = trim(preg_replace('/\b(tunisia|tunisie|tn)\b/i', ' ', $clean));
        $withoutGenericWords = trim(preg_replace('/\b(produit|food|alimentaire|tunisien|tunisienne)\b/i', ' ', $withoutTunisia));

        $queries = array_filter(array_unique([
            $name,
            $clean,
            $withoutTunisia,
            $withoutGenericWords
        ]));

        foreach ($this->queryTokens($this->normalizeForSearch($clean)) as $token) {
            if (isset(self::SEARCH_ALIASES[$token])) {
                $queries = array_merge($queries, self::SEARCH_ALIASES[$token]);
            }
        }

        return array_values($queries);
    }

    private function pickBestCandidate(string $query, array $candidates): ?array
    {
        $seen = [];
        $best = null;
        $bestScore = -9999;
        $normalizedQuery = $this->normalizeForSearch($query);

        foreach ($candidates as $candidate) {
            $code = (string) ($candidate['code'] ?? '');
            if ($code !== '' && isset($seen[$code])) {
                continue;
            }
            if ($code !== '') {
                $seen[$code] = true;
            }

            $score = $this->scoreCandidate($normalizedQuery, $candidate);
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $candidate;
            }
        }

        return $bestScore >= 8 ? $best : null;
    }

    private function scoreCandidate(string $normalizedQuery, array $product): int
    {
        $name = $this->normalizeForSearch($product['product_name_fr'] ?? $product['product_name'] ?? '');
        $brand = $this->normalizeForSearch($product['brands'] ?? '');
        $countries = $this->normalizeForSearch($product['countries'] ?? implode(' ', (array) ($product['countries_tags'] ?? [])));
        $categories = $this->normalizeForSearch($product['categories'] ?? implode(' ', (array) ($product['categories_tags'] ?? [])));
        $text = trim($name . ' ' . $brand . ' ' . $countries . ' ' . $categories);
        $score = 0;
        $queryBrandHint = $this->matchingTunisianBrandHint($normalizedQuery);
        $productHasTunisiaHint = false;
        $productHasMatchingBrand = false;

        if ($name !== '' && str_contains($normalizedQuery, $name)) {
            $score += 18;
        }
        if ($name !== '' && str_contains($name, $normalizedQuery)) {
            $score += 16;
        }
        if ($brand !== '' && str_contains($normalizedQuery, $brand)) {
            $score += 18;
        }
        if ($brand !== '' && str_contains($brand, $normalizedQuery)) {
            $score += 14;
        }

        foreach ($this->queryTokens($normalizedQuery) as $token) {
            if (strlen($token) < 3) {
                continue;
            }
            if (str_contains($name, $token)) {
                $score += 8;
            } elseif (str_contains($brand, $token)) {
                $score += 7;
            } elseif (str_contains($text, $token)) {
                $score += 3;
            }
        }

        foreach (self::TUNISIA_HINTS as $hint) {
            if (str_contains($countries, $this->normalizeForSearch($hint))) {
                $productHasTunisiaHint = true;
                $score += 14;
                break;
            }
        }

        foreach (self::TUNISIAN_BRANDS as $brandHint) {
            $brandHint = $this->normalizeForSearch($brandHint);
            if (str_contains($brand, $brandHint)) {
                if ($queryBrandHint && $brandHint === $queryBrandHint) {
                    $productHasMatchingBrand = true;
                    $score += 16;
                } else {
                    $score += 8;
                }
                break;
            }
        }

        if ($queryBrandHint && !$productHasTunisiaHint && !$productHasMatchingBrand) {
            $score -= 28;
        }

        if (!empty($product['image_front_small_url']) || !empty($product['image_url'])) {
            $score += 2;
        }
        if (!empty($product['nutriments'])) {
            $score += 2;
        }
        if (!empty($product['nutriscore_grade'])) {
            $score += 2;
        }

        return $score;
    }

    private function requestJson(string $url): ?array
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: " . self::USER_AGENT . "\r\nAccept: application/json\r\n",
                'timeout' => self::TIMEOUT_SECONDS
            ]
        ]);

        for ($attempt = 0; $attempt <= self::MAX_RETRIES; $attempt++) {
            $response = @file_get_contents($url, false, $context);
            if ($response !== false) {
                $data = json_decode($response, true);
                return is_array($data) ? $data : null;
            }

            if ($attempt < self::MAX_RETRIES) {
                usleep(250000);
            }
        }

        return null;
    }

    private function productFields(): string
    {
        return implode(',', [
            'code',
            'product_name',
            'product_name_fr',
            'generic_name',
            'generic_name_fr',
            'brands',
            'countries',
            'countries_tags',
            'categories',
            'categories_tags',
            'image_front_small_url',
            'image_url',
            'nutriscore_grade',
            'nova_group',
            'nutriments',
            'allergens_tags',
            'ingredients_text',
            'ingredients_text_fr',
            'url'
        ]);
    }

    private function normalizeProduct(array $product): array
    {
        $nutriments = $product['nutriments'] ?? [];
        $name = $product['product_name_fr']
            ?? $product['product_name']
            ?? $product['generic_name_fr']
            ?? $product['generic_name']
            ?? 'Produit alimentaire';

        $brands = $product['brands'] ?? '';
        $nutriScore = strtoupper((string) ($product['nutriscore_grade'] ?? ''));

        return [
            'name' => $this->cleanValue($name),
            'brand' => $this->cleanValue($brands),
            'image' => $this->cleanUrl($product['image_front_small_url'] ?? $product['image_url'] ?? ''),
            'nutriScore' => $nutriScore !== '' ? $nutriScore : 'Non disponible',
            'novaGroup' => $product['nova_group'] ?? null,
            'calories' => $this->nutrientValue($nutriments, ['energy-kcal_100g', 'energy-kcal']),
            'sugar' => $this->nutrientValue($nutriments, ['sugars_100g', 'sugars']),
            'fat' => $this->nutrientValue($nutriments, ['fat_100g', 'fat']),
            'salt' => $this->nutrientValue($nutriments, ['salt_100g', 'salt']),
            'allergens' => $this->formatTags($product['allergens_tags'] ?? []),
            'ingredients' => $this->cleanValue($product['ingredients_text_fr'] ?? $product['ingredients_text'] ?? ''),
            'sourceUrl' => $this->cleanUrl($product['url'] ?? '')
        ];
    }

    private function nutrientValue(array $nutriments, array $keys): ?float
    {
        foreach ($keys as $key) {
            if (isset($nutriments[$key]) && is_numeric($nutriments[$key])) {
                return round((float) $nutriments[$key], 2);
            }
        }

        return null;
    }

    private function formatTags($tags): array
    {
        if (!is_array($tags)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($tag) {
            $tag = preg_replace('/^[a-z]{2}:/', '', (string) $tag);
            return $this->cleanValue(str_replace('-', ' ', $tag));
        }, $tags)));
    }

    private function looksLikeBarcode(string $query): bool
    {
        return (bool) preg_match('/^\d{8,14}$/', $query);
    }

    private function cleanValue($value): string
    {
        return trim(preg_replace('/\s+/', ' ', (string) $value));
    }

    private function cleanSearchText(string $value): string
    {
        return trim(preg_replace('/\s+/', ' ', str_replace(['_', '-'], ' ', $value)));
    }

    private function normalizeForSearch($value): string
    {
        $value = strtolower($this->cleanSearchText((string) $value));
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        return trim(preg_replace('/[^a-z0-9]+/', ' ', $value));
    }

    private function queryTokens(string $normalizedQuery): array
    {
        return array_values(array_filter(explode(' ', $normalizedQuery)));
    }

    private function matchingTunisianBrandHint(string $normalizedQuery): ?string
    {
        foreach (self::TUNISIAN_BRANDS as $brandHint) {
            $brandHint = $this->normalizeForSearch($brandHint);
            if ($brandHint !== '' && str_contains($normalizedQuery, $brandHint)) {
                return $brandHint;
            }
        }

        return null;
    }

    private function cleanUrl($value): string
    {
        $value = trim((string) $value);
        return filter_var($value, FILTER_VALIDATE_URL) ? $value : '';
    }
}

?>
