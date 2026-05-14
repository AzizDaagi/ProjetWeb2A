<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? 'user') !== 'admin') {
    header('Location: ' . $baseUrl . '/index.php?action=login');
    exit;
}

$bodyClass = trim(($bodyClass ?? '') . ' admin-body'); 
$assetVersion = time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Smart Nutrition Admin' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/view/template_only/assets/css/style.css?v=<?= $assetVersion ?>">
</head>
<body class="<?= htmlspecialchars($bodyClass) ?>">

<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="admin-brand">
            <a href="<?= $baseUrl ?>/index.php?action=admin-dashboard" class="admin-brand-link">
                <img
                    src="<?= $baseUrl ?>/view/template_only/2-removebg-preview.png"
                    alt="Smart Nutrition"
                    class="brand-logo"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';"
                >
                <span class="brand-fallback"><i class="fa-solid fa-leaf"></i> Smart Nutrition</span>
            </a>
        </div>

        <div class="admin-menu-section">
            <p class="admin-menu-title">Navigation</p>
            <a href="<?= $baseUrl ?>/index.php?action=admin-dashboard" class="admin-side-link <?= ($_GET['action'] ?? '') === 'admin-dashboard' ? 'active' : '' ?>">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Dashboard</span>
            </a>
            <a href="<?= $baseUrl ?>/index.php?action=users-list" class="admin-side-link <?= ($_GET['action'] ?? '') === 'users-list' ? 'active' : '' ?>">
                <i class="fa-solid fa-users"></i>
                <span>Utilisateurs</span>
            </a>
        </div>

        <div class="admin-menu-section admin-modules-section">
            <p class="admin-menu-title">Modules</p>
            <a href="<?= $baseUrl ?>/index.php?action=admin-recipes" class="admin-side-link admin-module-btn <?= ($_GET['action'] ?? '') === 'admin-recipes' ? 'active' : '' ?>">
                <i class="fa-solid fa-book-open"></i>
                <span>Recettes</span>
            </a>
            <a href="<?= $baseUrl ?>/index.php?action=admin-aliments" class="admin-side-link admin-module-btn <?= ($_GET['action'] ?? '') === 'admin-aliments' ? 'active' : '' ?>">
                <i class="fa-solid fa-apple-whole"></i>
                <span>Aliments</span>
            </a>
            <a href="<?= $baseUrl ?>/index.php?action=admin-recommendations" class="admin-side-link admin-module-btn <?= ($_GET['action'] ?? '') === 'admin-recommendations' ? 'active' : '' ?>">
                <i class="fa-solid fa-heart-pulse"></i>
                <span>Recommandations</span>
            </a>
            <a href="<?= $baseUrl ?>/index.php?action=objectif" class="admin-side-link admin-module-btn <?= ($_GET['action'] ?? '') === 'objectif' ? 'active' : '' ?>">
                <i class="fa-solid fa-bullseye"></i>
                <span>Objectifs</span>
            </a>
            <a href="<?= $baseUrl ?>/index.php?action=recipe-generate" class="admin-side-link admin-module-btn <?= ($_GET['action'] ?? '') === 'recipe-generate' ? 'active' : '' ?>" style="background: rgba(155, 89, 182, 0.15); border-color: rgba(155, 89, 182, 0.4); color: #9b59b6;">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                <span>Générateur IA</span>
            </a>
            <a href="<?= $baseUrl ?>/index.php?action=recipe-stats" class="admin-side-link admin-module-btn <?= ($_GET['action'] ?? '') === 'recipe-stats' ? 'active' : '' ?>" style="background: rgba(52, 152, 219, 0.12); border-color: rgba(52, 152, 219, 0.35); color: #3498db;">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Statistiques</span>
            </a>
            <a href="<?= $baseUrl ?>/index.php?action=recipes-management" class="admin-side-link admin-module-btn" style="margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
                <i class="fa-solid fa-earth-europe"></i>
                <span>Voir le Site</span>
            </a>
        </div>
    </aside>

    <div class="admin-main-wrapper">
        <header class="admin-topbar">
            <div class="admin-top-actions">
                <button type="button" class="admin-icon-btn" aria-label="Notifications">
                    <i class="fa-solid fa-bell"></i>
                </button>

                <button type="button" id="themeToggle" class="admin-icon-btn theme-toggle admin-theme-toggle" aria-label="Changer le mode de couleur" aria-pressed="false">
                    <i class="fa-solid fa-moon"></i>
                </button>

                <div class="admin-user-chip">
                    <?php
                    $adminName = $_SESSION['user_name'] ?? 'Admin';
                    $adminNameParts = preg_split('/\s+/', $adminName);
                    $adminInitials = '';
                    foreach ($adminNameParts as $part) {
                        if ($part !== '') {
                            $adminInitials .= strtoupper(substr($part, 0, 1));
                            if (strlen($adminInitials) >= 2) break;
                        }
                    }
                    if ($adminInitials === '') $adminInitials = 'AD';
                    ?>
                    <span class="admin-user-avatar"><?= htmlspecialchars($adminInitials) ?></span>
                    <div class="admin-user-meta">
                        <strong><?= htmlspecialchars($adminName) ?></strong>
                        <span>Administrator</span>
                    </div>
                </div>

                <a href="<?= $baseUrl ?>/index.php?action=logout" class="admin-logout-btn" title="Deconnexion">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </a>
            </div>
        </header>

        <main class="admin-main">
