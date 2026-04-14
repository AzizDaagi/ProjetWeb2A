<?php

require_once __DIR__ . "/controller/FrontOffice/FrontController.php";
require_once __DIR__ . "/controller/BackOffice/ActiviteController.php";

$frontController = new FrontController();
$backController = new ActiviteController();

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
    case 'editActivite':
        $backController->editActivite();
        break;
    case 'updateActivite':
        $backController->updateActivite();
        break;
    case 'deleteActivite':
        $backController->deleteActivite();
        break;
    default:
        $frontController->home();
        break;
}

?>