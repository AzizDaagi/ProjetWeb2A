<?php

session_start();

if (file_exists(__DIR__ . '/env.php')) {
    require_once __DIR__ . '/env.php';
}

$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }

        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        if ($key !== '' && getenv($key) === false) {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
}

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
    header('Location: /projet-web-25-26/index.php?action=login');
    exit;
}

$isAdminSession = isset($_SESSION['user_id']) && (($_SESSION['user_role'] ?? 'user') === 'admin');

$adminOnlyActions = [
    'admin-dashboard',
    'users-list',
    'users-search',
    'users-report',
    'edit-user',
    'create-user',
    'store-user',
    'update-user',
    'delete-user',
    'auth-management',
    'recommendations-management',
    'admin-community',
    'admin-community-reports',
    'admin-community-report-details',
    'admin-community-review-post'
];

$clientOnlyActions = [
    'home',
    'profile',
    'update-profile',
    'save-face-descriptor',
    'clear-face-descriptor',
    'weather-sport',
    'community',
    'recipes-management',
    'foods-management',
    'tracking-management',
    'planner-management'
];

if (isset($_SESSION['user_id'])) {
    if ($isAdminSession && in_array($action, $clientOnlyActions, true)) {
        header('Location: /projet-web-25-26/index.php?action=admin-dashboard');
        exit;
    }

    if (!$isAdminSession && in_array($action, $adminOnlyActions, true)) {
        header('Location: /projet-web-25-26/index.php?action=home');
        exit;
    }
}

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

} elseif ($action === 'community') {
    if ($isAdminSession) {
        header('Location: /projet-web-25-26/index.php?action=admin-community');
        exit;
    }

    $pageTitle = 'Smart Nutrition - CommunautÃ©';
    $showNav = true;
    $isAdminTemplate = false;
    $bodyClass = trim((string) (($bodyClass ?? '') . ' front-community-page'));
    $additionalStylesheets = [
        '/projet-web-25-26/view/back/style/community.css?v=' . filemtime(__DIR__ . '/view/back/style/community.css'),
        'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'
    ];
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/front/community.php';
    include __DIR__ . '/view/layouts/footer.php';

} elseif ($action === 'recommendations-management') {
    if ($isAdminSession) {
        header('Location: /projet-web-25-26/index.php?action=admin-community');
        exit;
    }

    header('Location: /projet-web-25-26/index.php?action=community');
    exit;

} elseif ($action === 'admin-community') {
    if (!$isAdminSession) {
        header('Location: /projet-web-25-26/index.php?action=home');
        exit;
    }

    $pageTitle = 'Back Office - Communaute';
    $isAdminTemplate = true;
    $bodyClass = trim((string) (($bodyClass ?? '') . ' backoffice-page community-admin-page'));
    $additionalStylesheets = [
        'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
        '/projet-web-25-26/view/back/style/community.css?v=' . filemtime(__DIR__ . '/view/back/style/community.css')
    ];
    $additionalScripts = [
        'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
        '/projet-web-25-26/view/back/style/community.js?v=' . filemtime(__DIR__ . '/view/back/style/community.js')
    ];
    define('SMART_ADMIN_VIEW', true);
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/back/community.php';
    include __DIR__ . '/view/layouts/footer.php';

} elseif ($action === 'admin-community-reports') {
    if (!$isAdminSession) {
        header('Location: /projet-web-25-26/index.php?action=home');
        exit;
    }

    $pageTitle = 'Back Office - Signalements';
    $isAdminTemplate = true;
    $bodyClass = trim((string) (($bodyClass ?? '') . ' backoffice-page community-admin-page'));
    $additionalStylesheets = [
        '/projet-web-25-26/view/back/style/community.css?v=' . filemtime(__DIR__ . '/view/back/style/community.css')
    ];
    $additionalScripts = [
        '/projet-web-25-26/view/back/style/community.js?v=' . filemtime(__DIR__ . '/view/back/style/community.js')
    ];
    define('SMART_ADMIN_VIEW', true);
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/back/reports.php';
    include __DIR__ . '/view/layouts/footer.php';

} elseif ($action === 'admin-community-report-details') {
    if (!$isAdminSession) {
        header('Location: /projet-web-25-26/index.php?action=home');
        exit;
    }

    $pageTitle = 'Back Office - Details du signalement';
    $isAdminTemplate = true;
    $bodyClass = trim((string) (($bodyClass ?? '') . ' backoffice-page community-admin-page'));
    $additionalStylesheets = [
        '/projet-web-25-26/view/back/style/community.css?v=' . filemtime(__DIR__ . '/view/back/style/community.css')
    ];
    $additionalScripts = [
        '/projet-web-25-26/view/back/style/community.js?v=' . filemtime(__DIR__ . '/view/back/style/community.js')
    ];
    define('SMART_ADMIN_VIEW', true);
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/back/report_details.php';
    include __DIR__ . '/view/layouts/footer.php';

} elseif ($action === 'admin-community-review-post') {
    if (!$isAdminSession) {
        header('Location: /projet-web-25-26/index.php?action=home');
        exit;
    }

    $pageTitle = 'Back Office - Revision publication';
    $isAdminTemplate = true;
    $bodyClass = trim((string) (($bodyClass ?? '') . ' backoffice-page community-admin-page'));
    $additionalStylesheets = [
        '/projet-web-25-26/view/back/style/community.css?v=' . filemtime(__DIR__ . '/view/back/style/community.css')
    ];
    $additionalScripts = [
        '/projet-web-25-26/view/back/style/community.js?v=' . filemtime(__DIR__ . '/view/back/style/community.js')
    ];
    define('SMART_ADMIN_VIEW', true);
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/back/review_post.php';
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
    header('Location: /projet-web-25-26/index.php?action=nutrition_dashboard');
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
        header('Location: /projet-web-25-26/index.php?action=' . $fallbackAction);
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

