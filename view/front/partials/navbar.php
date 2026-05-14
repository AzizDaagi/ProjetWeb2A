<?php
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
    'chatbot',
    'clear_chat',
    'clearChat',
    'stats',
], true) || in_array($currentController, ['suivi', 'objectif', 'stats'], true);

require __DIR__ . '/../../layouts/nav.php';

if ($showNutritionSubnav) {
    require __DIR__ . '/nutrition_navbar.php';
}

require __DIR__ . '/chatbot_widget.php';
