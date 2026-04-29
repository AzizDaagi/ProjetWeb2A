<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Pour les tests : On simule qu'on est l'admin pour voir la navbar complète
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'Admin Test';
$_SESSION['user_role'] = 'admin';

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
    <link rel="stylesheet" href="/projetwebmalek/view/template_only/assets/css/style.css?v=<?= $assetVersion ?>">
</head>
<body class="<?= htmlspecialchars($bodyClass) ?>">

<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="admin-brand">
            <a href="/projetwebmalek/view/backoffice/index.php" class="admin-brand-link">
                <img
                    src="/projetwebmalek/view/template_only/2-removebg-preview.png"
                    alt="Smart Nutrition"
                    class="brand-logo"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';"
                >
                <span class="brand-fallback"><i class="fa-solid fa-leaf"></i> Smart Nutrition</span>
            </a>
        </div>

        <div class="admin-menu-section">
            <p class="admin-menu-title">Navigation</p>
            <a href="/projetwebmalek/view/backoffice/index.php" class="admin-side-link">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Dashboard</span>
            </a>
            <a href="/projetwebmalek/view/backoffice/manage_users.php" class="admin-side-link">
                <i class="fa-solid fa-users"></i>
                <span>Utilisateurs</span>
            </a>
        </div>

        <div class="admin-menu-section admin-modules-section">
            <p class="admin-menu-title">Modules</p>
            <a href="/projetwebmalek/view/backoffice/manage_recettes.php" class="admin-side-link admin-module-btn">
                <i class="fa-solid fa-book-open"></i>
                <span>Recettes</span>
            </a>
            <a href="/projetwebmalek/view/backoffice/manage_aliments.php" class="admin-side-link admin-module-btn">
                <i class="fa-solid fa-apple-whole"></i>
                <span>Aliments</span>
            </a>
            <a href="/projetwebmalek/view/backoffice/manage_recommandations.php" class="admin-side-link admin-module-btn">
                <i class="fa-solid fa-heart-pulse"></i>
                <span>Recommandations</span>
            </a>
            <a href="/projetwebmalek/view/frontoffice/liste_recettes.php" class="admin-side-link admin-module-btn" style="margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
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
                    <span class="admin-user-avatar"><i class="fa-solid fa-user"></i></span>
                    <div class="admin-user-meta">
                        <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong>
                        <span>Administrator</span>
                    </div>
                </div>

                <a href="#" class="admin-logout-btn" title="Deconnexion">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </a>
            </div>
        </header>

        <main class="admin-main">
