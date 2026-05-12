<?php

require_once __DIR__ . '/HuggingFaceClient.php';
require_once __DIR__ . '/Post.php';

class PostTagger
{
    private PDO $db;
    private HuggingFaceClient $client;
    private Post $postModel;
    private string $model;

    private const CATEGORY_LABELS = [
        'Question' => 'question',
        'Recipe' => 'recipe',
        'Progress update' => 'progress',
        'Advice' => 'advice',
        'Product review' => 'product_review',
    ];

    public function __construct(PDO $db, ?HuggingFaceClient $client = null)
    {
        $this->db = $db;
        $this->client = $client ?? new HuggingFaceClient();
        $this->postModel = new Post($db);
        $this->model = getenv('HF_TAGGING_MODEL') ?: (getenv('HF_ZERO_SHOT_MODEL') ?: 'MoritzLaurer/mDeBERTa-v3-base-xnli-multilingual-nli-2mil7');
    }

    public function inferAndStore(int $postId, string $text): array
    {
        $text = trim($text);
        if ($text === '' || !$this->client->isConfigured()) {
            return [
                'category' => null,
                'score' => 0.0,
                'stored' => false,
            ];
        }

        $response = $this->client->zeroShotClassification($this->model, $text, array_keys(self::CATEGORY_LABELS));
        $best = $this->extractBestLabel($response);
        $label = (string) ($best['label'] ?? '');
        $category = self::CATEGORY_LABELS[$label] ?? null;
        $score = (float) ($best['score'] ?? 0);

        if (!$category) {
            return [
                'category' => null,
                'score' => $score,
                'stored' => false,
            ];
        }

        return [
            'category' => $category,
            'score' => $score,
            'stored' => $this->postModel->updatePostCategoryFromAi($postId, $category, $score),
            'raw_response' => $response,
        ];
    }

    private function extractBestLabel(array $response): array
    {
        if (isset($response['labels'], $response['scores']) && is_array($response['labels']) && is_array($response['scores'])) {
            $best = [];
            foreach ($response['labels'] as $index => $label) {
                $score = (float) ($response['scores'][$index] ?? 0);
                if ($best === [] || $score > (float) ($best['score'] ?? 0)) {
                    $best = [
                        'label' => (string) $label,
                        'score' => $score,
                    ];
                }
            }
            return $best;
        }

        $items = $response;
        if (isset($response[0]) && is_array($response[0]) && isset($response[0][0])) {
            $items = $response[0];
        }

        $best = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            if ($best === [] || (float) ($item['score'] ?? 0) > (float) ($best['score'] ?? 0)) {
                $best = $item;
            }
        }

        return $best;
    }
}

?>
