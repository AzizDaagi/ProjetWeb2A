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

$publicActions = ['login', 'register', 'face-login', 'forgot', 'reset-password'];
if (!isset($_SESSION['user_id']) && !in_array($action, $publicActions, true)) {
    header('Location: /smart_nutrition/index.php?action=login');
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

} elseif ($action === 'logout') {
    $user = new UserController();
    $user->logout();

} elseif ($action === 'users-list') {
    $user = new UserController();
    $user->usersList();

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
    $pageTitle = 'Planning';
    $moduleTitle = 'Planning';
    $moduleDescription = 'Module en cours de developpement. Vous pourrez planifier les activites et les objectifs.';
    if ($isAdminSession) {
        $isAdminTemplate = true;
    }
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/front/modules/coming-soon.php';
    include __DIR__ . '/view/layouts/footer.php';

} else {
    if (isset($_SESSION['user_id'])) {
        $fallbackAction = (($_SESSION['user_role'] ?? 'user') === 'admin') ? 'admin-dashboard' : 'home';
    } else {
        $fallbackAction = 'login';
    }
    header('Location: /smart_nutrition/index.php?action=' . $fallbackAction);
    exit;
}
