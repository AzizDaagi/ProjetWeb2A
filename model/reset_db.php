<?php
try {
    // 1. Connexion sans preciser la base (pour pouvoir la supprimer)
    $pdo = new PDO(
        'mysql:host=127.0.0.1', 
        'root', 
        '', 
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // 2. Suppression globale et re-creation (ceci regle l'erreur 1932 de XAMPP)
    $pdo->exec("DROP DATABASE IF EXISTS projetwebmalek_db;");
    $pdo->exec("CREATE DATABASE projetwebmalek_db;");
    $pdo->exec("USE projetwebmalek_db;");
    
    // 3. Importation du fichier database.sql
    $sql = file_get_contents(__DIR__ . '/database.sql');
    $pdo->exec($sql);
    
    echo "<div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>";
    echo "<h1 style='color: green;'>✅ Base de données réparée !</h1>";
    echo "<p>Le problème de la table <b>recettes</b> (Erreur 1932) a été causé par une corruption de XAMPP.</p>";
    echo "<p>J'ai réinitialisé la base et recréé toutes vos tables à partir de zéro.</p>";
    echo "<p>Vous pouvez maintenant retourner sur votre page !</p>";
    echo "<a href='../view/backoffice/manage_recettes.php' style='display:inline-block; padding:10px 20px; background:#007BFF; color:white; text-decoration:none; border-radius:5px;'>Aller à la gestion des recettes</a>";
    echo "</div>";
} catch (PDOException $e) {
    echo "<h1>Erreur lors de la réparation</h1><p>" . $e->getMessage() . "</p>";
}
?>
