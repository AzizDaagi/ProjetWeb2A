<?php

class ModerationJob
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        self::createTableIfNotExists($db);
    }

    public static function createTableIfNotExists(PDO $db): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS moderation_jobs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            content_type VARCHAR(20) NOT NULL,
            content_id INT NOT NULL,
            job_type VARCHAR(20) NOT NULL,
            payload LONGTEXT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            attempts INT NOT NULL DEFAULT 0,
            error_message TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_moderation_jobs_status (status, created_at),
            INDEX idx_moderation_jobs_content (content_type, content_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $db->exec($sql);
    }

    public function enqueue(string $contentType, int $contentId, string $jobType, array $payload = []): int
    {
        $sql = "INSERT INTO moderation_jobs (content_type, content_id, job_type, payload)
                VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $contentType,
            $contentId,
            $jobType,
            json_encode($payload)
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function claimPending(int $limit = 3): array
    {
        $limit = max(1, min(10, $limit));
        $this->db->beginTransaction();

        $selectSql = "SELECT *
                      FROM moderation_jobs
                      WHERE status = 'pending'
                      ORDER BY created_at ASC
                      LIMIT $limit
                      FOR UPDATE";
        $jobs = $this->db->query($selectSql)->fetchAll(PDO::FETCH_ASSOC);

        if ($jobs) {
            $ids = array_map('intval', array_column($jobs, 'id'));
            $updateSql = "UPDATE moderation_jobs
                          SET status = 'processing', attempts = attempts + 1
                          WHERE id IN (" . implode(',', $ids) . ")";
            $this->db->exec($updateSql);
        }

        $this->db->commit();

        return $jobs;
    }

    public function markDone(int $jobId): void
    {
        $stmt = $this->db->prepare("UPDATE moderation_jobs SET status = 'done', error_message = NULL WHERE id = ?");
        $stmt->execute([$jobId]);
    }

    public function markFailed(int $jobId, string $message): void
    {
        $stmt = $this->db->prepare("UPDATE moderation_jobs SET status = 'failed', error_message = ? WHERE id = ?");
        $stmt->execute([substr($message, 0, 1000), $jobId]);
    }

    public function hasPending(): bool
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM moderation_jobs WHERE status = 'pending'");
        return (int) $stmt->fetchColumn() > 0;
    }
}

?>
