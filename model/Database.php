<?php

class Database
{
    private static $connection = null;
    private static $schemaChecked = false;

    public static function getConnection()
    {
        if (self::$connection === null) {
            $host = getenv('DB_HOST') ?: '127.0.0.1';
            $port = (int) (getenv('DB_PORT') ?: 3306);
            $dbName = getenv('DB_NAME') ?: 'smart_nutrition';
            $username = getenv('DB_USER') ?: 'root';
            $password = getenv('DB_PASSWORD') ?: '';
            $charset = 'utf8mb4';
            $timeout = (int) (getenv('DB_CONNECT_TIMEOUT') ?: 5);
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
                http_response_code(503);
                die('Database connection error: ' . $e->getMessage());
            }
        }

        if (!self::$schemaChecked) {
            self::ensureUserRoleColumn();
            self::ensureUserProfileColumns();
            self::ensureUserResetColumns();
            self::ensureUserFaceColumns();
            self::ensureNutritionRequestUserIdColumn();
            self::ensureProductSchema();
            self::$schemaChecked = true;
        }

        return self::$connection;
    }

    private static function ensureNutritionRequestUserIdColumn()
    {
        $stmt = self::$connection->query("SHOW COLUMNS FROM nutrition_requests LIKE 'user_id'");
        $column = $stmt->fetch();

        if (!$column) {
            self::$connection->exec("ALTER TABLE nutrition_requests ADD COLUMN `user_id` INT NULL AFTER `id`, ADD INDEX (`user_id`) ");
        }
    }

    private static function ensureUserRoleColumn()
    {
        $stmt = self::$connection->query("SHOW COLUMNS FROM users LIKE 'role'");
        $column = $stmt->fetch();

        if (!$column) {
            self::$connection->exec("ALTER TABLE users ADD COLUMN `role` VARCHAR(50) NOT NULL DEFAULT 'user' AFTER `password`");
            return;
        }

        $type = strtolower((string) ($column['Type'] ?? ''));
        if (strpos($type, 'varchar') === false) {
            self::$connection->exec("ALTER TABLE users MODIFY COLUMN `role` VARCHAR(50) NOT NULL DEFAULT 'user'");
        }
    }

    private static function ensureUserProfileColumns()
    {
        $profileColumns = [
            ['name' => 'date_naissance', 'definition' => 'DATE NULL AFTER `prenom`'],
            ['name' => 'sexe', 'definition' => 'VARCHAR(10) NULL AFTER `date_naissance`'],
            ['name' => 'age', 'definition' => 'INT NULL AFTER `date_naissance`'],
            ['name' => 'poids', 'definition' => 'DECIMAL(5,2) NULL AFTER `age`'],
            ['name' => 'taille', 'definition' => 'DECIMAL(5,2) NULL AFTER `poids`'],
            ['name' => 'objectif', 'definition' => 'VARCHAR(255) NULL AFTER `taille`'],
        ];

        foreach ($profileColumns as $column) {
            $stmt = self::$connection->prepare('SHOW COLUMNS FROM users LIKE :column_name');
            $stmt->execute(['column_name' => $column['name']]);

            if (!$stmt->fetch()) {
                self::$connection->exec("ALTER TABLE users ADD COLUMN `{$column['name']}` {$column['definition']}");
            }
        }
    }

    private static function ensureUserFaceColumns()
    {
        $faceColumns = [
            ['name' => 'face_descriptor', 'definition' => 'LONGTEXT NULL AFTER `role`'],
            ['name' => 'face_updated_at', 'definition' => 'DATETIME NULL AFTER `face_descriptor`'],
        ];

        foreach ($faceColumns as $column) {
            $stmt = self::$connection->prepare('SHOW COLUMNS FROM users LIKE :column_name');
            $stmt->execute(['column_name' => $column['name']]);

            if (!$stmt->fetch()) {
                self::$connection->exec("ALTER TABLE users ADD COLUMN `{$column['name']}` {$column['definition']}");
            }
        }
    }

    private static function ensureUserResetColumns()
    {
        $resetColumns = [
            ['name' => 'password_reset_token', 'definition' => 'VARCHAR(255) NULL AFTER `password`'],
            ['name' => 'password_reset_expires', 'definition' => 'DATETIME NULL AFTER `password_reset_token`'],
        ];

        foreach ($resetColumns as $column) {
            $stmt = self::$connection->prepare('SHOW COLUMNS FROM users LIKE :column_name');
            $stmt->execute(['column_name' => $column['name']]);

            if (!$stmt->fetch()) {
                self::$connection->exec("ALTER TABLE users ADD COLUMN `{$column['name']}` {$column['definition']}");
            }
        }
    }

    private static function ensureProductSchema()
    {
        self::$connection->exec("
            CREATE TABLE IF NOT EXISTS produit (
                id INT NOT NULL AUTO_INCREMENT,
                name VARCHAR(255) DEFAULT NULL,
                description TEXT DEFAULT NULL,
                price DECIMAL(10,2) DEFAULT NULL,
                calories INT DEFAULT NULL,
                image VARCHAR(255) DEFAULT NULL,
                added_by VARCHAR(100) DEFAULT NULL,
                is_approved TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        self::$connection->exec("
            CREATE TABLE IF NOT EXISTS commande (
                id INT NOT NULL AUTO_INCREMENT,
                product_id INT DEFAULT NULL,
                buyer_name VARCHAR(100) DEFAULT NULL,
                buyer_phone VARCHAR(20) DEFAULT NULL,
                buyer_address TEXT DEFAULT NULL,
                quantity INT DEFAULT NULL,
                total_price DECIMAL(10,2) DEFAULT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                buyer_email VARCHAR(255) DEFAULT NULL,
                PRIMARY KEY (id),
                KEY product_id (product_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        self::ensureColumn('commande', 'buyer_email', 'ALTER TABLE commande ADD COLUMN buyer_email VARCHAR(255) DEFAULT NULL');

        self::$connection->exec("
            CREATE TABLE IF NOT EXISTS commande_item (
                id INT NOT NULL AUTO_INCREMENT,
                commande_id INT NOT NULL,
                product_id INT NOT NULL,
                quantity INT NOT NULL DEFAULT 1,
                unit_price DECIMAL(10,2) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_commande_id (commande_id),
                KEY idx_product_id (product_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
    }

    private static function ensureColumn(string $table, string $column, string $alterSql): void
    {
        $stmt = self::$connection->prepare("SHOW COLUMNS FROM `$table` LIKE :column_name");
        $stmt->execute(['column_name' => $column]);

        if (!$stmt->fetch()) {
            self::$connection->exec($alterSql);
        }
    }

}
