<?php
$pageTitle = 'Détails de la Recette';
require_once __DIR__ . '/../../controler/RecetteController.php';

$controller        = new RecetteController();
$recette           = null;
$aliments_associes = [];

if (isset($_GET['id'])) {
    $recette = $controller->getRecette($_GET['id']);
    if ($recette) {
        $aliments_associes = $controller->getAlimentsByRecette($recette['id']);
    }
}

require_once __DIR__ . '/../template_only/layouts/header.php';
?>

<div class="submit-page-wrapper" style="max-width:860px;">

    <a href="liste_recettes.php" class="submit-back-btn">
        <i class="fa-solid fa-arrow-left"></i> Retour au catalogue
    </a>

    <?php if ($recette): ?>
    <div class="submit-form-card" style="padding:36px 32px;">

        <!-- Image -->
        <?php if (!empty($recette['image_url'])): ?>
        <div style="width:100%;height:320px;border-radius:10px;overflow:hidden;margin-bottom:28px;background:rgba(30,39,46,1);">
            <img src="<?= htmlspecialchars((string)$recette['image_url']) ?>"
                 alt="<?= htmlspecialchars((string)$recette['nom']) ?>"
                 style="width:100%;height:100%;object-fit:cover;">
        </div>
        <?php endif; ?>

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
                <?= htmlspecialchars((string)$recette['niveau_difficulte']) ?>
            </span>
        </div>

        <!-- Ingredients -->
        <?php if (!empty($aliments_associes)): ?>
        <div style="margin-bottom:28px;">
            <h3 style="margin:0 0 14px;font-size:16px;font-weight:700;color:rgba(236,240,241,0.8);
                       border-left:3px solid #2ecc71;padding-left:10px;">
                <i class="fa-solid fa-basket-shopping"></i> Ingrédients requis
            </h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px;">
                <?php foreach ($aliments_associes as $a): ?>
                    <div style="background:rgba(52,152,219,0.08);border:1px solid rgba(52,152,219,0.2);
                                border-radius:8px;padding:10px 14px;display:flex;
                                align-items:center;justify-content:space-between;gap:8px;">
                        <span style="font-weight:600;font-size:14px;">
                            <?= htmlspecialchars((string)$a['nom']) ?>
                        </span>
                        <span class="product-card-badge badge-green" style="font-size:11px;">
                            <?= htmlspecialchars((string)$a['calories']) ?> kcal
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
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

        <!-- Edit link -->
        <div style="margin-top:28px;text-align:right;">
            <a href="/projetwebmalek/view/backoffice/manage_recettes.php"
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
        <a href="liste_recettes.php" class="submit-back-btn" style="margin-bottom:0;">Retour au catalogue</a>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../template_only/layouts/footer.php'; ?>
