<?php $currentAction = $_GET['action'] ?? ''; ?>
<?php $isUsersAction = in_array($currentAction, ['users-list', 'create-user', 'store-user', 'edit-user', 'update-user', 'delete-user'], true); ?>
<?php $isRecipesAction = $currentAction === 'recipes-management'; ?>
<?php $isFoodsAction = $currentAction === 'foods-management'; ?>
<?php $isRecommendationsAction = $currentAction === 'recommendations-management'; ?>
<?php $isTrackingAction = $currentAction === 'tracking-management'; ?>
<?php $isPlannerAction = $currentAction === 'planner-management'; ?>
<?php
$moduleDescriptions = [
    'recipes-management' => [
        'title' => 'Recette alimentation',
        'description' => 'Module en cours de developpement pour creer, modifier et supprimer des recettes alimentaires.',
    ],
    'foods-management' => [
        'title' => 'Ecommerce',
        'description' => 'Module ecommerce pour gerer les produits, le panier, les commandes et le suivi de vente.',
    ],
    'recommendations-management' => [
        'title' => 'Communaute',
        'description' => 'Module communaute pour publier des recommandations, echanger et moderer les contenus.',
    ],
    'tracking-management' => [
        'title' => 'Activite sportif',
        'description' => 'Module activite sportif pour suivre les seances, les indicateurs et la progression.',
    ],
    'planner-management' => [
        'title' => 'Planning',
        'description' => 'Module planning pour organiser les objectifs, les rappels et les taches hebdomadaires.',
    ],
];
$currentModule = $moduleDescriptions[$currentAction] ?? null;
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
            <a href="/smart_nutrition/index.php?action=admin-dashboard" class="admin-brand-link">
                <img
                    src="/smart_nutrition/2-removebg-preview.png"
                    alt="Smart Nutrition"
                    class="brand-logo"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';"
                >
                <span class="brand-fallback"><i class="fa-solid fa-leaf"></i> Smart Nutrition</span>
            </a>
        </div>

        <div class="admin-menu-section">
            <p class="admin-menu-title">Navigation</p>
            <a href="/smart_nutrition/index.php?action=admin-dashboard" class="admin-side-link<?= $currentAction === 'admin-dashboard' ? ' active' : '' ?>">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Dashboard</span>
            </a>
            <a href="/smart_nutrition/index.php?action=users-list" class="admin-side-link<?= $isUsersAction ? ' active' : '' ?>">
                <i class="fa-solid fa-users"></i>
                <span>Utilisateurs</span>
            </a>
        </div>

        <div class="admin-menu-section admin-modules-section">
            <p class="admin-menu-title">Modules</p>
            <button
                type="button"
                class="admin-side-link admin-module-btn<?= $isRecipesAction ? ' active' : '' ?>"
                data-module-title="Recette alimentation"
                data-module-description="Module en cours de developpement pour creer, modifier et supprimer des recettes alimentaires."
            >
                <i class="fa-solid fa-book-open"></i>
                <span>Recette alimentation</span>
            </button>
            <button
                type="button"
                class="admin-side-link admin-module-btn<?= $isFoodsAction ? ' active' : '' ?>"
                data-module-title="Ecommerce"
                data-module-description="Module ecommerce pour gerer les produits, le panier, les commandes et le suivi de vente."
            >
                <i class="fa-solid fa-apple-whole"></i>
                <span>Ecommerce</span>
            </button>
            <button
                type="button"
                class="admin-side-link admin-module-btn<?= $isRecommendationsAction ? ' active' : '' ?>"
                data-module-title="Communaute"
                data-module-description="Module communaute pour publier des recommandations, echanger et moderer les contenus."
            >
                <i class="fa-solid fa-users"></i>
                <span>Communaute</span>
            </button>
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
                data-module-title="Planning"
                data-module-description="Module planning pour organiser les objectifs, les rappels et les taches hebdomadaires."
            >
                <i class="fa-solid fa-calendar-check"></i>
                <span>Planning</span>
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

            <a href="/smart_nutrition/index.php?action=logout" class="admin-logout-btn" title="Deconnexion">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </div>
    </header>
</div>
