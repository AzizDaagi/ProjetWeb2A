<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/config/app.php';
include __DIR__ . '/config/db.php';
require_once __DIR__ . '/Controller/ProduitController.php';

$controller = new ProduitController($conn);

$action = $_GET['action'] ?? 'front';

switch ($action) {

    case 'backList':
        $controller->backList();
        break;

    case 'create':
        $controller->create();
        break;

    case 'delete':
        $controller->delete();
        break;

    case 'edit':
    $controller->edit();
    break;

    case 'frontCreate':
    $controller->frontCreate();
    break;

    case 'pending':
    $controller->pending();
    break;

    case 'approve':
    $controller->approve();
    break;

    default:
        $controller->frontList();
        break;
}
