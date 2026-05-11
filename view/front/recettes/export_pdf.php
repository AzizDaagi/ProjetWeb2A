<?php
require_once __DIR__ . '/../../../controller/RecetteController.php';

$controller = new RecetteController();
$type = $_GET['type'] ?? 'liste';
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

$recettes = [];
$recette = null;
$alimentsRecette = [];
$nutrition = [];
$stats = null;
$pageTitle = 'Smart Nutrition - Export PDF';

if ($type === 'liste') {
    $recettes = $controller->listRecettes();
    $pageTitle = 'Liste des recettes - Smart Nutrition';

    foreach ($recettes as &$item) {
        $item['aliments'] = $controller->getAlimentsByRecette($item['id']);
        $item['nutrition'] = $controller->calculerNutritionTotale($item['id']);
    }
    unset($item);
} elseif ($type === 'recette' && $id) {
    $recette = $controller->getRecette($id);
    $alimentsRecette = $controller->getAlimentsByRecette($id);
    $nutrition = $controller->calculerNutritionTotale($id);
    $pageTitle = (($recette['nom'] ?? 'Recette') . ' - Smart Nutrition');
} elseif ($type === 'statistiques') {
    $stats = $controller->getStatistiquesNutritionnelles();
    $pageTitle = 'Statistiques nutritionnelles - Smart Nutrition';
}

