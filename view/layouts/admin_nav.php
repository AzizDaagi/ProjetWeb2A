<?php
$baseUrl = '/projet-web-25-26';
$currentAction = $_GET['action'] ?? 'admin-dashboard';

$isDashboardAction = $currentAction === 'admin-dashboard';
$isUsersAction = in_array($currentAction, ['users-list', 'create-user', 'edit-user', 'users-report'], true);
$isAlimentsAction = in_array($currentAction, [
    'admin-aliments',
    'admin-aliment-create',
    'admin-aliment-edit',
    'admin-aliment-store',
    'admin-aliment-update',
    'admin-aliment-delete',
], true);
$isObjectivesAction = in_array($currentAction, [
    'admin-objectifs',
    'admin-objectif-show',
    'admin-objectif-delete',
    'admin-objectif-create',
    'admin-objectif-edit',
    'admin-objectif-store',
    'admin-objectif-update',
], true);
$isRecipesAction = in_array($currentAction, [
    'admin-recipes',
    'admin-recipe-create',
    'admin-recipe-edit',
    'admin-recipe-store',
    'admin-recipe-update',
    'admin-recipe-delete',
    'recipes-management',
    'recipe-details',
    'recipe-generate',
    'recipe-optimize',
    'recipe-save-optimization',
    'recipe-stats',
    'recipe-export',
], true);
$isRecommendationsAction = in_array($currentAction, [
    'admin-recommendations',
    'admin-recommendation-create',
    'admin-recommendation-edit',
    'admin-recommendation-store',
    'admin-recommendation-update',
    'admin-recommendation-delete',
], true);
$isCommunityAction = $currentAction === 'admin-community';
$isCommunityReportsAction = in_array($currentAction, ['admin-community-reports', 'admin-community-report-details', 'admin-community-review-post'], true);
$isSportAction = in_array($currentAction, ['admin-sport', 'admin_index', 'admin_show', 'admin_requests'], true);
$adminSportRouteExists = true;

$moduleInfo = [
    'dashboard' => [
        'title' => 'Dashboard',
        'text' => 'Module actif pour le pilotage global du back-office Smart Nutrition.',
    ],
    'users' => [
        'title' => 'Utilisateurs',
        'text' => 'Module actif pour la gestion des comptes, profils et statistiques utilisateurs.',
    ],
    'aliments' => [
        'title' => 'Aliments',
        'text' => 'Module actif pour gerer le catalogue nutritionnel officiel.',
    ],
    'objectives' => [
        'title' => 'Objectifs',
        'text' => 'Module actif pour gerer les objectifs caloriques et nutritionnels.',
    ],
    'recipes' => [
        'title' => 'Recettes',
        'text' => 'Module actif pour gerer les recettes et leurs ingredients.',
    ],
    'recommendations' => [
        'title' => 'Recommandations',
        'text' => 'Module actif pour gerer les regles de recommandations nutritionnelles.',
    ],
    'community' => [
        'title' => 'Community',
        'text' => 'Module actif pour gerer les posts, commentaires, reactions et signalements.',
    ],
    'community_reports' => [
        'title' => 'Signalements',
        'text' => 'Module actif pour moderer les contenus signales par les utilisateurs.',
    ],
    'sport' => [
        'title' => 'Activite sportive',
        'text' => 'Module actif pour gerer les activites sportives et le suivi des exercices.',
    ],
];

$activeModuleKey = 'dashboard';
if ($isUsersAction) {
    $activeModuleKey = 'users';
} elseif ($isCommunityReportsAction) {
    $activeModuleKey = 'community_reports';
} elseif ($isCommunityAction) {
    $activeModuleKey = 'community';
} elseif ($isRecommendationsAction) {
    $activeModuleKey = 'recommendations';
} elseif ($isRecipesAction) {
    $activeModuleKey = 'recipes';
} elseif ($isObjectivesAction) {
    $activeModuleKey = 'objectives';
} elseif ($isAlimentsAction) {
    $activeModuleKey = 'aliments';
} elseif ($isSportAction) {
    $activeModuleKey = 'sport';
}

