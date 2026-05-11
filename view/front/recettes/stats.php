<?php
$pageTitle = 'Smart Nutrition | Statistiques nutritionnelles';
$projectBaseUrl = $baseUrl ?? '/projet-web-25-26';
$routeBase = $projectBaseUrl . '/index.php';

require_once __DIR__ . '/../../../controller/RecetteController.php';

$controller = new RecetteController();
$stats = $controller->getStatistiquesNutritionnelles();

require_once __DIR__ . '/../../layouts/header.php';
?>

<section style="background:linear-gradient(135deg,rgba(52,152,219,0.18),rgba(155,89,182,0.12));border-bottom:1px solid rgba(255,255,255,0.07);padding:54px 32px 44px;text-align:center;">
    <span style="display:inline-flex;align-items:center;justify-content:center;width:68px;height:68px;background:rgba(52,152,219,0.15);border:1px solid rgba(52,152,219,0.4);border-radius:50%;font-size:26px;color:#3498db;margin-bottom:16px;">
        <i class="fa-solid fa-chart-pie"></i>
    </span>
    <h1 style="margin:0 0 10px;font-size:32px;font-weight:900;background:linear-gradient(135deg,#3498db,#9b59b6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">
        Statistiques nutritionnelles
    </h1>
    <p style="color:rgba(236,240,241,0.6);font-size:15px;max-width:520px;margin:0 auto;">
        Vue globale des valeurs nutritionnelles calculees sur l ensemble du catalogue de recettes.
    </p>
</section>

