<?php
$pageTitle = 'Details de la recette';
$bodyClass = 'recipes-page recipe-detail-page';
$projectBaseUrl = $baseUrl ?? '/projet-web-25-26';
$routeBase = $projectBaseUrl . '/index.php';
$isRecipeAdmin = $isAdminSession ?? ((($_SESSION['user_role'] ?? 'user') === 'admin'));

require_once __DIR__ . '/../../../controller/RecetteController.php';

$resolveImageUrl = static function ($imageUrl) use ($projectBaseUrl) {
    $imageUrl = trim((string) $imageUrl);

    if ($imageUrl === '') {
        return null;
    }

    if (strpos($imageUrl, '/projetwebmalek/') === 0) {
        return $projectBaseUrl . '/' . ltrim(substr($imageUrl, strlen('/projetwebmalek/')), '/');
    }

    if (strpos($imageUrl, 'projetwebmalek/') === 0) {
        return $projectBaseUrl . '/' . ltrim(substr($imageUrl, strlen('projetwebmalek/')), '/');
    }

    if (strpos($imageUrl, '/projet-web-25-26/') === 0 || preg_match('#^https?://#i', $imageUrl)) {
        return $imageUrl;
    }

    if (strpos($imageUrl, '/view/uploads/') === 0) {
        return $projectBaseUrl . $imageUrl;
    }

    if (strpos($imageUrl, 'view/uploads/') === 0) {
        return $projectBaseUrl . '/' . ltrim($imageUrl, '/');
    }

    if (preg_match('/^[A-Za-z0-9_.-]+\.(jpg|jpeg|png|gif|webp|svg)$/i', $imageUrl)) {
        return $projectBaseUrl . '/view/uploads/recettes/' . basename($imageUrl);
    }

    return $imageUrl;
};

$controller = new RecetteController();
$recette = null;
$alimentsAssocies = [];
$nutritionTotale = [];
$optimisedFlash = isset($_GET['optimised']) && $_GET['optimised'] === '1';

if (isset($_GET['id'])) {
    $recette = $controller->getRecette($_GET['id']);
    if ($recette) {
        $alimentsAssocies = $controller->getAlimentsByRecette($recette['id']);
        $nutritionTotale = $controller->calculerNutritionTotale($recette['id']);
    }
}

require_once __DIR__ . '/../../layouts/header.php';
?>

