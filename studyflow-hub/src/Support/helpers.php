<?php

declare(strict_types=1);

use StudyFlow\Core\Session;
use StudyFlow\Core\Response;
use StudyFlow\Core\Middleware\CsrfMiddleware;
use StudyFlow\Core\Middleware\HoneypotMiddleware;

if (!function_exists('h')) {
    function h(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): void
    {
        Response::redirect($path);
    }
}

if (!function_exists('flash_set')) {
    function flash_set(string $key, mixed $value): void
    {
        Session::flashSet($key, $value);
    }
}

if (!function_exists('flash_get')) {
    function flash_get(string $key, mixed $default = null): mixed
    {
        return Session::flashGet($key, $default);
    }
}

if (!function_exists('is_logged_in')) {
    function is_logged_in(): bool
    {
        return Session::isLoggedIn();
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return CsrfMiddleware::generateToken();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        $token = csrf_token();
        return '<input type="hidden" name="csrf_token" value="' . h($token) . '">';
    }
}

if (!function_exists('honeypot_field')) {
    function honeypot_field(): string
    {
        $name = HoneypotMiddleware::getFieldName();
        return '<div style="display:none !important;"><input type="text" name="' . h($name) . '" value="" tabindex="-1" autocomplete="off"></div>';
    }
}
