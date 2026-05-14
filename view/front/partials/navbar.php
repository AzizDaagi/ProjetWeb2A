<?php
$baseUrl = '/Web';
$currentAction = (string) ($_GET['action'] ?? '');
$currentController = (string) ($_GET['controller'] ?? '');
$showNutritionSubnav = in_array($currentAction, [
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
    'nutrition_external_lookup',
    'nutrition_usda_lookup',
    'chrono_nutrition',
    'prediction_dashboard',
    'prediction_weekly_trend',
    'prediction_scenarios',
    'prediction_goal_date',
    'prediction_what_if',
    'prediction_confidence',
    'stats',
], true) || in_array($currentController, ['suivi', 'objectif', 'stats'], true);
?>
<nav class="navbar">

    <div class="navbar-brand">
        <a
            href="<?= htmlspecialchars($baseUrl) ?>/index.php?action=home"
            class="brand-link"
            style="background: transparent !important; box-shadow: none !important; border: 0 !important; border-radius: 0 !important; padding: 0 !important;">
            <img
                src="/Web/view/assets/images/logo.png"
                alt="Smart Nutrition"
                class="brand-logo navbar-preview-logo"
                style="background: transparent !important; box-shadow: none !important; border: 0 !important; border-radius: 0 !important;"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">

            <span class="brand-fallback">
                <i class="fa-solid fa-leaf"></i> Smart Nutrition
            </span>
        </a>
    </div>

    <ul class="navbar-menu">
        <li>
            <a href="<?= htmlspecialchars($baseUrl) ?>/index.php?action=profile" class="nav-link">
                <i class="fa-solid fa-user"></i> Mon profile
            </a>
        </li>

        <li>
            <a href="<?= htmlspecialchars($baseUrl) ?>/index.php?action=tracking-management" class="nav-link">
                <i class="fa-solid fa-chart-line"></i> Activite sportif
            </a>
        </li>

        <li>
            <a href="<?= htmlspecialchars($baseUrl) ?>/index.php?action=recipes-management" class="nav-link">
                <i class="fa-solid fa-book-open"></i> Recette alimentation
            </a>
        </li>

        <li>
            <a href="<?= htmlspecialchars($baseUrl) ?>/index.php?action=foods-management" class="nav-link">
                <i class="fa-solid fa-apple-whole"></i> Ecommerce
            </a>
        </li>

        <li>
            <a href="<?= htmlspecialchars($baseUrl) ?>/index.php?action=recommendations-management" class="nav-link">
                <i class="fa-solid fa-users"></i> Communaute
            </a>
        </li>

        <li>
            <a href="<?= htmlspecialchars($baseUrl) ?>/index.php?action=nutrition_dashboard" class="nav-link">
                <i class="fa-solid fa-calendar-check"></i> suivi nutritionnel
            </a>
        </li>

        <?php if (($_SESSION['user_role'] ?? 'user') === 'admin'): ?>
        <li>
            <a href="<?= htmlspecialchars($baseUrl) ?>/index.php?action=admin-dashboard" class="nav-link">
                <i class="fa-solid fa-gear"></i> Back Office
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <div class="navbar-footer">
        <button type="button" id="themeToggle" class="nav-link theme-toggle">
            <i class="fa-solid fa-moon"></i> Dark
        </button>
    </div>

</nav>

<?php if ($showNutritionSubnav): ?>
    <?php require __DIR__ . '/nutrition_navbar.php'; ?>
<?php endif; ?>

<?php require __DIR__ . '/chatbot_widget.php'; ?>