$exportDate = date('d/m/Y a H:i');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($pageTitle) ?></title>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 12px; color: #1a1a2e; background: #fff; line-height: 1.5; }
  a { color: inherit; text-decoration: none; }
  .page { max-width: 800px; margin: 0 auto; padding: 30px 36px; }
  .pdf-header { display: flex; align-items: center; justify-content: space-between; border-bottom: 3px solid #2ecc71; padding-bottom: 14px; margin-bottom: 24px; }
  .pdf-logo { font-size: 20px; font-weight: 900; color: #2ecc71; letter-spacing: -0.5px; }
  .pdf-logo span { color: #1a1a2e; }
  .pdf-meta { font-size: 10px; color: #888; text-align: right; }
  h1 { font-size: 22px; font-weight: 900; color: #1a1a2e; margin-bottom: 4px; }
  h2 { font-size: 15px; font-weight: 800; color: #1a1a2e; margin: 20px 0 10px; border-left: 4px solid #2ecc71; padding-left: 10px; }
  h3 { font-size: 13px; font-weight: 700; color: #333; margin-bottom: 8px; }
  .subtitle { font-size: 11px; color: #888; margin-bottom: 20px; }
  .macro-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; margin-bottom: 14px; }
  .macro-cell { text-align: center; border: 1px solid #e0e0e0; border-radius: 8px; padding: 10px 6px; }
  .macro-cell .val { font-size: 18px; font-weight: 900; }
  .macro-cell .lbl { font-size: 9px; text-transform: uppercase; letter-spacing: 0.8px; color: #888; margin-bottom: 2px; }
  .c-cal  { color: #e74c3c; } .bg-cal  { background: #fdf0ee; border-color: #f5c6be; }
  .c-prot { color: #2980b9; } .bg-prot { background: #eaf4fb; border-color: #aed6f1; }
  .c-gluc { color: #27ae60; } .bg-gluc { background: #eafaf1; border-color: #a9dfbf; }
  .c-lip  { color: #e67e22; } .bg-lip  { background: #fdf5e9; border-color: #f5cba7; }
  .c-fib  { color: #8e44ad; } .bg-fib  { background: #f5eef8; border-color: #d7bde2; }
  table { width: 100%; border-collapse: collapse; font-size: 11px; }
  thead tr { background: #2ecc71; color: #fff; }
  thead th { padding: 8px 10px; text-align: left; font-weight: 700; }
  tbody tr { border-bottom: 1px solid #f0f0f0; }
  tbody tr:nth-child(even) { background: #f9f9f9; }
  tbody td { padding: 7px 10px; vertical-align: top; }
  .bar-row { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
  .bar-label { width: 80px; font-size: 11px; color: #555; flex-shrink: 0; }
  .bar-track { flex: 1; height: 10px; background: #f0f0f0; border-radius: 5px; overflow: hidden; }
  .bar-fill  { height: 100%; border-radius: 5px; }
  .bar-val   { width: 55px; font-size: 11px; font-weight: 700; text-align: right; flex-shrink: 0; }
  .extremes { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 12px; }
  .extreme-card { border: 1px solid #e0e0e0; border-radius: 8px; padding: 14px; }
  .extreme-card .badge { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: block; }
  .extreme-card h3 { font-size: 13px; margin-bottom: 6px; }
  .extreme-card .kcal { font-size: 24px; font-weight: 900; }
  .pdf-footer { margin-top: 36px; padding-top: 12px; border-top: 1px solid #e0e0e0; font-size: 10px; color: #aaa; text-align: center; }
  @media print {
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .no-print { display: none !important; }
    .page { padding: 0; max-width: 100%; }
    table { page-break-inside: auto; }
    tr { page-break-inside: avoid; }
  }
  .print-btn {
    position: fixed; bottom: 24px; right: 24px; z-index: 9999;
    background: #2ecc71; color: white; border: none; cursor: pointer;
    padding: 12px 22px; border-radius: 30px;
    font-size: 14px; font-weight: 700; box-shadow: 0 4px 16px rgba(46,204,113,.45);
  }
</style>
</head>
<body>
<button class="print-btn no-print" onclick="window.print()">Imprimer / PDF</button>

<div class="page">
  <header class="pdf-header">
    <div class="pdf-logo">Smart<span>Nutrition</span></div>
    <div class="pdf-meta">
      Exporte le <?= $exportDate ?><br>
      <?= htmlspecialchars($pageTitle) ?>
    </div>
  </header>

  <?php if ($type === 'liste'): ?>
  <h1>Liste des recettes</h1>
  <p class="subtitle"><?= count($recettes) ?> recette(s) disponible(s) dans le catalogue</p>
  <table>
    <thead>
      <tr>
        <th style="width:28%">Nom</th>
        <th style="width:12%">Calories</th>
        <th style="width:14%">Temps</th>
        <th style="width:14%">Difficulte</th>
        <th>Ingredients</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($recettes as $item): ?>
      <?php $calories = round((float) $item['nutrition']['calories']); ?>
      <tr>
        <td><strong><?= htmlspecialchars((string) $item['nom']) ?></strong></td>
        <td><span class="c-cal" style="font-weight:700;"><?= $calories ?> kcal</span></td>
        <td><?= htmlspecialchars((string) $item['temps_preparation']) ?></td>
        <td><?= htmlspecialchars((string) $item['niveau_difficulte']) ?></td>
        <td>
          <?php if (!empty($item['aliments'])): ?>
            <?php foreach ($item['aliments'] as $aliment): ?>
            <span style="display:inline-block;background:#eafaf1;border:1px solid #a9dfbf;color:#27ae60;font-size:10px;font-weight:700;padding:2px 7px;border-radius:10px;margin:2px 2px 2px 0;">
                <?= (float) $aliment['quantite'] ?>g <?= htmlspecialchars((string) $aliment['nom']) ?>
            </span>
            <?php endforeach; ?>
          <?php else: ?>
            <span style="color:#aaa;">-</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <?php elseif ($type === 'recette' && $recette): ?>
  <h1><?= htmlspecialchars((string) $recette['nom']) ?></h1>
  <p class="subtitle">
    <?= htmlspecialchars((string) $recette['temps_preparation']) ?>
    &nbsp;&bull;&nbsp;
    <?= htmlspecialchars((string) $recette['niveau_difficulte']) ?>
  </p>

  <h2>Valeurs nutritionnelles totales</h2>
  <div class="macro-grid">
    <div class="macro-cell bg-cal"><div class="lbl">Calories</div><div class="val c-cal"><?= round((float) ($nutrition['calories'] ?? 0)) ?></div><div style="font-size:9px;color:#aaa;">kcal</div></div>
    <div class="macro-cell bg-prot"><div class="lbl">Proteines</div><div class="val c-prot"><?= round((float) ($nutrition['proteines'] ?? 0), 1) ?></div><div style="font-size:9px;color:#aaa;">g</div></div>
    <div class="macro-cell bg-gluc"><div class="lbl">Glucides</div><div class="val c-gluc"><?= round((float) ($nutrition['glucides'] ?? 0), 1) ?></div><div style="font-size:9px;color:#aaa;">g</div></div>
    <div class="macro-cell bg-lip"><div class="lbl">Lipides</div><div class="val c-lip"><?= round((float) ($nutrition['lipides'] ?? 0), 1) ?></div><div style="font-size:9px;color:#aaa;">g</div></div>
    <div class="macro-cell bg-fib"><div class="lbl">Fibres</div><div class="val c-fib"><?= round((float) ($nutrition['fibres'] ?? 0), 1) ?></div><div style="font-size:9px;color:#aaa;">g</div></div>
  </div>

  <?php
  $calProt = (float) ($nutrition['proteines'] ?? 0) * 4;
  $calGluc = (float) ($nutrition['glucides'] ?? 0) * 4;
  $calLip = (float) ($nutrition['lipides'] ?? 0) * 9;
  $calTotal = $calProt + $calGluc + $calLip;
  $pctProt = $calTotal > 0 ? round($calProt / $calTotal * 100) : 0;
  $pctGluc = $calTotal > 0 ? round($calGluc / $calTotal * 100) : 0;
  $pctLip = $calTotal > 0 ? round($calLip / $calTotal * 100) : 0;
  ?>
  <h2>Repartition calorique</h2>
  <div class="bar-row"><div class="bar-label">Proteines</div><div class="bar-track"><div class="bar-fill" style="width:<?= $pctProt ?>%;background:#2980b9;"></div></div><div class="bar-val c-prot"><?= $pctProt ?>%</div></div>
  <div class="bar-row"><div class="bar-label">Glucides</div><div class="bar-track"><div class="bar-fill" style="width:<?= $pctGluc ?>%;background:#27ae60;"></div></div><div class="bar-val c-gluc"><?= $pctGluc ?>%</div></div>
  <div class="bar-row"><div class="bar-label">Lipides</div><div class="bar-track"><div class="bar-fill" style="width:<?= $pctLip ?>%;background:#e67e22;"></div></div><div class="bar-val c-lip"><?= $pctLip ?>%</div></div>

  <h2>Ingredients</h2>
  <?php if (!empty($alimentsRecette)): ?>
  <table>
    <thead>
      <tr>
        <th>Ingredient</th>
        <th>Quantite</th>
        <th>Calories</th>
        <th>Proteines</th>
        <th>Glucides</th>
        <th>Lipides</th>
        <th>Fibres</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($alimentsRecette as $aliment): ?>
      <?php $qte = (float) ($aliment['quantite'] ?? 0); ?>
      <tr>
        <td><strong><?= htmlspecialchars((string) $aliment['nom']) ?></strong></td>
        <td><?= $qte ?>g</td>
        <td class="c-cal"><?= round((float) $aliment['calories'] * $qte / 100) ?></td>
        <td class="c-prot"><?= round((float) $aliment['proteines'] * $qte / 100, 1) ?>g</td>
        <td class="c-gluc"><?= round((float) $aliment['glucides'] * $qte / 100, 1) ?>g</td>
        <td class="c-lip"><?= round((float) $aliment['lipides'] * $qte / 100, 1) ?>g</td>
        <td class="c-fib"><?= round((float) ($aliment['fibres'] ?? 0) * $qte / 100, 1) ?>g</td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
  <p style="color:#aaa;font-style:italic;">Aucun ingredient associe.</p>
  <?php endif; ?>

  <h2>Preparation</h2>
  <p style="line-height:1.8;color:#333;font-size:11.5px;white-space:pre-line;"><?= htmlspecialchars((string) ($recette['description'] ?? '')) ?></p>

  <?php elseif ($type === 'statistiques' && $stats): ?>
  <h1>Statistiques nutritionnelles</h1>
  <p class="subtitle">Analyse sur <?= (int) $stats['nb_valides'] ?> recette(s) parmi <?= (int) $stats['nb_recettes'] ?> au total</p>

  <?php $moyennes = $stats['moyennes']; ?>
  <h2>Moyennes par recette</h2>
  <div class="macro-grid">
    <div class="macro-cell bg-cal"><div class="lbl">Calories moy.</div><div class="val c-cal"><?= $moyennes['calories'] ?></div><div style="font-size:9px;color:#aaa;">kcal</div></div>
    <div class="macro-cell bg-prot"><div class="lbl">Proteines moy.</div><div class="val c-prot"><?= $moyennes['proteines'] ?></div><div style="font-size:9px;color:#aaa;">g</div></div>
    <div class="macro-cell bg-gluc"><div class="lbl">Glucides moy.</div><div class="val c-gluc"><?= $moyennes['glucides'] ?></div><div style="font-size:9px;color:#aaa;">g</div></div>
    <div class="macro-cell bg-lip"><div class="lbl">Lipides moy.</div><div class="val c-lip"><?= $moyennes['lipides'] ?></div><div style="font-size:9px;color:#aaa;">g</div></div>
    <div class="macro-cell bg-fib"><div class="lbl">Fibres moy.</div><div class="val c-fib"><?= $moyennes['fibres'] ?></div><div style="font-size:9px;color:#aaa;">g</div></div>
  </div>

  <?php
  $barData = [
      ['label' => 'Proteines', 'val' => $moyennes['proteines'], 'unit' => 'g', 'color' => '#2980b9'],
      ['label' => 'Glucides', 'val' => $moyennes['glucides'], 'unit' => 'g', 'color' => '#27ae60'],
      ['label' => 'Lipides', 'val' => $moyennes['lipides'], 'unit' => 'g', 'color' => '#e67e22'],
      ['label' => 'Fibres', 'val' => $moyennes['fibres'], 'unit' => 'g', 'color' => '#8e44ad'],
  ];
  $barMax = max(array_column($barData, 'val')) ?: 1;
  ?>
  <h2>Graphique des moyennes</h2>
  <?php foreach ($barData as $bar): ?>
  <div class="bar-row">
    <div class="bar-label"><?= $bar['label'] ?></div>
    <div class="bar-track"><div class="bar-fill" style="width:<?= round($bar['val'] / $barMax * 100) ?>%;background:<?= $bar['color'] ?>;"></div></div>
    <div class="bar-val" style="color:<?= $bar['color'] ?>;"><?= $bar['val'] ?><?= $bar['unit'] ?></div>
  </div>
  <?php endforeach; ?>

  <h2>Recettes extremes</h2>
  <?php $pc = $stats['plus_calorique']; $mc = $stats['moins_calorique']; ?>
  <div class="extremes">
    <div class="extreme-card" style="border-top:4px solid #e74c3c;">
      <span class="badge c-cal">La plus calorique</span>
      <h3><?= htmlspecialchars((string) $pc['recette']['nom']) ?></h3>
      <div class="kcal c-cal"><?= round((float) $pc['nutrition']['calories']) ?> <span style="font-size:13px;font-weight:400;color:#999;">kcal</span></div>
      <div style="margin-top:8px;font-size:10px;color:#666;">
        Prot: <?= $pc['nutrition']['proteines'] ?>g &bull;
        Gluc: <?= $pc['nutrition']['glucides'] ?>g &bull;
        Lip: <?= $pc['nutrition']['lipides'] ?>g
      </div>
    </div>
    <div class="extreme-card" style="border-top:4px solid #2ecc71;">
      <span class="badge c-gluc">La moins calorique</span>
      <h3><?= htmlspecialchars((string) $mc['recette']['nom']) ?></h3>
      <div class="kcal c-gluc"><?= round((float) $mc['nutrition']['calories']) ?> <span style="font-size:13px;font-weight:400;color:#999;">kcal</span></div>
      <div style="margin-top:8px;font-size:10px;color:#666;">
        Prot: <?= $mc['nutrition']['proteines'] ?>g &bull;
        Gluc: <?= $mc['nutrition']['glucides'] ?>g &bull;
        Lip: <?= $mc['nutrition']['lipides'] ?>g
      </div>
    </div>
  </div>

  <?php $ecart = round((float) $pc['nutrition']['calories'] - (float) $mc['nutrition']['calories']); ?>
  <div style="margin-top:16px;padding:12px 16px;background:#f9f9f9;border:1px solid #e0e0e0;border-radius:8px;text-align:center;">
    <span style="font-size:11px;color:#888;">Ecart calorique max/min :</span>
    <strong style="font-size:16px;color:#1a1a2e;margin-left:8px;"><?= $ecart ?> kcal</strong>
  </div>

  <?php else: ?>
  <p style="color:#aaa;text-align:center;margin-top:40px;">Aucune donnee disponible a exporter.</p>
  <?php endif; ?>

  <div class="pdf-footer">
    Smart Nutrition &copy; <?= date('Y') ?> - Document genere le <?= $exportDate ?>
  </div>
</div>

<script>
window.addEventListener('load', function() {
    setTimeout(function() {
        window.print();
    }, 400);
});
</script>
</body>
</html>
