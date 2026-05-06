<?php
require_once '../../model/connection.php';
require_once '../../model/Post.php';

header('Content-Type: application/json');

$reportId = $_POST['report_id'] ?? null;

if (!$reportId) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing report id'
    ]);
    exit;
}

$postModel = new Post(config::getConnexion());
$success = $postModel->resolveReport((int) $reportId);

echo json_encode([
    'success' => $success,
    'message' => $success ? 'Report resolved' : 'Unable to resolve report'
]);
