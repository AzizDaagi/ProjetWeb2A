<?php
<<<<<<< HEAD

session_start();

$baseUrl = '/projet-web-25-26';

require_once __DIR__ . '/env.php';

require_once __DIR__ . '/model/Database.php';
require_once __DIR__ . '/controller/AuthController.php';
require_once __DIR__ . '/controller/UserController.php';
require_once __DIR__ . '/controller/WeatherController.php';

if (isset($_SESSION['user_id']) && !isset($_SESSION['user_role'])) {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare(
        'SELECT COALESCE(NULLIF(u.role, ""), "user") AS role
         FROM users u
         WHERE u.id = :id
         LIMIT 1'
    );
    $stmt->execute(['id' => (int) $_SESSION['user_id']]);
    $_SESSION['user_role'] = $stmt->fetchColumn() ?: 'user';
}

$defaultAction = 'login';
if (isset($_SESSION['user_id'])) {
    $defaultAction = (($_SESSION['user_role'] ?? 'user') === 'admin') ? 'admin-dashboard' : 'home';
}

$action = $_GET['action'] ?? $defaultAction;

$publicActions = ['login', 'register', 'face-login', 'google-login', 'forgot', 'reset-password'];
if (!isset($_SESSION['user_id']) && !in_array($action, $publicActions, true)) {
    header('Location: ' . $baseUrl . '/index.php?action=login');
    exit;
}

$isAdminSession = isset($_SESSION['user_id']) && (($_SESSION['user_role'] ?? 'user') === 'admin');

