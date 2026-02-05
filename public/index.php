<?php

declare(strict_types=1);

require __DIR__ . '/../src/Config.php';
require __DIR__ . '/../src/Database.php';
require __DIR__ . '/../src/Router.php';
require __DIR__ . '/../src/Auth.php';
require __DIR__ . '/../src/Controller/BaseController.php';
require __DIR__ . '/../src/Controller/ClientController.php';
require __DIR__ . '/../src/Controller/RestaurantController.php';
require __DIR__ . '/../src/Controller/AdminController.php';
require __DIR__ . '/../src/Controller/AgencyController.php';
require __DIR__ . '/../src/Repository/SettingsRepository.php';
require __DIR__ . '/../src/Repository/RestaurantRepository.php';
require __DIR__ . '/../src/Repository/UserRepository.php';
require __DIR__ . '/../src/Repository/OrderRepository.php';

session_start();

$config = Config::fromEnv();
$db = new Database($config);
$router = new Router();

$router->get('/', function () use ($db, $config): void {
    $controller = new AgencyController($db, $config);
    $controller->home();
});

$router->post('/contact', function () use ($db, $config): void {
    $controller = new AgencyController($db, $config);
    $controller->submitContact();
});

$router->get('/admin/messages-portal-7f9a', function () use ($db, $config): void {
    $controller = new AgencyController($db, $config);
    $controller->adminMessages();
});

$router->get('/restaurant', function () use ($db, $config): void {
    $controller = new RestaurantController($db, $config);
    $controller->dashboard();
});

$router->get('/restaurant/login', function () use ($db, $config): void {
    $controller = new RestaurantController($db, $config);
    $controller->showLogin();
});

$router->post('/restaurant/login', function () use ($db, $config): void {
    $controller = new RestaurantController($db, $config);
    $controller->login();
});

$router->post('/restaurant/orders/status', function () use ($db, $config): void {
    $controller = new RestaurantController($db, $config);
    $controller->updateOrderStatus();
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

$router->post('/admin/settings/distance', function () use ($db, $config): void {
    $controller = new AdminController($db, $config);
    $controller->updateDistance();
});

$router->get('/admin/restaurants/toggle', function () use ($db, $config): void {
    $controller = new AdminController($db, $config);
    $controller->toggleRestaurantValidation();
});

$router->get('/admin/restaurants/delete', function () use ($db, $config): void {
    $controller = new AdminController($db, $config);
    $controller->deleteRestaurant();
});

$router->get('/admin/impersonate', function () use ($db, $config): void {
    $controller = new AdminController($db, $config);
    $controller->impersonate();
});

$router->get('/admin/stop-impersonation', function () use ($db, $config): void {
    $controller = new AdminController($db, $config);
    $controller->stopImpersonation();
});

$router->get('/logout', function (): void {
    Auth::logout();
    header('Location: /');
    exit;
});

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
