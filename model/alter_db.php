<?php
require_once '../controler/config.php';
try {
    $pdo = Config::getConnexion();
    $pdo->exec("ALTER TABLE aliments ADD COLUMN IF NOT EXISTS fibres FLOAT NOT NULL DEFAULT 0.0");
    echo "Success: Column fibres added.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
