<?php
$pageTitle = $pageTitle ?? 'Backoffice';
$adminInitials = $adminInitials ?? 'AD';
$adminRole = $adminRole ?? 'Administrateur';
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$basePath = rtrim(dirname($scriptName), '/');
$basePath = $basePath === '.' ? '' : $basePath;
$assetBase = $basePath . '/public/backoffice';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars((string) $pageTitle) ?> | Smart Nutrition</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($assetBase) ?>/css/style.css">
</head>
<body class="admin-template theme-dark">
<header class="admin-topbar">
    <div class="admin-topbar-head">
        <span class="admin-overline">Administration</span>
        <strong><?= htmlspecialchars((string) $pageTitle) ?></strong>
    </div>

    <div class="admin-top-actions">
        <a href="index.php?controller=suivi&action=index" class="admin-chip-link">
            <i class="fa-solid fa-arrow-left"></i>
            Retour au suivi
        </a>

        <button type="button" id="themeToggle" class="admin-icon-btn admin-theme-toggle" aria-label="Changer le mode de couleur" aria-pressed="false">
            <i class="fa-solid fa-moon"></i>
        </button>

        <div class="admin-user-chip">
            <span class="admin-user-avatar"><?= htmlspecialchars($adminInitials) ?></span>
            <div class="admin-user-meta">
                <strong><?= htmlspecialchars($adminRole) ?></strong>
                <span>Smart Nutrition</span>
            </div>
        </div>
    </div>
</header>
