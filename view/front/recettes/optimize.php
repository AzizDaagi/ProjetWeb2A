<?php
ob_start();

$pageTitle = 'Smart Nutrition | Optimiser la recette';
$projectBaseUrl = $baseUrl ?? '/projet-web-25-26';
$routeBase = $projectBaseUrl . '/index.php';

require_once __DIR__ . '/../../../controller/RecetteController.php';

$controller = new RecetteController();
$recette = null;
$result = null;

$objectif = $_POST['objectif'] ?? $_GET['objectif'] ?? 'equilibre_global';
$idRecette = isset($_POST['id_recette']) ? (int) $_POST['id_recette'] : (isset($_GET['id']) ? (int) $_GET['id'] : 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appliquer'])) {
    $nouvellesQuantites = $_POST['nouvelles_quantites'] ?? [];
    $nouvellesQuantitesCastes = [];

    foreach ($nouvellesQuantites as $idAliment => $quantite) {
        $idAliment = (int) $idAliment;
        $quantite = (float) $quantite;
        if ($idAliment > 0) {
            $nouvellesQuantitesCastes[$idAliment] = $quantite;
        }
    }

    if (!empty($nouvellesQuantitesCastes) && $idRecette > 0) {
        $controller->appliquerOptimisation($idRecette, $nouvellesQuantitesCastes);
    }

    ob_end_clean();
    header('Location: ' . $routeBase . '?action=recipe-details&id=' . $idRecette . '&optimised=1');
    exit;
}

if ($idRecette > 0) {
    $recette = $controller->getRecette($idRecette);
    if ($recette) {
        $result = $controller->optimiserRecette($idRecette, $objectif);
    }
}

$objectifLabels = [
    'equilibre_global' => ['label' => 'Equilibre global', 'icon' => 'fa-scale-balanced', 'color' => '#2ecc71'],
    'plus_proteines' => ['label' => 'Plus de proteines', 'icon' => 'fa-dumbbell', 'color' => '#3498db'],
    'moins_lipides' => ['label' => 'Moins de lipides', 'icon' => 'fa-droplet-slash', 'color' => '#f39c12'],
    'plus_fibres' => ['label' => 'Plus de fibres', 'icon' => 'fa-leaf', 'color' => '#27ae60'],
];
$objInfo = $objectifLabels[$objectif] ?? $objectifLabels['equilibre_global'];

require_once __DIR__ . '/../../layouts/header.php';

function macroBadge($pct, $min, $max)
{
    $ok = ($pct >= $min && $pct <= $max);

    return $ok
        ? "<span style='color:#2ecc71;font-size:13px;'>OK {$pct}%</span>"
        : "<span style='color:#e74c3c;font-size:13px;'>Hors cible {$pct}%</span>";
}
?>

<section style="background:linear-gradient(135deg,rgba(46,204,113,0.15),rgba(52,152,219,0.1));border-bottom:1px solid rgba(255,255,255,0.07);padding:50px 32px 40px;text-align:center;">
    <span style="display:inline-flex;align-items:center;justify-content:center;width:64px;height:64px;background:rgba(46,204,113,0.15);border:1px solid rgba(46,204,113,0.4);border-radius:50%;font-size:24px;color:#2ecc71;margin-bottom:16px;">
        <i class="fa-solid fa-arrow-up-right-dots"></i>
    </span>
    <h1 style="margin:0 0 10px;font-size:30px;font-weight:900;background:linear-gradient(135deg,#2ecc71,#3498db);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">
        Optimiseur nutritionnel
    </h1>
    <p style="color:rgba(236,240,241,0.6);font-size:15px;max-width:540px;margin:0 auto;">
        Choisissez un objectif et le systeme ajuste automatiquement les quantites de chaque ingredient.
    </p>
</section>

