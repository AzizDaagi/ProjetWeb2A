<?php
/**
 * Script de migration : change le type de la colonne quantite en FLOAT.
 * Accès : http://localhost/projetwebmalek/model/migrate_quantite.php
 * À supprimer après exécution.
 */
require_once '../controler/config.php';
$pdo = Config::getConnexion();

try {
    $pdo->exec("ALTER TABLE recette_aliment MODIFY COLUMN quantite FLOAT DEFAULT 0");
    echo "<p style='color:green;font-family:monospace;'>✅ Migration réussie : colonne <strong>quantite</strong> convertie en FLOAT.</p>";
} catch (PDOException $e) {
    echo "<p style='color:red;font-family:monospace;'>❌ Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
