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

$host = trim((string) ($_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1'));
$port = (int) ($_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: 3306);
$dbName = trim((string) ($_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'smart_nutrition'));
$user = trim((string) ($_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root'));
$password = (string) ($_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '');

$pdo = new PDO(
    sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $dbName),
    $user,
    $password,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);

function recetteTableExists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name');
    $stmt->execute(['table_name' => $table]);
    return (int) $stmt->fetchColumn() > 0;
}

function recetteColumnExists(PDO $pdo, string $table, string $column): bool
{
    if (!recetteTableExists($pdo, $table)) {
        return false;
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name AND COLUMN_NAME = :column_name');
    $stmt->execute([
        'table_name' => $table,
        'column_name' => $column,
    ]);
    return (int) $stmt->fetchColumn() > 0;
}

function recetteIndexExists(PDO $pdo, string $table, string $index): bool
{
    if (!recetteTableExists($pdo, $table)) {
        return false;
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name AND INDEX_NAME = :index_name');
    $stmt->execute([
        'table_name' => $table,
        'index_name' => $index,
    ]);
    return (int) $stmt->fetchColumn() > 0;
}

function recetteCreateTable(PDO $pdo, string $sql): void
{
    $pdo->exec($sql);
}

function recetteAddColumnIfMissing(PDO $pdo, string $table, string $column, string $definition): void
{
    if (!recetteColumnExists($pdo, $table, $column)) {
        $pdo->exec(sprintf('ALTER TABLE `%s` ADD COLUMN `%s` %s', $table, $column, $definition));
    }
}

function recetteAddIndexIfMissing(PDO $pdo, string $table, string $index, string $definition): void
{
    if (!recetteTableExists($pdo, $table) || recetteIndexExists($pdo, $table, $index)) {
        return;
    }

    $pdo->exec(sprintf('ALTER TABLE `%s` ADD %s', $table, $definition));
}

recetteCreateTable($pdo, "
    CREATE TABLE IF NOT EXISTS `recettes` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nom` VARCHAR(255) NOT NULL,
        `description` TEXT NULL,
        `temps_preparation` VARCHAR(100) NULL,
        `niveau_difficulte` VARCHAR(100) NULL,
        `image_url` VARCHAR(500) NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

recetteCreateTable($pdo, "
    CREATE TABLE IF NOT EXISTS `recette_aliment` (
        `id_recette` INT NOT NULL,
        `id_aliment` INT NOT NULL,
        `quantite` FLOAT DEFAULT 0,
        PRIMARY KEY (`id_recette`, `id_aliment`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

recetteAddIndexIfMissing($pdo, 'recette_aliment', 'idx_recette_aliment_aliment', 'INDEX `idx_recette_aliment_aliment` (`id_aliment`)');

recetteCreateTable($pdo, "
    CREATE TABLE IF NOT EXISTS `recommandations` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `titre` VARCHAR(255) NOT NULL,
        `type_objectif` VARCHAR(100) NOT NULL,
        `contenu_regle` TEXT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

if (recetteTableExists($pdo, 'recommandation') && recetteTableExists($pdo, 'recommandations')) {
    $targetCount = (int) $pdo->query('SELECT COUNT(*) FROM `recommandations`')->fetchColumn();
    if ($targetCount === 0
        && recetteColumnExists($pdo, 'recommandation', 'titre')
        && recetteColumnExists($pdo, 'recommandation', 'type_objectif')
        && recetteColumnExists($pdo, 'recommandation', 'contenu_regle')) {
        $pdo->exec('INSERT INTO `recommandations` (`titre`, `type_objectif`, `contenu_regle`) SELECT `titre`, `type_objectif`, `contenu_regle` FROM `recommandation`');
    }
}

recetteCreateTable($pdo, "
    CREATE TABLE IF NOT EXISTS `aliments` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nom` VARCHAR(255) NOT NULL,
        `calories` FLOAT DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

foreach ([
    'type' => "VARCHAR(100) NULL",
    'proteines' => "FLOAT DEFAULT 0",
    'glucides' => "FLOAT DEFAULT 0",
    'lipides' => "FLOAT DEFAULT 0",
    'unite' => "VARCHAR(50) DEFAULT 'g'",
    'sucre_g' => "FLOAT DEFAULT 0",
    'fibres' => "FLOAT DEFAULT 0",
    'image_url' => "VARCHAR(500) NULL",
] as $column => $definition) {
    recetteAddColumnIfMissing($pdo, 'aliments', $column, $definition);
}

echo "Migration recettes integration terminee." . PHP_EOL;
