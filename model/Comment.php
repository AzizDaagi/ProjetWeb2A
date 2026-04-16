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

    // CREATE comment
    public function addComment($postId, $userId, $content) {
        if (empty($content)) return false;

        $sql = "INSERT INTO comments (post_id, user_id, comment_text)
                VALUES (?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$postId, $userId, $content]);
    }

    // UPDATE comment
    public function updateComment($id, $content, $userId) {
        if (empty($content)) return false;

        $sql = "UPDATE comments SET comment_text = ? WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([$content, $id, $userId]);
    }

    // DELETE comment
    public function deleteComment($id) {
        $sql = "DELETE FROM comments WHERE id = ?";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([$id]);
    }
}