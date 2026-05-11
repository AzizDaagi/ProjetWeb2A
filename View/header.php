<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Smart Nutrition' ?></title>
    <?php
    $assetVersion = time();
    $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    $showNav = $showNav ?? true;
    $useSidebar = $useSidebar ?? false;
    $bodyClass = trim(($bodyClass ?? '') . ($useSidebar ? ' has-sidebar' : '') . ($showNav ? '' : ' no-nav'));
    ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= $basePath ?>/View/style.css?v=<?= $assetVersion ?>">
    <link rel="stylesheet" href="<?= $basePath ?>/View/backoffice.css?v=<?= $assetVersion ?>">
</head>
<body class="<?= htmlspecialchars($bodyClass) ?>">
    <?php if ($showNav): ?>
        <?php if ($useSidebar): ?>
            <?php include __DIR__ . '/sidebar.php'; ?>
        <?php else: ?>
            <?php include __DIR__ . '/nav.php'; ?>
        <?php endif; ?>
    <?php else: ?>
        <div class="theme-toggle-floating-wrap">
            <button type="button" id="themeToggle" class="theme-toggle theme-toggle-floating" aria-label="Toggle color mode" aria-pressed="false">
                <i class="fa-solid fa-moon"></i> Dark
            </button>
        </div>
    <?php endif; ?>

    <main class="main-content">
