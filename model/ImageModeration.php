<?php

require_once __DIR__ . '/HuggingFaceClient.php';

class ImageModeration
{
    private PDO $db;
    private HuggingFaceClient $client;
    private string $model;
    private float $threshold;
    private bool $enabled;

    public function __construct(PDO $db, ?HuggingFaceClient $client = null)
    {
        $this->db = $db;
        $this->client = $client ?? new HuggingFaceClient();
        $this->model = getenv('HF_IMAGE_MODERATION_MODEL') ?: 'Falconsai/nsfw_image_detection';
        $this->threshold = (float) (getenv('AI_IMAGE_MODERATION_THRESHOLD') ?: 0.7);
        $this->enabled = strtolower((string) (getenv('AI_IMAGE_MODERATION_ENABLED') ?: 'true')) !== 'false';
        $this->ensureTable();
    }

    public function analyzeAndStore(string $contentType, int $contentId, ?string $imagePath): array
    {
        if (!$this->enabled) {
            return $this->storeResult($contentType, $contentId, [
                'label' => 'DISABLED',
                'score' => 0.0,
                'status' => 'skipped',
                'raw_response' => [],
                'error_message' => null,
            ]);
        }

        if (!$imagePath || !is_file($imagePath)) {
            return $this->storeResult($contentType, $contentId, [
                'label' => 'NO_IMAGE',
                'score' => 0.0,
                'status' => 'skipped',
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
            $mimeType = mime_content_type($imagePath) ?: 'application/octet-stream';
            $response = $this->client->imageClassification($this->model, $imagePath, $mimeType);
            $topResult = $this->extractTopResult($response);
            $label = (string) ($topResult['label'] ?? 'UNKNOWN');
            $score = (float) ($topResult['score'] ?? 0.0);
            $status = ($this->isUnsafeLabel($label) && $score >= $this->threshold) ? 'review' : 'allowed';

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

    public function getResultsForContentType(string $contentType): array
    {
        $stmt = $this->db->prepare("SELECT * FROM image_moderation WHERE content_type = ? ORDER BY updated_at DESC");
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
            'skipped' => 0,
            'error' => 0,
        ];

        $stmt = $this->db->query("SELECT status, COUNT(*) AS total FROM image_moderation GROUP BY status");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $counts[(string) $row['status']] = (int) $row['total'];
        }

        return $counts;
    }

    private function extractTopResult(array $response): array
    {
        $best = [];
        foreach ($response as $item) {
            if (!is_array($item)) {
                continue;
            }

            if ($best === [] || (float) ($item['score'] ?? 0) > (float) ($best['score'] ?? 0)) {
                $best = $item;
            }
        }

        return $best;
    }

    private function isUnsafeLabel(string $label): bool
    {
        $normalized = strtolower($label);
        return str_contains($normalized, 'nsfw')
            || str_contains($normalized, 'unsafe')
            || str_contains($normalized, 'porn')
            || str_contains($normalized, 'sexy')
            || str_contains($normalized, 'hentai')
            || str_contains($normalized, 'nude');
    }

    private function storeResult(string $contentType, int $contentId, array $result): array
    {
        $contentType = $contentType === 'comment' ? 'comment' : 'post';
        $rawJson = json_encode($result['raw_response'] ?? []);

        $sql = "INSERT INTO image_moderation
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
        $sql = "CREATE TABLE IF NOT EXISTS image_moderation (
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
            UNIQUE KEY unique_image_moderation (content_type, content_id),
            INDEX idx_image_moderation_status (status),
            INDEX idx_image_moderation_content (content_type, content_id)
        )";

        $this->db->exec($sql);
    }
}
