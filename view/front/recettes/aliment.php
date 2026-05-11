<?php
$pageTitle = 'Details de l aliment';
$bodyClass = 'recipes-page recipe-detail-page';
$projectBaseUrl = $baseUrl ?? '/projet-web-25-26';
$routeBase = $projectBaseUrl . '/index.php';

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
        return $projectBaseUrl . '/view/uploads/aliments/' . basename($imageUrl);
    }

    return $imageUrl;
};

$controller = new RecetteController();
$aliment = null;

if (isset($_GET['id'])) {
    $aliment = $controller->getAliment($_GET['id']);
}

require_once __DIR__ . '/../../layouts/header.php';
?>

<div class="submit-page-wrapper" style="max-width:820px;">
    <a href="<?= htmlspecialchars($routeBase) ?>?action=recipes-management" class="submit-back-btn">
        <i class="fa-solid fa-arrow-left"></i> Retour au catalogue
    </a>

    <?php if ($aliment): ?>
    <div class="submit-form-card" style="padding:36px 32px;">
        <?php $foodImageUrl = $resolveImageUrl($aliment['image_url'] ?? null); ?>
        <div class="recipe-visual-card recipe-visual-card-sm<?= $foodImageUrl ? '' : ' is-placeholder' ?>">
            <?php if ($foodImageUrl): ?>
                <img
                    src="<?= htmlspecialchars($foodImageUrl) ?>"
                    alt="<?= htmlspecialchars((string) $aliment['nom']) ?>"
                    onerror="this.closest('.recipe-visual-card').classList.add('is-placeholder'); this.remove();"
                >
            <?php endif; ?>
            <div class="recipe-visual-placeholder">
                <i class="fa-solid fa-apple-whole"></i>
                <span>Image aliment indisponible</span>
            </div>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
            <h1 style="margin:0;font-size:32px;font-weight:800;"><?= htmlspecialchars((string) $aliment['nom']) ?></h1>
            <span class="product-card-badge badge-green" style="font-size:14px;padding:6px 14px;">
                <i class="fa-solid fa-fire"></i>
                <?= htmlspecialchars((string) $aliment['calories']) ?> kcal
            </span>
        </div>

        <div style="margin-bottom:28px;">
            <span class="product-card-badge badge-blue" style="font-size:13px;padding:6px 16px;">
                <i class="fa-solid fa-tag"></i> <?= htmlspecialchars((string) ($aliment['type'] ?? 'aliment')) ?>
            </span>
        </div>

        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px;">
            <div style="background:rgba(52,152,219,0.1);border:1px solid rgba(52,152,219,0.25);border-radius:10px;padding:16px;text-align:center;">
                <p style="margin:0 0 4px;font-size:11px;text-transform:uppercase;letter-spacing:1px;color:rgba(236,240,241,0.5);">Proteines</p>
                <strong style="font-size:22px;color:#3498db;"><?= htmlspecialchars((string) ($aliment['proteines'] ?? 0)) ?></strong>
                <span style="font-size:12px;color:rgba(236,240,241,0.5);"> g</span>
            </div>
            <div style="background:rgba(46,204,113,0.1);border:1px solid rgba(46,204,113,0.25);border-radius:10px;padding:16px;text-align:center;">
                <p style="margin:0 0 4px;font-size:11px;text-transform:uppercase;letter-spacing:1px;color:rgba(236,240,241,0.5);">Glucides</p>
                <strong style="font-size:22px;color:#2ecc71;"><?= htmlspecialchars((string) ($aliment['glucides'] ?? 0)) ?></strong>
                <span style="font-size:12px;color:rgba(236,240,241,0.5);"> g</span>
            </div>
            <div style="background:rgba(243,156,18,0.1);border:1px solid rgba(243,156,18,0.25);border-radius:10px;padding:16px;text-align:center;">
                <p style="margin:0 0 4px;font-size:11px;text-transform:uppercase;letter-spacing:1px;color:rgba(236,240,241,0.5);">Lipides</p>
                <strong style="font-size:22px;color:#f39c12;"><?= htmlspecialchars((string) ($aliment['lipides'] ?? 0)) ?></strong>
                <span style="font-size:12px;color:rgba(236,240,241,0.5);"> g</span>
            </div>
            <div style="background:rgba(155,89,182,0.1);border:1px solid rgba(155,89,182,0.25);border-radius:10px;padding:16px;text-align:center;">
                <p style="margin:0 0 4px;font-size:11px;text-transform:uppercase;letter-spacing:1px;color:rgba(236,240,241,0.5);">Fibres</p>
                <strong style="font-size:22px;color:#9b59b6;"><?= htmlspecialchars((string) ($aliment['fibres'] ?? 0)) ?></strong>
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
        <p style="color:rgba(236,240,241,0.5);margin-bottom:24px;">Cet aliment n existe pas ou a ete supprime.</p>
        <a href="<?= htmlspecialchars($routeBase) ?>?action=recipes-management" class="submit-back-btn" style="margin-bottom:0;">Retour au catalogue</a>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
