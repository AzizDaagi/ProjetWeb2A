<?php
$pageTitle = 'Détails de l\'Aliment';
require_once __DIR__ . '/../../controler/AlimentController.php';

$controller = new AlimentController();
$aliment = null;

if (isset($_GET['id'])) {
    $aliment = $controller->getAliment($_GET['id']);
}

require_once __DIR__ . '/../template_only/layouts/header.php';
?>


<div class="submit-page-wrapper" style="max-width:820px;">

    <!-- Back button -->
    <a href="liste_recettes.php" class="submit-back-btn">
        <i class="fa-solid fa-arrow-left"></i> Retour au catalogue
    </a>

    <?php if ($aliment): ?>
    <!-- Detail Card -->
    <div class="submit-form-card" style="padding:36px 32px;">

        <!-- Image -->
        <?php if (!empty($aliment['image_url'])): ?>
        <div style="width:100%;height:300px;border-radius:10px;overflow:hidden;margin-bottom:28px;background:rgba(30,39,46,1);">
            <img src="<?= htmlspecialchars((string)$aliment['image_url']) ?>"
                 alt="<?= htmlspecialchars((string)$aliment['nom']) ?>"
                 style="width:100%;height:100%;object-fit:cover;">
        </div>
        <?php endif; ?>

        <!-- Title + badge row -->
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
            <h1 style="margin:0;font-size:32px;font-weight:800;"><?= htmlspecialchars((string)$aliment['nom']) ?></h1>
            <span class="product-card-badge badge-green" style="font-size:14px;padding:6px 14px;">
                <i class="fa-solid fa-fire"></i>
                <?= htmlspecialchars((string)$aliment['calories']) ?> kcal
            </span>
        </div>

        <!-- Type badge -->
        <div style="margin-bottom:28px;">
            <span class="product-card-badge badge-blue" style="font-size:13px;padding:6px 16px;">
                <i class="fa-solid fa-tag"></i> <?= htmlspecialchars((string)$aliment['type']) ?>
            </span>
        </div>

        <!-- Macros grid -->
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px;">
            <div style="background:rgba(52,152,219,0.1);border:1px solid rgba(52,152,219,0.25);border-radius:10px;padding:16px;text-align:center;">
                <p style="margin:0 0 4px;font-size:11px;text-transform:uppercase;letter-spacing:1px;color:rgba(236,240,241,0.5);">Protéines</p>
                <strong style="font-size:22px;color:#3498db;"><?= htmlspecialchars((string)$aliment['proteines']) ?></strong>
                <span style="font-size:12px;color:rgba(236,240,241,0.5);"> g</span>
            </div>
            <div style="background:rgba(46,204,113,0.1);border:1px solid rgba(46,204,113,0.25);border-radius:10px;padding:16px;text-align:center;">
                <p style="margin:0 0 4px;font-size:11px;text-transform:uppercase;letter-spacing:1px;color:rgba(236,240,241,0.5);">Glucides</p>
                <strong style="font-size:22px;color:#2ecc71;"><?= htmlspecialchars((string)$aliment['glucides']) ?></strong>
                <span style="font-size:12px;color:rgba(236,240,241,0.5);"> g</span>
            </div>
            <div style="background:rgba(243,156,18,0.1);border:1px solid rgba(243,156,18,0.25);border-radius:10px;padding:16px;text-align:center;">
                <p style="margin:0 0 4px;font-size:11px;text-transform:uppercase;letter-spacing:1px;color:rgba(236,240,241,0.5);">Lipides</p>
                <strong style="font-size:22px;color:#f39c12;"><?= htmlspecialchars((string)$aliment['lipides']) ?></strong>
                <span style="font-size:12px;color:rgba(236,240,241,0.5);"> g</span>
            </div>
        </div>

        <p style="font-size:12px;color:rgba(236,240,241,0.35);text-align:center;margin:0;">
            Valeurs pour 100g de produit
        </p>
    </div>

    <?php else: ?>
    <div class="submit-form-card" style="text-align:center;padding:60px 30px;">
        <i class="fa-solid fa-circle-exclamation" style="font-size:40px;color:#e74c3c;display:block;margin-bottom:16px;"></i>
        <h2 style="margin:0 0 10px;">Aliment introuvable</h2>
        <p style="color:rgba(236,240,241,0.5);margin-bottom:24px;">Cet aliment n'existe pas ou a été supprimé.</p>
        <a href="liste_recettes.php" class="submit-back-btn" style="margin-bottom:0;">Retour au catalogue</a>
    </div>
    <?php endif; ?>
</div>


<?php require_once __DIR__ . '/../template_only/layouts/footer.php'; ?>
