<?php
require_once __DIR__ . '/model/Database.php';

try {
    $conn = Database::getConnection();
    
    $query = "SHOW COLUMNS FROM exercice LIKE 'muscle_principal'";
    $stmt = $conn->query($query);
    if ($stmt->rowCount() == 0) {
        $sql = "ALTER TABLE exercice 
            ADD COLUMN muscle_principal VARCHAR(100) NOT NULL,
            ADD COLUMN muscle_secondaire VARCHAR(100) DEFAULT NULL,
            ADD COLUMN niveau_difficulte VARCHAR(50) NOT NULL,
            ADD COLUMN calories_estimees INT NOT NULL;";
        $conn->exec($sql);
        echo "Database updated successfully.\n";
    } else {
        echo "Database already up to date.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
