<?php

declare(strict_types=1);

namespace Support;

class Response
{
    public static function view(string $view, array $data = [], int $status = 200): void
    {
        http_response_code($status);
        extract($data, EXTR_SKIP);
        $viewPath = __DIR__ . '/../../views/' . $view . '.php';
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            self::text(500, "View not found: $view");
        }
    }

    public static function json(int $status, array $data): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public static function redirect(string $url, int $status = 302): void
    {
        http_response_code($status);
        header('Location: ' . $url);
        exit;
    }

    public static function text(int $status, string $message): void
    {
        http_response_code($status);
        header('Content-Type: text/plain');
        echo $message;
    }

    public static function notFound(string $message = '404 Not Found'): void
    {
        self::text(404, $message);
    }

    public static function methodNotAllowed(array $allowedMethods = []): void
    {
        header('Allow: ' . implode(', ', $allowedMethods));
        self::text(405, '405 Method Not Allowed');
    }
}