if ($action === 'home') {
    $pageTitle = 'Smart Nutrition - Accueil';
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/front/home.php';
    include __DIR__ . '/view/layouts/footer.php';
} elseif ($action === 'login') {
    $auth = new AuthController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $auth->login();
    } else {
        $auth->showLogin();
    }
} elseif ($action === 'face-login') {
    $auth = new AuthController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $auth->loginWithFace();
    } else {
        $auth->showLogin();
    }
} elseif ($action === 'google-login') {
    $auth = new AuthController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $auth->loginWithGoogle();
    } else {
        $auth->showLogin();
    }
} elseif ($action === 'register') {
    $auth = new AuthController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $auth->register();
    } else {
        $auth->showRegister();
    }
} elseif ($action === 'forgot') {
    $auth = new AuthController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $auth->forgotPassword();
    } else {
        $auth->showForgotPassword();
    }
} elseif ($action === 'reset-password') {
    $auth = new AuthController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $auth->performReset();
    } else {
        $auth->showResetForm();
    }
} elseif ($action === 'profile') {
    $user = new UserController();
    $user->profile();
} elseif ($action === 'update-profile') {
    $user = new UserController();
    $user->updateProfile();
} elseif ($action === 'save-face-descriptor') {
    $user = new UserController();
    $user->saveFaceDescriptor();
} elseif ($action === 'clear-face-descriptor') {
    $user = new UserController();
    $user->clearFaceDescriptor();
} elseif ($action === 'weather-sport') {
    $weather = new WeatherController();
    $weather->currentSportWeather();
} elseif ($action === 'logout') {
    $user = new UserController();
    $user->logout();
} elseif ($action === 'users-list') {
    $user = new UserController();
    $user->usersList();
} elseif ($action === 'users-search') {
    $user = new UserController();
    $user->usersSearch();
} elseif ($action === 'users-report') {
    $user = new UserController();
    $user->usersReport();
} elseif ($action === 'edit-user') {
    $user = new UserController();
    $user->editUser();
} elseif ($action === 'create-user') {
    $user = new UserController();
    $user->createUser();
} elseif ($action === 'store-user') {
    $user = new UserController();
    $user->storeUser();
} elseif ($action === 'update-user') {
    $user = new UserController();
    $user->updateUser();
} elseif ($action === 'delete-user') {
    $user = new UserController();
    $user->deleteUser();
} elseif ($action === 'admin-dashboard') {
    $user = new UserController();
    $user->adminDashboard();
} elseif ($action === 'auth-management') {
    $pageTitle = 'Authentification';
    if ($isAdminSession) {
        $isAdminTemplate = true;
    }
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/front/modules/auth-management.php';
    include __DIR__ . '/view/layouts/footer.php';
} elseif ($action === 'recipes-management') {
    $pageTitle = 'Recette alimentation';
    $moduleTitle = 'Recette alimentation';
    $moduleDescription = 'Module en cours de developpement. Vous pourrez creer, modifier et supprimer des recettes alimentaires.';
    if ($isAdminSession) {
        $isAdminTemplate = true;
    }
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/front/modules/coming-soon.php';
    include __DIR__ . '/view/layouts/footer.php';
} elseif ($action === 'foods-management') {
    $pageTitle = 'Ecommerce';
    $moduleTitle = 'Ecommerce';
    $moduleDescription = 'Module en cours de developpement. Vous pourrez gerer les produits, commandes et ventes.';
    if ($isAdminSession) {
        $isAdminTemplate = true;
    }
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/front/modules/coming-soon.php';
    include __DIR__ . '/view/layouts/footer.php';
} elseif ($action === 'recommendations-management') {
    $pageTitle = 'Communaute';
    $moduleTitle = 'Communaute';
    $moduleDescription = 'Module en cours de developpement. Vous pourrez gerer les interactions et les contenus de la communaute.';
    if ($isAdminSession) {
        $isAdminTemplate = true;
    }
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/front/modules/coming-soon.php';
    include __DIR__ . '/view/layouts/footer.php';
} elseif ($action === 'tracking-management') {
    $pageTitle = 'Activite sportif';
    $moduleTitle = 'Activite sportif';
    $moduleDescription = 'Module en cours de developpement. Vous pourrez suivre les activites sportives et la progression des utilisateurs.';
    if ($isAdminSession) {
        $isAdminTemplate = true;
    }
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/front/modules/coming-soon.php';
    include __DIR__ . '/view/layouts/footer.php';
} elseif ($action === 'planner-management') {
    header('Location: ' . $baseUrl . '/index.php?action=nutrition_dashboard');
    exit;
} else {
    $controllerName = $_GET['controller'] ?? 'suivi';

    if (!isset($_GET['controller']) && in_array($action, ['suivi', 'objectif', 'stats'], true)) {
        $controllerName = $action;
    }

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
    ], true)) {
        $controllerName = 'suivi';
    }

    if (!isset($_GET['controller']) && in_array($action, [
        'nutrition_external_lookup',
        'nutrition_usda_lookup',
    ], true)) {
        $controllerName = 'aliment';
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

    if (!isset($_GET['controller']) && in_array($action, [
        'prediction_dashboard',
        'prediction_weekly_trend',
        'prediction_scenarios',
        'prediction_goal_date',
        'prediction_what_if',
        'prediction_confidence',
    ], true)) {
        $controllerName = 'prediction';
    }

    $routes = [
        'suivi' => 'suivictrl',
        'aliment' => 'alimentctrl',
        'objectif' => 'objectifctrl',
        'backoffice' => 'BackofficeCtrl',
        'stats' => 'statsCtrl',
        'chatbot' => 'chatbotctrl',
        'reminder' => 'ReminderController',
        'nutritiondashboard' => 'NutritionDashboardController',
        'chronoNutrition' => 'ChronoNutritionController',
        'prediction' => 'PredictionController',
    ];

    if (!array_key_exists($controllerName, $routes)) {
        if (isset($_SESSION['user_id'])) {
            $fallbackAction = (($_SESSION['user_role'] ?? 'user') === 'admin') ? 'admin-dashboard' : 'home';
        } else {
            $fallbackAction = 'login';
        }

        header('Location: ' . $baseUrl . '/index.php?action=' . $fallbackAction);
        exit;
    }

    $controllerClass = $routes[$controllerName];
    require_once __DIR__ . "/controller/{$controllerClass}.php";

    $pdo = Database::getConnection();
    $controller = new $controllerClass($pdo);

    if (!method_exists($controller, $action)) {
        $action = 'index';
    }

    $controller->$action();
}
=======
// Redirect to the main front-office page (or back-office if you prefer)
header("Location: view/frontoffice/liste_recettes.php");
exit;
?>
>>>>>>> origin/GestionRecettes
