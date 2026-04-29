<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Smart Nutrition' ?></title>
    <?php $assetVersion = time(); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="/smart_nutrition/view/assets/style.css?v=<?= $assetVersion ?>">
</head>
<?php $showNav = $showNav ?? isset($_SESSION['user_id']); ?>
<?php $isAdminTemplate = isset($isAdminTemplate) && $isAdminTemplate === true; ?>
<?php $bodyClass = trim(($bodyClass ?? '') . ' ' . ($showNav ? 'with-nav' : 'no-nav') . ' ' . ($isAdminTemplate ? 'admin-template' : '')); ?>
<body class="<?= htmlspecialchars($bodyClass) ?>">
    <?php if ($showNav): ?>
    <?php if ($isAdminTemplate): ?>
    <?php include __DIR__ . '/admin_nav.php'; ?>
    <?php else: ?>
    <?php include __DIR__ . '/nav.php'; ?>
    <?php endif; ?>
    <?php else: ?>
    <div class="theme-toggle-floating-wrap">
        <button type="button" id="themeToggle" class="theme-toggle theme-toggle-floating" aria-label="Changer le mode de couleur" aria-pressed="false">
            <i class="fa-solid fa-moon"></i> Sombre
        </button>
    </div>
    <?php endif; ?>

    <main class="main-content">
