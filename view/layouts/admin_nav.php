<?php $currentAction = $_GET['action'] ?? ''; ?>
<?php $currentController = $_GET['controller'] ?? ''; ?>
<?php $isUsersAction = in_array($currentAction, ['users-list', 'create-user', 'store-user', 'edit-user', 'update-user', 'delete-user'], true); ?>
<?php $isRecipesAction = in_array($currentAction, ['recipes-management', 'admin-recipes', 'admin-recipe-generate'], true); ?>
<?php $isFoodsAction = ($currentController === 'backoffice' && in_array($currentAction, ['suivi', 'suiviCreate', 'suiviStore', 'suiviEdit', 'suiviUpdate', 'suiviDelete'], true)); ?>
<?php $isRecommendationsAction = in_array($currentAction, ['recommendations-management', 'admin-recommendations'], true); ?>
<?php $isTrackingAction = $currentAction === 'tracking-management'; ?>
<?php $isPlannerAction = in_array($currentAction, ['planner-management', 'suivi'], true); ?>
<?php
$moduleDescriptions = [
    'admin-recipes' => [
        'title' => 'Recette alimentation',
        'description' => 'Back-office officiel pour creer, modifier et publier les recettes du catalogue principal.',
    ],
    'suivi' => [
        'title' => 'Aliments',
        'description' => 'Back-office officiel du catalogue aliments partage par le suivi nutritionnel et les recettes.',
    ],
    'admin-recommendations' => [
        'title' => 'Recommandations',
        'description' => 'Back-office officiel pour gerer les recommandations nutritionnelles du projet.',
    ],
    'tracking-management' => [
        'title' => 'Activite sportif',
        'description' => 'Module activite sportif pour suivre les seances, les indicateurs et la progression.',
    ],
    'planner-management' => [
        'title' => 'Suivi nutritionnel',
        'description' => 'Acces au module de suivi nutritionnel, objectifs et progression.',
    ],
];
$currentModuleKey = $currentController === 'backoffice' && $isFoodsAction ? 'suivi' : $currentAction;
$currentModule = $moduleDescriptions[$currentModuleKey] ?? null;
$defaultModuleTitle = $currentModule['title'] ?? 'Description module';
$defaultModuleDescription = $currentModule['description'] ?? 'Cliquez sur un bouton de gestion pour afficher sa description ici.';
?>
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
            <a href="/projet-web-25-26/index.php?action=admin-dashboard" class="admin-brand-link">
                <img
                    src="/projet-web-25-26/view/assets/images/logo.png"
                    alt="Smart Nutrition"
                    class="brand-logo"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';"
                >
                <span class="brand-fallback"><i class="fa-solid fa-leaf"></i> Smart Nutrition</span>
            </a>
        </div>

        <div class="admin-menu-section">
            <p class="admin-menu-title">Navigation</p>
            <a href="/projet-web-25-26/index.php?action=admin-dashboard" class="admin-side-link<?= $currentAction === 'admin-dashboard' ? ' active' : '' ?>">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Dashboard</span>
            </a>
            <a href="/projet-web-25-26/index.php?action=users-list" class="admin-side-link<?= $isUsersAction ? ' active' : '' ?>">
                <i class="fa-solid fa-users"></i>
                <span>Utilisateurs</span>
            </a>
        </div>

        <div class="admin-menu-section admin-modules-section">
            <p class="admin-menu-title">Modules</p>
            <a
                href="/projet-web-25-26/index.php?action=admin-recipes"
                class="admin-side-link admin-module-btn<?= $isRecipesAction ? ' active' : '' ?>"
                data-module-title="Recette alimentation"
                data-module-description="Back-office officiel pour creer, modifier et publier les recettes du catalogue principal."
            >
                <i class="fa-solid fa-book-open"></i>
                <span>Recettes</span>
            </a>
            <a
                href="/projet-web-25-26/index.php?controller=backoffice&action=suivi"
                class="admin-side-link admin-module-btn<?= $isFoodsAction ? ' active' : '' ?>"
                data-module-title="Aliments"
                data-module-description="Back-office officiel du catalogue aliments partage par le suivi nutritionnel et les recettes."
            >
                <i class="fa-solid fa-apple-whole"></i>
                <span>Aliments</span>
            </a>
            <a
                href="/projet-web-25-26/index.php?action=admin-recommendations"
                class="admin-side-link admin-module-btn<?= $isRecommendationsAction ? ' active' : '' ?>"
                data-module-title="Recommandations"
                data-module-description="Back-office officiel pour gerer les recommandations nutritionnelles du projet."
            >
                <i class="fa-solid fa-heart-pulse"></i>
                <span>Recommandations</span>
            </a>
            <button
                type="button"
                class="admin-side-link admin-module-btn<?= $isTrackingAction ? ' active' : '' ?>"
                data-module-title="Activite sportif"
                data-module-description="Module activite sportif pour suivre les seances, les indicateurs et la progression."
            >
                <i class="fa-solid fa-chart-line"></i>
                <span>Activite sportif</span>
            </button>
            <button
                type="button"
                class="admin-side-link admin-module-btn<?= $isPlannerAction ? ' active' : '' ?>"
                data-module-title="Suivi nutritionnel"
                data-module-description="Acces au module de suivi nutritionnel, objectifs et progression."
            >
                <i class="fa-solid fa-calendar-check"></i>
                <span>Suivi nutritionnel</span>
            </button>

            <div id="adminModuleDescription" class="admin-module-description" tabindex="-1">
                <strong id="adminModuleDescriptionTitle"><?= htmlspecialchars($defaultModuleTitle) ?></strong>
                <p id="adminModuleDescriptionText"><?= htmlspecialchars($defaultModuleDescription) ?></p>
            </div>
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

            <a href="/projet-web-25-26/index.php?action=logout" class="admin-logout-btn" title="Deconnexion">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </div>
    </header>
</div>
