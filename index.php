<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

$pdo = new PDO("mysql:host=localhost;dbname=smart_nutrition", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$controllerName = $_GET['controller'] ?? 'suivi';
$action = $_GET['action'] ?? 'index';

if (!isset($_GET['controller']) && in_array($action, ['chatbot', 'clear_chat', 'clearChat'], true)) {
    $controllerName = 'chatbot';
}

$routes = [
    'suivi' => 'suivictrl',
    'objectif' => 'objectifctrl',
    'backoffice' => 'BackofficeCtrl',
    'stats' => 'statsCtrl',
    'chatbot' => 'chatbotctrl'
];

if (!array_key_exists($controllerName, $routes)) {
    $controllerName = 'suivi';
}

$controllerClass = $routes[$controllerName];
require_once __DIR__ . "/controllers/{$controllerClass}.php";

$controller = new $controllerClass($pdo);

if (!method_exists($controller, $action)) {
    $action = 'index';
}

$controller->$action();
