<?php

session_start();

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
    header('Location: /Web/index.php?action=login');
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
        header('Location: /Web/index.php?action=admin-dashboard');
        exit;
    }

    if (!$isAdminSession && in_array($action, $adminOnlyActions, true)) {
        header('Location: /Web/index.php?action=home');
        exit;
    }
}

if ($action === 'home') {
    $pageTitle = 'Smart Nutrition - Accueil';
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/frontoffice/home.php';
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
    include __DIR__ . '/view/frontoffice/modules/auth-management.php';
    include __DIR__ . '/view/layouts/footer.php';

} elseif ($action === 'recipes-management') {
    $pageTitle = 'Recette alimentation';
    $moduleTitle = 'Recette alimentation';
    $moduleDescription = 'Module en cours de developpement. Vous pourrez creer, modifier et supprimer des recettes alimentaires.';
    if ($isAdminSession) {
        $isAdminTemplate = true;
    }
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/frontoffice/modules/coming-soon.php';
    include __DIR__ . '/view/layouts/footer.php';

} elseif ($action === 'foods-management') {
    $pageTitle = 'Ecommerce';
    $moduleTitle = 'Ecommerce';
    $moduleDescription = 'Module en cours de developpement. Vous pourrez gerer les produits, commandes et ventes.';
    if ($isAdminSession) {
        $isAdminTemplate = true;
    }
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/frontoffice/modules/coming-soon.php';
    include __DIR__ . '/view/layouts/footer.php';

} elseif ($action === 'community') {
    if ($isAdminSession) {
        header('Location: /Web/index.php?action=admin-community');
        exit;
    }

    $pageTitle = 'Smart Nutrition - Communauté';
    $showNav = true;
    $isAdminTemplate = false;
    $bodyClass = trim((string) (($bodyClass ?? '') . ' front-community-page'));
    $additionalStylesheets = [
        '/Web/view/backOffice/style/community.css?v=' . filemtime(__DIR__ . '/view/backOffice/style/community.css'),
        'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'
    ];
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/frontoffice/community.php';
    include __DIR__ . '/view/layouts/footer.php';

} elseif ($action === 'recommendations-management') {
    if ($isAdminSession) {
        header('Location: /Web/index.php?action=admin-community');
        exit;
    }

    header('Location: /Web/index.php?action=community');
    exit;

} elseif ($action === 'admin-community') {
    if (!$isAdminSession) {
        header('Location: /Web/index.php?action=home');
        exit;
    }

    $pageTitle = 'Back Office - Communaute';
    $isAdminTemplate = true;
    $bodyClass = trim((string) (($bodyClass ?? '') . ' backoffice-page community-admin-page'));
    $additionalStylesheets = [
        'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
        '/Web/view/backOffice/style/community.css?v=' . filemtime(__DIR__ . '/view/backOffice/style/community.css')
    ];
    $additionalScripts = [
        'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
        '/Web/view/backOffice/style/community.js?v=' . filemtime(__DIR__ . '/view/backOffice/style/community.js')
    ];
    define('SMART_ADMIN_VIEW', true);
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/backOffice/community.php';
    include __DIR__ . '/view/layouts/footer.php';

} elseif ($action === 'admin-community-reports') {
    if (!$isAdminSession) {
        header('Location: /Web/index.php?action=home');
        exit;
    }

    $pageTitle = 'Back Office - Signalements';
    $isAdminTemplate = true;
    $bodyClass = trim((string) (($bodyClass ?? '') . ' backoffice-page community-admin-page'));
    $additionalStylesheets = [
        '/Web/view/backOffice/style/community.css?v=' . filemtime(__DIR__ . '/view/backOffice/style/community.css')
    ];
    $additionalScripts = [
        '/Web/view/backOffice/style/community.js?v=' . filemtime(__DIR__ . '/view/backOffice/style/community.js')
    ];
    define('SMART_ADMIN_VIEW', true);
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/backOffice/reports.php';
    include __DIR__ . '/view/layouts/footer.php';

} elseif ($action === 'admin-community-report-details') {
    if (!$isAdminSession) {
        header('Location: /Web/index.php?action=home');
        exit;
    }

    $pageTitle = 'Back Office - Details du signalement';
    $isAdminTemplate = true;
    $bodyClass = trim((string) (($bodyClass ?? '') . ' backoffice-page community-admin-page'));
    $additionalStylesheets = [
        '/Web/view/backOffice/style/community.css?v=' . filemtime(__DIR__ . '/view/backOffice/style/community.css')
    ];
    $additionalScripts = [
        '/Web/view/backOffice/style/community.js?v=' . filemtime(__DIR__ . '/view/backOffice/style/community.js')
    ];
    define('SMART_ADMIN_VIEW', true);
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/backOffice/report_details.php';
    include __DIR__ . '/view/layouts/footer.php';

} elseif ($action === 'admin-community-review-post') {
    if (!$isAdminSession) {
        header('Location: /Web/index.php?action=home');
        exit;
    }

    $pageTitle = 'Back Office - Revision publication';
    $isAdminTemplate = true;
    $bodyClass = trim((string) (($bodyClass ?? '') . ' backoffice-page community-admin-page'));
    $additionalStylesheets = [
        '/Web/view/backOffice/style/community.css?v=' . filemtime(__DIR__ . '/view/backOffice/style/community.css')
    ];
    $additionalScripts = [
        '/Web/view/backOffice/style/community.js?v=' . filemtime(__DIR__ . '/view/backOffice/style/community.js')
    ];
    define('SMART_ADMIN_VIEW', true);
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/backOffice/review_post.php';
    include __DIR__ . '/view/layouts/footer.php';

} elseif ($action === 'tracking-management') {
    $pageTitle = 'Activite sportif';
    $moduleTitle = 'Activite sportif';
    $moduleDescription = 'Module en cours de developpement. Vous pourrez suivre les activites sportives et la progression des utilisateurs.';
    if ($isAdminSession) {
        $isAdminTemplate = true;
    }
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/frontoffice/modules/coming-soon.php';
    include __DIR__ . '/view/layouts/footer.php';

} elseif ($action === 'planner-management') {
    $pageTitle = 'Planning';
    $moduleTitle = 'Planning';
    $moduleDescription = 'Module en cours de developpement. Vous pourrez planifier les activites et les objectifs.';
    if ($isAdminSession) {
        $isAdminTemplate = true;
    }
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/frontoffice/modules/coming-soon.php';
    include __DIR__ . '/view/layouts/footer.php';

} else {
    if (isset($_SESSION['user_id'])) {
        $fallbackAction = (($_SESSION['user_role'] ?? 'user') === 'admin') ? 'admin-dashboard' : 'home';
    } else {
        $fallbackAction = 'login';
    }
    header('Location: /Web/index.php?action=' . $fallbackAction);
    exit;
}
