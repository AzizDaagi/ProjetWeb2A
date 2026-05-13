<?php
/**
 * Export PDF – Smart Nutrition
 * Usage :
 *   export_pdf.php?type=liste
 *   export_pdf.php?type=recette&id=5
 *   export_pdf.php?type=statistiques
 */
require_once __DIR__ . '/../../controller/RecetteController.php';
require_once __DIR__ . '/../../controller/AlimentController.php';

$controller      = new RecetteController();
$alimentCtrl     = new AlimentController();

$type = $_GET['type'] ?? 'liste';
$id   = isset($_GET['id']) ? (int)$_GET['id'] : null;

// --- Chargement des données selon le type ---
$recettes    = [];
$recette     = null;
$aliments_r  = [];
$nutrition   = [];
$stats       = null;
$pageTitle   = 'Smart Nutrition – Export PDF';

if ($type === 'liste') {
    $recettes  = $controller->listRecettes();
    $pageTitle = 'Liste des Recettes – Smart Nutrition';
    foreach ($recettes as &$r) {
        $r['aliments'] = $controller->getAlimentsByRecette($r['id']);
        $r['nutrition'] = $controller->calculerNutritionTotale($r['id']);
    }
    unset($r);

} elseif ($type === 'recette' && $id) {
    $recette    = $controller->getRecette($id);
    $aliments_r = $controller->getAlimentsByRecette($id);
    $nutrition  = $controller->calculerNutritionTotale($id);
    $pageTitle  = ($recette['nom'] ?? 'Recette') . ' – Smart Nutrition';

} elseif ($type === 'statistiques') {
    $stats     = $controller->getStatistiquesNutritionnelles();
    $pageTitle = 'Statistiques Nutritionnelles – Smart Nutrition';
}

