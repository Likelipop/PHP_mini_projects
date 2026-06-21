<?php

declare(strict_types=1);

namespace StudyFlow\Core\Middleware;

use StudyFlow\Core\Session;
use StudyFlow\Core\Response;

class AuthMiddleware
{
    public static function handle(): void
    {
        Session::start();
        if (!Session::isLoggedIn()) {
            Session::flashSet('error', 'Vui lòng đăng nhập để tiếp tục.');
            Response::redirect('/login');
        }
    }
}
