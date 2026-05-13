<?php $currentAction = (string) ($_GET['action'] ?? ''); ?>
<?php $currentController = (string) ($_GET['controller'] ?? ''); ?>
<?php $isUsersAction = in_array($currentAction, ['users-list', 'create-user', 'store-user', 'edit-user', 'update-user', 'delete-user'], true); ?>
<?php $isSuiviAdmin = $currentController === 'backoffice' && in_array($currentAction, ['suivi', 'suiviCreate', 'suiviStore', 'suiviEdit', 'suiviUpdate', 'suiviDelete'], true); ?>
<?php $isObjectifsAdmin = $currentController === 'backoffice' && in_array($currentAction, ['objectifs', 'objectifShow', 'objectifDelete'], true); ?>
<?php $isRecipesAdmin = $currentAction === 'admin-recipes'; ?>
<?php $isRecommendationsAdmin = $currentAction === 'admin-recommendations'; ?>
<?php
$adminName = trim((string) ($_SESSION['user_name'] ?? ''));
if ($adminName === '') {
    $adminName = 'Administrateur';
}

$adminNameParts = preg_split('/\s+/', $adminName);
$adminInitials = '';
foreach ($adminNameParts as $part) {
    if ($part !== '') {
        $adminInitials .= strtoupper(substr($part, 0, 1));
        if (strlen($adminInitials) >= 2) {
            break;
        }
    }
}

if ($adminInitials === '') {
    $adminInitials = 'AD';
}
?>

<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="admin-brand">
            <a href="/projetwebmalek/index.php?action=admin-dashboard" class="admin-brand-link">
                <img
                    src="/projetwebmalek/view/assets/images/logo.png"
                    alt="Smart Nutrition"
                    class="brand-logo"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';"
                >
                <span class="brand-fallback"><i class="fa-solid fa-leaf"></i> Smart Nutrition</span>
            </a>
        </div>

        <div class="admin-menu-section">
            <p class="admin-menu-title">Navigation</p>
            <a href="/projetwebmalek/index.php?action=admin-dashboard" class="admin-side-link<?= $currentAction === 'admin-dashboard' ? ' active' : '' ?>">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Dashboard</span>
            </a>
            <a href="/projetwebmalek/index.php?action=users-list" class="admin-side-link<?= $isUsersAction ? ' active' : '' ?>">
                <i class="fa-solid fa-users"></i>
                <span>Utilisateurs</span>
            </a>
        </div>

        <div class="admin-menu-section admin-modules-section">
            <p class="admin-menu-title">Modules</p>
            <a href="/projetwebmalek/index.php?controller=backoffice&action=suivi" class="admin-side-link<?= $isSuiviAdmin ? ' active' : '' ?>">
                <i class="fa-solid fa-apple-whole"></i>
                <span>Aliments</span>
            </a>
            <a href="/projetwebmalek/index.php?controller=backoffice&action=objectifs" class="admin-side-link<?= $isObjectifsAdmin ? ' active' : '' ?>">
                <i class="fa-solid fa-bullseye"></i>
                <span>Objectifs</span>
            </a>
            <a href="/projetwebmalek/index.php?action=admin-recipes" class="admin-side-link<?= $isRecipesAdmin ? ' active' : '' ?>">
                <i class="fa-solid fa-book-open"></i>
                <span>Recettes</span>
            </a>
            <a href="/projetwebmalek/index.php?action=admin-recommendations" class="admin-side-link<?= $isRecommendationsAdmin ? ' active' : '' ?>">
                <i class="fa-solid fa-users"></i>
                <span>Recommandations</span>
            </a>
        </div>
    </aside>

    <header class="admin-topbar">
        <div class="admin-top-actions">
            <button type="button" class="admin-icon-btn" aria-label="Notifications">
                <i class="fa-solid fa-bell"></i>
            </button>

            <button type="button" id="themeToggle" class="admin-icon-btn theme-toggle admin-theme-toggle" aria-label="Changer le mode de couleur" aria-pressed="false">
                <i class="fa-solid fa-moon"></i>
            </button>

            <div class="admin-user-chip">
                <span class="admin-user-avatar"><?= htmlspecialchars($adminInitials) ?></span>
                <div class="admin-user-meta">
                    <strong><?= htmlspecialchars($adminName) ?></strong>
                    <span>Administrator</span>
                </div>
            </div>

            <a href="/projetwebmalek/index.php?action=logout" class="admin-logout-btn" title="Deconnexion">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </div>
    </header>
</div>
