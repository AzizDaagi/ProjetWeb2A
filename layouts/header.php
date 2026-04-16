<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Smart Nutrition' ?></title>
    <?php $assetVersion = time(); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(app_url('assets/css/style.css')) ?>?v=<?= $assetVersion ?>">
</head>
<?php $showNav = $showNav ?? true; ?>
<?php $bodyClass = trim(($bodyClass ?? '') . ' ' . ($showNav ? 'with-nav' : 'no-nav')); ?>
<body class="<?= htmlspecialchars($bodyClass) ?>">
    <?php if ($showNav): ?>
    <?php include __DIR__ . '/nav.php'; ?>
    <?php else: ?>
    <div class="theme-toggle-floating-wrap">
        <button type="button" id="themeToggle" class="theme-toggle theme-toggle-floating" aria-label="Toggle color mode" aria-pressed="false">
            <i class="fa-solid fa-moon"></i> Dark
        </button>
    </div>
    <?php endif; ?>

    <main class="main-content">
