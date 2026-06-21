<?php

declare(strict_types=1);

namespace StudyFlow\Controllers;

use StudyFlow\Services\UserService;
use StudyFlow\Core\Middleware\CsrfMiddleware;
use StudyFlow\Core\Middleware\HoneypotMiddleware;
use StudyFlow\Core\Middleware\RateLimitMiddleware;
use StudyFlow\Core\Request;
use StudyFlow\Core\Session;

class AuthController extends BaseController
{
    private UserService $userService;

    public function __construct()
    {
        parent::__construct();
        $this->userService = new UserService();
    }

    public function showLogin(): void
    {
        if (Session::isLoggedIn()) {
            $this->redirect('/');
            exit;
        }
        
        $this->render('auth/login', [
            'error' => flash_get('error'),
            'success' => flash_get('success'),
            'old' => flash_get('old', []),
        ]);
    }

    public function login(): void
    {
        // 1. Honeypot & CSRF Middleware
        HoneypotMiddleware::handle();
        CsrfMiddleware::handle();

        // 2. Rate Limit: Max 5 login requests per 30 seconds
        RateLimitMiddleware::handle(5, 30);

        $username = trim((string)Request::input('username', ''));
        $password = (string)Request::input('password', '');

        // Validation sequence for Login (Emptiness checks first)
        $error = '';
        if ($username === '') {
            $error = 'Tên đăng nhập không được để trống.';
        } elseif ($password === '') {
            $error = 'Mật khẩu không được để trống.';
        } else {
            $result = $this->userService->login($username, $password);
            if ($result['success']) {
                flash_set('success', 'Đăng nhập thành công!');
                $this->redirect('/');
                exit;
            } else {
                $error = $result['error'];
            }
        }

        // On failure: Do NOT redirect (stay on POST), render directly
        $this->render('auth/login', [
            'error' => $error,
            'old' => ['username' => $username],
        ]);
    }

    public function showRegister(): void
    {
        if (Session::isLoggedIn()) {
            $this->redirect('/');
            exit;
        }
        
        $this->render('auth/register', [
            'errors' => flash_get('errors', []),
            'old' => flash_get('old', []),
        ]);
    }

    public function register(): void
    {
        // 1. Honeypot & CSRF Middleware
        HoneypotMiddleware::handle();
        CsrfMiddleware::handle();

        // 2. Rate Limit: Max 3 registration requests per minute
        RateLimitMiddleware::handle(3, 60);

        $data = [
            'username' => trim((string)Request::input('username', '')),
            'email' => trim((string)Request::input('email', '')),
            'password' => (string)Request::input('password', ''),
            'confirm_password' => (string)Request::input('confirm_password', ''),
        ];

        $result = $this->userService->register($data);

        if ($result['success']) {
            flash_set('success', 'Đăng ký thành công! Hãy đăng nhập.');
            $this->redirect('/login');
            exit;
        } else {
            // On failure: Do NOT redirect (stay on POST), render directly
            $this->render('auth/register', [
                'errors' => $result['errors'],
                'old' => [
                    'username' => $data['username'],
                    'email' => $data['email']
                ]
            ]);
        }
    }

    public function logout(): void
    {
        // POST to logout for security
        CsrfMiddleware::handle();
        $this->userService->logout();
        $this->redirect('/login');
        exit;
    }
}
