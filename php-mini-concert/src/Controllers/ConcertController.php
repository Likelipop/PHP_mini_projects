<?php

namespace App\Controllers;

class ConcertController
{
    public function index($concerts)
    {
        // 1. Lấy tham số bộ lọc từ URL (phương thức GET)
        $filterStatus = $_GET['status'] ?? 'all'; // all, available, soldout
        $searchKeyword = $_GET['search'] ?? '';

        // 2. Thực hiện lọc dữ liệu
        $filteredConcerts = array_filter($concerts, function ($concert) use ($filterStatus, $searchKeyword) {
            $matchStatus = true;
            $matchSearch = true;

            // Lọc theo trạng thái vé
            if ($filterStatus === 'available') {
                $matchStatus = $concert['seats_available'] > 0;
            } elseif ($filterStatus === 'soldout') {
                $matchStatus = $concert['seats_available'] <= 0;
            }

            // Lọc theo từ khóa tìm kiếm (tên concert)
            if (!empty($searchKeyword)) {
                $matchSearch = stripos($concert['title'], $searchKeyword) !== false;
            }

            return $matchStatus && $matchSearch;
        });

        // 3. Chuẩn bị dữ liệu truyền ra view
        $data = [
            'title' => 'Tất cả Concerts',
            'concerts' => $filteredConcerts,
            'filters' => [
                'status' => $filterStatus,
                'search' => $searchKeyword
            ]
        ];

        // 4. Gọi View mới
        require dirname(__DIR__, 2) . '/views/concerts.php';
    }

    public function head(): void
    {
        // Giữ nguyên hoặc đổi sang text/html tùy nhu cầu kiểm tra của bạn
        http_response_code(200);
        header('Content-Type: text/html; charset=UTF-8');
        exit;
    }
}