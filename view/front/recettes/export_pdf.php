<?php
require_once __DIR__ . '/../../../model/Database.php';
require_once __DIR__ . '/../../../model/Recette.php';

$db = Database::getConnection();
$recetteModel = new Recette($db);

$type = $_GET['type'] ?? 'liste';
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

$recettes = [];
$recette = null;
$alimentsAssocies = [];
$nutrition = [];
$stats = null;
$pageTitle = 'Smart Nutrition - Export PDF';

if ($type === 'liste') {
    $recettes = $recetteModel->getAll();
    $pageTitle = 'Liste des recettes - Smart Nutrition';
    foreach ($recettes as &$item) {
        $item['aliments'] = $recetteModel->getIngredientsByRecette((int) $item['id']);
        $item['nutrition'] = $recetteModel->calculerNutritionTotale((int) $item['id']);
    }
    unset($item);
} elseif ($type === 'recette' && $id) {
    $recette = $recetteModel->getById($id);
    $alimentsAssocies = $recetteModel->getIngredientsByRecette($id);
    $nutrition = $recetteModel->calculerNutritionTotale($id);
    $pageTitle = ($recette['nom'] ?? 'Recette') . ' - Smart Nutrition';
} elseif ($type === 'statistiques') {
    $stats = $recetteModel->getStatistiquesNutritionnelles();
    $pageTitle = 'Statistiques nutritionnelles - Smart Nutrition';
}

