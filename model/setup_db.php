<?php
require_once '../controler/config.php';

try {
    $pdo = Config::getConnexion();
    $sql = file_get_contents(__DIR__ . '/database.sql');
    
    // Execute the SQL script
    $pdo->exec($sql);
    echo "<h1>Succès !</h1><p>Les tables de la base de données (incluant 'aliments' et 'recettes') ont été créées avec succès.</p>";
} catch (PDOException $e) {
    echo "<h1>Erreur</h1><p>" . $e->getMessage() . "</p>";
}
?>
