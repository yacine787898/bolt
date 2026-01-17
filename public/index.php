<?php

declare(strict_types=1);

require __DIR__ . '/../src/Config.php';
require __DIR__ . '/../src/Database.php';
require __DIR__ . '/../src/Router.php';
require __DIR__ . '/../src/Controller/BaseController.php';
require __DIR__ . '/../src/Controller/ClientController.php';
require __DIR__ . '/../src/Controller/RestaurantController.php';
require __DIR__ . '/../src/Controller/AdminController.php';
require __DIR__ . '/../src/Repository/SettingsRepository.php';
require __DIR__ . '/../src/Repository/RestaurantRepository.php';

$config = Config::fromEnv();
$db = new Database($config);
$router = new Router();

$router->get('/', function () use ($db, $config): void {
    $controller = new ClientController($db, $config);
    $controller->index();
});

$router->get('/restaurant', function () use ($db, $config): void {
    $controller = new RestaurantController($db, $config);
    $controller->dashboard();
});

$router->get('/admin', function () use ($db, $config): void {
    $controller = new AdminController($db, $config);
    $controller->dashboard();
});

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
