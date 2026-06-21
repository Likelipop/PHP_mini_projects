<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use StudyFlow\Core\Database;
use StudyFlow\Core\Router;
use StudyFlow\Core\Session;
use StudyFlow\Core\Middleware\CsrfMiddleware;

echo "=== CHẠY KIỂM THỬ TỰ ĐỘNG CORE MODULES ===\n\n";

// 1. Test Session helper
try {
    Session::start();
    Session::set('test_key', 'test_value');
    if (Session::get('test_key') === 'test_value') {
        echo "[OK] Session manager hoạt động đúng.\n";
    } else {
        echo "[FAIL] Session manager lỗi.\n";
    }
} catch (\Throwable $e) {
    echo "[FAIL] Session manager gặp lỗi: " . $e->getMessage() . "\n";
}

// 2. Test CSRF Middleware
try {
    $token1 = CsrfMiddleware::generateToken();
    $token2 = CsrfMiddleware::generateToken();
    if ($token1 !== '' && $token1 === $token2) {
        echo "[OK] CsrfMiddleware tạo token chính xác và nhất quán.\n";
    } else {
        echo "[FAIL] CsrfMiddleware lỗi tạo token.\n";
    }
} catch (\Throwable $e) {
    echo "[FAIL] CsrfMiddleware gặp lỗi: " . $e->getMessage() . "\n";
}

// 3. Test Router converting regex
try {
    $router = new Router();
    $router->get('/studyflow/{slug}', 'StudyFlowController@show');
    
    // Use reflection to inspect private variable
    $reflector = new \ReflectionClass($router);
    $property = $reflector->getProperty('routes');
    $property->setAccessible(true);
    $routes = $property->getValue($router);

    $keys = array_keys($routes['GET'] ?? []);
    if (count($keys) > 0 && preg_match($keys[0], '/studyflow/machine-learning', $matches)) {
        if ($matches['slug'] === 'machine-learning') {
            echo "[OK] Router regex matching và parse slug chính xác.\n";
        } else {
            echo "[FAIL] Router regex matching bị sai slug.\n";
        }
    } else {
        echo "[FAIL] Router regex converting lỗi.\n";
    }
} catch (\Throwable $e) {
    echo "[FAIL] Router gặp lỗi: " . $e->getMessage() . "\n";
}

// 4. Test Database Connection
try {
    $db = Database::getConnection();
    if ($db) {
        echo "[OK] Kết nối Database PostgreSQL thành công.\n";
    }
} catch (\Throwable $e) {
    // Expected to fail if running outside docker environment without env setup
    echo "[INFO] Thử kết nối DB: " . $e->getMessage() . " (Sẽ kết nối thành công khi chạy trong Docker container)\n";
}

echo "\n=== HOÀN TẤT KIỂM THỬ ===\n";
