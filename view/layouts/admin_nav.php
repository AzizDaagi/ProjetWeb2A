<?php
$currentAction = $_GET['action'] ?? 'admin-dashboard';

$isDashboardAction = $currentAction === 'admin-dashboard';
$isUsersAction = in_array($currentAction, ['users-list', 'create-user', 'edit-user', 'users-report'], true);
$isTrackingAction = in_array($currentAction, ['nutrition_dashboard', 'suivi', 'objectif', 'admin-aliments', 'admin-objectifs'], true);
$isRecipesAction = in_array($currentAction, ['admin-recipes', 'recipes-management', 'recipe-details', 'recipe-generate', 'recipe-optimize', 'recipe-save-optimization', 'recipe-stats', 'recipe-export'], true);
$isFoodsAction = in_array($currentAction, ['products-admin', 'product-create', 'product-edit', 'product-delete', 'products-pending', 'product-approve', 'products-prediction', 'products-predict', 'product-predict', 'admin-orders', 'admin-order-edit', 'admin-order-delete', 'admin-order-pdf'], true);
$isRecommendationsAction = $currentAction === 'admin-recommendations';
$isObjectivesAction = in_array($currentAction, ['admin-objectifs', 'objectif'], true);
$isCommunityAction = $currentAction === 'admin-community';
$isCommunityReportsAction = in_array($currentAction, ['admin-community-reports', 'admin-community-report-details', 'admin-community-review-post'], true);
$isNewsAction = in_array($currentAction, ['admin-news', 'admin-news-create', 'admin-news-edit'], true);

$adminObjectifsRouteExists = false;
$adminNewsRouteExists = false;
$objectifsHref = $adminObjectifsRouteExists
    ? '/Web/index.php?action=admin-objectifs'
    : '/Web/index.php?action=objectif';
$newsHref = '/Web/index.php?action=admin-news';

$moduleInfo = [
    'dashboard' => [
        'title' => 'Dashboard',
        'text' => 'Module actif pour le pilotage global du back-office Smart Nutrition.',
    ],
    'users' => [
        'title' => 'Utilisateurs',
        'text' => 'Module actif pour la gestion des comptes, profils et statistiques utilisateurs.',
    ],
    'tracking' => [
        'title' => 'Suivi',
        'text' => 'Module actif pour le suivi nutritionnel, les aliments et les indicateurs quotidiens.',
    ],
    'recipes' => [
        'title' => 'Recettes',
        'text' => 'Module actif pour gerer les recettes et leurs ingredients.',
    ],
    'foods' => [
        'title' => 'Ecommerce',
        'text' => 'Module actif pour gerer les produits, le panier, les commandes et les predictions de vente.',
    ],
    'recommendations' => [
        'title' => 'Recommandations',
        'text' => 'Module actif pour gerer les recommandations associees aux recettes.',
    ],
    'objectives' => [
        'title' => 'Objectifs',
        'text' => 'Module actif pour la gestion des objectifs caloriques et de la progression utilisateur.',
    ],
    'community' => [
        'title' => 'Community',
        'text' => 'Module actif pour gerer les posts, commentaires, reactions et signalements.',
    ],
    'community_reports' => [
        'title' => 'Signalements',
        'text' => 'Module actif pour moderer les contenus signales par les utilisateurs.',
    ],
    'news' => [
        'title' => 'Articles',
        'text' => 'Module actif pour gerer les articles nutrition et wellness.',
    ],
];

