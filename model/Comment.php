<?php

class Comment {

    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->ensureCommentReactionsTable();
    }

    // READ comments by post
    public function getComments($postId, $userId = null) {
        $sql = "SELECT c.*, u.username,
                    COALESCE(likes.likes_count, 0) AS likes_count,
                    CASE WHEN user_like.user_id IS NULL THEN 0 ELSE 1 END AS user_liked
                FROM comments c
                JOIN users u ON c.user_id = u.id
                LEFT JOIN (
                    SELECT comment_id, COUNT(*) AS likes_count
                    FROM comment_reactions
                    GROUP BY comment_id
                ) likes ON likes.comment_id = c.id
                LEFT JOIN comment_reactions user_like
                    ON user_like.comment_id = c.id AND user_like.user_id = ?
                WHERE c.post_id = ?
                ORDER BY c.created_at ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $postId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCommentById($id) {
        $sql = "SELECT c.*, u.username
                FROM comments c
                LEFT JOIN users u ON c.user_id = u.id
                WHERE c.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // CREATE comment
    public function addComment($postId, $userId, $content, $parentCommentId = null) {
        if (empty($content)) return false;

        if ($parentCommentId !== null) {
            $parentComment = $this->getCommentById($parentCommentId);

            if (
                !$parentComment ||
                (int) $parentComment['post_id'] !== (int) $postId ||
                !empty($parentComment['parent_comment_id'])
            ) {
                return false;
            }
        }

        $sql = "INSERT INTO comments (post_id, user_id, comment_text, parent_comment_id)
                VALUES (?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$postId, $userId, $content, $parentCommentId]) ? (int) $this->db->lastInsertId() : false;
    }

    // UPDATE comment
    public function updateComment($id, $content, $userId) {
        if (empty($content)) return false;

        $sql = "UPDATE comments SET comment_text = ? WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([$content, $id, $userId]);
    }

    public function updateCommentAsAdmin($id, $content) {
        if (empty($content)) return false;

        $sql = "UPDATE comments SET comment_text = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([$content, $id]);
    }

    // DELETE comment
    public function deleteComment($id) {
        $this->deleteReactionsForCommentThread($id);

        $deleteRepliesSql = "DELETE FROM comments WHERE parent_comment_id = ?";
        $deleteRepliesStmt = $this->db->prepare($deleteRepliesSql);
        $deleteRepliesStmt->execute([$id]);

        $deleteCommentSql = "DELETE FROM comments WHERE id = ?";
        $deleteCommentStmt = $this->db->prepare($deleteCommentSql);

        return $deleteCommentStmt->execute([$id]);
    }

    public function toggleLike($commentId, $userId) {
        $comment = $this->getCommentById($commentId);
        if (!$comment) {
            return false;
        }

        $existingSql = "SELECT id FROM comment_reactions WHERE comment_id = ? AND user_id = ? LIMIT 1";
        $existingStmt = $this->db->prepare($existingSql);
        $existingStmt->execute([$commentId, $userId]);
        $existingId = $existingStmt->fetchColumn();

        if ($existingId !== false) {
            $deleteSql = "DELETE FROM comment_reactions WHERE comment_id = ? AND user_id = ?";
            $deleteStmt = $this->db->prepare($deleteSql);
            return $deleteStmt->execute([$commentId, $userId]);
        }

        $insertSql = "INSERT INTO comment_reactions (comment_id, user_id) VALUES (?, ?)";
        $insertStmt = $this->db->prepare($insertSql);
        return $insertStmt->execute([$commentId, $userId]);
    }

    public function getLikeSummary($commentId, $userId) {
        $countSql = "SELECT COUNT(*) FROM comment_reactions WHERE comment_id = ?";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute([$commentId]);

        $userSql = "SELECT COUNT(*) FROM comment_reactions WHERE comment_id = ? AND user_id = ?";
        $userStmt = $this->db->prepare($userSql);
        $userStmt->execute([$commentId, $userId]);

        return [
            'likes_count' => (int) $countStmt->fetchColumn(),
            'user_liked' => (int) $userStmt->fetchColumn() > 0
        ];
    }

    private function deleteReactionsForCommentThread($id): void {
        $sql = "DELETE cr FROM comment_reactions cr
                JOIN comments c ON cr.comment_id = c.id
                WHERE c.id = ? OR c.parent_comment_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $id]);
    }

    public function deleteCommentAsAdmin($id) {
        return $this->deleteComment($id);
    }

    // ===== Dashboard methods =====

    public function getNewCommentsCountLast24h() {
        $sql = "SELECT COUNT(*) FROM comments WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        $stmt = $this->db->query($sql);
        return (int) $stmt->fetchColumn();
    }

    public function getCommentsDailyCountsLast7Days() {
        $sql = "SELECT DATE(created_at) as day, COUNT(*) as count
                FROM comments
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY DATE(created_at)
                ORDER BY day ASC";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $counts = [];
        foreach ($rows as $row) {
            $counts[$row['day']] = (int) $row['count'];
        }

        $result = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-$i days"));
            $result[] = $counts[$day] ?? 0;
        }
        return $result;
    }

    private function ensureCommentReactionsTable(): void {
        $this->db->exec("CREATE TABLE IF NOT EXISTS comment_reactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            comment_id INT NOT NULL,
            user_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_comment_user_like (comment_id, user_id),
            KEY idx_comment_reactions_comment (comment_id),
            KEY idx_comment_reactions_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
}
