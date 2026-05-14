<?php

session_start();

require_once __DIR__ . '/env.php';

require_once __DIR__ . '/model/Database.php';
require_once __DIR__ . '/controller/AuthController.php';
require_once __DIR__ . '/controller/UserController.php';
require_once __DIR__ . '/controller/WeatherController.php';
require_once __DIR__ . '/controller/ProduitController.php';
require_once __DIR__ . '/controller/CartController.php';
require_once __DIR__ . '/controller/CommandeController.php';

// Controllers from gestionActiviteesportive feature
require_once __DIR__ . '/controller/FrontController.php';
require_once __DIR__ . '/controller/ActiviteController.php';
require_once __DIR__ . '/controller/AdminController.php';
require_once __DIR__ . '/controller/NutritionRequestController.php';

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
$requestMethod = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

$publicActions = ['login', 'register', 'face-login', 'google-login', 'forgot', 'reset-password'];
if (!isset($_SESSION['user_id']) && !in_array($action, $publicActions, true)) {
    header('Location: /projet-web-25-26/index.php?action=login');
    exit;
}

$isAdminSession = isset($_SESSION['user_id']) && (($_SESSION['user_role'] ?? 'user') === 'admin');
$baseUrl = '/projet-web-25-26';

$renderAdminFragment = static function (string $pageTitle, string $viewPath, array $vars = []) {
    $isAdminTemplate = true;
    $bodyClass = trim((string) (($vars['bodyClass'] ?? '') . ' admin-page-body'));
    extract($vars, EXTR_SKIP);
    require __DIR__ . '/view/layouts/header.php';
    require $viewPath;
    require __DIR__ . '/view/layouts/footer.php';
};

$normalizeAdminAlimentPayload = static function (array $source): ?array {
    $nom = trim((string) ($source['nom'] ?? ''));
    $calories = isset($source['calories']) ? (float) $source['calories'] : 0;
    $proteines = isset($source['proteines']) ? (float) $source['proteines'] : 0;
    $glucides = isset($source['glucides']) ? (float) $source['glucides'] : 0;
    $lipides = isset($source['lipides']) ? (float) $source['lipides'] : 0;
    $unite = (string) ($source['unite'] ?? 'g');
    $type = (string) ($source['type'] ?? '');

    if (
        $nom === '' ||
        $calories <= 0 ||
        $proteines < 0 ||
        $glucides < 0 ||
        $lipides < 0 ||
        !in_array($unite, ['g', 'piece'], true) ||
        !in_array($type, ['proteine', 'glucide', 'lipide'], true)
    ) {
        $_SESSION['admin_aliment_error'] = 'Nom, calories, unite, type et macros valides sont obligatoires';
        return null;
    }

    return [
        'nom' => $nom,
        'calories' => $calories,
        'unite' => $unite,
        'proteines' => $proteines,
        'glucides' => $glucides,
        'lipides' => $lipides,
        'type' => $type,
        'sucre_g' => isset($source['sucre_g']) ? (float) $source['sucre_g'] : 0,
        'fibres' => isset($source['fibres']) ? (float) $source['fibres'] : 0,
        'image_url' => trim((string) ($source['image_url'] ?? '')) ?: null,
    ];
};

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
    'admin-aliments',
    'admin-aliment-create',
    'admin-aliment-store',
    'admin-aliment-edit',
    'admin-aliment-update',
    'admin-aliment-delete',
    'admin-objectifs',
    'admin-objectif-show',
    'admin-objectif-delete',
    'admin-recipes',
    'admin-recommendations',
    'admin-sport',
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
    'admin-order-pdf',
    // Activity admin actions
    'admin_index',
    'admin_show',
    'createActivite',
    'addExercice',
    'editExercice',
    'updateExercice',
    'deleteExercice',
    'editActivite',
    'updateActivite',
    'deleteActivite',
    'admin_requests',
    'admin_edit_request',
    'admin_update_request',
    'admin_delete_request',
    'export_requests_pdf'
];

