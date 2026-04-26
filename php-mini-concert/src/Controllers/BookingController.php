<?php

namespace App\Controllers;

use App\Support\Response;

class BookingController
{
    public function store(array $concerts, array $config): void
    {
        // 1. Nhận dữ liệu (Hỗ trợ cả JSON và Form truyền thống để dễ test)
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method !== 'POST') {
            Response::json(405, ['error' => 'Method Not Allowed']);
        }

        // Đọc dữ liệu từ JSON payload
        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true) ?? $_POST; // Fallback sang $_POST để dễ dùng với form HTML

        // 2. Validation logic (giữ nguyên các quy tắc của bạn)
        $concertId = $payload['concert_id'] ?? $payload['event_id'] ?? null;
        $customerName = trim($payload['name'] ?? $payload['student_name'] ?? '');
        $email = trim($payload['email'] ?? '');
        $quantity = (int) ($payload['quantity'] ?? 0);

        if (!$concertId || $customerName === '' || $email === '' || $quantity <= 0) {
            Response::json(415, ['error' => 'Dữ liệu không hợp lệ', 'message' => 'Vui lòng điền đầy đủ thông tin.']);
        }

        // 3. Kiểm tra Concert tồn tại & Vé còn trống
        $selectedConcert = null;
        foreach ($concerts as $concert) {
            if ($concert['id'] === (int) $concertId) {
                $selectedConcert = $concert;
                break;
            }
        }

        if (!$selectedConcert || $selectedConcert['seats_available'] < $quantity) {
            Response::json(422, ['error' => 'Lỗi đặt vé', 'message' => 'Concert không tồn tại hoặc đã hết vé.']);
        }

       // 4. Cập nhật dữ liệu vào file concerts.php
        $bookingId = 'TIC-' . strtoupper(substr(md5(time()), 0, 8));

        // Đường dẫn tới file dữ liệu
        $filePath = dirname(__DIR__, 2) . '/src/Data/concerts.php';

        // Duyệt qua mảng để cập nhật số lượng vé của concert đã chọn
        foreach ($concerts as &$concert) {
            if ($concert['id'] === (int) $concertId) {
                $concert['seats_available'] -= $quantity;
                break;
            }
        }

        // Chuyển mảng đã cập nhật thành chuỗi mã nguồn PHP để ghi lại vào file
        // Sử dụng var_export để tạo cấu trúc mảng PHP hợp lệ
        $newContent = "<?php" . PHP_EOL . PHP_EOL . "return " . var_export($concerts, true) . ";" . PHP_EOL;

        // Ghi dữ liệu mới xuống file
        file_put_contents($filePath, $newContent);

        // 5. TRẢ VỀ GIAO DIỆN HTML (E-Ticket)
        http_response_code(201);
        $data = [
            'title' => 'Xác nhận đặt vé thành công',
            'booking_id' => $bookingId,
            'customer_name' => $customerName,
            'email' => $email,
            'quantity' => $quantity,
            'concert' => $selectedConcert,
            'time' => date('H:i d/m/Y')
        ];

        

        require dirname(__DIR__, 2) . '/views/booking_success.php';
        exit;
    }

    public function options(): void
    {
        http_response_code(204);
        header('Allow: POST, OPTIONS');
        exit;
    }
}