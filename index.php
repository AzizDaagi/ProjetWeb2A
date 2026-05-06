    <?php
    session_start();

    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    $pdo = new PDO("mysql:host=localhost;dbname=smart_nutrition", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $controllerName = $_GET['controller'] ?? 'suivi';
    $action = $_GET['action'] ?? 'index';

    if (!isset($_GET['controller']) && in_array($action, ['chatbot', 'clear_chat', 'clearChat'], true)) {
        $controllerName = 'chatbot';
    }

    if (!isset($_GET['controller']) && $action === 'test_reminder') {
        $controllerName = 'reminder';
    }

    if (!isset($_GET['controller']) && in_array($action, [
        'nutrition_dashboard',
        'nutrition_dashboard_summary',
        'nutrition_health_score',
        'nutrition_daily_recommendations',
        'nutrition_weekly_analysis',
        'nutrition_smart_reminder',
    ], true)) {
        $controllerName = 'nutritiondashboard';
    }
    if (!isset($_GET['controller']) && in_array($action, [
        'nutrition_water_today',
        'nutrition_water_add',
        'nutrition_usda_lookup',
    ], true)) {
        $controllerName = 'suivi';
    }
    if (!isset($_GET['controller']) && in_array($action, [
    'chrono_nutrition',
    'chrono_profile_save',
    'chrono_profile_get',
    'chrono_optimal_timing',
    'chrono_fasting_window',
    'chrono_nutrient_timing',
    'chrono_sleep_sync',
    ], true)) {
    $controllerName = 'chronoNutrition';
}

    $routes = [
        'suivi' => 'suivictrl',
        'objectif' => 'objectifctrl',
        'backoffice' => 'BackofficeCtrl',
        'stats' => 'statsCtrl',
        'chatbot' => 'chatbotctrl',
        'reminder' => 'ReminderController',
        'nutritiondashboard' => 'NutritionDashboardController',
        'chronoNutrition' => 'ChronoNutritionController',
    ];

    if (!array_key_exists($controllerName, $routes)) {
        $controllerName = 'suivi';
    }

    $controllerClass = $routes[$controllerName];
    require_once __DIR__ . "/controllers/{$controllerClass}.php";

    $controller = new $controllerClass($pdo);

    if (!method_exists($controller, $action)) {
        $action = 'index';
    }

    $controller->$action();