<div class="submit-page-wrapper" style="max-width:900px;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:20px;">
        <a href="<?= htmlspecialchars($routeBase) ?>?action=recipes-management" class="submit-back-btn" style="margin-bottom:0;">
            <i class="fa-solid fa-arrow-left"></i> Retour au catalogue
        </a>
        <a href="<?= htmlspecialchars($routeBase) ?>?action=recipe-export&amp;type=statistiques" target="_blank"
           style="display:inline-flex;align-items:center;gap:8px;padding:10px 18px;background:rgba(52,152,219,0.15);border:1px solid rgba(52,152,219,0.4);color:#3498db;border-radius:8px;font-size:13px;font-weight:700;text-decoration:none;">
            <i class="fa-solid fa-file-pdf"></i> Exporter en PDF
        </a>
    </div>

    <?php if (!$stats): ?>
    <div class="submit-form-card" style="text-align:center;padding:70px 30px;">
        <i class="fa-solid fa-chart-simple" style="font-size:48px;color:rgba(52,152,219,0.3);margin-bottom:18px;"></i>
        <h2 style="color:rgba(236,240,241,0.5);font-size:18px;">Aucune donnee disponible</h2>
        <p style="color:rgba(236,240,241,0.4);font-size:14px;">Creez des recettes avec des ingredients et des quantites pour voir les statistiques.</p>
    </div>
    <?php else: ?>
    <?php
    $moyennes = $stats['moyennes'];
    $maxCal = $stats['plus_calorique']['nutrition']['calories'];
    $minCal = $stats['moins_calorique']['nutrition']['calories'];
    ?>

    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:28px;">
        <div style="background:rgba(52,152,219,0.08);border:1px solid rgba(52,152,219,0.2);border-radius:12px;padding:20px;text-align:center;">
            <p style="margin:0 0 6px;font-size:11px;text-transform:uppercase;letter-spacing:1px;color:rgba(236,240,241,0.5);">Recettes totales</p>
            <strong style="font-size:36px;color:#3498db;"><?= (int) $stats['nb_recettes'] ?></strong>
        </div>
        <div style="background:rgba(46,204,113,0.08);border:1px solid rgba(46,204,113,0.2);border-radius:12px;padding:20px;text-align:center;">
            <p style="margin:0 0 6px;font-size:11px;text-transform:uppercase;letter-spacing:1px;color:rgba(236,240,241,0.5);">Recettes analysees</p>
            <strong style="font-size:36px;color:#2ecc71;"><?= (int) $stats['nb_valides'] ?></strong>
        </div>
        <div style="background:rgba(155,89,182,0.08);border:1px solid rgba(155,89,182,0.2);border-radius:12px;padding:20px;text-align:center;">
            <p style="margin:0 0 6px;font-size:11px;text-transform:uppercase;letter-spacing:1px;color:rgba(236,240,241,0.5);">Moy. calories</p>
            <strong style="font-size:36px;color:#9b59b6;"><?= $moyennes['calories'] ?></strong>
            <span style="color:rgba(236,240,241,0.4);font-size:13px;"> kcal</span>
        </div>
    </div>

    <div class="submit-form-card" style="margin-bottom:28px;">
        <h2 style="margin:0 0 22px;font-size:17px;font-weight:800;display:flex;align-items:center;gap:10px;border-left:3px solid #3498db;padding-left:12px;">
            <i class="fa-solid fa-calculator" style="color:#3498db;"></i> Moyennes par recette
        </h2>

        <?php
        $macros = [
            ['label' => 'Calories', 'unit' => 'kcal', 'color' => '#e74c3c', 'icon' => 'fa-fire', 'val' => $moyennes['calories']],
            ['label' => 'Proteines', 'unit' => 'g', 'color' => '#3498db', 'icon' => 'fa-dumbbell', 'val' => $moyennes['proteines']],
            ['label' => 'Glucides', 'unit' => 'g', 'color' => '#2ecc71', 'icon' => 'fa-bread-slice', 'val' => $moyennes['glucides']],
            ['label' => 'Lipides', 'unit' => 'g', 'color' => '#f39c12', 'icon' => 'fa-droplet', 'val' => $moyennes['lipides']],
            ['label' => 'Fibres', 'unit' => 'g', 'color' => '#9b59b6', 'icon' => 'fa-leaf', 'val' => $moyennes['fibres']],
        ];
        $maxVal = max(array_column($macros, 'val')) ?: 1;
        ?>

        <div style="display:flex;flex-direction:column;gap:14px;">
            <?php foreach ($macros as $macro): ?>
            <?php $pct = round($macro['val'] / $maxVal * 100); ?>
            <div style="display:flex;align-items:center;gap:16px;">
                <div style="width:38px;height:38px;border-radius:8px;flex-shrink:0;display:flex;align-items:center;justify-content:center;background:<?= $macro['color'] ?>22;color:<?= $macro['color'] ?>;font-size:16px;">
                    <i class="fa-solid <?= $macro['icon'] ?>"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:6px;">
                        <span style="font-size:13px;color:rgba(236,240,241,0.7);"><?= $macro['label'] ?></span>
                        <strong style="color:<?= $macro['color'] ?>;font-size:18px;">
                            <?= $macro['val'] ?> <span style="font-size:12px;font-weight:400;color:rgba(236,240,241,0.4);"><?= $macro['unit'] ?></span>
                        </strong>
                    </div>
                    <div style="height:7px;background:rgba(255,255,255,0.06);border-radius:4px;overflow:hidden;">
                        <div style="height:100%;width:<?= $pct ?>%;background:<?= $macro['color'] ?>;border-radius:4px;transition:width .6s ease;"></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:28px;">
        <?php $plusCalorique = $stats['plus_calorique']; $nutritionMax = $plusCalorique['nutrition']; ?>
        <div style="background:linear-gradient(135deg,rgba(231,76,60,0.08),rgba(231,76,60,0.03));border:1px solid rgba(231,76,60,0.3);border-top:4px solid #e74c3c;border-radius:12px;padding:22px;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;">
                <i class="fa-solid fa-fire" style="color:#e74c3c;font-size:18px;"></i>
                <span style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#e74c3c;">La plus calorique</span>
            </div>
            <h3 style="margin:0 0 14px;font-size:18px;font-weight:800;"><?= htmlspecialchars((string) $plusCalorique['recette']['nom']) ?></h3>
            <div style="font-size:34px;font-weight:900;color:#e74c3c;margin-bottom:12px;"><?= round((float) $nutritionMax['calories']) ?> <span style="font-size:14px;font-weight:400;color:rgba(236,240,241,0.5);">kcal</span></div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:16px;">
                <div style="text-align:center;background:rgba(255,255,255,0.04);border-radius:6px;padding:8px;"><div style="font-size:10px;color:rgba(236,240,241,0.4);margin-bottom:2px;">Prot.</div><strong style="color:#3498db;font-size:15px;"><?= $nutritionMax['proteines'] ?>g</strong></div>
                <div style="text-align:center;background:rgba(255,255,255,0.04);border-radius:6px;padding:8px;"><div style="font-size:10px;color:rgba(236,240,241,0.4);margin-bottom:2px;">Gluc.</div><strong style="color:#2ecc71;font-size:15px;"><?= $nutritionMax['glucides'] ?>g</strong></div>
                <div style="text-align:center;background:rgba(255,255,255,0.04);border-radius:6px;padding:8px;"><div style="font-size:10px;color:rgba(236,240,241,0.4);margin-bottom:2px;">Lip.</div><strong style="color:#f39c12;font-size:15px;"><?= $nutritionMax['lipides'] ?>g</strong></div>
            </div>
            <a href="<?= htmlspecialchars($routeBase) ?>?action=recipe-details&amp;id=<?= (int) $plusCalorique['recette']['id'] ?>"
               style="display:block;text-align:center;padding:9px;border-radius:7px;background:rgba(231,76,60,0.15);border:1px solid rgba(231,76,60,0.3);color:#e74c3c;font-size:13px;font-weight:600;text-decoration:none;">
                <i class="fa-solid fa-arrow-right"></i> Voir la recette
            </a>
        </div>

        <?php $moinsCalorique = $stats['moins_calorique']; $nutritionMin = $moinsCalorique['nutrition']; ?>
        <div style="background:linear-gradient(135deg,rgba(46,204,113,0.08),rgba(46,204,113,0.03));border:1px solid rgba(46,204,113,0.3);border-top:4px solid #2ecc71;border-radius:12px;padding:22px;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;">
                <i class="fa-solid fa-feather" style="color:#2ecc71;font-size:18px;"></i>
                <span style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#2ecc71;">La moins calorique</span>
            </div>
            <h3 style="margin:0 0 14px;font-size:18px;font-weight:800;"><?= htmlspecialchars((string) $moinsCalorique['recette']['nom']) ?></h3>
            <div style="font-size:34px;font-weight:900;color:#2ecc71;margin-bottom:12px;"><?= round((float) $nutritionMin['calories']) ?> <span style="font-size:14px;font-weight:400;color:rgba(236,240,241,0.5);">kcal</span></div>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:16px;">
                <div style="text-align:center;background:rgba(255,255,255,0.04);border-radius:6px;padding:8px;"><div style="font-size:10px;color:rgba(236,240,241,0.4);margin-bottom:2px;">Prot.</div><strong style="color:#3498db;font-size:15px;"><?= $nutritionMin['proteines'] ?>g</strong></div>
                <div style="text-align:center;background:rgba(255,255,255,0.04);border-radius:6px;padding:8px;"><div style="font-size:10px;color:rgba(236,240,241,0.4);margin-bottom:2px;">Gluc.</div><strong style="color:#2ecc71;font-size:15px;"><?= $nutritionMin['glucides'] ?>g</strong></div>
                <div style="text-align:center;background:rgba(255,255,255,0.04);border-radius:6px;padding:8px;"><div style="font-size:10px;color:rgba(236,240,241,0.4);margin-bottom:2px;">Lip.</div><strong style="color:#f39c12;font-size:15px;"><?= $nutritionMin['lipides'] ?>g</strong></div>
            </div>
            <a href="<?= htmlspecialchars($routeBase) ?>?action=recipe-details&amp;id=<?= (int) $moinsCalorique['recette']['id'] ?>"
               style="display:block;text-align:center;padding:9px;border-radius:7px;background:rgba(46,204,113,0.12);border:1px solid rgba(46,204,113,0.3);color:#2ecc71;font-size:13px;font-weight:600;text-decoration:none;">
                <i class="fa-solid fa-arrow-right"></i> Voir la recette
            </a>
        </div>
    </div>

    <?php $ecart = round((float) $maxCal - (float) $minCal); ?>
    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:12px;padding:20px 24px;margin-bottom:28px;">
        <h3 style="margin:0 0 16px;font-size:15px;font-weight:700;color:rgba(236,240,241,0.6);">
            <i class="fa-solid fa-arrows-left-right-to-line" style="color:#9b59b6;"></i>
            Ecart calorique max/min
        </h3>
        <div style="display:flex;align-items:center;gap:16px;">
            <span style="font-size:13px;color:#2ecc71;"><?= round((float) $minCal) ?> kcal</span>
            <div style="flex:1;height:10px;background:rgba(255,255,255,0.06);border-radius:5px;overflow:hidden;">
                <div style="height:100%;width:100%;background:linear-gradient(90deg,#2ecc71,#e74c3c);border-radius:5px;"></div>
            </div>
            <span style="font-size:13px;color:#e74c3c;"><?= round((float) $maxCal) ?> kcal</span>
        </div>
        <p style="margin:10px 0 0;text-align:center;font-size:13px;color:rgba(236,240,241,0.5);">
            Ecart de <strong style="color:white;"><?= $ecart ?> kcal</strong> entre la recette la plus et la moins calorique
        </p>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
