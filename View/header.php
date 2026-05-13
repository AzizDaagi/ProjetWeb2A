<?php
$stylePath = __DIR__ . '/assets/css/style.css';
$backofficeStylePath = __DIR__ . '/assets/css/backoffice.css';
$assetVersion = is_file($stylePath) ? (string) filemtime($stylePath) : (string) time();
$backofficeAssetVersion = is_file($backofficeStylePath) ? (string) filemtime($backofficeStylePath) : $assetVersion;
$showNav = $showNav ?? true;
$isBackOfficeTemplate = str_contains((string) ($bodyClass ?? ''), 'back-office');
$bodyClass = trim(($bodyClass ?? '') . ' ' . ($isBackOfficeTemplate ? 'admin-template back-office' : ($showNav ? 'with-nav' : 'no-nav')));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Smart Nutrition') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(asset_url('css/style.css')) ?>?v=<?= htmlspecialchars($assetVersion) ?>">
    <?php if ($isBackOfficeTemplate): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars(asset_url('css/backoffice.css')) ?>?v=<?= htmlspecialchars($backofficeAssetVersion) ?>">
    <?php endif; ?>
</head>
<body class="<?= htmlspecialchars($bodyClass) ?>">
    <?php if ($isBackOfficeTemplate): ?>
        <?php require __DIR__ . '/backoffice_nav.php'; ?>
    <?php elseif ($showNav): ?>
        <?php require __DIR__ . '/nav.php'; ?>
    <?php else: ?>
        <div class="theme-toggle-floating-wrap">
            <button type="button" id="themeToggle" class="theme-toggle theme-toggle-floating" aria-label="Toggle color mode" aria-pressed="false">
                <i class="fa-solid fa-moon"></i> Dark
            </button>
        </div>
    <?php endif; ?>

    <main class="main-content">
