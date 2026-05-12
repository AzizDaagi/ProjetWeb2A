<?php
require_once __DIR__ . '/../model/connection.php';
require_once __DIR__ . '/../model/Notification.php';

header('Content-Type: application/json');

$db = config::getConnexion();
$notificationModel = new Notification($db);
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$userId = $_SESSION['user_id'] ?? 1;

if ($action === 'list') {
    $notifications = $notificationModel->getRecentForUser($userId);
    echo json_encode([
        'success' => true,
        'unreadCount' => $notificationModel->getUnreadCount($userId),
        'notifications' => $notifications
    ]);
    exit;
}

if ($action === 'mark_read') {
    $notificationId = $_POST['id'] ?? null;
    if (!$notificationId) {
        echo json_encode([
            'success' => false,
            'message' => 'Identifiant de notification manquant'
        ]);
        exit;
    }

    echo json_encode([
        'success' => $notificationModel->markAsRead((int) $notificationId, $userId),
        'unreadCount' => $notificationModel->getUnreadCount($userId)
    ]);
    exit;
}

if ($action === 'mark_all_read') {
    echo json_encode([
        'success' => $notificationModel->markAllAsRead($userId),
        'unreadCount' => $notificationModel->getUnreadCount($userId)
    ]);
    exit;
}

http_response_code(404);
echo json_encode([
    'success' => false,
    'message' => 'Action introuvable'
]);

?>