$activeModuleKey = 'dashboard';
if ($isUsersAction) {
    $activeModuleKey = 'users';
} elseif ($isCommunityReportsAction) {
    $activeModuleKey = 'community_reports';
} elseif ($isCommunityAction) {
    $activeModuleKey = 'community';
} elseif ($isNewsAction) {
    $activeModuleKey = 'news';
} elseif ($isRecommendationsAction) {
    $activeModuleKey = 'recommendations';
} elseif ($isRecipesAction) {
    $activeModuleKey = 'recipes';
} elseif ($isFoodsAction) {
    $activeModuleKey = 'foods';
} elseif ($isObjectivesAction) {
    $activeModuleKey = 'objectives';
} elseif ($isTrackingAction) {
    $activeModuleKey = 'tracking';
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
            <a href="/Web/index.php?action=admin-dashboard" class="admin-brand-link">
                <img
                    src="/Web/view/assets/images/logo.png"
                    alt="Smart Nutrition"
                    class="brand-logo"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';"
                >
                <span class="brand-fallback"><i class="fa-solid fa-leaf"></i> Smart Nutrition</span>
            </a>
        </div>

        <div class="admin-menu-section">
            <p class="admin-menu-title admin-side-section-title">Navigation</p>
            <a href="/Web/index.php?action=admin-dashboard" class="admin-side-link<?= $isDashboardAction ? ' active' : '' ?>">
                <span>Ã°Å¸Â§Â­</span>
                <span>Dashboard</span>
            </a>
            <a href="/Web/index.php?action=users-list" class="admin-side-link<?= $isUsersAction ? ' active' : '' ?>">
                <span>Ã°Å¸â€˜Â¥</span>
                <span>Utilisateurs</span>
            </a>
        </div>

        <div class="admin-menu-section admin-modules-section">
            <p class="admin-menu-title admin-side-section-title">Modules</p>

            <a href="/Web/index.php?action=nutrition_dashboard" class="admin-side-link<?= $isTrackingAction ? ' active' : '' ?>">
                <span>Ã°Å¸ÂÅ½</span>
                <span>Suivi</span>
            </a>

            <a href="/Web/index.php?action=admin-recipes" class="admin-side-link<?= $isRecipesAction ? ' active' : '' ?>">
                <span>Ã°Å¸â€œâ€“</span>
                <span>Recettes</span>
            </a>

            <a href="/Web/index.php?action=products-admin" class="admin-side-link<?= $isFoodsAction ? ' active' : '' ?>">
                <span>Ã°Å¸ÂÂ</span>
                <span>Ecommerce</span>
            </a>

            <a href="/Web/index.php?action=admin-recommendations" class="admin-side-link<?= $isRecommendationsAction ? ' active' : '' ?>">
                <span>Ã°Å¸â€™â„¢</span>
                <span>Recommandations</span>
            </a>

            <a href="<?= htmlspecialchars($objectifsHref, ENT_QUOTES, 'UTF-8') ?>" class="admin-side-link<?= $isObjectivesAction ? ' active' : '' ?>">
                <span>Ã°Å¸Å½Â¯</span>
                <span>Objectifs</span>
            </a>

            <a href="/Web/index.php?action=admin-community" class="admin-side-link<?= $isCommunityAction ? ' active' : '' ?>">
                <span>Ã°Å¸â€™Â¬</span>
                <span>Community</span>
            </a>

            <a href="/Web/index.php?action=admin-community-reports" class="admin-side-link<?= $isCommunityReportsAction ? ' active' : '' ?>">
                <span>Ã°Å¸Å¡Â©</span>
                <span>Reports Community</span>
            </a>

            <a href="<?= htmlspecialchars($newsHref, ENT_QUOTES, 'UTF-8') ?>" class="admin-side-link<?= $isNewsAction ? ' active' : '' ?>">
                <span>Ã°Å¸â€œÂ°</span>
                <span>News / Articles</span>
            </a>
            <?php if (!$adminNewsRouteExists): ?>
                <!-- TODO: add admin-news route in index.php when the back-office news module is wired -->
            <?php endif; ?>

            <div class="admin-module-description admin-side-info-card" tabindex="-1">
                <h4><?= htmlspecialchars($currentModule['title']) ?></h4>
                <p><?= htmlspecialchars($currentModule['text']) ?></p>
            </div>
        </div>
    </aside>

    <header class="admin-topbar">
        <div class="admin-top-actions">
            <div class="notification-center admin-notification-center" data-notification-endpoint="/Web/controller/notificationController.php">
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

            <a href="/Web/index.php?action=logout" class="admin-logout-btn" title="Deconnexion">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </div>
    </header>
</div>
