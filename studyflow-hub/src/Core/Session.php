<?php

declare(strict_types=1);

namespace StudyFlow\Core;

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Apply secure session cookie settings
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            
            session_start();
        }
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function flashSet(string $key, mixed $value): void
    {
        self::start();
        $_SESSION['_flash'][$key] = $value;
    }

    public static function flashGet(string $key, mixed $default = null): mixed
    {
        self::start();
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public static function isLoggedIn(): bool
    {
        return self::get('user_id') !== null;
    }

    public static function requireLogin(): void
    {
        if (!self::isLoggedIn()) {
            self::flashSet('error', 'Vui lòng đăng nhập để tiếp tục.');
            Response::redirect('/login');
        }
    }

    public static function checkSessionTimeout(): void
    {
        self::start();
        $idleLimit = 500; // 500 seconds limit as per requirements
        
        if (!self::isLoggedIn()) {
            return;
        }

        $lastActivity = self::get('last_activity_at') ?? time();
        if (time() - $lastActivity > $idleLimit) {
            self::logout();
            self::start();
            self::flashSet('error', 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.');
            Response::redirect('/login');
        }
        
        self::set('last_activity_at', time());
    }

    public static function checkSessionContext(): void
    {
        self::start();
        if (!self::isLoggedIn()) {
            return;
        }

        $currentAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $savedAgent = self::get('user_agent') ?? '';
        
        if ($savedAgent !== '' && $savedAgent !== $currentAgent) {
            self::logout();
            self::start();
            self::flashSet('error', 'Phiên có dấu hiệu bất thường. Vui lòng đăng nhập lại.');
            Response::redirect('/login');
        }
    }

    public static function logout(): void
    {
        self::start();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 42000,
                $params['path'], 
                $params['domain'], 
                $params['secure'], 
                $params['httponly']
            );
        }
        session_destroy();
    }
}
