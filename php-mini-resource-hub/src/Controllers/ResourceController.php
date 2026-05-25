<?php

declare(strict_types=1);

namespace Controllers;

use Support\Response;
use Support\Database;
use Support\Storage;
use Parsedown;

class ResourceController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check authentication for certain actions
        if (!$this->isLoggedIn() && in_array($_SERVER['REQUEST_METHOD'], ['POST'])) {
            Response::text(401, 'Unauthorized: Please login to perform this action');
            exit;
        }
    }

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
        if (!$this->isLoggedIn()) {
            Response::redirect('/login');
            return;
        }
        
        Response::view('resources/create');
    }

    public function store(): void
    {
        if (!$this->isLoggedIn()) {
            Response::text(401, 'Unauthorized');
            return;
        }

        $title = $_POST['title'] ?? '';
        $markdown = $_POST['markdown_recommendation'] ?? '';
        $file = $_FILES['file'] ?? null;
        $userId = (int) $_SESSION['user_id'];

        if (empty($title)) {
            Response::text(400, 'Title is required');
            return;
        }

        $objectKey = null;

        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $objectKey = uniqid() . '_' . basename($file['name']);
            if (!Storage::upload($objectKey, $file['tmp_name'])) {
                Response::text(500, 'Failed to upload file');
                return;
            }
        }

        $db = Database::getConnection();
        $stmt = $db->prepare('INSERT INTO resources (user_id, title, markdown_recommendation, minio_object_key) VALUES (?, ?, ?, ?)');
        $stmt->execute([$userId, $title, $markdown, $objectKey]);

        Response::redirect('/resources?created=1');
    }

    private function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }
}
