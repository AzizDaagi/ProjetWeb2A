<?php
require_once __DIR__ . '/../model/connection.php';
require_once __DIR__ . '/../model/ModerationJob.php';
require_once __DIR__ . '/../model/AiModeration.php';
require_once __DIR__ . '/../model/ImageModeration.php';

header('Content-Type: application/json');

$db = config::getConnexion();
$jobs = new ModerationJob($db);
$aiModeration = new AiModeration($db);
$imageModeration = new ImageModeration($db);

$claimed = $jobs->claimPending(3);
$processed = 0;

foreach ($claimed as $job) {
    try {
        $payload = json_decode((string) ($job['payload'] ?? '{}'), true);
        if (!is_array($payload)) {
            $payload = [];
        }

        if ($job['job_type'] === 'text') {
            $text = (string) ($payload['text'] ?? '');
            $aiModeration->analyzeAndStore((string) $job['content_type'], (int) $job['content_id'], $text);
        } elseif ($job['job_type'] === 'image') {
            $imagePath = $payload['image_path'] ?? null;
            $imageModeration->analyzeAndStore((string) $job['content_type'], (int) $job['content_id'], $imagePath);
        }

        $jobs->markDone((int) $job['id']);
        $processed++;
    } catch (Throwable $e) {
        $jobs->markFailed((int) $job['id'], $e->getMessage());
    }
}

echo json_encode([
    'success' => true,
    'processed' => $processed,
    'hasPending' => $jobs->hasPending()
]);

?>
