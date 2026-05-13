<?php
/**
 * Force la migration de quantite en FLOAT.
 * Accès : http://localhost/projetwebmalek/model/force_migrate.php
 */
require_once '../controler/config.php';
$pdo = Config::getConnexion();

try {
    // Étape 1 : Changer le type de la colonne (force avec CAST des données existantes)
    $pdo->exec("ALTER TABLE recette_aliment MODIFY COLUMN quantite FLOAT NOT NULL DEFAULT 0");
    echo "<p style='font-family:monospace;color:green'>✅ Étape 1 : Colonne quantite convertie en FLOAT.</p>";
    
    // Étape 2 : Vérifier
    $col = $pdo->query("SHOW COLUMNS FROM recette_aliment LIKE 'quantite'")->fetch(PDO::FETCH_ASSOC);
    echo "<p style='font-family:monospace;'>Type actuel : <strong>" . $col['Type'] . "</strong></p>";
    echo "<pre>" . print_r($col, true) . "</pre>";
    
    echo "<p style='font-family:monospace;color:green;font-size:18px;'>✅ Migration terminée avec succès ! Vous pouvez tester l'optimisation.</p>";
    echo "<p><a href='http://localhost/projetwebmalek/view/frontoffice/liste_recettes.php'>→ Retour au site</a></p>";
    
} catch (PDOException $e) {
    echo "<h2 style='color:red'>Erreur lors de la migration :</h2>";
    echo "<pre style='color:red'>" . htmlspecialchars($e->getMessage()) . "</pre>";
    
    // Tentative avec CAST explicite
    echo "<p>Tentative alternative...</p>";
    try {
        $pdo->exec("ALTER TABLE recette_aliment CHANGE quantite quantite FLOAT NOT NULL DEFAULT 0");
        echo "<p style='color:green'>✅ Migration alternative réussie !</p>";
    } catch (PDOException $e2) {
        echo "<pre style='color:red'>" . htmlspecialchars($e2->getMessage()) . "</pre>";
    }
}
?>
