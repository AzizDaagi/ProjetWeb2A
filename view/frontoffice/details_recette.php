<?php
$baseUrl = $baseUrl ?? rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$pageTitle = 'Détails de la Recette';
require_once __DIR__ . '/../../controller/RecetteController.php';

$controller        = new RecetteController();
$recette           = null;
$aliments_associes = [];

$nutrition_totale = [];
$optimised_flash = isset($_GET['optimised']) && $_GET['optimised'] == '1';
if (isset($_GET['id'])) {
    $recette = $controller->getRecette($_GET['id']);
    if ($recette) {
        $aliments_associes = $controller->getAlimentsByRecette($recette['id']);
        $nutrition_totale = $controller->calculerNutritionTotale($recette['id']);
    }
}

require_once __DIR__ . '/../template_only/layouts/header.php';
?>

<div class="submit-page-wrapper" style="max-width:1200px; margin: 0 auto; padding: 24px;">

    <a href="<?= $baseUrl ?>/index.php?action=recipes-management" class="submit-back-btn">
        <i class="fa-solid fa-arrow-left"></i> Retour au catalogue
    </a>

    <?php if ($optimised_flash): ?>
    <div style="background:rgba(46,204,113,0.12);border:1px solid #2ecc71;border-left:4px solid #2ecc71;
                padding:14px 18px;border-radius:8px;margin-bottom:20px;color:#2ecc71;font-size:14px;
                display:flex;align-items:center;gap:10px;">
        <i class="fa-solid fa-circle-check" style="font-size:18px;"></i>
        Recette optimisée et enregistrée avec succès !
    </div>
    <?php endif; ?>

    <?php if ($recette): ?>
    <div class="submit-form-card" style="padding:36px 32px;">

        <!-- Image Section with Fallback -->
        <div style="width:100%; height:320px; border-radius:14px; overflow:hidden; margin-bottom:28px; background:rgba(30,39,46,0.5); border:1px solid rgba(255,255,255,0.08); position:relative;">
            <?php if (!empty($recette['image_url'])): ?>
                <img src="<?= htmlspecialchars((string)$recette['image_url']) ?>"
                     alt="<?= htmlspecialchars((string)$recette['nom']) ?>"
                     style="width:100%; height:100%; object-fit:cover;"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="product-card-img-placeholder" style="display:none; height:100%; font-size:64px;">
                    <i class="fa-solid fa-utensils"></i>
                </div>
            <?php else: ?>
                <div class="product-card-img-placeholder" style="height:100%; font-size:64px;">
                    <i class="fa-solid fa-utensils"></i>
                </div>
            <?php endif; ?>
        </div>

        <!-- Title -->
        <h1 style="margin:0 0 20px;font-size:32px;font-weight:800;">
            <?= htmlspecialchars((string)$recette['nom']) ?>
        </h1>

        <!-- Time + Difficulty badges -->
        <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:28px;">
            <span class="product-card-badge badge-orange" style="font-size:14px;padding:8px 16px;">
                <i class="fa-solid fa-clock"></i>
                <?= htmlspecialchars((string)$recette['temps_preparation']) ?>
            </span>
            <span class="product-card-badge badge-blue" style="font-size:14px;padding:8px 16px;">
                <i class="fa-solid fa-chart-bar"></i>
                <?= htmlspecialchars((string)$recette['difficulte']) ?>
            </span>
        </div>

        <!-- Ingredients -->
        <?php if (!empty($aliments_associes)): ?>
        <div style="margin-bottom:28px;">
            <h3 style="margin:0 0 14px;font-size:16px;font-weight:700;color:rgba(236,240,241,0.8);
                       border-left:3px solid #2ecc71;padding-left:10px;">
                <i class="fa-solid fa-basket-shopping"></i> Ingrédients requis
            </h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:10px;">
                <?php foreach ($aliments_associes as $a): ?>
                    <div style="background:rgba(52,152,219,0.08);border:1px solid rgba(52,152,219,0.2);
                                border-radius:8px;padding:10px 14px;display:flex;
                                align-items:center;justify-content:space-between;gap:8px;">
                        <span style="font-weight:600;font-size:14px;display:flex;align-items:center;gap:6px;">
                            <span style="color:#2ecc71;font-weight:700;"><?= htmlspecialchars((string)($a['quantite'] ?? 0)) ?>g</span>
                            <?= htmlspecialchars((string)$a['nom']) ?>
                        </span>
                        <span class="product-card-badge badge-blue" style="font-size:11px;opacity:0.8;">
                            <?= htmlspecialchars((string)$a['calories']) ?> kcal/100g
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Total Nutrition Panel -->
        <div style="margin-bottom:28px;">
            <h3 style="margin:0 0 14px;font-size:16px;font-weight:700;color:rgba(236,240,241,0.8);
                       border-left:3px solid #f39c12;padding-left:10px;">
                <i class="fa-solid fa-fire"></i> Valeurs Nutritionnelles Globales
            </h3>
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap:14px; margin-bottom:20px;">
                <div style="background:rgba(231,76,60,0.1);border:1px solid rgba(231,76,60,0.25);border-radius:10px;padding:12px;text-align:center;">
                    <p style="margin:0 0 4px;font-size:11px;text-transform:uppercase;letter-spacing:1px;color:rgba(236,240,241,0.5);">Calories</p>
                    <strong style="font-size:20px;color:#e74c3c;"><?= round($nutrition_totale['calories']) ?></strong>
                    <span style="font-size:11px;color:rgba(236,240,241,0.5);"> kcal</span>
                </div>
                <div style="background:rgba(52,152,219,0.1);border:1px solid rgba(52,152,219,0.25);border-radius:10px;padding:12px;text-align:center;">
                    <p style="margin:0 0 4px;font-size:11px;text-transform:uppercase;letter-spacing:1px;color:rgba(236,240,241,0.5);">Protéines</p>
                    <strong style="font-size:20px;color:#3498db;"><?= round($nutrition_totale['proteines'], 1) ?></strong>
                    <span style="font-size:11px;color:rgba(236,240,241,0.5);"> g</span>
                </div>
                <div style="background:rgba(46,204,113,0.1);border:1px solid rgba(46,204,113,0.25);border-radius:10px;padding:12px;text-align:center;">
                    <p style="margin:0 0 4px;font-size:11px;text-transform:uppercase;letter-spacing:1px;color:rgba(236,240,241,0.5);">Glucides</p>
                    <strong style="font-size:20px;color:#2ecc71;"><?= round($nutrition_totale['glucides'], 1) ?></strong>
                    <span style="font-size:11px;color:rgba(236,240,241,0.5);"> g</span>
                </div>
                <div style="background:rgba(243,156,18,0.1);border:1px solid rgba(243,156,18,0.25);border-radius:10px;padding:12px;text-align:center;">
                    <p style="margin:0 0 4px;font-size:11px;text-transform:uppercase;letter-spacing:1px;color:rgba(236,240,241,0.5);">Lipides</p>
                    <strong style="font-size:20px;color:#f39c12;"><?= round($nutrition_totale['lipides'], 1) ?></strong>
                    <span style="font-size:11px;color:rgba(236,240,241,0.5);"> g</span>
                </div>
                <div style="background:rgba(155,89,182,0.1);border:1px solid rgba(155,89,182,0.25);border-radius:10px;padding:12px;text-align:center;">
                    <p style="margin:0 0 4px;font-size:11px;text-transform:uppercase;letter-spacing:1px;color:rgba(236,240,241,0.5);">Fibres</p>
                    <strong style="font-size:20px;color:#9b59b6;"><?= round($nutrition_totale['fibres'], 1) ?></strong>
                    <span style="font-size:11px;color:rgba(236,240,241,0.5);"> g</span>
                </div>
            </div>
            <p style="font-size:12px;color:rgba(236,240,241,0.4);text-align:right;margin:0;">* Calculé d'après les quantités exactes des ingrédients</p>
        </div>
        <?php endif; ?>

        <!-- Steps -->
        <div>
            <h3 style="margin:0 0 14px;font-size:16px;font-weight:700;color:rgba(236,240,241,0.8);
                       border-left:3px solid #3498db;padding-left:10px;">
                <i class="fa-solid fa-list-check"></i> Préparation (Étapes)
            </h3>
            <div style="font-size:15px;line-height:1.9;color:rgba(236,240,241,0.75);
                        background:rgba(10,16,28,0.5);border-radius:8px;
                        padding:20px;border:1px solid rgba(87,101,116,0.3);">
                <?= nl2br(htmlspecialchars((string)$recette['description'])) ?>
            </div>
        </div>

        <!-- Action Buttons -->
        <div style="margin-top:28px;display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
            <a href="<?= $baseUrl ?>/index.php?action=recipe-optimize&id=<?= $recette['id'] ?>"
               class="submit-btn"
               style="background:linear-gradient(135deg,#2ecc71,#27ae60);
                      box-shadow:0 4px 15px rgba(46,204,113,0.35);
                      text-decoration:none;display:inline-flex;">
                <i class="fa-solid fa-arrow-up-right-dots"></i> Optimiser cette recette
            </a>
            <a href="<?= $baseUrl ?>/index.php?action=recipe-export&type=recette&id=<?= $recette['id'] ?>" target="_blank"
               style="display:inline-flex;align-items:center;gap:8px;padding:10px 18px;
                      background:rgba(231,76,60,0.12);border:1px solid rgba(231,76,60,0.35);
                      color:#e74c3c;border-radius:8px;font-size:13px;font-weight:700;text-decoration:none;">
                <i class="fa-solid fa-file-pdf"></i> Exporter PDF
            </a>
            <a href="<?= $baseUrl ?>/index.php?action=admin-recipes"
               class="catalog-btn catalog-btn-blue" style="display:inline-flex;">
                <i class="fa-solid fa-pen"></i> Modifier cette recette
            </a>
        </div>
    </div>

    <?php else: ?>
    <div class="submit-form-card" style="text-align:center;padding:60px 30px;">
        <i class="fa-solid fa-circle-exclamation" style="font-size:40px;color:#e74c3c;display:block;margin-bottom:16px;"></i>
        <h2 style="margin:0 0 10px;">Recette introuvable</h2>
        <p style="color:rgba(236,240,241,0.5);margin-bottom:24px;">Cette recette n'existe pas ou a été supprimée.</p>
        <a href="<?= $baseUrl ?>/index.php?action=recipes-management" class="submit-back-btn" style="margin-bottom:0;">Retour au catalogue</a>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../template_only/layouts/footer.php'; ?>
