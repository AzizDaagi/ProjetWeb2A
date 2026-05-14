<?php

$projectRoot = dirname(__DIR__, 2);
$envFile = $projectRoot . '/.env';

if (is_file($envFile) && is_readable($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (is_array($lines)) {
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            if ($key !== '' && getenv($key) === false && !array_key_exists($key, $_ENV)) {
                putenv($key . '=' . $value);
                $_ENV[$key] = $value;
            }
        }
    }
}

$env = static function (string $key, string $default = ''): string {
    $value = $_ENV[$key] ?? getenv($key);
    if ($value === false || $value === null) {
        return $default;
    }

    return trim((string) $value);
};

$host = $env('DB_HOST', '127.0.0.1');
$port = (int) $env('DB_PORT', '3306');
$dbName = $env('DB_NAME', 'smart_nutrition');
$user = $env('DB_USER', 'root');
$password = $env('DB_PASSWORD', '');

$dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $dbName);

$pdo = new PDO($dsn, $user, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$tableExists = static function (PDO $pdo, string $table): bool {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name');
    $stmt->execute(['table_name' => $table]);
    return (int) $stmt->fetchColumn() > 0;
};

$columnExists = static function (PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name AND COLUMN_NAME = :column_name');
    $stmt->execute([
        'table_name' => $table,
        'column_name' => $column,
    ]);
    return (int) $stmt->fetchColumn() > 0;
};

$indexExists = static function (PDO $pdo, string $table, string $index): bool {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name AND INDEX_NAME = :index_name');
    $stmt->execute([
        'table_name' => $table,
        'index_name' => $index,
    ]);
    return (int) $stmt->fetchColumn() > 0;
};

$addColumnIfMissing = static function (PDO $pdo, string $table, string $column, string $definition) use ($columnExists): void {
    if (!$columnExists($pdo, $table, $column)) {
        $pdo->exec(sprintf('ALTER TABLE `%s` ADD COLUMN `%s` %s', $table, $column, $definition));
    }
};

$addIndexIfMissing = static function (PDO $pdo, string $table, string $index, string $definition) use ($indexExists): void {
    if (!$indexExists($pdo, $table, $index)) {
        $pdo->exec(sprintf('ALTER TABLE `%s` ADD %s', $table, $definition));
    }
};

$createTableIfMissing = static function (PDO $pdo, string $sql): void {
    $pdo->exec($sql);
};

$createTableIfMissing($pdo, <<<SQL
CREATE TABLE IF NOT EXISTS `posts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `post_category` VARCHAR(32) NOT NULL DEFAULT 'advice',
    `post_category_source` VARCHAR(20) NOT NULL DEFAULT 'manual',
    `post_category_score` DECIMAL(8,6) NULL,
    `image` LONGTEXT NULL,
    `product_analysis_json` LONGTEXT NULL,
    `latitude` DECIMAL(10,8) NULL,
    `longitude` DECIMAL(11,8) NULL,
    `location_accuracy` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

$createTableIfMissing($pdo, <<<SQL
CREATE TABLE IF NOT EXISTS `comments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `post_id` INT NOT NULL,
    `parent_comment_id` INT NULL,
    `user_id` INT NOT NULL,
    `comment_text` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

$createTableIfMissing($pdo, <<<SQL
CREATE TABLE IF NOT EXISTS `comment_reactions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `comment_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uniq_comment_user_like` (`comment_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

$createTableIfMissing($pdo, <<<SQL
CREATE TABLE IF NOT EXISTS `post_reactions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `post_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `reaction_type` VARCHAR(20) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uniq_post_user_reaction` (`post_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

$createTableIfMissing($pdo, <<<SQL
CREATE TABLE IF NOT EXISTS `post_reports` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `post_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `reason` VARCHAR(50) NOT NULL,
    `details` TEXT NULL,
    `status` VARCHAR(30) NOT NULL DEFAULT 'pending',
    `post_author_user_id` INT NULL,
    `post_title_snapshot` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uniq_post_user_report` (`post_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

$createTableIfMissing($pdo, <<<SQL
CREATE TABLE IF NOT EXISTS `content_moderation` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `content_type` VARCHAR(20) NOT NULL,
    `content_id` INT NOT NULL,
    `model` VARCHAR(255) NOT NULL,
    `label` VARCHAR(100) NOT NULL,
    `score` DECIMAL(8,6) NOT NULL DEFAULT 0.000000,
    `status` VARCHAR(20) NOT NULL DEFAULT 'allowed',
    `threshold_value` DECIMAL(8,6) NOT NULL DEFAULT 0.700000,
    `raw_response` LONGTEXT NULL,
    `error_message` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_content_moderation` (`content_type`, `content_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

$createTableIfMissing($pdo, <<<SQL
CREATE TABLE IF NOT EXISTS `image_moderation` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `content_type` VARCHAR(20) NOT NULL,
    `content_id` INT NOT NULL,
    `model` VARCHAR(255) NOT NULL,
    `label` VARCHAR(100) NOT NULL,
    `score` DECIMAL(8,6) NOT NULL DEFAULT 0.000000,
    `status` VARCHAR(20) NOT NULL DEFAULT 'allowed',
    `threshold_value` DECIMAL(8,6) NOT NULL DEFAULT 0.700000,
    `raw_response` LONGTEXT NULL,
    `error_message` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_image_moderation` (`content_type`, `content_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

$createTableIfMissing($pdo, <<<SQL
CREATE TABLE IF NOT EXISTS `moderation_jobs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `content_type` VARCHAR(20) NOT NULL,
    `content_id` INT NOT NULL,
    `job_type` VARCHAR(20) NOT NULL,
    `payload` LONGTEXT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
    `attempts` INT NOT NULL DEFAULT 0,
    `error_message` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

$createTableIfMissing($pdo, <<<SQL
CREATE TABLE IF NOT EXISTS `news_articles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `content` LONGTEXT NOT NULL,
    `summary` TEXT NULL,
    `image_url` VARCHAR(500) NULL,
    `image_local_path` VARCHAR(500) NULL,
    `category` ENUM('nutrition','fitness','wellness','health_tips') DEFAULT 'health_tips',
    `source` VARCHAR(100) DEFAULT 'AI Generated',
    `source_url` VARCHAR(500) NULL,
    `generated_by_ai` TINYINT(1) DEFAULT 1,
    `is_published` TINYINT(1) DEFAULT 1,
    `views_count` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

$createTableIfMissing($pdo, <<<SQL
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `actor_user_id` INT NULL,
    `type` VARCHAR(50) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `link_url` VARCHAR(500) NULL,
    `post_id` INT NULL,
    `comment_id` INT NULL,
    `report_id` INT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `email_sent` TINYINT(1) DEFAULT 0,
    `email_error` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `read_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

$communityColumns = [
    'users' => [
        'username' => "VARCHAR(100) NULL AFTER `email`",
    ],
    'posts' => [
        'post_category' => "VARCHAR(32) NOT NULL DEFAULT 'advice'",
        'post_category_source' => "VARCHAR(20) NOT NULL DEFAULT 'manual'",
        'post_category_score' => "DECIMAL(8,6) NULL",
        'image' => "LONGTEXT NULL",
        'product_analysis_json' => "LONGTEXT NULL",
        'latitude' => "DECIMAL(10,8) NULL",
        'longitude' => "DECIMAL(11,8) NULL",
        'location_accuracy' => "INT NULL",
        'updated_at' => "TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP",
    ],
    'comments' => [
        'parent_comment_id' => "INT NULL",
        'updated_at' => "TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP",
    ],
    'comment_reactions' => [],
    'post_reactions' => [],
    'post_reports' => [
        'status' => "VARCHAR(30) NOT NULL DEFAULT 'pending'",
        'post_author_user_id' => "INT NULL",
        'post_title_snapshot' => "VARCHAR(255) NULL",
        'updated_at' => "TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP",
    ],
    'content_moderation' => [
        'threshold_value' => "DECIMAL(8,6) NOT NULL DEFAULT 0.700000",
        'raw_response' => "LONGTEXT NULL",
        'error_message' => "TEXT NULL",
        'updated_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
    ],
    'image_moderation' => [
        'threshold_value' => "DECIMAL(8,6) NOT NULL DEFAULT 0.700000",
        'raw_response' => "LONGTEXT NULL",
        'error_message' => "TEXT NULL",
        'updated_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
    ],
    'moderation_jobs' => [
        'attempts' => "INT NOT NULL DEFAULT 0",
        'error_message' => "TEXT NULL",
        'updated_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
    ],
    'news_articles' => [
        'summary' => "TEXT NULL",
        'image_url' => "VARCHAR(500) NULL",
        'image_local_path' => "VARCHAR(500) NULL",
        'category' => "ENUM('nutrition','fitness','wellness','health_tips') DEFAULT 'health_tips'",
        'source' => "VARCHAR(100) DEFAULT 'AI Generated'",
        'source_url' => "VARCHAR(500) NULL",
        'generated_by_ai' => "TINYINT(1) DEFAULT 1",
        'is_published' => "TINYINT(1) DEFAULT 1",
        'views_count' => "INT DEFAULT 0",
        'updated_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
    ],
    'notifications' => [
        'actor_user_id' => "INT NULL",
        'link_url' => "VARCHAR(500) NULL",
        'post_id' => "INT NULL",
        'comment_id' => "INT NULL",
        'report_id' => "INT NULL",
        'is_read' => "TINYINT(1) DEFAULT 0",
        'email_sent' => "TINYINT(1) DEFAULT 0",
        'email_error' => "VARCHAR(255) NULL",
        'read_at' => "TIMESTAMP NULL DEFAULT NULL",
    ],
];

foreach ($communityColumns as $table => $columns) {
    if (!$tableExists($pdo, $table)) {
        continue;
    }

    foreach ($columns as $column => $definition) {
        $addColumnIfMissing($pdo, $table, $column, $definition);
    }
}

$indexes = [
    ['table' => 'posts', 'name' => 'idx_posts_user', 'definition' => 'INDEX `idx_posts_user` (`user_id`)'],
    ['table' => 'posts', 'name' => 'idx_posts_created', 'definition' => 'INDEX `idx_posts_created` (`created_at`)'],
    ['table' => 'comments', 'name' => 'idx_comments_post', 'definition' => 'INDEX `idx_comments_post` (`post_id`)'],
    ['table' => 'comments', 'name' => 'idx_comments_parent', 'definition' => 'INDEX `idx_comments_parent` (`parent_comment_id`)'],
    ['table' => 'comments', 'name' => 'idx_comments_user', 'definition' => 'INDEX `idx_comments_user` (`user_id`)'],
    ['table' => 'comment_reactions', 'name' => 'uniq_comment_user_like', 'definition' => 'UNIQUE KEY `uniq_comment_user_like` (`comment_id`, `user_id`)'],
    ['table' => 'post_reactions', 'name' => 'uniq_post_user_reaction', 'definition' => 'UNIQUE KEY `uniq_post_user_reaction` (`post_id`, `user_id`)'],
    ['table' => 'post_reports', 'name' => 'uniq_post_user_report', 'definition' => 'UNIQUE KEY `uniq_post_user_report` (`post_id`, `user_id`)'],
    ['table' => 'post_reports', 'name' => 'idx_post_reports_status', 'definition' => 'INDEX `idx_post_reports_status` (`status`)'],
    ['table' => 'post_reports', 'name' => 'idx_post_reports_post', 'definition' => 'INDEX `idx_post_reports_post` (`post_id`)'],
    ['table' => 'content_moderation', 'name' => 'unique_content_moderation', 'definition' => 'UNIQUE KEY `unique_content_moderation` (`content_type`, `content_id`)'],
    ['table' => 'image_moderation', 'name' => 'unique_image_moderation', 'definition' => 'UNIQUE KEY `unique_image_moderation` (`content_type`, `content_id`)'],
    ['table' => 'moderation_jobs', 'name' => 'idx_moderation_jobs_status', 'definition' => 'INDEX `idx_moderation_jobs_status` (`status`)'],
    ['table' => 'moderation_jobs', 'name' => 'idx_moderation_jobs_content', 'definition' => 'INDEX `idx_moderation_jobs_content` (`content_type`, `content_id`)'],
    ['table' => 'notifications', 'name' => 'idx_notifications_user', 'definition' => 'INDEX `idx_notifications_user` (`user_id`)'],
    ['table' => 'notifications', 'name' => 'idx_notifications_is_read', 'definition' => 'INDEX `idx_notifications_is_read` (`is_read`)'],
];

foreach ($indexes as $index) {
    if ($tableExists($pdo, $index['table'])) {
        $addIndexIfMissing($pdo, $index['table'], $index['name'], $index['definition']);
    }
}

if ($tableExists($pdo, 'users') && $columnExists($pdo, 'users', 'username')) {
    $hasPrenom = $columnExists($pdo, 'users', 'prenom');
    $hasNom = $columnExists($pdo, 'users', 'nom');

    if ($hasPrenom || $hasNom) {
        $prenomExpr = $hasPrenom ? "COALESCE(prenom, '')" : "''";
        $nomExpr = $hasNom ? "COALESCE(nom, '')" : "''";
        $pdo->exec("
            UPDATE `users`
            SET `username` = NULLIF(TRIM(CONCAT({$prenomExpr}, ' ', {$nomExpr})), '')
            WHERE (`username` IS NULL OR TRIM(`username`) = '')
        ");
    }

    $pdo->exec("
        UPDATE `users`
        SET `username` = SUBSTRING_INDEX(`email`, '@', 1)
        WHERE (`username` IS NULL OR TRIM(`username`) = '')
          AND `email` IS NOT NULL
          AND TRIM(`email`) <> ''
    ");
}

echo 'Migration Community additive terminée.';
