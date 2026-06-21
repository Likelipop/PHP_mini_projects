<?php

declare(strict_types=1);

namespace StudyFlow\Core;

class Response
{
    public static function view(string $view, array $data = [], int $status = 200): void
    {
        http_response_code($status);
        extract($data, EXTR_SKIP);
        
        // Resolve path to the page file
        $viewPath = __DIR__ . '/../../views/pages/' . $view . '.php';
        
        if (!file_exists($viewPath)) {
            self::text(500, "View not found: views/pages/$view.php");
            return;
        }

        // Check if layout should be bypassed (for HTMX partial updates or explicit flag)
        $isHtmx = isset($_SERVER['HTTP_HX_REQUEST']);
        $bypassLayout = $isHtmx || (isset($noLayout) && $noLayout);

        if ($bypassLayout) {
            require $viewPath;
        } else {
            require __DIR__ . '/../../views/layouts/main.php';
        }
    }

    public static function json(int $status, array $data): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
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
        exit;
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
