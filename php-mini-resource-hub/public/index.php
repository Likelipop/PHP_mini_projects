<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Router;

$router = new Router();

// Register routes
$router->get('/', 'Controllers\HomeController@index');
$router->get('/health', 'Controllers\HealthController@index');
$router->get('/resources', 'Controllers\ResourceController@index');
$router->get('/resources/create', 'Controllers\ResourceController@create');
$router->post('/resources', 'Controllers\ResourceController@store');
$router->get('/login', 'Controllers\AuthController@login');
$router->post('/login', 'Controllers\AuthController@handleLogin');
$router->get('/signup', 'Controllers\AuthController@signup');
$router->post('/signup', 'Controllers\AuthController@handleSignup');
$router->get('/logout', 'Controllers\AuthController@logout');

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$router->dispatch($method, $path);
