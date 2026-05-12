<?php

require_once __DIR__ . '/MailService.php';

class Notification
{
    private $db;
    private $mailService;

    public function __construct($db)
    {
        $this->db = $db;
        $this->mailService = new MailService();
        self::createTableIfNotExists($db);
    }

    public static function createTableIfNotExists($db)
    {
        $sql = "CREATE TABLE IF NOT EXISTS notifications (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            actor_user_id INT NULL,
            type VARCHAR(50) NOT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            link_url VARCHAR(500) NULL,
            post_id INT NULL,
            comment_id INT NULL,
            report_id INT NULL,
            is_read BOOLEAN DEFAULT FALSE,
            email_sent BOOLEAN DEFAULT FALSE,
            email_error VARCHAR(255) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            read_at TIMESTAMP NULL DEFAULT NULL,
            INDEX idx_notifications_user_read (user_id, is_read),
            INDEX idx_notifications_created (created_at),
            INDEX idx_notifications_post (post_id),
            INDEX idx_notifications_report (report_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $result = $db->exec($sql);
        self::ensureUserEmailColumn($db);

        return $result;
    }

    public function create($userId, $actorUserId, $type, $title, $message, $linkUrl = null, $postId = null, $commentId = null, $reportId = null, $sendEmail = true)
    {
        if (!$userId || ((int) $userId === (int) $actorUserId && $type !== 'report_resolved' && !$this->allowSelfNotificationForTesting())) {
            return false;
        }

        $sql = "INSERT INTO notifications
                (user_id, actor_user_id, type, title, message, link_url, post_id, comment_id, report_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $created = $stmt->execute([
            (int) $userId,
            $actorUserId ? (int) $actorUserId : null,
            $type,
            $title,
            $message,
            $linkUrl,
            $postId ? (int) $postId : null,
            $commentId ? (int) $commentId : null,
            $reportId ? (int) $reportId : null
        ]);

        if (!$created) {
            return false;
        }

        $notificationId = (int) $this->db->lastInsertId();

        if ($sendEmail) {
            $this->sendEmailForNotification($notificationId, (int) $userId, $title, $message, $linkUrl);
        }

        return $notificationId;
    }

    public function getRecentForUser($userId, $limit = 12)
    {
        $limit = max(1, min(50, (int) $limit));
        $sql = "SELECT n.*, actor.username AS actor_username
                FROM notifications n
                LEFT JOIN users actor ON n.actor_user_id = actor.id
                WHERE n.user_id = ?
                ORDER BY n.created_at DESC
                LIMIT $limit";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int) $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUnreadCount($userId)
    {
        $sql = "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = FALSE";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int) $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function markAsRead($notificationId, $userId)
    {
        $sql = "UPDATE notifications
                SET is_read = TRUE, read_at = CURRENT_TIMESTAMP
                WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([(int) $notificationId, (int) $userId]);
    }

    public function markAllAsRead($userId)
    {
        $sql = "UPDATE notifications
                SET is_read = TRUE, read_at = CURRENT_TIMESTAMP
                WHERE user_id = ? AND is_read = FALSE";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([(int) $userId]);
    }

    public function getUserEmail($userId)
    {
        if ($this->columnExists('users', 'email')) {
            $sql = "SELECT email FROM users WHERE id = ? LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([(int) $userId]);
            $email = trim((string) $stmt->fetchColumn());
            if ($email !== '') {
                return $email;
            }
        }

        return trim((string) (getenv('MAIL_FALLBACK_TO') ?: ''));
    }

    private function sendEmailForNotification($notificationId, $userId, $title, $message, $linkUrl)
    {
        $recipient = $this->getUserEmail($userId);
        $result = $this->mailService->sendNotificationEmail($recipient, $title, $message, $linkUrl);

        $sql = "UPDATE notifications
                SET email_sent = ?, email_error = ?
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $result['success'] ? 1 : 0,
            $result['success'] ? null : substr((string) ($result['message'] ?? 'Echec email'), 0, 255),
            (int) $notificationId
        ]);
    }

    private function columnExists($tableName, $columnName)
    {
        $sql = "SELECT COUNT(*)
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = ?
                  AND COLUMN_NAME = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tableName, $columnName]);
        return (int) $stmt->fetchColumn() > 0;
    }

    private function allowSelfNotificationForTesting()
    {
        $mode = strtolower(trim((string) (getenv('ALLOW_SELF_NOTIFICATIONS') ?: 'auto')));
        if ($mode === 'true' || $mode === '1' || $mode === 'yes') {
            return true;
        }
        if ($mode === 'false' || $mode === '0' || $mode === 'no') {
            return false;
        }

        $stmt = $this->db->query("SELECT COUNT(*) FROM users");
        return (int) $stmt->fetchColumn() <= 1;
    }

    private static function ensureUserEmailColumn($db)
    {
        $sql = "SELECT COUNT(*)
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'users'
                  AND COLUMN_NAME = 'email'";
        $stmt = $db->query($sql);
        if ((int) $stmt->fetchColumn() > 0) {
            return;
        }

        $db->exec("ALTER TABLE users ADD COLUMN email VARCHAR(255) NULL AFTER username");
    }
}

?>