$exportDate = date('d/m/Y à H:i');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($pageTitle) ?></title>
<style>
  /* =========================================================
     RESET & BASE
  ========================================================= */
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'Segoe UI', Arial, sans-serif;
    font-size: 12px;
    color: #1a1a2e;
    background: #fff;
    line-height: 1.5;
  }
  a { color: inherit; text-decoration: none; }

  /* =========================================================
     LAYOUT
  ========================================================= */
  .page { max-width: 800px; margin: 0 auto; padding: 30px 36px; }

  /* Header de rapport */
  .pdf-header {
    display: flex; align-items: center; justify-content: space-between;
    border-bottom: 3px solid #2ecc71;
    padding-bottom: 14px; margin-bottom: 24px;
  }
  .pdf-logo { font-size: 20px; font-weight: 900; color: #2ecc71; letter-spacing: -0.5px; }
  .pdf-logo span { color: #1a1a2e; }
  .pdf-meta { font-size: 10px; color: #888; text-align: right; }

  /* Titres */
  h1 { font-size: 22px; font-weight: 900; color: #1a1a2e; margin-bottom: 4px; }
  h2 { font-size: 15px; font-weight: 800; color: #1a1a2e; margin: 20px 0 10px;
       border-left: 4px solid #2ecc71; padding-left: 10px; }
  h3 { font-size: 13px; font-weight: 700; color: #333; margin-bottom: 8px; }

  .subtitle { font-size: 11px; color: #888; margin-bottom: 20px; }

  /* =========================================================
     CARDS / SECTIONS
  ========================================================= */
  .section { margin-bottom: 24px; }

  /* Grille macro (4 colonnes) */
  .macro-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; margin-bottom: 14px; }
  .macro-cell {
    text-align: center; border: 1px solid #e0e0e0; border-radius: 8px; padding: 10px 6px;
  }
  .macro-cell .val { font-size: 18px; font-weight: 900; }
  .macro-cell .lbl { font-size: 9px; text-transform: uppercase; letter-spacing: 0.8px; color: #888; margin-bottom: 2px; }

  /* Couleurs macros */
  .c-cal  { color: #e74c3c; } .bg-cal  { background: #fdf0ee; border-color: #f5c6be; }
  .c-prot { color: #2980b9; } .bg-prot { background: #eaf4fb; border-color: #aed6f1; }
  .c-gluc { color: #27ae60; } .bg-gluc { background: #eafaf1; border-color: #a9dfbf; }
  .c-lip  { color: #e67e22; } .bg-lip  { background: #fdf5e9; border-color: #f5cba7; }
  .c-fib  { color: #8e44ad; } .bg-fib  { background: #f5eef8; border-color: #d7bde2; }

  /* Tableau recettes */
  table { width: 100%; border-collapse: collapse; font-size: 11px; }
  thead tr { background: #2ecc71; color: #fff; }
  thead th { padding: 8px 10px; text-align: left; font-weight: 700; }
  tbody tr { border-bottom: 1px solid #f0f0f0; }
  tbody tr:nth-child(even) { background: #f9f9f9; }
  tbody td { padding: 7px 10px; vertical-align: top; }

  /* Ingrédients liste */
  .ingredient-list { list-style: none; padding: 0; display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }
  .ingredient-list li {
    background: #eafaf1; border: 1px solid #a9dfbf; color: #27ae60;
    font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 12px;
  }

  /* Barre chart */
  .bar-row { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
  .bar-label { width: 80px; font-size: 11px; color: #555; flex-shrink: 0; }
  .bar-track { flex: 1; height: 10px; background: #f0f0f0; border-radius: 5px; overflow: hidden; }
  .bar-fill  { height: 100%; border-radius: 5px; }
  .bar-val   { width: 55px; font-size: 11px; font-weight: 700; text-align: right; flex-shrink: 0; }

  /* Podium recettes extrêmes */
  .extremes { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 12px; }
  .extreme-card { border: 1px solid #e0e0e0; border-radius: 8px; padding: 14px; }
  .extreme-card .badge { font-size: 10px; font-weight: 700; text-transform: uppercase;
                         letter-spacing: 1px; margin-bottom: 8px; display: block; }
  .extreme-card h3 { font-size: 13px; margin-bottom: 6px; }
  .extreme-card .kcal { font-size: 24px; font-weight: 900; }

  /* Footer */
  .pdf-footer {
    margin-top: 36px; padding-top: 12px; border-top: 1px solid #e0e0e0;
    font-size: 10px; color: #aaa; text-align: center;
  }

  /* Séparateur de recette */
  .recette-sep { border: none; border-top: 1px dashed #ddd; margin: 18px 0; }

  /* =========================================================
     PRINT
  ========================================================= */
  @media print {
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .no-print { display: none !important; }
    .page { padding: 0; max-width: 100%; }
    table { page-break-inside: auto; }
    tr    { page-break-inside: avoid; }
    .recette-block { page-break-inside: avoid; }
  }

  /* Bouton imprimer (masqué à l'impression) */
  .print-btn {
    position: fixed; bottom: 24px; right: 24px; z-index: 9999;
    background: #2ecc71; color: white; border: none; cursor: pointer;
    padding: 12px 22px; border-radius: 30px;
    font-size: 14px; font-weight: 700; box-shadow: 0 4px 16px rgba(46,204,113,.45);
  }
</style>
</head>
<body>

<!-- Bouton imprimer -->
<button class="print-btn no-print" onclick="window.print()">
  🖨️ Télécharger PDF
</button>

<div class="page">

  <!-- En-tête rapport -->
  <header class="pdf-header">
    <div class="pdf-logo">🥗 Smart<span>Nutrition</span></div>
    <div class="pdf-meta">
      Exporté le <?= $exportDate ?><br>
      <?= htmlspecialchars($pageTitle) ?>
    </div>
  </header>

  <!-- ===================================================
       TYPE : LISTE DES RECETTES
  =================================================== -->
  <?php if ($type === 'liste'): ?>
  <h1>📋 Liste des Recettes</h1>
  <p class="subtitle"><?= count($recettes) ?> recette(s) disponible(s) dans le catalogue</p>

  <table>
    <thead>
      <tr>
        <th style="width:28%">Nom</th>
        <th style="width:12%">Calories</th>
        <th style="width:14%">Temps</th>
        <th style="width:14%">Difficulté</th>
        <th>Ingrédients</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($recettes as $r):
        $cal = round($r['nutrition']['calories']);
        $als = $r['aliments'];
      ?>
      <tr>
        <td><strong><?= htmlspecialchars($r['nom']) ?></strong></td>
        <td><span class="c-cal" style="font-weight:700;"><?= $cal ?> kcal</span></td>
        <td><?= htmlspecialchars($r['temps_preparation']) ?></td>
        <td><?= htmlspecialchars($r['difficulte']) ?></td>
        <td>
          <?php if (!empty($als)): ?>
            <?php foreach ($als as $a): ?>
              <span style="display:inline-block;background:#eafaf1;border:1px solid #a9dfbf;color:#27ae60;
                           font-size:10px;font-weight:700;padding:2px 7px;border-radius:10px;margin:2px 2px 2px 0;">
                <?= $a['quantite'] ?>g <?= htmlspecialchars($a['nom']) ?>
              </span>
            <?php endforeach; ?>
          <?php else: ?>
            <span style="color:#aaa;">–</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- ===================================================
       TYPE : DÉTAIL D'UNE RECETTE
  =================================================== -->
  <?php elseif ($type === 'recette' && $recette): ?>
  <h1><?= htmlspecialchars($recette['nom']) ?></h1>
  <p class="subtitle">
    ⏱ <?= htmlspecialchars($recette['temps_preparation']) ?>
    &nbsp;&bull;&nbsp;
    📊 <?= htmlspecialchars($recette['difficulte']) ?>
  </p>

  <!-- Valeurs nutritionnelles -->
  <h2>🔥 Valeurs Nutritionnelles Totales</h2>
  <div class="macro-grid">
    <div class="macro-cell bg-cal">
      <div class="lbl">Calories</div>
      <div class="val c-cal"><?= round($nutrition['calories']) ?></div>
      <div style="font-size:9px;color:#aaa;">kcal</div>
    </div>
    <div class="macro-cell bg-prot">
      <div class="lbl">Protéines</div>
      <div class="val c-prot"><?= round($nutrition['proteines'],1) ?></div>
      <div style="font-size:9px;color:#aaa;">g</div>
    </div>
    <div class="macro-cell bg-gluc">
      <div class="lbl">Glucides</div>
      <div class="val c-gluc"><?= round($nutrition['glucides'],1) ?></div>
      <div style="font-size:9px;color:#aaa;">g</div>
    </div>
    <div class="macro-cell bg-lip">
      <div class="lbl">Lipides</div>
      <div class="val c-lip"><?= round($nutrition['lipides'],1) ?></div>
      <div style="font-size:9px;color:#aaa;">g</div>
    </div>
    <div class="macro-cell bg-fib">
      <div class="lbl">Fibres</div>
      <div class="val c-fib"><?= round($nutrition['fibres'],1) ?></div>
      <div style="font-size:9px;color:#aaa;">g</div>
    </div>
  </div>

  <!-- Graphique répartition calorique -->
  <?php
    $cP = $nutrition['proteines'] * 4;
    $cG = $nutrition['glucides']  * 4;
    $cL = $nutrition['lipides']   * 9;
    $cT = $cP + $cG + $cL;
    $pP = $cT > 0 ? round($cP/$cT*100) : 0;
    $pG = $cT > 0 ? round($cG/$cT*100) : 0;
    $pL = $cT > 0 ? round($cL/$cT*100) : 0;
  ?>
  <h2>📊 Répartition Calorique</h2>
  <div class="bar-row">
    <div class="bar-label">Protéines</div>
    <div class="bar-track"><div class="bar-fill" style="width:<?=$pP?>%;background:#2980b9;"></div></div>
    <div class="bar-val c-prot"><?=$pP?>%</div>
  </div>
  <div class="bar-row">
    <div class="bar-label">Glucides</div>
    <div class="bar-track"><div class="bar-fill" style="width:<?=$pG?>%;background:#27ae60;"></div></div>
    <div class="bar-val c-gluc"><?=$pG?>%</div>
  </div>
  <div class="bar-row">
    <div class="bar-label">Lipides</div>
    <div class="bar-track"><div class="bar-fill" style="width:<?=$pL?>%;background:#e67e22;"></div></div>
    <div class="bar-val c-lip"><?=$pL?>%</div>
  </div>

  <!-- Liste des aliments -->
  <h2>🧺 Ingrédients</h2>
  <?php if (!empty($aliments_r)): ?>
  <table>
    <thead>
      <tr>
        <th>Ingrédient</th>
        <th>Quantité</th>
        <th>Calories</th>
        <th>Protéines</th>
        <th>Glucides</th>
        <th>Lipides</th>
        <th>Fibres</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($aliments_r as $a):
        $q = (float)($a['quantite'] ?? 0);
      ?>
      <tr>
        <td><strong><?= htmlspecialchars($a['nom']) ?></strong></td>
        <td><?= $q ?>g</td>
        <td class="c-cal"><?= round($a['calories'] * $q / 100) ?></td>
        <td class="c-prot"><?= round($a['proteines'] * $q / 100, 1) ?>g</td>
        <td class="c-gluc"><?= round($a['glucides']  * $q / 100, 1) ?>g</td>
        <td class="c-lip"><?= round($a['lipides']   * $q / 100, 1) ?>g</td>
        <td class="c-fib"><?= round(($a['fibres'] ?? 0) * $q / 100, 1) ?>g</td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
  <p style="color:#aaa;font-style:italic;">Aucun ingrédient associé.</p>
  <?php endif; ?>

  <!-- Description -->
  <h2>📝 Préparation</h2>
  <p style="line-height:1.8;color:#333;font-size:11.5px;white-space:pre-line;"><?= htmlspecialchars($recette['description']) ?></p>

  <!-- ===================================================
       TYPE : STATISTIQUES
  =================================================== -->
  <?php elseif ($type === 'statistiques' && $stats): ?>
  <h1>📊 Statistiques Nutritionnelles</h1>
  <p class="subtitle">Analyse sur <?= $stats['nb_valides'] ?> recette(s) parmi <?= $stats['nb_recettes'] ?> au total</p>

  <!-- Moyennes -->
  <h2>📐 Moyennes par Recette</h2>
  <div class="macro-grid">
    <?php $m = $stats['moyennes']; ?>
    <div class="macro-cell bg-cal">
      <div class="lbl">Calories moy.</div>
      <div class="val c-cal"><?= $m['calories'] ?></div>
      <div style="font-size:9px;color:#aaa;">kcal</div>
    </div>
    <div class="macro-cell bg-prot">
      <div class="lbl">Protéines moy.</div>
      <div class="val c-prot"><?= $m['proteines'] ?></div>
      <div style="font-size:9px;color:#aaa;">g</div>
    </div>
    <div class="macro-cell bg-gluc">
      <div class="lbl">Glucides moy.</div>
      <div class="val c-gluc"><?= $m['glucides'] ?></div>
      <div style="font-size:9px;color:#aaa;">g</div>
    </div>
    <div class="macro-cell bg-lip">
      <div class="lbl">Lipides moy.</div>
      <div class="val c-lip"><?= $m['lipides'] ?></div>
      <div style="font-size:9px;color:#aaa;">g</div>
    </div>
    <div class="macro-cell bg-fib">
      <div class="lbl">Fibres moy.</div>
      <div class="val c-fib"><?= $m['fibres'] ?></div>
      <div style="font-size:9px;color:#aaa;">g</div>
    </div>
  </div>

  <!-- Graphique barres moyennes -->
  <h2>📊 Graphique des Moyennes</h2>
  <?php
  $barData = [
    ['label'=>'Protéines', 'val'=>$m['proteines'], 'unit'=>'g', 'color'=>'#2980b9'],
    ['label'=>'Glucides',  'val'=>$m['glucides'],  'unit'=>'g', 'color'=>'#27ae60'],
    ['label'=>'Lipides',   'val'=>$m['lipides'],   'unit'=>'g', 'color'=>'#e67e22'],
    ['label'=>'Fibres',    'val'=>$m['fibres'],    'unit'=>'g', 'color'=>'#8e44ad'],
  ];
  $barMax = max(array_column($barData,'val')) ?: 1;
  ?>
  <?php foreach ($barData as $b): ?>
  <div class="bar-row">
    <div class="bar-label"><?= $b['label'] ?></div>
    <div class="bar-track">
      <div class="bar-fill" style="width:<?= round($b['val']/$barMax*100) ?>%;background:<?= $b['color'] ?>;"></div>
    </div>
    <div class="bar-val" style="color:<?= $b['color'] ?>;"><?= $b['val'] ?><?= $b['unit'] ?></div>
  </div>
  <?php endforeach; ?>

  <!-- Extrêmes -->
  <h2>🏆 Recettes Extrêmes</h2>
  <div class="extremes">
    <?php $pc = $stats['plus_calorique']; $mc = $stats['moins_calorique']; ?>
    <!-- Plus calorique -->
    <div class="extreme-card" style="border-top:4px solid #e74c3c;">
      <span class="badge c-cal">🔥 La plus calorique</span>
      <h3><?= htmlspecialchars($pc['recette']['nom']) ?></h3>
      <div class="kcal c-cal"><?= round($pc['nutrition']['calories']) ?> <span style="font-size:13px;font-weight:400;color:#999;">kcal</span></div>
      <div style="margin-top:8px;font-size:10px;color:#666;">
        Prot: <?= $pc['nutrition']['proteines'] ?>g &bull;
        Gluc: <?= $pc['nutrition']['glucides'] ?>g &bull;
        Lip: <?= $pc['nutrition']['lipides'] ?>g
      </div>
    </div>
    <!-- Moins calorique -->
    <div class="extreme-card" style="border-top:4px solid #2ecc71;">
      <span class="badge c-gluc">🌿 La moins calorique</span>
      <h3><?= htmlspecialchars($mc['recette']['nom']) ?></h3>
      <div class="kcal c-gluc"><?= round($mc['nutrition']['calories']) ?> <span style="font-size:13px;font-weight:400;color:#999;">kcal</span></div>
      <div style="margin-top:8px;font-size:10px;color:#666;">
        Prot: <?= $mc['nutrition']['proteines'] ?>g &bull;
        Gluc: <?= $mc['nutrition']['glucides'] ?>g &bull;
        Lip: <?= $mc['nutrition']['lipides'] ?>g
      </div>
    </div>
  </div>

  <!-- Écart -->
  <?php $ecart = round($pc['nutrition']['calories'] - $mc['nutrition']['calories']); ?>
  <div style="margin-top:16px;padding:12px 16px;background:#f9f9f9;border:1px solid #e0e0e0;border-radius:8px;text-align:center;">
    <span style="font-size:11px;color:#888;">Écart calorique max/min :</span>
    <strong style="font-size:16px;color:#1a1a2e;margin-left:8px;"><?= $ecart ?> kcal</strong>
  </div>

  <?php else: ?>
  <p style="color:#aaa;text-align:center;margin-top:40px;">Aucune donnée disponible à exporter.</p>
  <?php endif; ?>

  <!-- Footer rapport -->
  <div class="pdf-footer">
    Smart Nutrition &copy; <?= date('Y') ?> &mdash; Document généré le <?= $exportDate ?>
  </div>

</div>

<script>
// Auto-print après chargement complet
window.addEventListener('load', function() {
    // Petit délai pour que les barres CSS s'affichent correctement
    setTimeout(function() { window.print(); }, 400);
});
</script>
</body>
</html>