$currentModule = $moduleInfo[$activeModuleKey] ?? $moduleInfo['dashboard'];

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
            <a href="<?= $baseUrl ?>/index.php?action=admin-dashboard" class="admin-brand-link">
                <img
                    src="<?= $baseUrl ?>/view/assets/images/logo.png"
                    alt="Smart Nutrition"
                    class="brand-logo"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';"
                >
                <span class="brand-fallback"><i class="fa-solid fa-leaf"></i> Smart Nutrition</span>
            </a>
        </div>

        <div class="admin-menu-section">
            <p class="admin-menu-title admin-side-section-title">Navigation</p>
            <a href="<?= $baseUrl ?>/index.php?action=admin-dashboard" class="admin-side-link<?= $isDashboardAction ? ' active' : '' ?>">
                <i class="fa-solid fa-compass"></i>
                <span>Dashboard</span>
            </a>
            <a href="<?= $baseUrl ?>/index.php?action=users-list" class="admin-side-link<?= $isUsersAction ? ' active' : '' ?>">
                <i class="fa-solid fa-users"></i>
                <span>Utilisateurs</span>
            </a>
        </div>

        <div class="admin-menu-section admin-modules-section">
            <p class="admin-menu-title admin-side-section-title">Modules</p>

            <a href="<?= $baseUrl ?>/index.php?action=admin-aliments" class="admin-side-link<?= $isAlimentsAction ? ' active' : '' ?>">
                <i class="fa-solid fa-apple-whole"></i>
                <span>Aliments</span>
            </a>

            <a href="<?= $baseUrl ?>/index.php?action=admin-objectifs" class="admin-side-link<?= $isObjectivesAction ? ' active' : '' ?>">
                <i class="fa-solid fa-bullseye"></i>
                <span>Objectifs</span>
            </a>

            <a href="<?= $baseUrl ?>/index.php?action=admin-recipes" class="admin-side-link<?= $isRecipesAction ? ' active' : '' ?>">
                <i class="fa-solid fa-book-open"></i>
                <span>Recettes</span>
            </a>

            <a href="<?= $baseUrl ?>/index.php?action=admin-recommendations" class="admin-side-link<?= $isRecommendationsAction ? ' active' : '' ?>">
                <i class="fa-solid fa-heart-pulse"></i>
                <span>Recommandations</span>
            </a>

            <a href="<?= $baseUrl ?>/index.php?action=admin-community" class="admin-side-link<?= $isCommunityAction ? ' active' : '' ?>">
                <i class="fa-solid fa-comments"></i>
                <span>Community</span>
            </a>

            <a href="<?= $baseUrl ?>/index.php?action=admin-community-reports" class="admin-side-link<?= $isCommunityReportsAction ? ' active' : '' ?>">
                <i class="fa-solid fa-flag"></i>
                <span>Signalements</span>
            </a>

            <?php if ($adminSportRouteExists): ?>
            <a href="<?= $baseUrl ?>/index.php?action=admin-sport" class="admin-side-link<?= $isSportAction ? ' active' : '' ?>">
                <i class="fa-solid fa-person-running"></i>
                <span>Activite sportive</span>
            </a>
            <?php endif; ?>

            <div class="admin-module-description admin-side-info-card" tabindex="-1">
                <h4><?= htmlspecialchars($currentModule['title']) ?></h4>
                <p><?= htmlspecialchars($currentModule['text']) ?></p>
            </div>
        </div>
    </aside>

    <div class="admin-workspace">
        <header class="admin-topbar">
            <div class="admin-top-actions">
                <div class="notification-center admin-notification-center" data-notification-endpoint="<?= $baseUrl ?>/controller/notificationController.php">
                    <button type="button" id="notificationToggle" class="admin-icon-btn notification-toggle" aria-label="Notifications" aria-expanded="false">
                        <i class="fa-solid fa-bell"></i>
                        <span id="notificationBadge" class="notification-badge" hidden>0</span>
                    </button>
                    <div id="notificationDropdown" class="notification-dropdown" hidden>
                        <div class="notification-header">
                            <strong>Notifications</strong>
                            <button type="button" id="notificationMarkAll" class="notification-mark-all">Tout marquer comme lu</button>
                        </div>
                        <div id="notificationList" class="notification-list">
                            <p class="notification-empty">Aucune notification pour le moment.</p>
                        </div>
                        <button type="button" id="notificationShowOlder" class="notification-show-older" hidden>Voir les anciennes notifications</button>
                    </div>
                </div>

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

                <a href="<?= $baseUrl ?>/index.php?action=logout" class="admin-logout-btn" title="Deconnexion">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </a>
            </div>
        </header>
