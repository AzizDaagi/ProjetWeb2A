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
            $dbName = trim((string) ($_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'projetwebmalek_db'));
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
            self::ensureUserRoleColumn();
            self::ensureUserProfileColumns();
            self::ensureUserResetColumns();
            self::ensureUserFaceColumns();
            self::$schemaChecked = true;
        }

        return self::$connection;
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

}
