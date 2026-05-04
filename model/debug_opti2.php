<?php
/**
 * DEBUG COMPLET - Optimisation recette
 * Accès : http://localhost/projetwebmalek/model/debug_opti2.php
 */
require_once '../controler/config.php';

$pdo = Config::getConnexion();

echo "<style>body{font-family:monospace;padding:20px;background:#1a1a2e;color:#eee;}
h2{color:#2ecc71;border-bottom:1px solid #2ecc71;padding-bottom:6px;}
.ok{color:#2ecc71;} .err{color:#e74c3c;} .warn{color:#f39c12;}
pre{background:#0d0d1a;padding:12px;border-radius:6px;overflow:auto;}
table{border-collapse:collapse;width:100%;}
th,td{border:1px solid #333;padding:8px;text-align:left;}
th{background:#2ecc7122;}
</style>";

echo "<h1>🔍 Diagnostic - Optimisation Recette</h1>";

// ============================================================
// 1. ÉTAT DE LA TABLE recette_aliment
// ============================================================
echo "<h2>1. Structure de la table recette_aliment</h2>";
$cols = $pdo->query("SHOW COLUMNS FROM recette_aliment")->fetchAll(PDO::FETCH_ASSOC);
echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
foreach ($cols as $c) {
    $highlight = $c['Field'] === 'quantite' ? ' style="background:#2ecc7133;"' : '';
    echo "<tr$highlight><td>{$c['Field']}</td><td>{$c['Type']}</td><td>{$c['Null']}</td><td>{$c['Key']}</td><td>{$c['Default']}</td></tr>";
}
echo "</table>";

// ============================================================
// 2. CONTENU ACTUEL
// ============================================================
echo "<h2>2. Contenu actuel de recette_aliment</h2>";
$rows = $pdo->query("SELECT ra.*, r.nom as nom_recette, a.nom as nom_aliment 
                     FROM recette_aliment ra
                     JOIN recettes r ON r.id = ra.id_recette
                     JOIN aliments a ON a.id = ra.id_aliment
                     LIMIT 30")->fetchAll(PDO::FETCH_ASSOC);

if (empty($rows)) {
    echo "<p class='err'>❌ Aucune ligne dans recette_aliment !</p>";
} else {
    echo "<table><tr><th>id_recette</th><th>nom_recette</th><th>id_aliment</th><th>nom_aliment</th><th>quantite</th></tr>";
    foreach ($rows as $r) {
        echo "<tr><td>{$r['id_recette']}</td><td>{$r['nom_recette']}</td><td>{$r['id_aliment']}</td><td>{$r['nom_aliment']}</td><td>{$r['quantite']}</td></tr>";
    }
    echo "</table>";
}

// ============================================================
// 3. TEST UPDATE DIRECT (sans passer par le controller)
// ============================================================
echo "<h2>3. Test UPDATE direct sur la première ligne</h2>";
if (!empty($rows)) {
    $test = $rows[0];
    $oldQte = $test['quantite'];
    $newQte = 777.0;
    
    echo "<p>Cible : id_recette=<b>{$test['id_recette']}</b>, id_aliment=<b>{$test['id_aliment']}</b></p>";
    echo "<p>Ancienne quantite: <b>$oldQte</b> → Nouvelle: <b>$newQte</b></p>";
    
    $stmt = $pdo->prepare("UPDATE recette_aliment SET quantite = :qte WHERE id_recette = :id_recette AND id_aliment = :id_aliment");
    $ok = $stmt->execute([
        'qte'        => (float)$newQte,
        'id_recette' => (int)$test['id_recette'],
        'id_aliment' => (int)$test['id_aliment']
    ]);
    $affected = $stmt->rowCount();
    
    echo "<p>Execute: " . ($ok ? "<span class='ok'>✅ OUI</span>" : "<span class='err'>❌ NON</span>") . "</p>";
    echo "<p>Lignes affectées: <b class='" . ($affected > 0 ? 'ok' : 'err') . "'>$affected</b></p>";
    
    // Vérification immédiate
    $check = $pdo->prepare("SELECT quantite FROM recette_aliment WHERE id_recette = ? AND id_aliment = ?");
    $check->execute([(int)$test['id_recette'], (int)$test['id_aliment']]);
    $after = $check->fetch(PDO::FETCH_ASSOC);
    $afterQte = $after['quantite'] ?? 'NULL';
    
    if ((float)$afterQte === (float)$newQte) {
        echo "<p class='ok'>✅ Valeur confirmée en DB: $afterQte</p>";
    } else {
        echo "<p class='err'>❌ Valeur en DB: $afterQte (attendu: $newQte) — L'UPDATE n'a PAS fonctionné !</p>";
    }
    
    // Restauration
    $pdo->prepare("UPDATE recette_aliment SET quantite = :qte WHERE id_recette = :id_recette AND id_aliment = :id_aliment")
        ->execute(['qte' => $oldQte, 'id_recette' => (int)$test['id_recette'], 'id_aliment' => (int)$test['id_aliment']]);
    echo "<p class='warn'>⚠️ Valeur restaurée à: $oldQte</p>";
    
} else {
    echo "<p class='err'>Pas de données à tester.</p>";
}

// ============================================================
// 4. SIMULER exactement le traitement du formulaire POST
// ============================================================
echo "<h2>4. Simulation du traitement POST (comme le bouton Enregistrer)</h2>";
if (!empty($rows)) {
    $firstRow = $rows[0];
    
    // Simuler $_POST['nouvelles_quantites'] tel qu'il vient du formulaire HTML
    // (clés string, valeurs string)
    $simulatedPost = [
        (string)$firstRow['id_aliment'] => "888"   // string key, string value
    ];
    
    echo "<p>Simulation POST avec: <code>" . json_encode($simulatedPost) . "</code></p>";
    
    $id_recette_str = (string)$firstRow['id_recette']; // string comme en POST
    
    // Cast comme dans le code corrigé
    $nouvellesQuantitesCastes = [];
    foreach ($simulatedPost as $al_id => $qte) {
        $al_id_int = (int)$al_id;
        $qte_float = (float)$qte;
        if ($al_id_int > 0) {
            $nouvellesQuantitesCastes[$al_id_int] = $qte_float;
        }
    }
    echo "<p>Après cast: <code>" . json_encode($nouvellesQuantitesCastes) . "</code></p>";
    
    $stmt2 = $pdo->prepare("UPDATE recette_aliment SET quantite = :qte WHERE id_recette = :id_recette AND id_aliment = :id_aliment");
    $ok2 = $stmt2->execute([
        'qte'        => reset($nouvellesQuantitesCastes),
        'id_recette' => (int)$id_recette_str,
        'id_aliment' => key($nouvellesQuantitesCastes)
    ]);
    $affected2 = $stmt2->rowCount();
    
    echo "<p>Résultat: " . ($ok2 ? "<span class='ok'>✅ Execute OK</span>" : "<span class='err'>❌ Execute FAIL</span>") . ", lignes affectées: <b class='" . ($affected2 > 0 ? 'ok' : 'err') . "'>$affected2</b></p>";
    
    // Check
    $check2 = $pdo->prepare("SELECT quantite FROM recette_aliment WHERE id_recette = ? AND id_aliment = ?");
    $check2->execute([(int)$firstRow['id_recette'], (int)$firstRow['id_aliment']]);
    $after2 = $check2->fetch(PDO::FETCH_ASSOC);
    echo "<p>Valeur en DB après simulation: <b>" . ($after2['quantite'] ?? 'NULL') . "</b></p>";
    
    // Restauration
    $pdo->prepare("UPDATE recette_aliment SET quantite = :qte WHERE id_recette = :id_recette AND id_aliment = :id_aliment")
        ->execute(['qte' => $firstRow['quantite'], 'id_recette' => (int)$firstRow['id_recette'], 'id_aliment' => (int)$firstRow['id_aliment']]);
    echo "<p class='warn'>⚠️ Valeur restaurée.</p>";
}

// ============================================================
// 5. VÉRIFIER output_buffering
// ============================================================
echo "<h2>5. Configuration PHP pertinente</h2>";
echo "<table><tr><th>Paramètre</th><th>Valeur</th></tr>";
$params = ['output_buffering', 'session.use_cookies', 'display_errors', 'error_reporting'];
foreach ($params as $p) {
    $v = ini_get($p);
    echo "<tr><td>$p</td><td>$v</td></tr>";
}
echo "</table>";

echo "<h2>6. Test ob_start / header</h2>";
echo "<p>ob_get_level(): <b>" . ob_get_level() . "</b> (si > 0, les headers peuvent être envoyés)</p>";

echo "<p class='ok' style='margin-top:30px;font-size:18px;'>✅ Diagnostic terminé — lisez les résultats ci-dessus.</p>";
?>
