<?php
class config
{
    private static $pdo = null;
    private static $envLoaded = false;

    public static function loadEnv()
    {
        if (self::$envLoaded) {
            return;
        }
        
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    [$key, $value] = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    if (!getenv($key)) {
                        putenv("$key=$value");
                    }
                }
            }
        }
        
        self::$envLoaded = true;
    }

    public static function getConnexion()
    {
        self::loadEnv();
        
        if (!isset(self::$pdo)) {

            $servername = getenv('DB_HOST') ?: "localhost";
            $username = getenv('DB_USER') ?: "root";
            $password = getenv('DB_PASSWORD') ?: "";
            $dbname = getenv('DB_NAME') ?: "smart_nutrition";

            try {
                self::$pdo = new PDO(
                    "mysql:host=$servername;dbname=$dbname",
                    $username,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
                self::ensureIntegratedSchema();

            } catch (Exception $e) {
                die('Erreur: ' . $e->getMessage());
            }
        }

        return self::$pdo;
    }

    private static function ensureIntegratedSchema()
    {
        self::ensureUserCompatibilityColumns();

        self::$pdo->exec("CREATE TABLE IF NOT EXISTS posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            content TEXT NOT NULL,
            post_category VARCHAR(32) NOT NULL DEFAULT 'advice',
            post_category_source VARCHAR(20) NOT NULL DEFAULT 'manual',
            post_category_score DECIMAL(8, 6) NULL,
            image VARCHAR(500) NULL,
            product_analysis_json LONGTEXT NULL,
            latitude DECIMAL(10, 8) NULL,
            longitude DECIMAL(11, 8) NULL,
            location_accuracy INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_posts_user (user_id),
            INDEX idx_posts_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        self::$pdo->exec("CREATE TABLE IF NOT EXISTS comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT NOT NULL,
            user_id INT NOT NULL,
            comment_text TEXT NOT NULL,
            parent_comment_id INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_comments_post (post_id),
            INDEX idx_comments_user (user_id),
            INDEX idx_comments_parent (parent_comment_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        self::$pdo->exec("CREATE TABLE IF NOT EXISTS post_reactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT NOT NULL,
            user_id INT NOT NULL,
            reaction_type VARCHAR(20) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_post_user_reaction (post_id, user_id),
            INDEX idx_post_reactions_post (post_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        self::$pdo->exec("CREATE TABLE IF NOT EXISTS post_reports (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT NOT NULL,
            user_id INT NOT NULL,
            reason VARCHAR(50) NOT NULL,
            details TEXT NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'pending',
            post_author_user_id INT NULL,
            post_title_snapshot VARCHAR(255) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_post_user_report (post_id, user_id),
            INDEX idx_post_reports_status (status),
            INDEX idx_post_reports_post (post_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    private static function ensureUserCompatibilityColumns()
    {
        $stmt = self::$pdo->query("SHOW COLUMNS FROM users LIKE 'username'");
        if (!$stmt->fetch()) {
            self::$pdo->exec("ALTER TABLE users ADD COLUMN username VARCHAR(255) NULL AFTER prenom");
        }

        self::$pdo->exec("UPDATE users
            SET username = TRIM(CONCAT(COALESCE(prenom, ''), ' ', COALESCE(nom, '')))
            WHERE username IS NULL OR TRIM(username) = ''");
    }
}
?>
