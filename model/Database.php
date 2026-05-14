<?php

require_once dirname(__DIR__) . '/env.php';

class Database
{
    private static $connection = null;
    private static $schemaChecked = false;

    public static function getConnection()
    {
        if (self::$connection === null) {
            $host = trim((string) ($_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1'));
            $port = (int) ($_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: 3306);
            $dbName = trim((string) ($_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'smart_nutrition'));
            $username = trim((string) ($_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root'));
            $password = (string) ($_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '');
            $charset = 'utf8mb4';
            $timeout = (int) ($_ENV['DB_CONNECT_TIMEOUT'] ?? getenv('DB_CONNECT_TIMEOUT') ?: 5);
            if ($timeout < 1) {
                $timeout = 5;
            }

            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset={$charset}";

            try {
                self::$connection = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_TIMEOUT => $timeout,
                ]);
            } catch (PDOException $e) {
                error_log('Database connection error: ' . $e->getMessage());
                http_response_code(503);
                die('Service de base de donnees indisponible.');
            }
        }

        if (!self::$schemaChecked) {
            self::ensureCoreSchema();
            self::$schemaChecked = true;
        }

        return self::$connection;
    }

    public static function tableExists(PDO $pdo, string $tableName): bool
    {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name');
        $stmt->execute(['table_name' => $tableName]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function columnExists(PDO $pdo, string $tableName, string $columnName): bool
    {
        if (!self::tableExists($pdo, $tableName)) {
            return false;
        }

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name AND COLUMN_NAME = :column_name');
        $stmt->execute([
            'table_name' => $tableName,
            'column_name' => $columnName,
        ]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function indexExists(PDO $pdo, string $tableName, string $indexName): bool
    {
        if (!self::tableExists($pdo, $tableName)) {
            return false;
        }

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name AND INDEX_NAME = :index_name');
        $stmt->execute([
            'table_name' => $tableName,
            'index_name' => $indexName,
        ]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function createTableIfMissing(PDO $pdo, string $tableName, string $sql): void
    {
        if (!self::tableExists($pdo, $tableName)) {
            $pdo->exec($sql);
        }
    }

    public static function addColumnIfMissing(PDO $pdo, string $tableName, string $columnName, string $definition): void
    {
        if (!self::tableExists($pdo, $tableName)) {
            return;
        }

        if (!self::columnExists($pdo, $tableName, $columnName)) {
            $pdo->exec(sprintf('ALTER TABLE `%s` ADD COLUMN `%s` %s', $tableName, $columnName, $definition));
        }
    }

    public static function addIndexIfMissing(PDO $pdo, string $tableName, string $indexName, string $definition): void
    {
        if (!self::tableExists($pdo, $tableName)) {
            return;
        }

        if (!self::indexExists($pdo, $tableName, $indexName)) {
            $pdo->exec(sprintf('ALTER TABLE `%s` ADD %s', $tableName, $definition));
        }
    }

    private static function ensureCoreSchema(): void
    {
        self::ensureUsersTable();
        self::ensureNutritionSchema();
        self::ensureCommunitySchema();
        self::ensureRecipeSchema();
        self::ensureProductSchema();
        self::ensureActivitySchema();
    }

    private static function ensureUsersTable(): void
    {
        self::createTableIfMissing(self::$connection, 'users', "
            CREATE TABLE IF NOT EXISTS `users` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `nom` VARCHAR(100) NULL,
                `prenom` VARCHAR(100) NULL,
                `email` VARCHAR(255) NOT NULL,
                `password` VARCHAR(255) NOT NULL,
                `role` VARCHAR(50) NOT NULL DEFAULT 'user',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $columns = [
            'username' => "VARCHAR(100) NULL AFTER `email`",
            'role' => "VARCHAR(50) NOT NULL DEFAULT 'user' AFTER `password`",
            'poids' => "DECIMAL(5,2) NULL",
            'taille' => "DECIMAL(5,2) NULL",
            'age' => "INT NULL",
            'sexe' => "VARCHAR(20) NULL",
            'niveau_activite' => "VARCHAR(50) NULL",
            'objectif' => "VARCHAR(255) NULL",
            'date_naissance' => "DATE NULL",
            'face_descriptor' => "LONGTEXT NULL",
            'face_updated_at' => "DATETIME NULL",
            'reset_token' => "VARCHAR(255) NULL",
            'reset_expires' => "DATETIME NULL",
            'password_reset_token' => "VARCHAR(255) NULL",
            'password_reset_expires' => "DATETIME NULL",
        ];

        foreach ($columns as $column => $definition) {
            self::addColumnIfMissing(self::$connection, 'users', $column, $definition);
        }
    }

    private static function ensureNutritionSchema(): void
    {
        self::createTableIfMissing(self::$connection, 'nutrition_requests', "
            CREATE TABLE IF NOT EXISTS `nutrition_requests` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NULL,
                `request_type` VARCHAR(100) NULL,
                `input_data` LONGTEXT NULL,
                `response_data` LONGTEXT NULL,
                `status` VARCHAR(50) DEFAULT 'pending',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        self::addIndexIfMissing(self::$connection, 'nutrition_requests', 'idx_nutrition_requests_user_id', 'INDEX `idx_nutrition_requests_user_id` (`user_id`)');
        self::addIndexIfMissing(self::$connection, 'nutrition_requests', 'idx_nutrition_requests_type', 'INDEX `idx_nutrition_requests_type` (`request_type`)');

        self::createTableIfMissing(self::$connection, 'aliments', "
            CREATE TABLE IF NOT EXISTS `aliments` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `nom` VARCHAR(255) NOT NULL,
                `calories` DECIMAL(10,2) DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        foreach ([
            'type' => "VARCHAR(100) NULL",
            'proteines' => "DECIMAL(10,2) DEFAULT 0",
            'glucides' => "DECIMAL(10,2) DEFAULT 0",
            'lipides' => "DECIMAL(10,2) DEFAULT 0",
            'unite' => "VARCHAR(50) DEFAULT 'g'",
            'sucre_g' => "DECIMAL(10,2) DEFAULT 0",
            'fibres' => "DECIMAL(10,2) DEFAULT 0",
            'image_url' => "VARCHAR(500) NULL",
        ] as $column => $definition) {
            self::addColumnIfMissing(self::$connection, 'aliments', $column, $definition);
        }

        self::createTableIfMissing(self::$connection, 'objectif', "
            CREATE TABLE IF NOT EXISTS `objectif` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NULL,
                `calories_cible` DECIMAL(10,2) DEFAULT 2000,
                `date_creation` DATE NULL,
                `objectif_type` VARCHAR(50) DEFAULT 'maintien',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        foreach ([
            'proteines_cible' => "DECIMAL(10,2) DEFAULT 0",
            'glucides_cible' => "DECIMAL(10,2) DEFAULT 0",
            'lipides_cible' => "DECIMAL(10,2) DEFAULT 0",
            'sucre_max_g' => "DECIMAL(10,2) DEFAULT 0",
            'poids_cible' => "DECIMAL(5,2) NULL",
            'activite' => "VARCHAR(50) NULL",
        ] as $column => $definition) {
            self::addColumnIfMissing(self::$connection, 'objectif', $column, $definition);
        }

        self::createTableIfMissing(self::$connection, 'repas_consomme', "
            CREATE TABLE IF NOT EXISTS `repas_consomme` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NULL,
                `objectif_id` INT NULL,
                `aliment_id` INT NULL,
                `nom_repas` VARCHAR(100) NULL,
                `quantite` DECIMAL(10,2) DEFAULT 0,
                `calories_calculees` DECIMAL(10,2) DEFAULT 0,
                `date_consommation` DATE NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        foreach ([
            'eau_ml' => "DECIMAL(10,2) DEFAULT 0",
            'is_water' => "TINYINT(1) DEFAULT 0",
            'is_demo' => "TINYINT(1) DEFAULT 0",
            'sucre_g' => "DECIMAL(10,2) DEFAULT 0",
        ] as $column => $definition) {
            self::addColumnIfMissing(self::$connection, 'repas_consomme', $column, $definition);
        }

        self::createTableIfMissing(self::$connection, 'water_logs', "
            CREATE TABLE IF NOT EXISTS `water_logs` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NULL,
                `amount_ml` DECIMAL(10,2) DEFAULT 0,
                `logged_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        self::createTableIfMissing(self::$connection, 'hydration_logs', "
            CREATE TABLE IF NOT EXISTS `hydration_logs` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NULL,
                `amount_ml` DECIMAL(10,2) DEFAULT 0,
                `logged_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        self::createTableIfMissing(self::$connection, 'chrono_profiles', "
            CREATE TABLE IF NOT EXISTS `chrono_profiles` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NULL,
                `chronotype` VARCHAR(50) DEFAULT 'standard',
                `wake_time` TIME NULL,
                `sleep_time` TIME NULL,
                `sleep_quality` VARCHAR(50) NULL,
                `energy_peak` VARCHAR(50) NULL,
                `energy_dip` VARCHAR(50) NULL,
                `workout_time` VARCHAR(50) NULL,
                `last_caffeine_time` VARCHAR(50) NULL,
                `preferred_meals_count` INT DEFAULT 3,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        self::createTableIfMissing(self::$connection, 'chrono_recommendations', "
            CREATE TABLE IF NOT EXISTS `chrono_recommendations` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NULL,
                `recommendation_type` VARCHAR(50) NULL,
                `payload` LONGTEXT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        self::createTableIfMissing(self::$connection, 'nutrition_predictions', "
            CREATE TABLE IF NOT EXISTS `nutrition_predictions` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NULL,
                `prediction_type` VARCHAR(50) NULL,
                `payload` LONGTEXT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    private static function ensureCommunitySchema(): void
    {
        $tables = [
            'posts' => "
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
            ",
            'comments' => "
                CREATE TABLE IF NOT EXISTS `comments` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `post_id` INT NOT NULL,
                    `parent_comment_id` INT NULL,
                    `user_id` INT NOT NULL,
                    `comment_text` TEXT NOT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            'comment_reactions' => "
                CREATE TABLE IF NOT EXISTS `comment_reactions` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `comment_id` INT NOT NULL,
                    `user_id` INT NOT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY `uniq_comment_user_like` (`comment_id`, `user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            'post_reactions' => "
                CREATE TABLE IF NOT EXISTS `post_reactions` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `post_id` INT NOT NULL,
                    `user_id` INT NOT NULL,
                    `reaction_type` VARCHAR(20) NOT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY `uniq_post_user_reaction` (`post_id`, `user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ",
            'post_reports' => "
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
            ",
            'content_moderation' => "
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
            ",
            'image_moderation' => "
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
            ",
            'moderation_jobs' => "
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
            ",
            'news_articles' => "
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
            ",
            'notifications' => "
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
            ",
        ];

        foreach ($tables as $name => $sql) {
            self::createTableIfMissing(self::$connection, $name, $sql);
        }
    }

    private static function ensureRecipeSchema(): void
    {
        self::createTableIfMissing(self::$connection, 'recettes', "
            CREATE TABLE IF NOT EXISTS `recettes` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `nom` VARCHAR(255) NOT NULL,
                `description` TEXT NULL,
                `temps_preparation` INT NULL,
                `niveau_difficulte` VARCHAR(50) NULL,
                `image_url` VARCHAR(500) NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        self::createTableIfMissing(self::$connection, 'recette_aliment', "
            CREATE TABLE IF NOT EXISTS `recette_aliment` (
                `id_recette` INT NOT NULL,
                `id_aliment` INT NOT NULL,
                `quantite` FLOAT DEFAULT 0,
                PRIMARY KEY (`id_recette`, `id_aliment`),
                KEY `idx_recette_aliment_aliment` (`id_aliment`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        self::addIndexIfMissing(self::$connection, 'recette_aliment', 'idx_recette_aliment_aliment', 'INDEX `idx_recette_aliment_aliment` (`id_aliment`)');

        self::createTableIfMissing(self::$connection, 'recommandations', "
            CREATE TABLE IF NOT EXISTS `recommandations` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `titre` VARCHAR(255) NOT NULL,
                `type_objectif` VARCHAR(100) NOT NULL,
                `contenu_regle` TEXT NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        if (self::tableExists(self::$connection, 'recommandation')) {
            $targetCount = (int) self::$connection->query('SELECT COUNT(*) FROM `recommandations`')->fetchColumn();
            if ($targetCount === 0
                && self::columnExists(self::$connection, 'recommandation', 'titre')
                && self::columnExists(self::$connection, 'recommandation', 'type_objectif')
                && self::columnExists(self::$connection, 'recommandation', 'contenu_regle')) {
                self::$connection->exec('INSERT INTO `recommandations` (`titre`, `type_objectif`, `contenu_regle`) SELECT `titre`, `type_objectif`, `contenu_regle` FROM `recommandation`');
            }
        }
    }

    private static function ensureProductSchema(): void
    {
        self::createTableIfMissing(self::$connection, 'produit', "
            CREATE TABLE IF NOT EXISTS `produit` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(255) DEFAULT NULL,
                `description` TEXT DEFAULT NULL,
                `price` DECIMAL(10,2) DEFAULT NULL,
                `calories` INT DEFAULT NULL,
                `image` VARCHAR(255) DEFAULT NULL,
                `added_by` VARCHAR(100) DEFAULT NULL,
                `is_approved` TINYINT(1) DEFAULT 1,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        self::createTableIfMissing(self::$connection, 'commande', "
            CREATE TABLE IF NOT EXISTS `commande` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `product_id` INT DEFAULT NULL,
                `buyer_name` VARCHAR(100) DEFAULT NULL,
                `buyer_phone` VARCHAR(20) DEFAULT NULL,
                `buyer_address` TEXT DEFAULT NULL,
                `quantity` INT DEFAULT NULL,
                `total_price` DECIMAL(10,2) DEFAULT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `buyer_email` VARCHAR(255) DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `product_id` (`product_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        self::createTableIfMissing(self::$connection, 'commande_item', "
            CREATE TABLE IF NOT EXISTS `commande_item` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `commande_id` INT NOT NULL,
                `product_id` INT NOT NULL,
                `quantity` INT NOT NULL DEFAULT 1,
                `unit_price` DECIMAL(10,2) NOT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_commande_id` (`commande_id`),
                KEY `idx_product_id` (`product_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
    }

    private static function ensureActivitySchema(): void
    {
        self::createTableIfMissing(self::$connection, 'activite', "
            CREATE TABLE IF NOT EXISTS `activite` (
                `id_activite` INT AUTO_INCREMENT PRIMARY KEY,
                `nom_activite` VARCHAR(255) NOT NULL,
                `description` TEXT NULL,
                `duree_minutes` INT DEFAULT 0,
                `calories_brulees` INT DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        self::createTableIfMissing(self::$connection, 'exercice', "
            CREATE TABLE IF NOT EXISTS `exercice` (
                `id_exercice` INT AUTO_INCREMENT PRIMARY KEY,
                `id_activite` INT NOT NULL,
                `nom_exercice` VARCHAR(255) NOT NULL,
                `description` TEXT NULL,
                `series` INT DEFAULT 0,
                `repetitions` INT DEFAULT 0,
                `duree_secondes` INT DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
}
