<?php
require_once __DIR__ . '/Database.php';

try {
    $pdo = Database::getConnection();
} catch (Exception $e) {
    fwrite(STDERR, "DB connection failed: " . $e->getMessage() . "\n");
    exit(1);
}

$columns = [
    ['name' => 'password_reset_token', 'definition' => 'VARCHAR(255) NULL AFTER `password`'],
    ['name' => 'password_reset_expires', 'definition' => 'DATETIME NULL AFTER `password_reset_token`'],
];

foreach ($columns as $col) {
    $stmt = $pdo->prepare('SHOW COLUMNS FROM users LIKE :name');
    $stmt->execute(['name' => $col['name']]);
    if ($stmt->fetch()) {
        echo "Column {$col['name']} already exists.\n";
        continue;
    }

    $sql = "ALTER TABLE users ADD COLUMN `{$col['name']}` {$col['definition']}";
    try {
        $pdo->exec($sql);
        echo "Added column {$col['name']}.\n";
    } catch (Exception $e) {
        fwrite(STDERR, "Failed to add {$col['name']}: " . $e->getMessage() . "\n");
        exit(2);
    }
}

echo "Done.\n";
