<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Controllers\ConcertController; 
use App\Controllers\HomeController;
use App\Controllers\BookingController; 
use App\Support\Env;
use App\Support\Response;
use Dotenv\Dotenv;

// 1. Load môi trường
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// ... (Phần required env và error_reporting giữ nguyên) ...

$config = require dirname(__DIR__) . '/config/app.php';
$concerts = require dirname(__DIR__) . '/src/Data/concerts.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// --- BẮT ĐẦU ROUTING ---

// Trang chủ
if ($path === '/' && $method === 'GET') {
    $controller = new HomeController();
    $data = $controller->index($config, $concerts);
    require dirname(__DIR__) . '/views/home.php';
    exit;
}

// Tuyến đường /concerts (Đã gộp GET và HEAD)
if ($path === '/concerts') {
    if ($method === 'GET') {
        (new ConcertController())->index($concerts);
        exit;
    }
    if ($method === 'HEAD') {
        (new ConcertController())->head();
        exit;
    }
    // Nếu vào /concerts mà không phải GET/HEAD -> báo lỗi 405
    Response::json(405, ['error' => 'Method Not Allowed'], ['Allow' => 'GET, HEAD']);
}

// Tuyến đường /bookings (Đã đồng bộ tên và gộp POST/OPTIONS)
if ($path === '/bookings') {
    if ($method === 'POST') {
        (new BookingController())->store($concerts, $config);
        exit;
    }
    if ($method === 'OPTIONS') {
        (new BookingController())->options();
        exit;
    }
    // Nếu vào /bookings mà không phải POST/OPTIONS -> báo lỗi 405
    Response::json(405, ['error' => 'Method Not Allowed'], ['Allow' => 'POST, OPTIONS']);
}

// Health Check
if ($path === '/health' && $method === 'GET') {
    Response::json(200, [
        'status' => 'ok',
        'app' => $config['app']['name']
    ]); 
    exit;
}

// error handling

// --- TIẾP TỤC PHẦN ROUTING TRONG index.php ---

// 1. Xử lý Route /bookings
if ($path === '/bookings') {
    
    // Kiểm tra Method (Trường hợp 1: Sai Method)
    if ($method !== 'POST' && $method !== 'OPTIONS') {
        Response::json(405, [
            'error' => 'Method Not Allowed',
            'message' => "Phương thức $method không được hỗ trợ cho đường dẫn này."
        ], ['Allow' => 'POST, OPTIONS']);
        exit;
    }

    if ($method === 'POST') {
        // Kiểm tra Content-Type (Trường hợp 2: Sai Content-Type)
        // Lưu ý: Nếu dùng Form HTML thì Content-Type thường là application/x-www-form-urlencoded
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (!str_contains($contentType, 'application/json') && !str_contains($contentType, 'x-www-form-urlencoded')) {
            Response::json(415, [
                'error' => 'Unsupported Media Type',
                'message' => 'Hệ thống chỉ chấp nhận định dạng JSON hoặc Form Data.'
            ]);
            exit;
        }

        // Lấy dữ liệu và kiểm tra (Trường hợp 3: Dữ liệu không hợp lệ sơ bộ)
        $input = str_contains($contentType, 'application/json') 
            ? json_decode(file_get_contents('php://input'), true) 
            : $_POST;

        if (empty($input)) {
            Response::json(400, [
                'error' => 'Bad Request',
                'message' => 'Dữ liệu gửi lên không được để trống.'
            ]);
            exit;
        }

        // Nếu mọi thứ ổn, mới gọi Controller
        (new BookingController())->store($concerts, $config);
        exit;
    }

    if ($method === 'OPTIONS') {
        (new BookingController())->options();
        exit;
    }
}

// 2. Xử lý Route /concerts
if ($path === '/concerts') {
    if ($method !== 'GET' && $method !== 'HEAD') {
        Response::json(405, [
            'error' => 'Method Not Allowed'
        ], ['Allow' => 'GET, HEAD']);
        exit;
    }

    (new ConcertController())->index($concerts);
    exit;
}

// 3. Mặc định 404 cho các đường dẫn không tồn tại
Response::json(404, [
    'error' => 'Not Found',
    'message' => 'Đường dẫn bạn yêu cầu không tồn tại.'
]);

// Mặc định: 404 Not Found
Response::json(404, ['error' => 'Not Found']);