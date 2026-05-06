<?php

class Comment {

    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // READ comments by post
    public function getComments($postId) {
        $sql = "SELECT c.*, u.username 
                FROM comments c
                JOIN users u ON c.user_id = u.id
                WHERE c.post_id = ?
                ORDER BY c.created_at ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$postId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCommentById($id) {
        $sql = "SELECT * FROM comments WHERE id = ?";
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
        return $stmt->execute([$postId, $userId, $content, $parentCommentId]);
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
        $deleteRepliesSql = "DELETE FROM comments WHERE parent_comment_id = ?";
        $deleteRepliesStmt = $this->db->prepare($deleteRepliesSql);
        $deleteRepliesStmt->execute([$id]);

        $deleteCommentSql = "DELETE FROM comments WHERE id = ?";
        $deleteCommentStmt = $this->db->prepare($deleteCommentSql);

        return $deleteCommentStmt->execute([$id]);
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
}
