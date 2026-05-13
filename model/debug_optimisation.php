<?php
/**
 * Debug : vérifie le fonctionnement de appliquerOptimisation
 * Accès : http://localhost/projetwebmalek/model/debug_optimisation.php
 * À supprimer après diagnostic.
 */
require_once '../controler/config.php';
require_once '../controler/RecetteController.php';

$pdo = Config::getConnexion();

// 1. Vérifier le type de la colonne quantite
$col = $pdo->query("SHOW COLUMNS FROM recette_aliment LIKE 'quantite'")->fetch(PDO::FETCH_ASSOC);
echo "<h2>1. Type colonne quantite</h2>";
echo "<pre>" . print_r($col, true) . "</pre>";

// 2. Voir le contenu actuel de recette_aliment
echo "<h2>2. Contenu actuel recette_aliment</h2>";
$rows = $pdo->query("SELECT * FROM recette_aliment LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>" . print_r($rows, true) . "</pre>";

// 3. Tester un UPDATE manuel
echo "<h2>3. Test UPDATE manuel</h2>";
if (!empty($rows)) {
    $test = $rows[0];
    $newQte = 999;
    $stmt = $pdo->prepare("UPDATE recette_aliment SET quantite = :qte WHERE id_recette = :id_recette AND id_aliment = :id_aliment");
    $ok = $stmt->execute(['qte' => $newQte, 'id_recette' => $test['id_recette'], 'id_aliment' => $test['id_aliment']]);
    $affected = $stmt->rowCount();
    echo "<p>Execute OK: " . ($ok ? 'OUI' : 'NON') . "</p>";
    echo "<p>Lignes affectées: $affected</p>";

    // Vérifier que la valeur est bien mise à jour
    $check = $pdo->prepare("SELECT quantite FROM recette_aliment WHERE id_recette = ? AND id_aliment = ?");
    $check->execute([$test['id_recette'], $test['id_aliment']]);
    $after = $check->fetch(PDO::FETCH_ASSOC);
    echo "<p>Valeur après UPDATE: " . $after['quantite'] . " (attendu: $newQte)</p>";
    
    // Remettre à l'ancienne valeur
    $restore = $pdo->prepare("UPDATE recette_aliment SET quantite = :qte WHERE id_recette = :id_recette AND id_aliment = :id_aliment");
    $restore->execute(['qte' => $test['quantite'], 'id_recette' => $test['id_recette'], 'id_aliment' => $test['id_aliment']]);
    echo "<p style='color:green'>Valeur restaurée à: " . $test['quantite'] . "</p>";
} else {
    echo "<p style='color:red'>Aucune ligne dans recette_aliment — ajoutez d'abord des recettes avec des ingrédients.</p>";
}

// 4. Vérifier si la migration a été faite
echo "<h2>4. Résultat migration (FLOAT ?)</h2>";
if ($col && $col['Type'] !== 'float') {
    echo "<p style='color:red'>❌ Colonne encore en <b>" . $col['Type'] . "</b> — migration NON appliquée.</p>";
    echo "<p>Exécution de la migration maintenant...</p>";
    try {
        $pdo->exec("ALTER TABLE recette_aliment MODIFY COLUMN quantite FLOAT DEFAULT 0");
        echo "<p style='color:green'>✅ Migration appliquée avec succès !</p>";
    } catch (Exception $e) {
        echo "<p style='color:red'>Erreur: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color:green'>✅ Colonne en FLOAT — migration OK.</p>";
}
?>