$exportDate = date('d/m/Y H:i');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($pageTitle) ?></title>
<style>
body { font-family: Arial, sans-serif; color: #0f172a; margin: 0; padding: 24px; }
.page { max-width: 900px; margin: 0 auto; }
.head { display:flex; justify-content:space-between; align-items:flex-end; border-bottom:2px solid #16a34a; padding-bottom:12px; margin-bottom:24px; }
.logo { font-size:22px; font-weight:900; color:#16a34a; }
.meta { font-size:12px; color:#64748b; text-align:right; }
h1 { margin:0 0 8px; font-size:28px; }
h2 { margin:24px 0 12px; font-size:18px; color:#0f172a; }
.subtitle { color:#64748b; margin-bottom:18px; }
.macro-grid { display:grid; grid-template-columns:repeat(5,1fr); gap:10px; margin-bottom:18px; }
.macro { border:1px solid #cbd5e1; border-radius:12px; padding:12px; text-align:center; }
.macro strong { display:block; font-size:20px; }
table { width:100%; border-collapse:collapse; }
th, td { border:1px solid #e2e8f0; padding:10px; text-align:left; vertical-align:top; }
th { background:#f8fafc; font-size:12px; text-transform:uppercase; }
.chip { display:inline-block; margin:2px 4px 2px 0; padding:4px 8px; border-radius:999px; background:#eff6ff; color:#1d4ed8; font-size:11px; font-weight:700; }
.print-btn { position:fixed; right:20px; bottom:20px; background:#16a34a; color:#fff; border:0; border-radius:999px; padding:12px 18px; font-weight:700; cursor:pointer; }
@media print { .print-btn { display:none; } body { padding:0; } }
</style>
</head>
<body>
<button class="print-btn" onclick="window.print()">Imprimer / PDF</button>
<div class="page">
    <div class="head">
        <div class="logo">Smart Nutrition</div>
        <div class="meta">
            <?= htmlspecialchars($pageTitle) ?><br>
            Exporte le <?= htmlspecialchars($exportDate) ?>
        </div>
    </div>

    <?php if ($type === 'liste'): ?>
        <h1>Liste des recettes</h1>
        <p class="subtitle"><?= count($recettes) ?> recette(s) disponible(s)</p>
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Calories</th>
                    <th>Temps</th>
                    <th>Difficulte</th>
                    <th>Ingredients</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recettes as $item): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars((string) $item['nom']) ?></strong></td>
                        <td><?= round((float) ($item['nutrition']['calories'] ?? 0)) ?> kcal</td>
                        <td><?= htmlspecialchars((string) ($item['temps_preparation'] ?? '-')) ?></td>
                        <td><?= htmlspecialchars((string) ($item['difficulte'] ?? 'Moyen')) ?></td>
                        <td>
                            <?php foreach ($item['aliments'] as $aliment): ?>
                                <span class="chip"><?= htmlspecialchars((string) ($aliment['quantite'] ?? 0)) ?> g <?= htmlspecialchars((string) $aliment['nom']) ?></span>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php elseif ($type === 'recette' && $recette): ?>
        <h1><?= htmlspecialchars((string) $recette['nom']) ?></h1>
        <p class="subtitle"><?= htmlspecialchars((string) ($recette['temps_preparation'] ?? '-')) ?> | <?= htmlspecialchars((string) ($recette['difficulte'] ?? 'Moyen')) ?></p>

        <div class="macro-grid">
            <div class="macro"><strong><?= round((float) ($nutrition['calories'] ?? 0)) ?></strong><span>kcal</span></div>
            <div class="macro"><strong><?= round((float) ($nutrition['proteines'] ?? 0), 1) ?></strong><span>Proteines</span></div>
            <div class="macro"><strong><?= round((float) ($nutrition['glucides'] ?? 0), 1) ?></strong><span>Glucides</span></div>
            <div class="macro"><strong><?= round((float) ($nutrition['lipides'] ?? 0), 1) ?></strong><span>Lipides</span></div>
            <div class="macro"><strong><?= round((float) ($nutrition['fibres'] ?? 0), 1) ?></strong><span>Fibres</span></div>
        </div>

        <h2>Ingredients</h2>
        <table>
            <thead>
                <tr>
                    <th>Ingredient</th>
                    <th>Quantite</th>
                    <th>Calories</th>
                    <th>Proteines</th>
                    <th>Glucides</th>
                    <th>Lipides</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($alimentsAssocies as $aliment): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) $aliment['nom']) ?></td>
                        <td><?= htmlspecialchars((string) ($aliment['quantite'] ?? 0)) ?> g</td>
                        <td><?= round(((float) $aliment['calories'] * (float) ($aliment['quantite'] ?? 0)) / 100) ?></td>
                        <td><?= round(((float) $aliment['proteines'] * (float) ($aliment['quantite'] ?? 0)) / 100, 1) ?> g</td>
                        <td><?= round(((float) $aliment['glucides'] * (float) ($aliment['quantite'] ?? 0)) / 100, 1) ?> g</td>
                        <td><?= round(((float) $aliment['lipides'] * (float) ($aliment['quantite'] ?? 0)) / 100, 1) ?> g</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Preparation</h2>
        <p><?= nl2br(htmlspecialchars((string) ($recette['description'] ?? ''))) ?></p>
    <?php elseif ($type === 'statistiques' && $stats): ?>
        <h1>Statistiques nutritionnelles</h1>
        <p class="subtitle"><?= htmlspecialchars((string) $stats['nb_valides']) ?> recette(s) analysee(s)</p>
        <?php $m = $stats['moyennes']; ?>
        <div class="macro-grid">
            <div class="macro"><strong><?= htmlspecialchars((string) $stats['nb_recettes']) ?></strong><span>Recettes</span></div>
            <div class="macro"><strong><?= htmlspecialchars((string) $m['calories']) ?></strong><span>Calories</span></div>
            <div class="macro"><strong><?= htmlspecialchars((string) $m['proteines']) ?></strong><span>Proteines</span></div>
            <div class="macro"><strong><?= htmlspecialchars((string) $m['glucides']) ?></strong><span>Glucides</span></div>
            <div class="macro"><strong><?= htmlspecialchars((string) $m['lipides']) ?></strong><span>Lipides</span></div>
        </div>
    <?php else: ?>
        <p>Aucune donnee disponible a exporter.</p>
    <?php endif; ?>
</div>
<script>
window.addEventListener('load', function () {
    setTimeout(function () { window.print(); }, 300);
});
</script>
</body>
</html>
