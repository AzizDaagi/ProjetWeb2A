<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || (($_SESSION['user_role'] ?? 'user') !== 'admin')) {
    header('Location: /Web/index.php?action=login');
    exit;
}
require_once __DIR__ . '/../../model/connection.php';
require_once __DIR__ . '/../../model/Post.php';
require_once __DIR__ . '/../../model/Notification.php';
require_once __DIR__ . '/../../model/InputValidator.php';

header('Content-Type: application/json');

$reportId = $_POST['report_id'] ?? null;

if (!$reportId) {
    echo json_encode([
        'success' => false,
        'message' => 'Identifiant du signalement manquant'
    ]);
    exit;
}

$postModel = new Post(config::getConnexion());
$db = config::getConnexion();
$notificationModel = new Notification($db);
$report = $postModel->getReportById((int) $reportId);
$reviewNote = InputValidator::cleanMultiline($_POST['review_note'] ?? '');
$validationError = InputValidator::validateReviewNote($reviewNote);
if ($validationError) {
    echo json_encode([
        'success' => false,
        'message' => $validationError
    ]);
    exit;
}
$success = $postModel->resolveReport((int) $reportId, $reviewNote);

if ($success && $report) {
    $recipientUserId = !empty($report['post_user_id']) ? (int) $report['post_user_id'] : (int) ($report['user_id'] ?? 0);
    $postTitle = $report['post_title'] ?? 'the reported post';
    $reason = ucwords(str_replace('_', ' ', $report['reason'] ?? 'report'));
    $message = !empty($report['post_user_id'])
        ? 'Un administrateur a examine et resolu le signalement concernant votre publication "' . $postTitle . '". Raison : ' . $reason . '.'
        : 'Un administrateur a examine et resolu votre signalement concernant une publication qui a depuis ete supprimee. Raison : ' . $reason . '.';
    if ($reviewNote !== '') {
        $message .= ' Note de revision : ' . $reviewNote;
    }

    if ($recipientUserId > 0) {
        $linkUrl = !empty($report['post_user_id'])
            ? '/Web/index.php?action=community#post-' . (int) $report['post_id']
            : '/Web/index.php?action=community';

        $notificationModel->create(
            $recipientUserId,
            1,
            'report_resolved',
            'Signalement resolu',
            $message,
            $linkUrl,
            (int) $report['post_id'],
            null,
            (int) $report['id']
        );
    }
}

echo json_encode([
    'success' => $success,
    'message' => $success ? 'Signalement resolu' : 'Impossible de resoudre le signalement'
]);