$clientOnlyActions = [
    'home',
    'profile',
    'update-profile',
    'save-face-descriptor',
    'clear-face-descriptor',
    'weather-sport',
    'community',
    'suivi',
    'objectif',
    'stats',
    'chatbot',
    'clear_chat',
    'clearChat',
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
    'tracking-management',
    // Activity client actions
    'activites',
    'showExercices',
    'export_activite_pdf',
    'nutrition_request',
    'process_nutrition_request',
    'nutrition_success',
    'my_nutrition_requests',
    'edit_nutrition_request',
    'update_nutrition_request',
    'delete_nutrition_request',
    'export_nutrition_pdf'
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
    if ($requestMethod === 'POST') {
        $auth->login();
    } else {
        $auth->showLogin();
    }

} elseif ($action === 'face-login') {
    $auth = new AuthController();
    if ($requestMethod === 'POST') {
        $auth->loginWithFace();
    } else {
        $auth->showLogin();
    }

} elseif ($action === 'google-login') {
    $auth = new AuthController();
    if ($requestMethod === 'POST') {
        $auth->loginWithGoogle();
    } else {
        $auth->showLogin();
    }

} elseif ($action === 'register') {
    $auth = new AuthController();
    if ($requestMethod === 'POST') {
        $auth->register();
    } else {
        $auth->showRegister();
    }

} elseif ($action === 'forgot') {
    $auth = new AuthController();
    if ($requestMethod === 'POST') {
        $auth->forgotPassword();
    } else {
        $auth->showForgotPassword();
    }

} elseif ($action === 'reset-password') {
    $auth = new AuthController();
    if ($requestMethod === 'POST') {
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

} elseif ($action === 'admin-aliments') {
    require_once __DIR__ . '/model/aliment.php';
    $pageTitle = 'Gestion des aliments';
    $db = Database::getConnection();
    $aliments = [];
    $alimentsError = null;

    try {
        $stmt = $db->query('SELECT * FROM aliments ORDER BY nom ASC');
        $aliments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $alimentsError = $e->getMessage();
    }

    $renderAdminFragment($pageTitle, __DIR__ . '/view/back/aliments/index.php', compact('pageTitle', 'baseUrl', 'aliments', 'alimentsError'));

} elseif ($action === 'admin-aliment-create') {
    $pageTitle = 'Ajouter un aliment';
    $aliment = $_SESSION['admin_aliment_old'] ?? [];
    unset($_SESSION['admin_aliment_old']);
    $renderAdminFragment($pageTitle, __DIR__ . '/view/back/aliments/create.php', compact('pageTitle', 'baseUrl', 'aliment'));

} elseif ($action === 'admin-aliment-store') {
    require_once __DIR__ . '/model/aliment.php';
    $_SESSION['admin_aliment_old'] = $_POST;
    $payload = $normalizeAdminAlimentPayload($_POST);
    if ($payload === null) {
        header('Location: ' . $baseUrl . '/index.php?action=admin-aliment-create');
        exit;
    }

    $alimentModel = new Aliment(Database::getConnection());
    $alimentModel->create($payload);
    unset($_SESSION['admin_aliment_old']);
    $_SESSION['admin_aliment_success'] = 'Aliment ajoute avec succes';
    header('Location: ' . $baseUrl . '/index.php?action=admin-aliments');
    exit;

} elseif ($action === 'admin-aliment-edit') {
    require_once __DIR__ . '/model/aliment.php';
    $pageTitle = 'Modifier un aliment';
    $alimentModel = new Aliment(Database::getConnection());
    $aliment = !empty($_GET['id']) ? $alimentModel->getById($_GET['id']) : null;

    if (!$aliment) {
        $_SESSION['admin_aliment_error'] = 'Aliment introuvable';
        header('Location: ' . $baseUrl . '/index.php?action=admin-aliments');
        exit;
    }

    $oldInput = $_SESSION['admin_aliment_old'] ?? [];
    unset($_SESSION['admin_aliment_old']);
    if (!empty($oldInput) && (int) ($oldInput['id'] ?? 0) === (int) $aliment['id']) {
        $aliment = array_merge($aliment, $oldInput);
    }

    $renderAdminFragment($pageTitle, __DIR__ . '/view/back/aliments/edit.php', compact('pageTitle', 'baseUrl', 'aliment'));

} elseif ($action === 'admin-aliment-update') {
    require_once __DIR__ . '/model/aliment.php';
    $_SESSION['admin_aliment_old'] = $_POST;
    $payload = $normalizeAdminAlimentPayload($_POST);
    $id = (int) ($_POST['id'] ?? 0);

    if ($payload === null || $id <= 0) {
        header('Location: ' . $baseUrl . '/index.php?action=admin-aliment-edit&id=' . urlencode((string) $id));
        exit;
    }

    $payload['id'] = $id;
    $alimentModel = new Aliment(Database::getConnection());
    $alimentModel->update($payload);
    unset($_SESSION['admin_aliment_old']);
    $_SESSION['admin_aliment_success'] = 'Aliment modifie avec succes';
    header('Location: ' . $baseUrl . '/index.php?action=admin-aliments');
    exit;

} elseif ($action === 'admin-aliment-delete') {
    require_once __DIR__ . '/model/aliment.php';
    if (!empty($_GET['id'])) {
        $alimentModel = new Aliment(Database::getConnection());
        $alimentModel->delete($_GET['id']);
        $_SESSION['admin_aliment_success'] = 'Aliment supprime avec succes';
    }
    header('Location: ' . $baseUrl . '/index.php?action=admin-aliments');
    exit;

} elseif ($action === 'admin-objectifs') {
    require_once __DIR__ . '/model/ObjectifCalculatorService.php';
    $pageTitle = 'Gestion des objectifs';
    $db = Database::getConnection();
    $objectifs = [];
    $objectifsError = null;
    $objectifCalculator = new ObjectifCalculatorService();

    try {
        $stmt = $db->query("
            SELECT
                o.*,
                COALESCE(rc.repas_count, 0) AS repas_count
            FROM objectif o
            LEFT JOIN (
                SELECT objectif_id, COUNT(*) AS repas_count
                FROM repas_consomme
                GROUP BY objectif_id
            ) rc ON rc.objectif_id = o.id
            ORDER BY o.id DESC
        ");
        $objectifs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($objectifs as &$objectif) {
            $objectif['activite_label'] = $objectifCalculator->getActiviteLabel($objectif['activite'] ?? null);
        }
        unset($objectif);
    } catch (Throwable $e) {
        $objectifsError = $e->getMessage();
    }

    $sexeOptions = $objectifCalculator->getSexeOptions();
    $objectifTypeOptions = $objectifCalculator->getObjectifTypeOptions();
    $renderAdminFragment($pageTitle, __DIR__ . '/view/back/objectifs/index.php', compact('pageTitle', 'baseUrl', 'objectifs', 'objectifsError', 'sexeOptions', 'objectifTypeOptions'));

} elseif ($action === 'admin-objectif-show') {
    require_once __DIR__ . '/model/objectif.php';
    require_once __DIR__ . '/model/ObjectifCalculatorService.php';
    $pageTitle = 'Detail objectif';
    $objectifModel = new Objectif(Database::getConnection());
    $objectifCalculator = new ObjectifCalculatorService();
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    $objectif = $id > 0 ? $objectifModel->getById($id) : null;

    if (!$objectif) {
        $_SESSION['admin_objectif_error'] = 'Objectif introuvable';
        header('Location: ' . $baseUrl . '/index.php?action=admin-objectifs');
        exit;
    }

    $objectifSummary = isset($objectif['poids'], $objectif['taille'], $objectif['age'], $objectif['sexe'], $objectif['activite'], $objectif['objectif_type'])
        ? $objectifCalculator->calculateNutritionTargets($objectif)
        : [];
    $repasCount = $objectifModel->countLinkedMeals($id);
    $sexeLabel = $objectifCalculator->getSexeLabel($objectif['sexe'] ?? 'homme');
    $activiteLabel = $objectifCalculator->getActiviteLabel($objectif['activite'] ?? null);
    $objectifTypeLabel = $objectifCalculator->getObjectifTypeLabel($objectif['objectif_type'] ?? 'maintien');
    $renderAdminFragment($pageTitle, __DIR__ . '/view/back/objectifs/show.php', compact('pageTitle', 'baseUrl', 'objectif', 'objectifSummary', 'repasCount', 'sexeLabel', 'activiteLabel', 'objectifTypeLabel'));

} elseif ($action === 'admin-objectif-delete') {
    require_once __DIR__ . '/model/objectif.php';
    $objectifModel = new Objectif(Database::getConnection());
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    if ($id > 0) {
        $deleted = $objectifModel->delete($id);
        if ($deleted) {
            $_SESSION['admin_objectif_success'] = 'Objectif supprime avec succes';
        } else {
            $_SESSION['admin_objectif_error'] = $objectifModel->getLastError() ?: "Impossible de supprimer l'objectif";
        }
    }
    header('Location: ' . $baseUrl . '/index.php?action=admin-objectifs');
    exit;

} elseif ($action === 'admin-recipes') {
    require_once __DIR__ . '/controller/RecetteController.php';
    $recipes = new RecetteController(Database::getConnection());
    $recipes->adminIndex();

} elseif ($action === 'admin-recommendations') {
    require_once __DIR__ . '/controller/RecommandationController.php';
    $recommendations = new RecommandationController(Database::getConnection());
    $recommendations->adminIndex();

} elseif ($action === 'admin-sport') {
    $back = new ActiviteController();
    $back->index();
} elseif ($action === 'auth-management') {
    $pageTitle = 'Authentification';
    if ($isAdminSession) {
        $isAdminTemplate = true;
    }
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/front/modules/auth-management.php';
    include __DIR__ . '/view/layouts/footer.php';

} elseif ($action === 'recipes-management') {
    require_once __DIR__ . '/controller/RecetteController.php';
    $recipes = new RecetteController(Database::getConnection());
    $recipes->showRecipesManagement();

} elseif ($action === 'recipe-details') {
    require_once __DIR__ . '/controller/RecetteController.php';
    $recipes = new RecetteController(Database::getConnection());
    $recipes->showRecipeDetails();

} elseif ($action === 'recipe-details-aliment') {
    require_once __DIR__ . '/controller/RecetteController.php';
    $recipes = new RecetteController(Database::getConnection());
    $recipes->showAlimentDetails();

} elseif ($action === 'recipe-generate') {
    require_once __DIR__ . '/controller/RecetteController.php';
    $recipes = new RecetteController(Database::getConnection());
    $recipes->showGenerator();

} elseif ($action === 'recipe-optimize') {
    require_once __DIR__ . '/controller/RecetteController.php';
    $recipes = new RecetteController(Database::getConnection());
    $recipes->showOptimizer();

} elseif ($action === 'recipe-save-optimization') {
    require_once __DIR__ . '/controller/RecetteController.php';
    $recipes = new RecetteController(Database::getConnection());
    $recipes->saveOptimization();

} elseif ($action === 'recipe-stats') {
    require_once __DIR__ . '/controller/RecetteController.php';
    $recipes = new RecetteController(Database::getConnection());
    $recipes->showStats();

} elseif ($action === 'recipe-export') {
    require_once __DIR__ . '/controller/RecetteController.php';
    $recipes = new RecetteController(Database::getConnection());
    $recipes->exportPdf();
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

    $pageTitle = 'Smart Nutrition - Communauté';
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

    if ($controllerName === 'backoffice') {
        $legacyAdminActionMap = [
            'suivi' => 'admin-aliments',
            'suiviCreate' => 'admin-aliment-create',
            'suiviStore' => 'admin-aliment-store',
            'suiviEdit' => 'admin-aliment-edit',
            'suiviUpdate' => 'admin-aliment-update',
            'suiviDelete' => 'admin-aliment-delete',
            'objectifs' => 'admin-objectifs',
            'objectifShow' => 'admin-objectif-show',
            'objectifDelete' => 'admin-objectif-delete',
        ];
        $legacyAction = (string) ($_GET['action'] ?? '');
        $targetAction = $legacyAdminActionMap[$legacyAction] ?? 'admin-dashboard';
        header('Location: ' . $baseUrl . '/index.php?action=' . $targetAction . (!empty($_GET['id']) ? '&id=' . urlencode((string) $_GET['id']) : ''));
        exit;
    }

    $routes = [
        'suivi' => 'suivictrl',
        'aliment' => 'alimentctrl',
        'objectif' => 'objectifctrl',
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
