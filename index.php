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

$publicActions = ['login', 'register', 'face-login', 'google-login', 'forgot', 'reset-password'];
if (!isset($_SESSION['user_id']) && !in_array($action, $publicActions, true)) {
    header('Location: /smart_nutritionn/gestionActiviteesportive/index.php?action=login');
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
    $pageTitle = 'Planning';
    $moduleTitle = 'Planning';
    $moduleDescription = 'Module en cours de developpement. Vous pourrez planifier les activites et les objectifs.';
    if ($isAdminSession) {
        $isAdminTemplate = true;
    }
    include __DIR__ . '/view/layouts/header.php';
    include __DIR__ . '/view/front/modules/coming-soon.php';
    include __DIR__ . '/view/layouts/footer.php';

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
    if (isset($_SESSION['user_id'])) {
        $fallbackAction = (($_SESSION['user_role'] ?? 'user') === 'admin') ? 'admin-dashboard' : 'home';
    } else {
        $fallbackAction = 'login';
    }
    header('Location: /smart_nutritionn/gestionActiviteesportive/index.php?action=' . $fallbackAction);
    exit;
}
