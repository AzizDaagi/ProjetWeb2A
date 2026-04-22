<?php

require_once __DIR__ . "/controller/FrontOffice/FrontController.php";
require_once __DIR__ . "/controller/BackOffice/ActiviteController.php";
require_once __DIR__ . "/controller/BackOffice/AdminController.php";
require_once __DIR__ . "/controller/BackOffice/NutritionRequestController.php";

$frontController = new FrontController();
$backController = new ActiviteController();
$adminController = new AdminController();
$nutritionReqController = new NutritionRequestController();

$action = isset($_GET['action']) ? $_GET['action'] : 'home';

switch ($action) {
    // ---- FRONT OFFICE ----
    case 'home':
        $frontController->home();
        break;
    case 'activites':
        $frontController->listActivites();
        break;
    case 'showExercices':
        $frontController->showExercices();
        break;

    // ---- BACK OFFICE ----
    case 'admin_login':
        $adminController->loginView();
        break;
    case 'admin_authenticate':
        $adminController->authenticate();
        break;
    case 'admin_dashboard':
        $adminController->dashboard();
        break;
    case 'admin_logout':
        $adminController->logout();
        break;
    case 'admin_index':
        $backController->index();
        break;
    case 'admin_show':
        $backController->show();
        break;
    case 'createActivite':
        $backController->createActivite();
        break;
    case 'addExercice':
        $backController->addExercice();
        break;
    case 'editExercice':
        $backController->editExercice();
        break;
    case 'updateExercice':
        $backController->updateExercice();
        break;
    case 'deleteExercice':
        $backController->deleteExercice();
        break;
    case 'editActivite':
        $backController->editActivite();
        break;
    case 'updateActivite':
        $backController->updateActivite();
        break;
    case 'deleteActivite':
        $backController->deleteActivite();
        break;
        
    // ---- FRONT OFFICE NUTRITION REQUEST ----
    case 'nutrition_request':
        $frontController->nutritionRequest();
        break;
    case 'process_nutrition_request':
        $frontController->processNutritionRequest();
        break;
    case 'nutrition_success':
        $frontController->nutritionSuccess();
        break;

    // ---- BACK OFFICE NUTRITION REQUEST ----
    case 'admin_requests':
        $nutritionReqController->index();
        break;
    case 'admin_edit_request':
        $nutritionReqController->edit();
        break;
    case 'admin_update_request':
        $nutritionReqController->update();
        break;
    case 'admin_delete_request':
        $nutritionReqController->delete();
        break;

    default:
        $frontController->home();
        break;
}

?>