<div class="submit-page-wrapper" style="max-width:900px;">
    <a href="<?= htmlspecialchars($routeBase) ?>?action=recipe-details&amp;id=<?= $idRecette ?>" class="submit-back-btn">
        <i class="fa-solid fa-arrow-left"></i> Retour a la recette
    </a>

    <?php if (!$recette): ?>
    <div class="submit-form-card" style="text-align:center;padding:60px 30px;">
        <i class="fa-solid fa-circle-exclamation" style="font-size:40px;color:#e74c3c;margin-bottom:16px;"></i>
        <p>Recette introuvable. <a href="<?= htmlspecialchars($routeBase) ?>?action=recipes-management" style="color:#2ecc71;">Retour au catalogue.</a></p>
    </div>
    <?php else: ?>

    <div style="display:flex;align-items:center;gap:16px;margin-bottom:24px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:12px;padding:18px 22px;">
        <i class="fa-solid fa-book-open" style="font-size:22px;color:#2ecc71;"></i>
        <div>
            <div style="font-size:13px;color:rgba(236,240,241,0.5);margin-bottom:2px;">Recette a optimiser</div>
            <div style="font-size:18px;font-weight:700;"><?= htmlspecialchars((string) $recette['nom']) ?></div>
        </div>
    </div>

    <div style="margin-bottom:28px;">
        <p style="font-size:14px;color:rgba(236,240,241,0.6);margin-bottom:12px;">Choisissez votre objectif :</p>
        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;">
            <?php foreach ($objectifLabels as $key => $info): ?>
            <a href="<?= htmlspecialchars($routeBase) ?>?action=recipe-optimize&amp;id=<?= $idRecette ?>&amp;objectif=<?= urlencode($key) ?>"
               style="display:flex;align-items:center;gap:12px;padding:14px 18px;border-radius:10px;text-decoration:none;transition:all .2s;background:rgba(255,255,255,<?= $objectif === $key ? '0.07' : '0.03' ?>);border:1px solid <?= $objectif === $key ? $info['color'] : 'rgba(255,255,255,0.08)' ?>;box-shadow:<?= $objectif === $key ? "0 0 12px {$info['color']}33" : 'none' ?>;">
                <span style="width:38px;height:38px;border-radius:8px;display:flex;align-items:center;justify-content:center;background:<?= $info['color'] ?>22;font-size:16px;color:<?= $info['color'] ?>;flex-shrink:0;">
                    <i class="fa-solid <?= $info['icon'] ?>"></i>
                </span>
                <span style="font-weight:<?= $objectif === $key ? '700' : '500' ?>;color:<?= $objectif === $key ? $info['color'] : 'rgba(236,240,241,0.8)' ?>;">
                    <?= $info['label'] ?>
                </span>
                <?php if ($objectif === $key): ?>
                <i class="fa-solid fa-circle-check" style="margin-left:auto;color:<?= $info['color'] ?>;font-size:14px;"></i>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if ($result): ?>
    <?php if (!empty($result['ecarts'])): ?>
    <div style="background:rgba(231,76,60,0.08);border:1px solid rgba(231,76,60,0.25);border-left:4px solid #e74c3c;border-radius:10px;padding:16px 20px;margin-bottom:24px;">
        <h3 style="margin:0 0 10px;font-size:14px;font-weight:700;color:#e74c3c;">
            <i class="fa-solid fa-triangle-exclamation"></i> Problemes detectes
        </h3>
        <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:6px;">
            <?php foreach ($result['ecarts'] as $ecart): ?>
            <li style="font-size:13px;color:rgba(236,240,241,0.75);">
                <i class="fa-solid fa-circle-dot" style="color:#e74c3c;margin-right:6px;"></i>
                <?= htmlspecialchars((string) $ecart['label']) ?>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php else: ?>
    <div style="background:rgba(46,204,113,0.08);border:1px solid rgba(46,204,113,0.25);border-left:4px solid #2ecc71;border-radius:10px;padding:16px 20px;margin-bottom:24px;">
        <p style="margin:0;font-size:13px;color:#2ecc71;">
            <i class="fa-solid fa-check-circle"></i> Cette recette est deja bien equilibree. Voici quand meme une version optimisee.
        </p>
    </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:28px;">
        <div style="background:rgba(10,16,28,0.6);border:1px solid rgba(87,101,116,0.4);border-radius:12px;padding:20px;">
            <h3 style="margin:0 0 16px;font-size:15px;font-weight:700;color:rgba(236,240,241,0.6);display:flex;align-items:center;gap:8px;">
                <span style="background:#e74c3c22;color:#e74c3c;padding:3px 10px;border-radius:4px;font-size:12px;">AVANT</span>
            </h3>
            <div style="display:flex;flex-direction:column;gap:10px;">
                <?php $avant = $result['avant']; $pctAvant = $result['pct_avant']; ?>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:13px;color:rgba(236,240,241,0.6);">Calories</span>
                    <strong style="color:#e74c3c;"><?= $avant['calories'] ?> kcal</strong>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:13px;color:rgba(236,240,241,0.6);">Proteines</span>
                    <span style="text-align:right;"><?= $avant['proteines'] ?>g &nbsp; <?= macroBadge($pctAvant['prot'], 15, 35) ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:13px;color:rgba(236,240,241,0.6);">Glucides</span>
                    <span style="text-align:right;"><?= $avant['glucides'] ?>g &nbsp; <?= macroBadge($pctAvant['gluc'], 40, 60) ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:13px;color:rgba(236,240,241,0.6);">Lipides</span>
                    <span style="text-align:right;"><?= $avant['lipides'] ?>g &nbsp; <?= macroBadge($pctAvant['lip'], 20, 35) ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:13px;color:rgba(236,240,241,0.6);">Fibres</span>
                    <strong style="color:#9b59b6;"><?= $avant['fibres'] ?>g</strong>
                </div>
                <div style="margin-top:8px;">
                    <div style="display:flex;height:8px;border-radius:4px;overflow:hidden;gap:1px;">
                        <div style="width:<?= $pctAvant['prot'] ?>%;background:#3498db;"></div>
                        <div style="width:<?= $pctAvant['gluc'] ?>%;background:#2ecc71;"></div>
                        <div style="width:<?= $pctAvant['lip'] ?>%;background:#f39c12;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div style="background:rgba(46,204,113,0.06);border:2px solid rgba(46,204,113,0.3);border-radius:12px;padding:20px;position:relative;">
            <h3 style="margin:0 0 16px;font-size:15px;font-weight:700;color:rgba(236,240,241,0.6);display:flex;align-items:center;gap:8px;">
                <span style="background:#2ecc7122;color:#2ecc71;padding:3px 10px;border-radius:4px;font-size:12px;">APRES</span>
                <span style="font-size:11px;color:rgba(155,89,182,0.8);">
                    <i class="fa-solid <?= $objInfo['icon'] ?>"></i> <?= $objInfo['label'] ?>
                </span>
            </h3>
            <div style="display:flex;flex-direction:column;gap:10px;">
                <?php $apres = $result['apres']; $pctApres = $result['pct_apres']; $diffKcal = $apres['calories'] - $avant['calories']; ?>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:13px;color:rgba(236,240,241,0.6);">Calories</span>
                    <span>
                        <strong style="color:#e74c3c;"><?= $apres['calories'] ?> kcal</strong>
                        <span style="font-size:11px;color:<?= $diffKcal <= 0 ? '#2ecc71' : '#e74c3c' ?>;margin-left:4px;">
                            (<?= $diffKcal > 0 ? '+' : '' ?><?= $diffKcal ?>)
                        </span>
                    </span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:13px;color:rgba(236,240,241,0.6);">Proteines</span>
                    <span style="text-align:right;"><?= $apres['proteines'] ?>g &nbsp; <?= macroBadge($pctApres['prot'], 15, 35) ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:13px;color:rgba(236,240,241,0.6);">Glucides</span>
                    <span style="text-align:right;"><?= $apres['glucides'] ?>g &nbsp; <?= macroBadge($pctApres['gluc'], 40, 60) ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:13px;color:rgba(236,240,241,0.6);">Lipides</span>
                    <span style="text-align:right;"><?= $apres['lipides'] ?>g &nbsp; <?= macroBadge($pctApres['lip'], 20, 35) ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:13px;color:rgba(236,240,241,0.6);">Fibres</span>
                    <strong style="color:#9b59b6;"><?= $apres['fibres'] ?>g</strong>
                </div>
                <div style="margin-top:8px;">
                    <div style="display:flex;height:8px;border-radius:4px;overflow:hidden;gap:1px;">
                        <div style="width:<?= $pctApres['prot'] ?>%;background:#3498db;"></div>
                        <div style="width:<?= $pctApres['gluc'] ?>%;background:#2ecc71;"></div>
                        <div style="width:<?= $pctApres['lip'] ?>%;background:#f39c12;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="submit-form-card" style="margin-bottom:24px;">
        <h3 style="margin:0 0 16px;font-size:16px;font-weight:700;border-left:3px solid #2ecc71;padding-left:10px;">
            <i class="fa-solid fa-ruler-combined"></i> Nouvelles quantites proposees
        </h3>
        <div style="display:flex;flex-direction:column;gap:8px;">
            <?php foreach ($result['aliments'] as $aliment): ?>
            <?php
            $oldQ = (float) ($aliment['quantite'] ?? 0);
            $newQ = $result['nouvelles_quantites'][$aliment['id']] ?? $oldQ;
            $diff = $newQ - $oldQ;
            $diffColor = $diff > 0 ? '#2ecc71' : ($diff < 0 ? '#e74c3c' : 'rgba(236,240,241,0.4)');
            $diffIcon = $diff > 0 ? 'arrow-up' : ($diff < 0 ? 'arrow-down' : 'minus');
            ?>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:8px;gap:12px;">
                <span style="font-weight:600;font-size:14px;"><?= htmlspecialchars((string) $aliment['nom']) ?></span>
                <div style="display:flex;align-items:center;gap:14px;flex-shrink:0;">
                    <span style="text-decoration:line-through;color:rgba(236,240,241,0.35);font-size:13px;"><?= $oldQ ?>g</span>
                    <i class="fa-solid fa-arrow-right" style="color:rgba(236,240,241,0.3);font-size:11px;"></i>
                    <strong style="color:#2ecc71;font-size:15px;"><?= $newQ ?>g</strong>
                    <?php if ($diff != 0): ?>
                    <span style="color:<?= $diffColor ?>;font-size:12px;display:flex;align-items:center;gap:3px;">
                        <i class="fa-solid fa-<?= $diffIcon ?>" style="font-size:10px;"></i>
                        <?= abs($diff) ?>g
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;">
        <form method="POST" action="<?= htmlspecialchars($routeBase) ?>?action=recipe-save-optimization" style="display:inline;">
            <input type="hidden" name="id_recette" value="<?= $idRecette ?>">
            <input type="hidden" name="objectif" value="<?= htmlspecialchars($objectif) ?>">
            <?php foreach ($result['nouvelles_quantites'] as $alId => $qte): ?>
            <input type="hidden" name="nouvelles_quantites[<?= (int) $alId ?>]" value="<?= htmlspecialchars((string) $qte) ?>">
            <?php endforeach; ?>
            <button type="submit" name="appliquer" value="1" class="submit-btn" style="background:linear-gradient(135deg,#2ecc71,#27ae60);">
                <i class="fa-solid fa-check"></i> Accepter et enregistrer
            </button>
        </form>

        <a href="<?= htmlspecialchars($routeBase) ?>?action=recipe-details&amp;id=<?= $idRecette ?>" class="submit-btn"
           style="background:transparent;border:1px solid rgba(87,101,116,0.5);color:rgba(236,240,241,0.7);box-shadow:none;text-decoration:none;display:inline-flex;">
            <i class="fa-solid fa-xmark"></i> Garder l originale
        </a>
    </div>

    <?php else: ?>
    <div class="submit-form-card" style="text-align:center;padding:60px 30px;">
        <i class="fa-solid fa-circle-exclamation" style="font-size:40px;color:#e74c3c;margin-bottom:16px;"></i>
        <p>Impossible d optimiser cette recette. Verifiez que des aliments avec des quantites sont bien associes.</p>
    </div>
    <?php endif; ?>

    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
