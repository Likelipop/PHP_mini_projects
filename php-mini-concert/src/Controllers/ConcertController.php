<?php

namespace App\Controllers;

class ConcertController
{
    public function index(array $concerts): void
    {
        // Thay vì Response::json, chúng ta chuẩn bị dữ liệu cho view
        $data = [
            'title' => 'Lịch Diễn Concert 2026',
            'concerts' => $concerts
        ];

        // Gọi file view để hiển thị giao diện
        // Lưu ý: dirname(__DIR__, 2) để nhảy từ src/Controllers ngược lên thư mục gốc
        require dirname(__DIR__, 2) . '/views/concerts_list.php';
        exit;
    }

    public function head(): void
    {
        // Giữ nguyên hoặc đổi sang text/html tùy nhu cầu kiểm tra của bạn
        http_response_code(200);
        header('Content-Type: text/html; charset=UTF-8');
        exit;
    }
}