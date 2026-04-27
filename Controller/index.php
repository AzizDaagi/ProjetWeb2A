<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/bootstrap.php';

use App\Controller\ProductController;
use App\Controller\CommandeController;
use App\Controller\CartController;
use App\Controller\MigrationController;
use App\Controller\Router;

try {
    $router = new Router();

    $router->get('home', [ProductController::class, 'index']);
    $router->match(['GET', 'POST'], 'product.create', [ProductController::class, 'createFront']);
    $router->get('admin.products', [ProductController::class, 'adminIndex']);
    $router->match(['GET', 'POST'], 'admin.products.create', [ProductController::class, 'createAdmin']);
    $router->match(['GET', 'POST'], 'admin.products.edit', [ProductController::class, 'edit']);
    $router->get('admin.products.pending', [ProductController::class, 'pending']);
    $router->get('admin.products.approve', [ProductController::class, 'approve']);
    $router->get('admin.products.delete', [ProductController::class, 'delete']);
    $router->get('template.preview', [ProductController::class, 'previewTemplate']);

    $router->match(['GET', 'POST'], 'order.create', [CommandeController::class, 'createFront']);
    $router->get('order.list', [CommandeController::class, 'frontList']);
    $router->match(['GET', 'POST'], 'order.edit', [CommandeController::class, 'editFront']);
    $router->get('order.delete', [CommandeController::class, 'deleteFront']);

    $router->get('admin.orders', [CommandeController::class, 'adminList']);
    $router->match(['GET', 'POST'], 'admin.orders.edit', [CommandeController::class, 'editAdmin']);
    $router->get('admin.orders.delete', [CommandeController::class, 'deleteAdmin']);

    // Cart routes
    $router->match(['GET', 'POST'], 'cart.add', [CartController::class, 'addToCart']);
    $router->get('cart.view', [CartController::class, 'viewCart']);
    $router->post('cart.update', [CartController::class, 'updateCart']);
    $router->get('cart.remove', [CartController::class, 'removeFromCart']);
    $router->get('cart.clear', [CartController::class, 'clearCart']);
    $router->get('cart.checkout', [CartController::class, 'checkoutForm']);
    $router->post('cart.process', [CartController::class, 'checkout']);

    $router->get('migrate.cart', [MigrationController::class, 'migrateCart']);
    $router->get('migrate.nullable', [MigrationController::class, 'migrateNullableProductId']);

    $legacyActionMap = [
        'front' => 'home',
        'frontCreate' => 'product.create',
        'backList' => 'admin.products',
        'create' => 'admin.products.create',
        'edit' => 'admin.products.edit',
        'delete' => 'admin.products.delete',
        'pending' => 'admin.products.pending',
        'approve' => 'admin.products.approve',
        'templatePreview' => 'template.preview',
        'orders.create' => 'order.create',
        'orders.list' => 'order.list',
        'orders.edit' => 'order.edit',
        'orders.delete' => 'order.delete',
        'admin.orders.index' => 'admin.orders',
        'products.index' => 'home',
        'products.create' => 'product.create',
        'admin.products.index' => 'admin.products',
    ];

    $requestedAction = $_GET['action'] ?? 'home';
    $action = $legacyActionMap[$requestedAction] ?? $requestedAction;

    $router->dispatch($action, $_SERVER['REQUEST_METHOD'] ?? 'GET');
} catch (Throwable $exception) {
    http_response_code(500);
    $message = $exception->getMessage();
    require dirname(__DIR__) . '/View/error.php';
}
