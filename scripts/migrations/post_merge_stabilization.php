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

$pdo = new PDO(
    sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $dbName),
    $user,
    $password,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);

function tableExists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name');
    $stmt->execute(['table_name' => $table]);
    return (int) $stmt->fetchColumn() > 0;
}

function columnExists(PDO $pdo, string $table, string $column): bool
{
    if (!tableExists($pdo, $table)) {
        return false;
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name AND COLUMN_NAME = :column_name');
    $stmt->execute([
        'table_name' => $table,
        'column_name' => $column,
    ]);
    return (int) $stmt->fetchColumn() > 0;
}

function indexExists(PDO $pdo, string $table, string $index): bool
{
    if (!tableExists($pdo, $table)) {
        return false;
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name AND INDEX_NAME = :index_name');
    $stmt->execute([
        'table_name' => $table,
        'index_name' => $index,
    ]);
    return (int) $stmt->fetchColumn() > 0;
}

function addColumnIfMissing(PDO $pdo, string $table, string $column, string $definition): void
{
    if (!columnExists($pdo, $table, $column)) {
        $pdo->exec(sprintf('ALTER TABLE `%s` ADD COLUMN `%s` %s', $table, $column, $definition));
    }
}

function createTable(PDO $pdo, string $sql): void
{
    $pdo->exec($sql);
}

function createTableIfMissing(PDO $pdo, string $table, string $sql): void
{
    if (!tableExists($pdo, $table)) {
        createTable($pdo, $sql);
    }
}

function addIndexIfMissing(PDO $pdo, string $table, string $index, string $definition): void
{
    if (!tableExists($pdo, $table) || indexExists($pdo, $table, $index)) {
        return;
    }

    $pdo->exec(sprintf('ALTER TABLE `%s` ADD %s', $table, $definition));
}

createTableIfMissing($pdo, 'users', "
    CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nom` VARCHAR(100) NULL,
        `prenom` VARCHAR(100) NULL,
        `email` VARCHAR(255) NOT NULL,
        `password` VARCHAR(255) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

foreach ([
    'username' => "VARCHAR(100) NULL AFTER `email`",
    'poids' => "DECIMAL(5,2) NULL",
    'taille' => "DECIMAL(5,2) NULL",
    'age' => "INT NULL",
    'sexe' => "VARCHAR(20) NULL",
    'niveau_activite' => "VARCHAR(50) NULL",
    'face_descriptor' => "LONGTEXT NULL",
    'reset_token' => "VARCHAR(255) NULL",
    'reset_expires' => "DATETIME NULL",
] as $column => $definition) {
    addColumnIfMissing($pdo, 'users', $column, $definition);
}

$pdo->exec("
    UPDATE `users`
    SET `username` = NULLIF(TRIM(CONCAT(COALESCE(`prenom`, ''), ' ', COALESCE(`nom`, ''))), '')
    WHERE (`username` IS NULL OR TRIM(`username`) = '')
");
$pdo->exec("
    UPDATE `users`
    SET `username` = SUBSTRING_INDEX(`email`, '@', 1)
    WHERE (`username` IS NULL OR TRIM(`username`) = '')
      AND `email` IS NOT NULL
      AND TRIM(`email`) <> ''
");

$tables = [
    'aliments' => "CREATE TABLE IF NOT EXISTS `aliments` (`id` INT AUTO_INCREMENT PRIMARY KEY, `nom` VARCHAR(255) NOT NULL, `calories` DECIMAL(10,2) DEFAULT 0, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'objectif' => "CREATE TABLE IF NOT EXISTS `objectif` (`id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT NULL, `calories_cible` DECIMAL(10,2) DEFAULT 2000, `date_creation` DATE NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'repas_consomme' => "CREATE TABLE IF NOT EXISTS `repas_consomme` (`id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT NULL, `objectif_id` INT NULL, `aliment_id` INT NULL, `nom_repas` VARCHAR(100) NULL, `quantite` DECIMAL(10,2) DEFAULT 0, `calories_calculees` DECIMAL(10,2) DEFAULT 0, `date_consommation` DATE NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'nutrition_requests' => "CREATE TABLE IF NOT EXISTS `nutrition_requests` (`id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT NULL, `request_type` VARCHAR(100) NULL, `input_data` LONGTEXT NULL, `response_data` LONGTEXT NULL, `status` VARCHAR(50) DEFAULT 'pending', `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, INDEX `idx_nutrition_requests_user_id` (`user_id`), INDEX `idx_nutrition_requests_type` (`request_type`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'water_logs' => "CREATE TABLE IF NOT EXISTS `water_logs` (`id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT NULL, `amount_ml` DECIMAL(10,2) DEFAULT 0, `logged_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'hydration_logs' => "CREATE TABLE IF NOT EXISTS `hydration_logs` (`id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT NULL, `amount_ml` DECIMAL(10,2) DEFAULT 0, `logged_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'chrono_profiles' => "CREATE TABLE IF NOT EXISTS `chrono_profiles` (`id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT NULL, `chronotype` VARCHAR(50) DEFAULT 'standard', `wake_time` TIME NULL, `sleep_time` TIME NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'chrono_recommendations' => "CREATE TABLE IF NOT EXISTS `chrono_recommendations` (`id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT NULL, `recommendation_type` VARCHAR(50) NULL, `payload` LONGTEXT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'nutrition_predictions' => "CREATE TABLE IF NOT EXISTS `nutrition_predictions` (`id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT NULL, `prediction_type` VARCHAR(50) NULL, `payload` LONGTEXT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'posts' => "CREATE TABLE IF NOT EXISTS `posts` (`id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT NOT NULL, `title` VARCHAR(255) NOT NULL, `content` TEXT NOT NULL, `post_category` VARCHAR(32) NOT NULL DEFAULT 'advice', `post_category_source` VARCHAR(20) NOT NULL DEFAULT 'manual', `post_category_score` DECIMAL(8,6) NULL, `image` LONGTEXT NULL, `product_analysis_json` LONGTEXT NULL, `latitude` DECIMAL(10,8) NULL, `longitude` DECIMAL(11,8) NULL, `location_accuracy` INT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'comments' => "CREATE TABLE IF NOT EXISTS `comments` (`id` INT AUTO_INCREMENT PRIMARY KEY, `post_id` INT NOT NULL, `parent_comment_id` INT NULL, `user_id` INT NOT NULL, `comment_text` TEXT NOT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'comment_reactions' => "CREATE TABLE IF NOT EXISTS `comment_reactions` (`id` INT AUTO_INCREMENT PRIMARY KEY, `comment_id` INT NOT NULL, `user_id` INT NOT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY `uniq_comment_user_like` (`comment_id`, `user_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'post_reactions' => "CREATE TABLE IF NOT EXISTS `post_reactions` (`id` INT AUTO_INCREMENT PRIMARY KEY, `post_id` INT NOT NULL, `user_id` INT NOT NULL, `reaction_type` VARCHAR(20) NOT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY `uniq_post_user_reaction` (`post_id`, `user_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'post_reports' => "CREATE TABLE IF NOT EXISTS `post_reports` (`id` INT AUTO_INCREMENT PRIMARY KEY, `post_id` INT NOT NULL, `user_id` INT NOT NULL, `reason` VARCHAR(50) NOT NULL, `details` TEXT NULL, `status` VARCHAR(30) NOT NULL DEFAULT 'pending', `post_author_user_id` INT NULL, `post_title_snapshot` VARCHAR(255) NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, UNIQUE KEY `uniq_post_user_report` (`post_id`, `user_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'content_moderation' => "CREATE TABLE IF NOT EXISTS `content_moderation` (`id` INT AUTO_INCREMENT PRIMARY KEY, `content_type` VARCHAR(20) NOT NULL, `content_id` INT NOT NULL, `model` VARCHAR(255) NOT NULL, `label` VARCHAR(100) NOT NULL, `score` DECIMAL(8,6) NOT NULL DEFAULT 0.000000, `status` VARCHAR(20) NOT NULL DEFAULT 'allowed', `threshold_value` DECIMAL(8,6) NOT NULL DEFAULT 0.700000, `raw_response` LONGTEXT NULL, `error_message` TEXT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, UNIQUE KEY `unique_content_moderation` (`content_type`, `content_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'image_moderation' => "CREATE TABLE IF NOT EXISTS `image_moderation` (`id` INT AUTO_INCREMENT PRIMARY KEY, `content_type` VARCHAR(20) NOT NULL, `content_id` INT NOT NULL, `model` VARCHAR(255) NOT NULL, `label` VARCHAR(100) NOT NULL, `score` DECIMAL(8,6) NOT NULL DEFAULT 0.000000, `status` VARCHAR(20) NOT NULL DEFAULT 'allowed', `threshold_value` DECIMAL(8,6) NOT NULL DEFAULT 0.700000, `raw_response` LONGTEXT NULL, `error_message` TEXT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, UNIQUE KEY `unique_image_moderation` (`content_type`, `content_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'moderation_jobs' => "CREATE TABLE IF NOT EXISTS `moderation_jobs` (`id` INT AUTO_INCREMENT PRIMARY KEY, `content_type` VARCHAR(20) NOT NULL, `content_id` INT NOT NULL, `job_type` VARCHAR(20) NOT NULL, `payload` LONGTEXT NULL, `status` VARCHAR(20) NOT NULL DEFAULT 'pending', `attempts` INT NOT NULL DEFAULT 0, `error_message` TEXT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'news_articles' => "CREATE TABLE IF NOT EXISTS `news_articles` (`id` INT AUTO_INCREMENT PRIMARY KEY, `title` VARCHAR(255) NOT NULL, `content` LONGTEXT NOT NULL, `summary` TEXT NULL, `image_url` VARCHAR(500) NULL, `image_local_path` VARCHAR(500) NULL, `category` ENUM('nutrition','fitness','wellness','health_tips') DEFAULT 'health_tips', `source` VARCHAR(100) DEFAULT 'AI Generated', `source_url` VARCHAR(500) NULL, `generated_by_ai` TINYINT(1) DEFAULT 1, `is_published` TINYINT(1) DEFAULT 1, `views_count` INT DEFAULT 0, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'notifications' => "CREATE TABLE IF NOT EXISTS `notifications` (`id` INT AUTO_INCREMENT PRIMARY KEY, `user_id` INT NOT NULL, `actor_user_id` INT NULL, `type` VARCHAR(50) NOT NULL, `title` VARCHAR(255) NOT NULL, `message` TEXT NOT NULL, `link_url` VARCHAR(500) NULL, `post_id` INT NULL, `comment_id` INT NULL, `report_id` INT NULL, `is_read` TINYINT(1) DEFAULT 0, `email_sent` TINYINT(1) DEFAULT 0, `email_error` VARCHAR(255) NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `read_at` TIMESTAMP NULL DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'recettes' => "CREATE TABLE IF NOT EXISTS `recettes` (`id` INT AUTO_INCREMENT PRIMARY KEY, `nom` VARCHAR(255) NOT NULL, `description` TEXT NULL, `temps_preparation` VARCHAR(100) NULL, `niveau_difficulte` VARCHAR(100) NULL, `image_url` VARCHAR(500) NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'recette_aliment' => "CREATE TABLE IF NOT EXISTS `recette_aliment` (`id_recette` INT NOT NULL, `id_aliment` INT NOT NULL, `quantite` FLOAT DEFAULT 0, PRIMARY KEY (`id_recette`, `id_aliment`), KEY `idx_recette_aliment_aliment` (`id_aliment`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'recommandations' => "CREATE TABLE IF NOT EXISTS `recommandations` (`id` INT AUTO_INCREMENT PRIMARY KEY, `titre` VARCHAR(255) NOT NULL, `type_objectif` VARCHAR(100) NOT NULL, `contenu_regle` TEXT NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'activite' => "CREATE TABLE IF NOT EXISTS `activite` (`id_activite` INT AUTO_INCREMENT PRIMARY KEY, `nom_activite` VARCHAR(255) NOT NULL, `description` TEXT NULL, `duree_minutes` INT DEFAULT 0, `calories_brulees` INT DEFAULT 0, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    'exercice' => "CREATE TABLE IF NOT EXISTS `exercice` (`id_exercice` INT AUTO_INCREMENT PRIMARY KEY, `id_activite` INT NOT NULL, `nom_exercice` VARCHAR(255) NOT NULL, `description` TEXT NULL, `series` INT DEFAULT 0, `repetitions` INT DEFAULT 0, `duree_secondes` INT DEFAULT 0, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
];

foreach ($tables as $tableName => $sql) {
    createTableIfMissing($pdo, $tableName, $sql);
}

addIndexIfMissing($pdo, 'nutrition_requests', 'idx_nutrition_requests_user_id', 'INDEX `idx_nutrition_requests_user_id` (`user_id`)');
addIndexIfMissing($pdo, 'nutrition_requests', 'idx_nutrition_requests_type', 'INDEX `idx_nutrition_requests_type` (`request_type`)');
addIndexIfMissing($pdo, 'recette_aliment', 'idx_recette_aliment_aliment', 'INDEX `idx_recette_aliment_aliment` (`id_aliment`)');

foreach ([
    'aliments' => [
        'image_url' => "VARCHAR(500) NULL",
        'fibres' => "FLOAT NOT NULL DEFAULT 0",
        'type' => "VARCHAR(100) NULL",
        'proteines' => "FLOAT DEFAULT 0",
        'glucides' => "FLOAT DEFAULT 0",
        'lipides' => "FLOAT DEFAULT 0",
        'unite' => "VARCHAR(50) DEFAULT 'g'",
        'sucre_g' => "FLOAT DEFAULT 0",
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
    'post_reports' => [
        'post_author_user_id' => "INT NULL",
        'post_title_snapshot' => "VARCHAR(255) NULL",
        'updated_at' => "TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP",
    ],
    'chrono_profiles' => [
        'sleep_quality' => "VARCHAR(50) NULL",
        'energy_peak' => "VARCHAR(50) NULL",
        'energy_dip' => "VARCHAR(50) NULL",
        'workout_time' => "VARCHAR(50) NULL",
        'last_caffeine_time' => "VARCHAR(50) NULL",
        'preferred_meals_count' => "INT DEFAULT 3",
    ],
] as $tableName => $columns) {
    foreach ($columns as $columnName => $definition) {
        addColumnIfMissing($pdo, $tableName, $columnName, $definition);
    }
}

if (tableExists($pdo, 'recommandation') && tableExists($pdo, 'recommandations')) {
    $targetCount = (int) $pdo->query('SELECT COUNT(*) FROM `recommandations`')->fetchColumn();
    if ($targetCount === 0
        && columnExists($pdo, 'recommandation', 'titre')
        && columnExists($pdo, 'recommandation', 'type_objectif')
        && columnExists($pdo, 'recommandation', 'contenu_regle')) {
        $pdo->exec('INSERT INTO `recommandations` (`titre`, `type_objectif`, `contenu_regle`) SELECT `titre`, `type_objectif`, `contenu_regle` FROM `recommandation`');
    }
}

echo "Migration post-merge terminée." . PHP_EOL;
