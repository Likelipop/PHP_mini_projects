<?php

declare(strict_types=1);

namespace StudyFlow\Core;

class Request
{
    public static function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    public static function getPath(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $position = strpos($uri, '?');
        if ($position === false) {
            return $uri;
        }
        return substr($uri, 0, $position);
    }

    public static function input(string $key, mixed $default = null): mixed
    {
        if (self::getMethod() === 'POST') {
            return $_POST[$key] ?? $default;
        }
        return $_GET[$key] ?? $default;
    }

    public static function all(): array
    {
        return array_merge($_GET, $_POST);
    }

    public static function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }

    public static function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
