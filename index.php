<?php

session_start();

require_once __DIR__ . '/env.php';

require_once __DIR__ . '/model/Database.php';
require_once __DIR__ . '/controller/AuthController.php';
require_once __DIR__ . '/controller/UserController.php';
require_once __DIR__ . '/controller/WeatherController.php';
require_once __DIR__ . '/controller/FrontController.php';
require_once __DIR__ . '/controller/ActiviteController.php';
require_once __DIR__ . '/controller/AdminController.php';
require_once __DIR__ . '/controller/NutritionRequestController.php';
require_once __DIR__ . '/controller/ProduitController.php';
require_once __DIR__ . '/controller/CartController.php';
require_once __DIR__ . '/controller/CommandeController.php';

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
    'admin-community-review-post',
    'admin-recipes',
    'admin-recommendations',
    'products-admin',
    'product-create',
    'product-edit',
    'product-delete',
    'products-pending',
    'product-approve',
    'products-prediction',
    'products-predict',
    'product-predict',
    'admin-orders',
    'admin-order-edit',
    'admin-order-delete',
    'admin-order-pdf'
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
    'recipe-details',
    'recipe-details-aliment',
    'recipe-generate',
    'recipe-optimize',
    'recipe-save-optimization',
    'recipe-stats',
    'recipe-export',
    'foods-management',
    'product-submit',
    'cart-add',
    'cart-view',
    'cart-update',
    'cart-remove',
    'cart-checkout',
    'cart-process',
    'cart-clear',
    'order-create',
    'order-list',
    'order-edit',
    'order-delete',
    'tracking-management'
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

} elseif ($action === 'admin-recipes') {
    $pageTitle = 'Back Office - Recettes';
    $isAdminTemplate = true;
    $bodyClass = trim((string) (($bodyClass ?? '') . ' backoffice-page recipes-admin-page'));
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/backoffice/manage_recettes.php';
    include __DIR__ . '/view/layouts/footer.php';

} elseif ($action === 'admin-recommendations') {
    $pageTitle = 'Back Office - Recommandations';
    $isAdminTemplate = true;
    $bodyClass = trim((string) (($bodyClass ?? '') . ' backoffice-page recommendations-admin-page'));
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/backoffice/manage_recommandations.php';
    include __DIR__ . '/view/layouts/footer.php';

} elseif ($action === 'auth-management') {
    $pageTitle = 'Authentification';
    if ($isAdminSession) {
        $isAdminTemplate = true;
    }
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/front/modules/auth-management.php';
    include __DIR__ . '/view/layouts/footer.php';

} elseif ($action === 'recipes-management') {
    $pageTitle = 'Nos recettes';
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/frontoffice/liste_recettes.php';
    include __DIR__ . '/view/layouts/footer.php';

} elseif ($action === 'recipe-details') {
    $pageTitle = 'Detail recette';
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/frontoffice/details_recette.php';
    include __DIR__ . '/view/layouts/footer.php';

} elseif ($action === 'recipe-details-aliment') {
    $pageTitle = 'Detail aliment';
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/frontoffice/details_aliment.php';
    include __DIR__ . '/view/layouts/footer.php';

} elseif ($action === 'recipe-generate') {
    $pageTitle = 'Generation recette';
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/frontoffice/generate_recette.php';
    include __DIR__ . '/view/layouts/footer.php';

} elseif ($action === 'recipe-optimize') {
    $pageTitle = 'Optimisation recette';
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/frontoffice/optimiser_recette.php';
    include __DIR__ . '/view/layouts/footer.php';

} elseif ($action === 'recipe-save-optimization') {
    include __DIR__ . '/view/frontoffice/save_optimisation.php';

} elseif ($action === 'recipe-stats') {
    $pageTitle = 'Statistiques nutritionnelles';
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/frontoffice/stats_nutrition.php';
    include __DIR__ . '/view/layouts/footer.php';

} elseif ($action === 'recipe-export') {
    include __DIR__ . '/view/frontoffice/export_pdf.php';

} elseif ($action === 'foods-management') {
    $products = new ProduitController();
    $products->frontList();

} elseif ($action === 'product-submit') {
    $products = new ProduitController();
    $products->frontCreate();

} elseif ($action === 'products-admin') {
    $products = new ProduitController();
    $products->backList();

} elseif ($action === 'product-create') {
    $products = new ProduitController();
    $products->create();

} elseif ($action === 'product-edit') {
    $products = new ProduitController();
    $products->edit();

} elseif ($action === 'product-delete') {
    $products = new ProduitController();
    $products->delete();

} elseif ($action === 'products-pending') {
    $products = new ProduitController();
    $products->pending();

} elseif ($action === 'product-approve') {
    $products = new ProduitController();
    $products->approve();

} elseif ($action === 'products-prediction') {
    $products = new ProduitController();
    $products->predictionPanel();

} elseif ($action === 'products-predict') {
    $products = new ProduitController();
    $products->formPredict();

} elseif ($action === 'product-predict') {
    $products = new ProduitController();
    $products->predictProductStats();

} elseif ($action === 'cart-add') {
    $cart = new CartController();
    $cart->add();

} elseif ($action === 'cart-view') {
    $cart = new CartController();
    $cart->view();

} elseif ($action === 'cart-update') {
    $cart = new CartController();
    $cart->update();

} elseif ($action === 'cart-remove') {
    $cart = new CartController();
    $cart->remove();

} elseif ($action === 'cart-checkout') {
    $cart = new CartController();
    $cart->checkoutForm();

} elseif ($action === 'cart-process') {
    $cart = new CartController();
    $cart->checkout();

} elseif ($action === 'cart-clear') {
    $cart = new CartController();
    $cart->clear();

} elseif ($action === 'order-create') {
    $orders = new CommandeController();
    $orders->createFront();

} elseif ($action === 'order-list') {
    $orders = new CommandeController();
    $orders->frontList();

} elseif ($action === 'order-edit') {
    $orders = new CommandeController();
    $orders->editFront();

} elseif ($action === 'order-delete') {
    $orders = new CommandeController();
    $orders->deleteFront();

} elseif ($action === 'admin-orders') {
    $orders = new CommandeController();
    $orders->adminList();

} elseif ($action === 'admin-order-edit') {
    $orders = new CommandeController();
    $orders->editAdmin();

} elseif ($action === 'admin-order-delete') {
    $orders = new CommandeController();
    $orders->deleteAdmin();

} elseif ($action === 'admin-order-pdf') {
    $orders = new CommandeController();
    $orders->downloadAdminPdf();

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
    $front = new FrontController();
    $front->activites();

} elseif ($action === 'suivi-nutritionnel') {
    $pageTitle = 'Suivi Nutritionnel';
    $moduleTitle = 'Suivi Nutritionnel';
    $moduleDescription = 'Cette section est désormais fusionnée avec Activité Sportif. Ce module est vide.';
    if ($isAdminSession) {
        $isAdminTemplate = true;
    }
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/front/modules/coming-soon.php';
    include __DIR__ . '/view/layouts/footer.php';

} elseif ($action === 'planner-management') {
    header('Location: /projet-web-25-26/index.php?action=nutrition_dashboard');
    exit;

} elseif ($action === 'activites') {
    $front = new FrontController();
    $front->activites();

} elseif ($action === 'showExercices') {
    $front = new FrontController();
    $front->showExercices();

} elseif ($action === 'export_activite_pdf') {
    $front = new FrontController();
    $front->exportActivitePDF();

} elseif ($action === 'nutrition_request') {
    $front = new FrontController();
    $front->nutritionRequest();

} elseif ($action === 'process_nutrition_request') {
    $front = new FrontController();
    $front->processNutritionRequest();

} elseif ($action === 'nutrition_success') {
    $front = new FrontController();
    $front->nutritionSuccess();

} elseif ($action === 'my_nutrition_requests') {
    $front = new FrontController();
    $front->myRequests();

} elseif ($action === 'edit_nutrition_request') {
    $front = new FrontController();
    $front->editRequest();

} elseif ($action === 'update_nutrition_request') {
    $front = new FrontController();
    $front->updateRequest();

} elseif ($action === 'delete_nutrition_request') {
    $front = new FrontController();
    $front->deleteRequest();

} elseif ($action === 'export_nutrition_pdf') {
    $front = new FrontController();
    $front->exportNutritionPDF();

} elseif ($action === 'admin_dashboard') {
    $admin = new AdminController();
    $admin->dashboard();

} elseif ($action === 'admin_index') {
    $back = new ActiviteController();
    $back->index();

} elseif ($action === 'admin_show') {
    $back = new ActiviteController();
    $back->show();

} elseif ($action === 'createActivite') {
    $back = new ActiviteController();
    $back->createActivite();

} elseif ($action === 'addExercice') {
    $back = new ActiviteController();
    $back->addExercice();

} elseif ($action === 'editExercice') {
    $back = new ActiviteController();
    $back->editExercice();

} elseif ($action === 'updateExercice') {
    $back = new ActiviteController();
    $back->updateExercice();

} elseif ($action === 'deleteExercice') {
    $back = new ActiviteController();
    $back->deleteExercice();

} elseif ($action === 'editActivite') {
    $back = new ActiviteController();
    $back->editActivite();

} elseif ($action === 'updateActivite') {
    $back = new ActiviteController();
    $back->updateActivite();

} elseif ($action === 'deleteActivite') {
    $back = new ActiviteController();
    $back->deleteActivite();

} elseif ($action === 'admin_requests') {
    $nutrition = new NutritionRequestController();
    $nutrition->index();

} elseif ($action === 'admin_edit_request') {
    $nutrition = new NutritionRequestController();
    $nutrition->edit();

} elseif ($action === 'admin_update_request') {
    $nutrition = new NutritionRequestController();
    $nutrition->update();

} elseif ($action === 'admin_delete_request') {
    $nutrition = new NutritionRequestController();
    $nutrition->delete();

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
?>
