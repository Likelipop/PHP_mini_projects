<?php

declare(strict_types=1);

namespace Controllers;

use Support\Response;
use Support\Database;
use Support\Storage;
use Parsedown;

class ResourceController
{
    public function index(): void
    {
        $db = Database::getConnection();
        $stmt = $db->query('SELECT r.*, u.name as author FROM resources r LEFT JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC');
        $resources = $stmt->fetchAll();

        $parsedown = new Parsedown();
        $parsedown->setSafeMode(true);

        foreach ($resources as &$resource) {
            if (!empty($resource['markdown_recommendation'])) {
                $resource['html_recommendation'] = $parsedown->text($resource['markdown_recommendation']);
            }
            if (!empty($resource['minio_object_key'])) {
                $resource['download_url'] = Storage::getDownloadUrl($resource['minio_object_key']);
            }
        }

        Response::view('resources/index', ['resources' => $resources]);
    }

    public function create(): void
    {
        require_login();
        Response::view('resources/create');
    }

    public function store(): void
    {
        require_login();

        $data = [
            'title'                   => trim($_POST['title'] ?? ''),
            'markdown_recommendation' => trim($_POST['markdown_recommendation'] ?? ''),
            'website'                 => trim($_POST['website'] ?? ''), // honeypot
        ];

        $file = $_FILES['file'] ?? null;
        $errors = $this->validate($data);

        if (!empty($errors)) {
            flash_set('errors', $errors);
            flash_set('old', ['title' => $data['title'], 'markdown_recommendation' => $data['markdown_recommendation']]);
            redirect('/resources/create');
            return;
        }

        $userId = (int) $_SESSION['user_id'];
        $objectKey = null;

        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $objectKey = uniqid() . '_' . basename($file['name']);
            if (!Storage::upload($objectKey, $file['tmp_name'])) {
                flash_set('errors', ['_global' => 'Lỗi tải lên file.']);
                flash_set('old', ['title' => $data['title'], 'markdown_recommendation' => $data['markdown_recommendation']]);
                redirect('/resources/create');
                return;
            }
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('INSERT INTO resources (user_id, title, markdown_recommendation, minio_object_key) VALUES (?, ?, ?, ?)');
            $stmt->execute([$userId, $data['title'], $data['markdown_recommendation'], $objectKey]);

            flash_set('success', 'Resource created successfully! Thanks for sharing with the community.');
            redirect('/resources'); // real PRG
        } catch (\Exception $e) {
            flash_set('errors', ['_global' => 'Lỗi lưu trữ tài nguyên.']);
            flash_set('old', ['title' => $data['title'], 'markdown_recommendation' => $data['markdown_recommendation']]);
            redirect('/resources/create');
        }
    }

    private function validate(array $data): array
    {
        $errors = [];

        // Honeypot
        if ($data['website'] !== '') {
            $errors['_global'] = 'Yêu cầu không hợp lệ.';
            return $errors;
        }

        // Rate limit
        $last = $_SESSION['last_resource_at'] ?? 0;
        if ($last && time() - $last < 5) {
            $errors['_global'] = 'Bạn gửi quá nhanh. Vui lòng thử lại sau vài giây.';
            return $errors;
        }

        if ($data['title'] === '') {
            $errors['title'] = 'Vui lòng nhập tiêu đề.';
        } elseif (mb_strlen($data['title']) < 5) {
            $errors['title'] = 'Tiêu đề phải có ít nhất 5 ký tự.';
        }

        if (empty($errors)) {
            $_SESSION['last_resource_at'] = time();
        }

        return $errors;
    }
}
