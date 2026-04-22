<?php
require_once __DIR__ . '/model/Database.php';

try {
    $conn = Database::getConnection();
    
    $query = "
    CREATE TABLE IF NOT EXISTS nutrition_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        current_weight FLOAT NOT NULL,
        current_goal ENUM('lose weight', 'gain muscle', 'maintain weight') NOT NULL,
        height FLOAT DEFAULT NULL,
        message TEXT,
        generated_activities TEXT,
        generated_exercises TEXT,
        selected_exercises TEXT,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );";
    
    $conn->exec($query);
    echo "TABLE CREATED SUCCESSFULLY";
} catch(PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