<div class="submit-page-wrapper" style="max-width:860px;">
    <a href="<?= htmlspecialchars($routeBase) ?>?action=recipes-management" class="submit-back-btn">
        <i class="fa-solid fa-arrow-left"></i> Retour au catalogue
    </a>

    <?php if ($optimisedFlash): ?>
    <div style="background:rgba(46,204,113,0.12);border:1px solid #2ecc71;border-left:4px solid #2ecc71;padding:14px 18px;border-radius:8px;margin-bottom:20px;color:#2ecc71;font-size:14px;display:flex;align-items:center;gap:10px;">
        <i class="fa-solid fa-circle-check" style="font-size:18px;"></i>
        Recette optimisee et enregistree avec succes.
    </div>
    <?php endif; ?>

    <?php if ($recette): ?>
    <div class="submit-form-card" style="padding:36px 32px;">
        <?php $recipeImageUrl = $resolveImageUrl($recette['image_url'] ?? null); ?>
        <div class="recipe-visual-card<?= $recipeImageUrl ? '' : ' is-placeholder' ?>">
            <?php if ($recipeImageUrl): ?>
                <img
                    src="<?= htmlspecialchars($recipeImageUrl) ?>"
                    alt="<?= htmlspecialchars((string) $recette['nom']) ?>"
                    onerror="this.closest('.recipe-visual-card').classList.add('is-placeholder'); this.remove();"
                >
            <?php endif; ?>
            <div class="recipe-visual-placeholder">
                <i class="fa-solid fa-utensils"></i>
                <span>Image recette indisponible</span>
            </div>
        </div>

        <h1 style="margin:0 0 20px;font-size:32px;font-weight:800;"><?= htmlspecialchars((string) $recette['nom']) ?></h1>

        <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:28px;">
            <span class="product-card-badge badge-orange" style="font-size:14px;padding:8px 16px;">
                <i class="fa-solid fa-clock"></i>
                <?= htmlspecialchars((string) $recette['temps_preparation']) ?>
            </span>
            <span class="product-card-badge badge-blue" style="font-size:14px;padding:8px 16px;">
                <i class="fa-solid fa-chart-bar"></i>
                <?= htmlspecialchars((string) $recette['niveau_difficulte']) ?>
            </span>
        </div>

        <?php if (!empty($alimentsAssocies)): ?>
        <div style="margin-bottom:28px;">
            <h3 style="margin:0 0 14px;font-size:16px;font-weight:700;color:rgba(236,240,241,0.8);border-left:3px solid #2ecc71;padding-left:10px;">
                <i class="fa-solid fa-basket-shopping"></i> Ingredients requis
            </h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:10px;">
                <?php foreach ($alimentsAssocies as $aliment): ?>
                <div style="background:rgba(52,152,219,0.08);border:1px solid rgba(52,152,219,0.2);border-radius:8px;padding:10px 14px;display:flex;align-items:center;justify-content:space-between;gap:8px;">
                    <span style="font-weight:600;font-size:14px;display:flex;align-items:center;gap:6px;">
                        <span style="color:#2ecc71;font-weight:700;"><?= htmlspecialchars((string) ($aliment['quantite'] ?? 0)) ?>g</span>
                        <?= htmlspecialchars((string) $aliment['nom']) ?>
                    </span>
                    <span class="product-card-badge badge-blue" style="font-size:11px;opacity:0.8;">
                        <?= htmlspecialchars((string) $aliment['calories']) ?> kcal/100g
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div style="margin-bottom:28px;">
            <h3 style="margin:0 0 14px;font-size:16px;font-weight:700;color:rgba(236,240,241,0.8);border-left:3px solid #f39c12;padding-left:10px;">
                <i class="fa-solid fa-fire"></i> Valeurs nutritionnelles globales
            </h3>
            <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:20px;">
                <div style="background:rgba(231,76,60,0.1);border:1px solid rgba(231,76,60,0.25);border-radius:10px;padding:12px;text-align:center;">
                    <p style="margin:0 0 4px;font-size:11px;text-transform:uppercase;letter-spacing:1px;color:rgba(236,240,241,0.5);">Calories</p>
                    <strong style="font-size:20px;color:#e74c3c;"><?= round((float) ($nutritionTotale['calories'] ?? 0)) ?></strong>
                    <span style="font-size:11px;color:rgba(236,240,241,0.5);"> kcal</span>
                </div>
                <div style="background:rgba(52,152,219,0.1);border:1px solid rgba(52,152,219,0.25);border-radius:10px;padding:12px;text-align:center;">
                    <p style="margin:0 0 4px;font-size:11px;text-transform:uppercase;letter-spacing:1px;color:rgba(236,240,241,0.5);">Proteines</p>
                    <strong style="font-size:20px;color:#3498db;"><?= round((float) ($nutritionTotale['proteines'] ?? 0), 1) ?></strong>
                    <span style="font-size:11px;color:rgba(236,240,241,0.5);"> g</span>
                </div>
                <div style="background:rgba(46,204,113,0.1);border:1px solid rgba(46,204,113,0.25);border-radius:10px;padding:12px;text-align:center;">
                    <p style="margin:0 0 4px;font-size:11px;text-transform:uppercase;letter-spacing:1px;color:rgba(236,240,241,0.5);">Glucides</p>
                    <strong style="font-size:20px;color:#2ecc71;"><?= round((float) ($nutritionTotale['glucides'] ?? 0), 1) ?></strong>
                    <span style="font-size:11px;color:rgba(236,240,241,0.5);"> g</span>
                </div>
                <div style="background:rgba(243,156,18,0.1);border:1px solid rgba(243,156,18,0.25);border-radius:10px;padding:12px;text-align:center;">
                    <p style="margin:0 0 4px;font-size:11px;text-transform:uppercase;letter-spacing:1px;color:rgba(236,240,241,0.5);">Lipides</p>
                    <strong style="font-size:20px;color:#f39c12;"><?= round((float) ($nutritionTotale['lipides'] ?? 0), 1) ?></strong>
                    <span style="font-size:11px;color:rgba(236,240,241,0.5);"> g</span>
                </div>
                <div style="background:rgba(155,89,182,0.1);border:1px solid rgba(155,89,182,0.25);border-radius:10px;padding:12px;text-align:center;">
                    <p style="margin:0 0 4px;font-size:11px;text-transform:uppercase;letter-spacing:1px;color:rgba(236,240,241,0.5);">Fibres</p>
                    <strong style="font-size:20px;color:#9b59b6;"><?= round((float) ($nutritionTotale['fibres'] ?? 0), 1) ?></strong>
                    <span style="font-size:11px;color:rgba(236,240,241,0.5);"> g</span>
                </div>
            </div>
            <p style="font-size:12px;color:rgba(236,240,241,0.4);text-align:right;margin:0;">* Calcule d apres les quantites exactes des ingredients</p>
        </div>
        <?php endif; ?>

        <div>
            <h3 style="margin:0 0 14px;font-size:16px;font-weight:700;color:rgba(236,240,241,0.8);border-left:3px solid #3498db;padding-left:10px;">
                <i class="fa-solid fa-list-check"></i> Preparation
            </h3>
            <div style="font-size:15px;line-height:1.9;color:rgba(236,240,241,0.75);background:rgba(10,16,28,0.5);border-radius:8px;padding:20px;border:1px solid rgba(87,101,116,0.3);">
                <?= nl2br(htmlspecialchars((string) $recette['description'])) ?>
            </div>
        </div>

        <div style="margin-top:28px;display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
            <a href="<?= htmlspecialchars($routeBase) ?>?action=recipe-optimize&amp;id=<?= (int) $recette['id'] ?>"
               class="submit-btn"
               style="background:linear-gradient(135deg,#2ecc71,#27ae60);box-shadow:0 4px 15px rgba(46,204,113,0.35);text-decoration:none;display:inline-flex;">
                <i class="fa-solid fa-arrow-up-right-dots"></i> Optimiser cette recette
            </a>
            <a href="<?= htmlspecialchars($routeBase) ?>?action=recipe-export&amp;type=recette&amp;id=<?= (int) $recette['id'] ?>" target="_blank"
               style="display:inline-flex;align-items:center;gap:8px;padding:10px 18px;background:rgba(231,76,60,0.12);border:1px solid rgba(231,76,60,0.35);color:#e74c3c;border-radius:8px;font-size:13px;font-weight:700;text-decoration:none;">
                <i class="fa-solid fa-file-pdf"></i> Exporter PDF
            </a>
            <?php if ($isRecipeAdmin): ?>
            <a href="<?= htmlspecialchars($routeBase) ?>?action=admin-recipes&amp;edit_id=<?= (int) $recette['id'] ?>"
               class="catalog-btn catalog-btn-blue" style="display:inline-flex;">
                <i class="fa-solid fa-pen"></i> Modifier cette recette
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="submit-form-card" style="text-align:center;padding:60px 30px;">
        <i class="fa-solid fa-circle-exclamation" style="font-size:40px;color:#e74c3c;display:block;margin-bottom:16px;"></i>
        <h2 style="margin:0 0 10px;">Recette introuvable</h2>
        <p style="color:rgba(236,240,241,0.5);margin-bottom:24px;">Cette recette n existe pas ou a ete supprimee.</p>
        <a href="<?= htmlspecialchars($routeBase) ?>?action=recipes-management" class="submit-back-btn" style="margin-bottom:0;">Retour au catalogue</a>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
