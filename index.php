<?php

require_once __DIR__ . "/controller/ActiviteController.php";

$controller = new ActiviteController();
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

switch ($action) {
    case 'index':
        $controller->index();
        break;
    case 'show':
        $controller->show();
        break;
    case 'createActivite':
        $controller->createActivite();
        break;
    case 'addExercice':
        $controller->addExercice();
        break;
    case 'editActivite':
        $controller->editActivite();
        break;
    case 'updateActivite':
        $controller->updateActivite();
        break;
    case 'deleteActivite':
        $controller->deleteActivite();
        break;
    default:
        $controller->index();
        break;
}

?>