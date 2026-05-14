<?php
class News {

    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Get all published news articles with pagination
     */
    public function getAllNews($limit = 10, $offset = 0) {
        $limit = (int) $limit;
        $offset = (int) $offset;
        $sql = "SELECT * FROM news_articles 
                WHERE is_published = TRUE 
                ORDER BY created_at DESC 
                LIMIT $limit OFFSET $offset";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get featured/latest news articles
     */
    public function getFeaturedNews($limit = 6) {
        $limit = (int) $limit;
        $sql = "SELECT * FROM news_articles 
                WHERE is_published = TRUE 
                ORDER BY created_at DESC 
                LIMIT $limit";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get news by category
     */
    public function getNewsByCategory($category, $limit = 10) {
        $limit = (int) $limit;
        $sql = "SELECT * FROM news_articles 
                WHERE category = ? AND is_published = TRUE 
                ORDER BY created_at DESC 
                LIMIT $limit";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$category]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get single news article by ID
     */
    public function getNewsById($id) {
        $sql = "SELECT * FROM news_articles WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $this->incrementViewCount($id);
        }
        
        return $result;
    }

    /**
     * Create new news article
     */
    public function createNews($title, $content, $summary, $image_url, $image_local_path = null, $category = 'health_tips', $source = 'AI Generated', $source_url = null, $generated_by_ai = true) {
        if (empty($title) || empty($content)) {
            return false;
        }

        $sql = "INSERT INTO news_articles 
                (title, content, summary, image_url, image_local_path, category, source, source_url, generated_by_ai, is_published, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, TRUE, NOW())";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            $title,
            $content,
            $summary,
            $image_url,
            $image_local_path,
            $category,
            $source,
            $source_url,
            $generated_by_ai ? 1 : 0
        ]) ? (int) $this->db->lastInsertId() : false;
    }

    /**
     * Update news article
     */
    public function updateNews($id, $title, $content, $summary, $image_url, $category, $is_published = true) {
        $sql = "UPDATE news_articles 
                SET title = ?, content = ?, summary = ?, image_url = ?, category = ?, is_published = ?, updated_at = NOW() 
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            $title,
            $content,
            $summary,
            $image_url,
            $category,
            $is_published ? 1 : 0,
            $id
        ]);
    }

    /**
     * Delete news article
     */
    public function deleteNews($id) {
        $sql = "DELETE FROM news_articles WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Increment view count
     */
    public function incrementViewCount($id) {
        $sql = "UPDATE news_articles SET views_count = views_count + 1 WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Check if article from source URL already exists
     */
    public function articleExists($source_url) {
        if (!$source_url) {
            return false;
        }
        
        $sql = "SELECT id FROM news_articles WHERE source_url = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$source_url]);
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    /**
     * Get total count of published news
     */
    public function getTotalNewsCount() {
        $sql = "SELECT COUNT(*) as count FROM news_articles WHERE is_published = TRUE";
        $stmt = $this->db->query($sql);
        return (int) $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    /**
     * Create news articles table if not exists
     */
    public static function createTableIfNotExists($db) {
        $sql = "CREATE TABLE IF NOT EXISTS news_articles (
            id INT PRIMARY KEY AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            content LONGTEXT NOT NULL,
            summary TEXT,
            image_url VARCHAR(500),
            image_local_path VARCHAR(500),
            category ENUM('nutrition', 'fitness', 'wellness', 'health_tips') DEFAULT 'health_tips',
            source VARCHAR(100) DEFAULT 'AI Generated',
            source_url VARCHAR(500),
            generated_by_ai BOOLEAN DEFAULT TRUE,
            is_published BOOLEAN DEFAULT TRUE,
            views_count INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_category (category),
            INDEX idx_published (is_published),
            INDEX idx_created (created_at),
            UNIQUE KEY unique_source_url (source_url)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        return $db->exec($sql);
    }
}
?>
