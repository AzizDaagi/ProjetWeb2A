<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

$pdo = new PDO("mysql:host=localhost;dbname=smart_nutrition", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$controllerName = $_GET['controller'] ?? 'aliment';
$action = $_GET['action'] ?? 'index';

$routes = [
    'aliment' => 'alimentctrl',
    'objectif' => 'objectifctrl',
    'adminAliment' => 'adminAlimentCtrl',
    'backoffice' => 'BackofficeCtrl',
    'stats' => 'statsCtrl'
];

if (!array_key_exists($controllerName, $routes)) {
    $controllerName = 'aliment';
}

$controllerClass = $routes[$controllerName];
require_once __DIR__ . "/controllers/{$controllerClass}.php";

$controller = new $controllerClass($pdo);

if (!method_exists($controller, $action)) {
    $action = 'index';
}

$controller->$action();