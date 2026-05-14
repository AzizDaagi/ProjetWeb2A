<?php
require_once __DIR__ . '/HuggingFaceClient.php';

class NewsGenerationService {

    private $hfClient;
    private $newsApiKey;
    private $unsplashApiKey;
    private $db;

    private const NEWSAPI_BASE_URL = 'https://newsapi.org/v2/everything';
    private const UNSPLASH_BASE_URL = 'https://api.unsplash.com/search/photos';
    private const CATEGORIES = ['nutrition', 'fitness', 'wellness'];
    private const HEALTHY_NEWS_QUERIES = [
        'nutrition' => '("nutrition" OR "healthy eating" OR "balanced diet" OR "healthy diet" OR "food nutrients")',
        'healthy_meals' => '("healthy meals" OR "healthy recipes" OR "meal prep" OR "balanced meals" OR "healthy breakfast")',
        'fitness' => '("fitness" OR "workout" OR "exercise" OR "strength training" OR "cardio")',
        'wellness' => '("wellness" OR "sleep health" OR "stress management" OR "mental health")'
    ];

    public function __construct($db, $newsApiKey = null, $unsplashApiKey = null) {
        $this->db = $db;
        $this->hfClient = new HuggingFaceClient();
        $this->loadEnvFile(__DIR__ . '/../.env');
        
        $this->newsApiKey = $newsApiKey ?? getenv('NEWSAPI_KEY') ?: '';
        $this->unsplashApiKey = $unsplashApiKey ?? getenv('UNSPLASH_ACCESS_KEY') ?: '';
    }

