<?php

declare(strict_types=1);

namespace StudyFlow\Core\Middleware;

use StudyFlow\Core\Session;
use StudyFlow\Core\Response;
use StudyFlow\Core\Request;

class CsrfMiddleware
{
    public static function generateToken(): string
    {
        $token = Session::get('csrf_token');
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::set('csrf_token', $token);
        }
        return $token;
    }

    public static function handle(): void
    {
        if (Request::getMethod() === 'POST') {
            $token = Request::input('csrf_token');
            $sessionToken = Session::get('csrf_token');
            
            if (!$token || !$sessionToken || !hash_equals($sessionToken, $token)) {
                Response::text(403, 'CSRF token validation failed.');
                exit;
            }
        }
    }
}
