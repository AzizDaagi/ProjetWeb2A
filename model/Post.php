<?php
class Post {

    private $db;
    public $database;
    private const ALLOWED_REACTIONS = ['love', 'laugh', 'sad', 'angry'];
    private const ALLOWED_REPORT_REASONS = ['spam', 'harassment', 'false_information', 'inappropriate_content', 'other'];
    public const ALLOWED_CATEGORIES = ['question', 'recipe', 'progress', 'advice', 'product_review'];

    public function __construct($db) {
        $this->db = $db;
        $this->database = $db;
        $this->ensurePostCategoryColumn();
        $this->ensureProductAnalysisColumn();
        $this->ensurePostLocationColumns();
        $this->ensureReportSnapshotColumns();
    }

    public function getAllPosts() {
        $sql = "SELECT p.*, u.username,
                    COALESCE(author_posts.posts_count, 0) AS author_posts_count,
                    COALESCE(author_comments.comments_count, 0) AS author_comments_count,
                    COALESCE(author_recipes.recipes_count, 0) AS author_recipes_count
                FROM posts p 
                JOIN users u ON p.user_id = u.id 
                LEFT JOIN (
                    SELECT user_id, COUNT(*) AS posts_count
                    FROM posts
                    GROUP BY user_id
                ) author_posts ON author_posts.user_id = p.user_id
                LEFT JOIN (
                    SELECT user_id, COUNT(*) AS comments_count
                    FROM comments
                    GROUP BY user_id
                ) author_comments ON author_comments.user_id = p.user_id
                LEFT JOIN (
                    SELECT user_id, COUNT(*) AS recipes_count
                    FROM posts
                    WHERE post_category = 'recipe'
                    GROUP BY user_id
                ) author_recipes ON author_recipes.user_id = p.user_id
                ORDER BY p.created_at DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPostById($id) {
        $sql = "SELECT p.*, u.username
                FROM posts p
                LEFT JOIN users u ON p.user_id = u.id
                WHERE p.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createPost($userId, $title, $content, $image = null, $productAnalysisJson = null, $latitude = null, $longitude = null, $locationAccuracy = null, $category = 'advice') {
        if(empty($title) || empty($content)) {
            return false;
        }

        $category = in_array($category, self::ALLOWED_CATEGORIES, true) ? $category : 'advice';

        $sql = "INSERT INTO posts (user_id, title, content, image, product_analysis_json, latitude, longitude, location_accuracy, post_category) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $userId,
            $title,
            $content,
            $image ?: null,
            $productAnalysisJson ?: null,
            $latitude,
            $longitude,
            $locationAccuracy,
            $category
        ]) ? (int) $this->db->lastInsertId() : false;
    }

    public function updatePost($id, $title, $content, $image, $userId = 1, $productAnalysisJson = null, $category = 'advice') {
        $category = in_array($category, self::ALLOWED_CATEGORIES, true) ? $category : 'advice';
        $sql = "UPDATE posts SET title = ?, content = ?, image = ?, product_analysis_json = ?, post_category = ?, post_category_source = 'manual', post_category_score = NULL WHERE id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$title, $content, $image ?: null, $productAnalysisJson ?: null, $category, $id, $userId]);
    }

