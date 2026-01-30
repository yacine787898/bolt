<?php

declare(strict_types=1);

require __DIR__ . '/../src/Config.php';
require __DIR__ . '/../src/Database.php';
require __DIR__ . '/../src/Router.php';
require __DIR__ . '/../src/Auth.php';
require __DIR__ . '/../src/Controller/BaseController.php';
require __DIR__ . '/../src/Controller/StoreController.php';
require __DIR__ . '/../src/Controller/AdminController.php';
require __DIR__ . '/../src/Repository/ProductRepository.php';
require __DIR__ . '/../src/Repository/OrderRepository.php';
require __DIR__ . '/../src/Repository/WilayaRepository.php';
require __DIR__ . '/../src/Service/ShippingService.php';

session_start();

$config = Config::fromEnv();
$db = new Database($config);
$router = new Router();

$router->get('/', function () use ($db, $config): void {
    $controller = new StoreController($db, $config);
    $controller->index();
});

$router->get('/produit/{slug}', function (array $params) use ($db, $config): void {
    $controller = new StoreController($db, $config);
    $controller->showProduct($params);
});

$router->get('/cart', function () use ($db, $config): void {
    $controller = new StoreController($db, $config);
    $controller->showCart();
});

$router->post('/cart/add', function () use ($db, $config): void {
    $controller = new StoreController($db, $config);
    $controller->addToCart();
});

$router->post('/cart/update', function () use ($db, $config): void {
    $controller = new StoreController($db, $config);
    $controller->updateCart();
});

$router->get('/checkout', function () use ($db, $config): void {
    $controller = new StoreController($db, $config);
    $controller->showCheckout();
});

$router->post('/checkout', function () use ($db, $config): void {
    $controller = new StoreController($db, $config);
    $controller->placeOrder();
});

$router->get('/order-success', function () use ($db, $config): void {
    $controller = new StoreController($db, $config);
    $controller->showOrderSuccess();
});

$router->get('/admin', function () use ($db, $config): void {
    $controller = new AdminController($db, $config);
    $controller->dashboard();
});

$router->get('/admin/login', function () use ($db, $config): void {
    $controller = new AdminController($db, $config);
    $controller->showLogin();
});

$router->post('/admin/login', function () use ($db, $config): void {
    $controller = new AdminController($db, $config);
    $controller->login();
});

$router->get('/admin/logout', function () use ($db, $config): void {
    $controller = new AdminController($db, $config);
    $controller->logout();
});

$router->post('/admin/products/save', function () use ($db, $config): void {
    $controller = new AdminController($db, $config);
    $controller->saveProduct();
});

$router->post('/admin/products/delete', function () use ($db, $config): void {
    $controller = new AdminController($db, $config);
    $controller->deleteProduct();
});

$router->post('/admin/orders/status', function () use ($db, $config): void {
    $controller = new AdminController($db, $config);
    $controller->updateOrderStatus();
});

$router->post('/admin/orders/send', function () use ($db, $config): void {
    $controller = new AdminController($db, $config);
    $controller->sendOrder();
});

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