    private function loadEnvFile($filePath) {
        if (file_exists($filePath)) {
            $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    [$key, $value] = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    if (!getenv($key)) {
                        putenv("$key=$value");
                    }
                }
            }
        }
    }

    /**
     * Fetch nutrition/fitness news from NewsAPI
     */
    public function fetchNewsFromAPI($keywords = 'nutrition OR fitness', $sortBy = 'publishedAt', $pageSize = 10) {
        if (empty($this->newsApiKey)) {
            throw new RuntimeException('Cle NewsAPI non configuree. Definissez NEWSAPI_KEY dans .env');
        }

        $params = [
            'q' => $keywords,
            'language' => 'en',
            'sortBy' => $sortBy,
            'pageSize' => $pageSize,
            'apiKey' => $this->newsApiKey
        ];

        $url = self::NEWSAPI_BASE_URL . '?' . http_build_query($params);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_USERAGENT => 'SmartNutrition/1.0'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new RuntimeException("La requete NewsAPI a echoue avec le statut $httpCode");
        }

        $data = json_decode($response, true);
        return $data['articles'] ?? [];
    }

    public function getHealthyNewsTopics() {
        return self::HEALTHY_NEWS_QUERIES;
    }

    /**
     * Download image from Unsplash based on keyword
     */
    public function downloadImageFromUnsplash($keyword, $outputDir) {
        if (empty($this->unsplashApiKey)) {
            // Return a default Unsplash URL if no API key
            return $this->getDefaultUnsplashUrl($keyword);
        }

        try {
            $params = [
                'query' => $keyword,
                'per_page' => 1,
                'orientation' => 'landscape',
                'client_id' => $this->unsplashApiKey
            ];

            $url = self::UNSPLASH_BASE_URL . '?' . http_build_query($params);

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_USERAGENT => 'SmartNutrition/1.0'
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                return $this->getDefaultUnsplashUrl($keyword);
            }

            $data = json_decode($response, true);
            
            if (empty($data['results'])) {
                return $this->getDefaultUnsplashUrl($keyword);
            }

            $imageUrl = $data['results'][0]['urls']['regular'];
            
            // Optionally download locally
            return $imageUrl;

        } catch (Exception $e) {
            return $this->getDefaultUnsplashUrl($keyword);
        }
    }

    /**
     * Get default Unsplash URL without API key
     */
    private function getDefaultUnsplashUrl($keyword) {
        $keyword = str_replace(' ', '+', trim($keyword));
        return "https://source.unsplash.com/800x450/?{$keyword},food,healthy";
    }

    /**
     * Generate summary using Hugging Face
     */
    public function generateSummaryWithHF($text, $maxLength = 150) {
        if (!$this->hfClient->isConfigured()) {
            // Fallback: truncate text
            return substr($text, 0, $maxLength) . '...';
        }

        try {
            // Try using text summarization if available
            // For now, we'll use a simple truncation with HF later if needed
            $summary = substr(strip_tags($text), 0, $maxLength);
            return rtrim($summary, ' ') . '...';
        } catch (Exception $e) {
            return substr($text, 0, $maxLength) . '...';
        }
    }

    /**
     * Determine category based on article content
     */
    public function categorizeArticle($title, $content) {
        $text = strtolower($title . ' ' . $content);
        
        if (preg_match('/(gym|exercise|workout|training|cardio|strength|muscle)/i', $text)) {
            return 'fitness';
        }
        if (preg_match('/(diet|nutrition|nutrient|calorie|protein|carb|recipe|meal|healthy eating|balanced diet|breakfast|lunch|dinner|vegetable|fruit)/i', $text)) {
            return 'nutrition';
        }
        if (preg_match('/(wellness|mental|sleep|stress|meditation|health)/i', $text)) {
            return 'wellness';
        }
        
        return 'health_tips';
    }

    /**
     * Process and store article from API
     */
    public function processAndStoreArticle($article, $newsModel) {
        try {
            // Check if already exists
            if ($newsModel->articleExists($article['url'] ?? null)) {
                return null;
            }

            $title = $article['title'] ?? '';
            $content = $article['description'] ?? $article['content'] ?? '';
            $imageUrl = $article['urlToImage'] ?? '';
            $sourceUrl = $article['url'] ?? '';
            $source = $article['source']['name'] ?? 'Source d actualite';

            if (empty($title) || empty($content)) {
                return null;
            }

            // Generate summary
            $summary = $this->generateSummaryWithHF($content);

            // Categorize
            $category = $this->categorizeArticle($title, $content);

            // Get or fetch image
            if (empty($imageUrl)) {
                $imageUrl = $this->downloadImageFromUnsplash($title, '');
            }

            // Store in database
            $newsId = $newsModel->createNews(
                $title,
                htmlspecialchars($content),
                $summary,
                $imageUrl,
                null,
                $category,
                $source,
                $sourceUrl,
                false // generated_by_ai = false (from API)
            );

            return $newsId;

        } catch (Exception $e) {
            error_log('Erreur lors du traitement de l article : ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate AI-created news article
     */
    public function generateAINews($topic = 'nutrition', $newsModel) {
        try {
            $prompts = [
                'nutrition' => [
                    'title' => 'Write a catchy 8-10 word headline about a nutrition topic:',
                    'content' => 'Write a 150-word engaging article about this nutrition fact or tip:'
                ],
                'fitness' => [
                    'title' => 'Write a catchy 8-10 word headline about a fitness or workout topic:',
                    'content' => 'Write a 150-word engaging article about this fitness tip:'
                ],
                'wellness' => [
                    'title' => 'Write a catchy 8-10 word headline about wellness or mental health:',
                    'content' => 'Write a 150-word engaging article about this wellness practice:'
                ]
            ];

            if (!isset($prompts[$topic])) {
                $topic = 'health_tips';
            }

            // For now, return null as full AI generation needs text-generation model
            // In production, you'd use a text generation endpoint
            return null;

        } catch (Exception $e) {
            error_log('Erreur lors de la generation d actualite IA : ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Fetch and store news articles (main entry point)
     */
    public function fetchAndStoreNews($newsModel, $keywords = 'nutrition OR fitness OR wellness', $limit = 5) {
        $stored = 0;
        
        try {
            $articles = $this->fetchNewsFromAPI($keywords, 'publishedAt', $limit);

            foreach ($articles as $article) {
                $result = $this->processAndStoreArticle($article, $newsModel);
                if ($result) {
                    $stored++;
                }
            }

            return [
                'success' => true,
                'stored' => $stored,
                'message' => "$stored nouvel article enregistre avec succes"
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'stored' => $stored,
                'message' => 'Erreur lors du chargement des actualites : ' . $e->getMessage()
            ];
        }
    }

    public function fetchAndStoreHealthyNewsMix($newsModel, $limitPerTopic = 4) {
        $stored = 0;
        $results = [];

        foreach (self::HEALTHY_NEWS_QUERIES as $topic => $query) {
            $topicStored = 0;

            try {
                $articles = $this->fetchNewsFromAPI($query, 'publishedAt', $limitPerTopic);

                foreach ($articles as $article) {
                    $result = $this->processAndStoreArticle($article, $newsModel);
                    if ($result) {
                        $topicStored++;
                        $stored++;
                    }
                }

                $results[$topic] = [
                    'success' => true,
                    'stored' => $topicStored,
                    'query' => $query
                ];
            } catch (Exception $e) {
                $results[$topic] = [
                    'success' => false,
                    'stored' => $topicStored,
                    'query' => $query,
                    'message' => $e->getMessage()
                ];
            }
        }

        return [
            'success' => $stored > 0,
            'stored' => $stored,
            'topics' => $results,
            'message' => $stored > 0
                ? "$stored nouvelles actualites sante enregistrees avec succes"
                : 'Aucune nouvelle actualite sante n a ete enregistree'
        ];
    }
}
?>
