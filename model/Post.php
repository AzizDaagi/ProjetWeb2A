<?php
class Post {

    private $db;
    public $database;

    public function __construct($db) {
        $this->db = $db;
        $this->database = $db;
    }

    public function getAllPosts() {
        $sql = "SELECT p.*, u.username 
                FROM posts p 
                JOIN users u ON p.user_id = u.id 
                ORDER BY p.created_at DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPostById($id) {
        $sql = "SELECT * FROM posts WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createPost($userId, $title, $content, $image = null) {
        if(empty($title) || empty($content)) {
            return false;
        }

        if ($image) {
            $sql = "INSERT INTO posts (user_id, title, content, image) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$userId, $title, $content, $image]);
        } else {
            $sql = "INSERT INTO posts (user_id, title, content) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$userId, $title, $content]);
        }
    }

    public function updatePost($id, $title, $content, $image, $userId = 1) {
        $sql = "UPDATE posts SET title = ?, content = ?, image = ? WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$title, $content, $image ?: null, $id, $userId]);
    }

    public function deletePost($id) {
        $sql = "DELETE FROM posts WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
}