    public function updatePostCategoryFromAi($id, string $category, float $score): bool {
        if (!in_array($category, self::ALLOWED_CATEGORIES, true)) {
            return false;
        }

        $sql = "UPDATE posts
                SET post_category = ?, post_category_source = 'ai', post_category_score = ?
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$category, max(0, min(1, $score)), $id]);
    }

    public function deletePost($id) {
        $sql = "DELETE FROM posts WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function getReactionSummary($postId, $userId) {
        $counts = array_fill_keys(self::ALLOWED_REACTIONS, 0);

        $countSql = "SELECT reaction_type, COUNT(*) AS total
                     FROM post_reactions
                     WHERE post_id = ?
                     GROUP BY reaction_type";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute([$postId]);

        foreach ($countStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $reactionType = $row['reaction_type'];
            if (isset($counts[$reactionType])) {
                $counts[$reactionType] = (int) $row['total'];
            }
        }

        $userReactionSql = "SELECT reaction_type
                            FROM post_reactions
                            WHERE post_id = ? AND user_id = ?
                            LIMIT 1";
        $userReactionStmt = $this->db->prepare($userReactionSql);
        $userReactionStmt->execute([$postId, $userId]);
        $userReaction = $userReactionStmt->fetchColumn() ?: null;

        return [
            'counts' => $counts,
            'user_reaction' => $userReaction
        ];
    }

    public function reactToPost($postId, $userId, $reactionType) {
        if (!in_array($reactionType, self::ALLOWED_REACTIONS, true)) {
            return false;
        }

        $existingSql = "SELECT reaction_type
                        FROM post_reactions
                        WHERE post_id = ? AND user_id = ?
                        LIMIT 1";
        $existingStmt = $this->db->prepare($existingSql);
        $existingStmt->execute([$postId, $userId]);
        $existingReaction = $existingStmt->fetchColumn();

        if ($existingReaction === $reactionType) {
            $deleteSql = "DELETE FROM post_reactions WHERE post_id = ? AND user_id = ?";
            $deleteStmt = $this->db->prepare($deleteSql);
            return $deleteStmt->execute([$postId, $userId]);
        }

        if ($existingReaction !== false) {
            $updateSql = "UPDATE post_reactions
                          SET reaction_type = ?, created_at = CURRENT_TIMESTAMP
                          WHERE post_id = ? AND user_id = ?";
            $updateStmt = $this->db->prepare($updateSql);
            return $updateStmt->execute([$reactionType, $postId, $userId]);
        }

        $insertSql = "INSERT INTO post_reactions (post_id, user_id, reaction_type)
                      VALUES (?, ?, ?)";
        $insertStmt = $this->db->prepare($insertSql);
        return $insertStmt->execute([$postId, $userId, $reactionType]);
    }

    public function deleteReactionsForPost($postId) {
        $sql = "DELETE FROM post_reactions WHERE post_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$postId]);
    }

    public function getUserReportForPost($postId, $userId) {
        $sql = "SELECT * FROM post_reports WHERE post_id = ? AND user_id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$postId, $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllReports() {
        $sql = "SELECT
                    pr.*,
                    COALESCE(p.title, pr.post_title_snapshot, '[Deleted post]') AS post_title,
                    p.content AS post_content,
                    p.image AS post_image,
                    p.created_at AS post_created_at,
                    COALESCE(p.user_id, pr.post_author_user_id) AS post_user_id,
                    reporter.username AS reporter_username,
                    author.username AS post_author_username
                FROM post_reports pr
                LEFT JOIN posts p ON pr.post_id = p.id
                LEFT JOIN users reporter ON pr.user_id = reporter.id
                LEFT JOIN users author ON COALESCE(p.user_id, pr.post_author_user_id) = author.id
                ORDER BY
                    CASE WHEN pr.status = 'pending' THEN 0 ELSE 1 END,
                    pr.created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReportById($reportId) {
        $sql = "SELECT
                    pr.*,
                    COALESCE(p.title, pr.post_title_snapshot, '[Deleted post]') AS post_title,
                    p.content AS post_content,
                    p.image AS post_image,
                    p.created_at AS post_created_at,
                    COALESCE(p.user_id, pr.post_author_user_id) AS post_user_id,
                    reporter.username AS reporter_username,
                    author.username AS post_author_username
                FROM post_reports pr
                LEFT JOIN posts p ON pr.post_id = p.id
                LEFT JOIN users reporter ON pr.user_id = reporter.id
                LEFT JOIN users author ON COALESCE(p.user_id, pr.post_author_user_id) = author.id
                WHERE pr.id = ?
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$reportId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function reportPost($postId, $userId, $reason, $details = '') {
        if (!in_array($reason, self::ALLOWED_REPORT_REASONS, true)) {
            return false;
        }

        $details = trim((string) $details);
        $existingReport = $this->getUserReportForPost($postId, $userId);
        $reportedPost = $this->getPostById($postId);
        $postAuthorUserId = $reportedPost ? (int) $reportedPost['user_id'] : null;
        $postTitleSnapshot = $reportedPost ? (string) $reportedPost['title'] : null;

        if ($existingReport) {
            $sql = "UPDATE post_reports
                    SET reason = ?, details = ?, status = 'pending',
                        post_author_user_id = COALESCE(post_author_user_id, ?),
                        post_title_snapshot = COALESCE(post_title_snapshot, ?),
                        created_at = CURRENT_TIMESTAMP
                    WHERE post_id = ? AND user_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$reason, $details !== '' ? $details : null, $postAuthorUserId, $postTitleSnapshot, $postId, $userId]);
        }

        $sql = "INSERT INTO post_reports (post_id, user_id, reason, details, status, post_author_user_id, post_title_snapshot)
                VALUES (?, ?, ?, ?, 'pending', ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$postId, $userId, $reason, $details !== '' ? $details : null, $postAuthorUserId, $postTitleSnapshot]);
    }

    public function deleteReportsForPost($postId) {
        $sql = "DELETE FROM post_reports WHERE post_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$postId]);
    }

    public function resolveReport($reportId, $reviewMessage = null) {
        $sql = "UPDATE post_reports SET status = 'resolved' WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$reportId]);
    }

    private function ensureReportSnapshotColumns() {
        if (!$this->columnExists('post_reports', 'post_author_user_id')) {
            $this->db->exec("ALTER TABLE post_reports ADD COLUMN post_author_user_id INT NULL AFTER status");
        }

        if (!$this->columnExists('post_reports', 'post_title_snapshot')) {
            $this->db->exec("ALTER TABLE post_reports ADD COLUMN post_title_snapshot VARCHAR(255) NULL AFTER post_author_user_id");
        }

        $this->db->exec("UPDATE post_reports pr
            JOIN posts p ON pr.post_id = p.id
            SET pr.post_author_user_id = COALESCE(pr.post_author_user_id, p.user_id),
                pr.post_title_snapshot = COALESCE(pr.post_title_snapshot, p.title)
            WHERE pr.post_author_user_id IS NULL OR pr.post_title_snapshot IS NULL");
    }

    private function ensurePostCategoryColumn() {
        if (!$this->columnExists('posts', 'post_category')) {
            $this->db->exec("ALTER TABLE posts ADD COLUMN post_category VARCHAR(32) NOT NULL DEFAULT 'advice' AFTER content");
        }

        if (!$this->columnExists('posts', 'post_category_source')) {
            $this->db->exec("ALTER TABLE posts ADD COLUMN post_category_source VARCHAR(20) NOT NULL DEFAULT 'manual' AFTER post_category");
        }

        if (!$this->columnExists('posts', 'post_category_score')) {
            $this->db->exec("ALTER TABLE posts ADD COLUMN post_category_score DECIMAL(8, 6) NULL AFTER post_category_source");
        }
    }

    private function ensureProductAnalysisColumn() {
        if (!$this->columnExists('posts', 'product_analysis_json')) {
            $this->db->exec("ALTER TABLE posts ADD COLUMN product_analysis_json LONGTEXT NULL AFTER image");
        }
    }

    private function ensurePostLocationColumns() {
        if (!$this->columnExists('posts', 'latitude')) {
            $this->db->exec("ALTER TABLE posts ADD COLUMN latitude DECIMAL(10, 8) NULL AFTER product_analysis_json");
        }

        if (!$this->columnExists('posts', 'longitude')) {
            $this->db->exec("ALTER TABLE posts ADD COLUMN longitude DECIMAL(11, 8) NULL AFTER latitude");
        }

        if (!$this->columnExists('posts', 'location_accuracy')) {
            $this->db->exec("ALTER TABLE posts ADD COLUMN location_accuracy INT NULL AFTER longitude");
        }
    }

    private function columnExists($tableName, $columnName) {
        $sql = "SELECT COUNT(*)
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = ?
                  AND COLUMN_NAME = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tableName, $columnName]);
        return (int) $stmt->fetchColumn() > 0;
    }

    // ===== Dashboard methods =====

    public function getNewPostsCountLast24h() {
        $sql = "SELECT COUNT(*) FROM posts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        $stmt = $this->db->query($sql);
        return (int) $stmt->fetchColumn();
    }

    public function getPostsDailyCountsLast7Days() {
        $sql = "SELECT DATE(created_at) as day, COUNT(*) as count
                FROM posts
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

    public function getNewReportsCountLast24h() {
        $sql = "SELECT COUNT(*) FROM post_reports WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        $stmt = $this->db->query($sql);
        return (int) $stmt->fetchColumn();
    }

    public function getReportsDailyCountsLast7Days() {
        $sql = "SELECT DATE(created_at) as day, COUNT(*) as count
                FROM post_reports
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

    public function getMostInteractedPostThisWeek() {
        $sql = "SELECT * FROM (
            SELECT
                p.id,
                p.title,
                u.username,
                COALESCE(c1.comment_count, 0) as comments_count,
                COALESCE(c2.reply_count, 0) as replies_count,
                COALESCE(r.reaction_count, 0) as reactions_count,
                COALESCE(c1.comment_count, 0) + COALESCE(c2.reply_count, 0) + COALESCE(r.reaction_count, 0) as total_interactions
            FROM posts p
            JOIN users u ON p.user_id = u.id
            LEFT JOIN (
                SELECT post_id, COUNT(*) as comment_count
                FROM comments
                WHERE parent_comment_id IS NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY post_id
            ) c1 ON p.id = c1.post_id
            LEFT JOIN (
                SELECT post_id, COUNT(*) as reply_count
                FROM comments
                WHERE parent_comment_id IS NOT NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY post_id
            ) c2 ON p.id = c2.post_id
            LEFT JOIN (
                SELECT post_id, COUNT(*) as reaction_count
                FROM post_reactions
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY post_id
            ) r ON p.id = r.post_id
        ) ranked
        WHERE total_interactions > 0
        ORDER BY total_interactions DESC
        LIMIT 1";

        $stmt = $this->db->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
