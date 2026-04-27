<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Controllers\EventController;
use App\Controllers\HomeController;
use App\Controllers\RegistrationController;
use App\Support\Env;
use App\Support\Response;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$dotenv->required(['APP_NAME', 'APP_ENV', 'APP_DEBUG', 'APP_URL', 'ORGANIZER_NAME', 'MAX_REGISTRATIONS_PER_REQUEST']);
$dotenv->required('APP_DEBUG')->isBoolean();
$dotenv->required('MAX_REGISTRATIONS_PER_REQUEST')->isInteger();

error_reporting(E_ALL);

if (Env::get('APP_ENV', 'prod') === 'dev' && Env::bool('APP_DEBUG', false)) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    ini_set('log_errors', '1');
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', dirname(__DIR__) . '/storage/logs/php-error.log');
}

$config = require dirname(__DIR__) . '/config/app.php';
$events = require dirname(__DIR__) . '/src/Data/events.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

if ($path === '/' && $method === 'GET') {
    $controller = new HomeController();
    $data = $controller->index($config, $events);
    require dirname(__DIR__) . '/views/home.php';
    exit;
}

if ($path === '/events' && $method === 'GET') {
    (new EventController())->index($events);
}

if ($path === '/events' && $method === 'HEAD') {
    (new EventController())->head();
}

if ($path === '/events' && !in_array($method, ['GET', 'HEAD'], true)) {
    Response::json(405, [
        'error' => 'Method Not Allowed'
    ], [
        'Allow' => 'GET, HEAD'
    ]);
}

if ($path === '/registrations' && $method === 'POST') {
    (new RegistrationController())->store($events, $config);
}

if ($path === '/registrations' && $method === 'OPTIONS') {
    (new RegistrationController())->options();
}

if ($path === '/registrations' && !in_array($method, ['POST', 'OPTIONS'], true)) {
    Response::json(405, [
        'error' => 'Method Not Allowed'
    ], [
        'Allow' => 'POST, OPTIONS'
    ]);
}

if ($path === '/health' && $method === 'GET') {
    Response::json(200, [
        'status' => 'ok',
        'app' => $config['app']['name']
    ]); 
}

Response::json(404, [
    'error' => 'Not Found'
]);