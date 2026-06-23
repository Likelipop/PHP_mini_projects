<?php

declare(strict_types=1);

// Bootstrap the application
require __DIR__ . '/../vendor/autoload.php';

use StudyFlow\Core\Router;
use StudyFlow\Core\Request;
use StudyFlow\Core\Response;
use StudyFlow\Core\Session;

// Start session
Session::start();

// Define Routes
$router = new Router();

// Auth Routes
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@showRegister');
$router->post('/register', 'AuthController@register');
$router->post('/logout', 'AuthController@logout');

// Main Pages
$router->get('/', 'HomeController@index');
$router->get('/profile', 'HomeController@profile');
$router->get('/health', 'HomeController@health');

// StudyFlow Routes
$router->get('/studyflows', 'StudyFlowController@index');
$router->get('/studyflows/create', 'StudyFlowController@showCreate');
$router->post('/studyflows/create', 'StudyFlowController@create');
$router->get('/studyflow/{slug}', 'StudyFlowController@show');
$router->post('/studyflow/{slug}/delete', 'StudyFlowController@delete');

// Asset and Notes Routes
$router->post('/asset/upload', 'AssetController@uploadApi');
$router->post('/asset/{id}/delete', 'AssetController@deleteAssetApi');
$router->post('/note/create', 'AssetController@createNoteApi');
$router->post('/note/{id}/save', 'AssetController@saveNoteApi');
$router->get('/assets/download/{id}', 'AssetController@download');

// API endpoints
$router->post('/api/folder/create', 'AssetController@makeFolderApi');
$router->post('/api/folder/{id}/rename', 'AssetController@renameFolderApi');
$router->post('/asset/{id}/rename', 'AssetController@renameAssetApi');
$router->post('/asset/{id}/move', 'AssetController@moveAssetApi');
$router->post('/api/assets/reorder', 'AssetController@reorderAssetsApi');

$router->get('/api/search', 'HomeController@searchApi');
$router->get('/api/tags/search', 'AssetController@searchTags');
$router->get('/api/tags/related', 'AssetController@getRelatedAssetsApi');
$router->post('/asset/fragment', 'AssetController@createFragmentApi');
$router->post('/api/assets/{id}/tags', 'AssetController@updateAssetTagsApi');
$router->get('/api/assets/{id}/fragments', 'AssetController@getFragmentsApi');
$router->get('/api/notifications', 'HomeController@notifications');

// Dispatch Request
$method = Request::getMethod();
$path = Request::getPath();

try {
    $router->dispatch($method, $path);
} catch (\Throwable $e) {
    // Safe error handling (hide stacktrace in production if desired)
    Response::text(500, 'Một lỗi hệ thống đã xảy ra: ' . $e->getMessage());
}
