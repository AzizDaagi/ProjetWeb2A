<?php

require_once __DIR__ . '/HuggingFaceClient.php';

class AiModeration
{
    private PDO $db;
    private HuggingFaceClient $client;
    private string $model;
    private string $mode;
    private float $threshold;

    public function __construct(PDO $db, ?HuggingFaceClient $client = null)
    {
        $this->db = $db;
        $this->client = $client ?? new HuggingFaceClient();
        $configuredModel = getenv('HF_MODERATION_MODEL') ?: '';
        $this->model = $configuredModel !== '' && $configuredModel !== 'cardiffnlp/twitter-roberta-base-offensive'
            ? $configuredModel
            : (getenv('HF_ZERO_SHOT_MODEL') ?: 'MoritzLaurer/mDeBERTa-v3-base-xnli-multilingual-nli-2mil7');
        $this->mode = strtolower((string) (getenv('AI_MODERATION_MODE') ?: 'review'));
        $this->threshold = (float) (getenv('AI_MODERATION_THRESHOLD') ?: 0.7);
        $this->ensureTable();
    }

    public function analyzeAndStore(string $contentType, int $contentId, string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            return $this->storeResult($contentType, $contentId, [
                'label' => 'EMPTY',
                'score' => 0.0,
                'status' => 'allowed',
                'raw_response' => [],
                'error_message' => null,
            ]);
        }

        if (!$this->client->isConfigured()) {
            return $this->storeResult($contentType, $contentId, [
                'label' => 'NOT_CONFIGURED',
                'score' => 0.0,
                'status' => 'error',
                'raw_response' => [],
                'error_message' => 'HF_TOKEN is missing.',
            ]);
        }

        try {
            $response = $this->client->zeroShotClassification($this->model, $text, ['offensive', 'safe']);
            $topResult = $this->extractMostRelevantResult($response);
            $label = (string) ($topResult['label'] ?? 'UNKNOWN');
            $score = (float) ($topResult['score'] ?? 0.0);
            $isOffensive = $this->isOffensiveLabel($label);
            $status = ($isOffensive && $score >= $this->threshold)
                ? ($this->mode === 'block' ? 'blocked' : 'review')
                : 'allowed';

            return $this->storeResult($contentType, $contentId, [
                'label' => $label,
                'score' => $score,
                'status' => $status,
                'raw_response' => $response,
                'error_message' => null,
            ]);
        } catch (Throwable $e) {
            return $this->storeResult($contentType, $contentId, [
                'label' => 'ERROR',
                'score' => 0.0,
                'status' => 'error',
                'raw_response' => [],
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    public function getResult(string $contentType, int $contentId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM content_moderation WHERE content_type = ? AND content_id = ? LIMIT 1");
        $stmt->execute([$contentType, $contentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getResultsForContentType(string $contentType): array
    {
        $stmt = $this->db->prepare("SELECT * FROM content_moderation WHERE content_type = ? ORDER BY updated_at DESC");
        $stmt->execute([$contentType]);

        $results = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $results[(int) $row['content_id']] = $row;
        }

        return $results;
    }

    public function getStatusCounts(): array
    {
        $counts = [
            'allowed' => 0,
            'review' => 0,
            'blocked' => 0,
            'error' => 0,
        ];

        $stmt = $this->db->query("SELECT status, COUNT(*) AS total FROM content_moderation GROUP BY status");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $status = (string) $row['status'];
            $counts[$status] = (int) $row['total'];
        }

        return $counts;
    }

    private function extractMostRelevantResult(array $response): array
    {
        $items = $response;
        if (isset($response[0]) && is_array($response[0]) && isset($response[0][0])) {
            $items = $response[0];
        }

        if (isset($response['labels'], $response['scores']) && is_array($response['labels']) && is_array($response['scores'])) {
            $items = [];
            foreach ($response['labels'] as $index => $label) {
                $items[] = [
                    'label' => $label,
                    'score' => (float) ($response['scores'][$index] ?? 0),
                ];
            }
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

    private function isOffensiveLabel(string $label): bool
    {
        $normalized = strtolower($label);
        return str_contains($normalized, 'offensive')
            || str_contains($normalized, 'toxic')
            || str_contains($normalized, 'abusive')
            || str_contains($normalized, 'hate')
            || $normalized === 'label_1';
    }

    private function storeResult(string $contentType, int $contentId, array $result): array
    {
        $contentType = $contentType === 'comment' ? 'comment' : 'post';
        $rawJson = json_encode($result['raw_response'] ?? []);

        $sql = "INSERT INTO content_moderation
                    (content_type, content_id, model, label, score, status, threshold_value, raw_response, error_message)
                VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    model = VALUES(model),
                    label = VALUES(label),
                    score = VALUES(score),
                    status = VALUES(status),
                    threshold_value = VALUES(threshold_value),
                    raw_response = VALUES(raw_response),
                    error_message = VALUES(error_message),
                    updated_at = CURRENT_TIMESTAMP";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $contentType,
            $contentId,
            $this->model,
            (string) ($result['label'] ?? 'UNKNOWN'),
            (float) ($result['score'] ?? 0),
            (string) ($result['status'] ?? 'error'),
            $this->threshold,
            $rawJson !== false ? $rawJson : '[]',
            $result['error_message'] ?? null,
        ]);

        return [
            'content_type' => $contentType,
            'content_id' => $contentId,
            'label' => (string) ($result['label'] ?? 'UNKNOWN'),
            'score' => (float) ($result['score'] ?? 0),
            'status' => (string) ($result['status'] ?? 'error'),
            'threshold' => $this->threshold,
        ];
    }

    private function ensureTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS content_moderation (
            id INT AUTO_INCREMENT PRIMARY KEY,
            content_type VARCHAR(20) NOT NULL,
            content_id INT NOT NULL,
            model VARCHAR(255) NOT NULL,
            label VARCHAR(100) NOT NULL,
            score DECIMAL(8,6) NOT NULL DEFAULT 0,
            status VARCHAR(20) NOT NULL DEFAULT 'allowed',
            threshold_value DECIMAL(8,6) NOT NULL DEFAULT 0.700000,
            raw_response LONGTEXT NULL,
            error_message TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_content_moderation (content_type, content_id),
            INDEX idx_content_moderation_status (status),
            INDEX idx_content_moderation_content (content_type, content_id)
        )";

        $this->db->exec($sql);
    }
}
