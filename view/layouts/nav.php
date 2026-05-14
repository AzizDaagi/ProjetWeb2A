<?php
if (!empty($GLOBALS['front_nav_rendered'])) {
    return;
}
$GLOBALS['front_nav_rendered'] = true;

$baseUrl = '/projet-web-25-26';
$currentAction = (string) ($_GET['action'] ?? 'home');

$isHomeActive = $currentAction === 'home';
$isProfileActive = $currentAction === 'profile';
$isNutritionActive = in_array($currentAction, [
    'suivi',
    'objectif',
    'nutrition_dashboard',
    'nutrition_dashboard_summary',
    'nutrition_health_score',
    'nutrition_daily_recommendations',
    'nutrition_weekly_analysis',
    'nutrition_smart_reminder',
    'nutrition_water_today',
    'nutrition_water_add',
    'nutrition_usda_lookup',
    'chrono_nutrition',
    'chrono_profile_save',
    'chrono_profile_get',
    'chrono_optimal_timing',
    'chrono_fasting_window',
    'chrono_nutrient_timing',
    'chrono_sleep_sync',
    'prediction_dashboard',
    'prediction_weekly_trend',
    'prediction_scenarios',
    'prediction_goal_date',
    'prediction_what_if',
    'prediction_confidence',
    'chatbot',
    'clear_chat',
    'clearChat',
], true);
$isRecipesActive = in_array($currentAction, [
    'recipes-management',
    'recipe-details',
    'recipe-details-aliment',
    'recipe-generate',
    'recipe-optimize',
    'recipe-save-optimization',
    'recipe-stats',
    'recipe-export',
], true);
$isCommunityActive = $currentAction === 'community';
$isSportActive = stripos($currentAction, 'tracking') !== false
    || stripos($currentAction, 'sport') !== false
    || stripos($currentAction, 'activity') !== false
    || stripos($currentAction, 'activite') !== false
    || in_array($currentAction, ['activites', 'showExercices', 'weather-sport'], true);
?>

<nav class="site-navbar" aria-label="Navigation principale">
    <div class="site-navbar__inner">
        <a class="site-navbar__brand" href="<?= $baseUrl ?>/index.php?action=home">
            <img
                src="<?= $baseUrl ?>/view/assets/images/logo.png"
                alt="Smart Nutrition"
                class="brand-logo"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';"
            >
            <span class="brand-fallback"><i class="fa-solid fa-leaf"></i> Smart Nutrition</span>
            <span class="site-navbar__brand-text">Smart Nutrition</span>
        </a>

        <div class="site-navbar__links">
            <a href="<?= $baseUrl ?>/index.php?action=home" class="site-navbar__link<?= $isHomeActive ? ' active' : '' ?>">
                <i class="fa-solid fa-house"></i>
                <span>Accueil</span>
            </a>
            <a href="<?= $baseUrl ?>/index.php?action=profile" class="site-navbar__link<?= $isProfileActive ? ' active' : '' ?>">
                <i class="fa-solid fa-user"></i>
                <span>Profil</span>
            </a>
            <a href="<?= $baseUrl ?>/index.php?action=nutrition_dashboard" class="site-navbar__link<?= $isNutritionActive ? ' active' : '' ?>">
                <i class="fa-solid fa-heart-pulse"></i>
                <span>Suivi nutritionnel</span>
            </a>
            <a href="<?= $baseUrl ?>/index.php?action=recipes-management" class="site-navbar__link<?= $isRecipesActive ? ' active' : '' ?>">
                <i class="fa-solid fa-book-open"></i>
                <span>Recettes</span>
            </a>
            <a href="<?= $baseUrl ?>/index.php?action=tracking-management" class="site-navbar__link<?= $isSportActive ? ' active' : '' ?>">
                <i class="fa-solid fa-person-running"></i>
                <span>Activite sportive</span>
            </a>
            <a href="<?= $baseUrl ?>/index.php?action=community" class="site-navbar__link<?= $isCommunityActive ? ' active' : '' ?>">
                <i class="fa-solid fa-users"></i>
                <span>Community</span>
            </a>
        </div>

        <div class="site-navbar__actions">
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="notification-center" data-notification-endpoint="<?= $baseUrl ?>/controller/notificationController.php">
                    <button type="button" id="notificationToggle" class="site-navbar__button notification-toggle" aria-label="Notifications" aria-expanded="false">
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
            <?php endif; ?>

            <button type="button" id="themeToggle" class="site-navbar__button theme-toggle" aria-label="Changer le mode de couleur" aria-pressed="false">
                <i class="fa-solid fa-moon"></i>
                <span>Sombre</span>
            </button>

            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="site-navbar__pill">
                    Connecte : <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur') ?></strong>
                </span>
                <a href="<?= $baseUrl ?>/index.php?action=logout" class="site-navbar__pill site-navbar__logout">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Deconnexion</span>
                </a>
            <?php else: ?>
                <a href="<?= $baseUrl ?>/index.php?action=login" class="site-navbar__pill">
                    <i class="fa-solid fa-lock"></i>
                    <span>Connexion</span>
                </a>
                <a href="<?= $baseUrl ?>/index.php?action=register" class="site-navbar__pill">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Inscription</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>
