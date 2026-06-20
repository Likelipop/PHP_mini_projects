<?php

declare(strict_types=1);

namespace Controllers;

use Support\Response;
use Support\Database;

class AuthController
{
    public function login(): void
    {
        if (is_logged_in()) {
            redirect('/');
            return;
        }
        
        Response::view('auth/login');
    }

    public function signup(): void
    {
        if (is_logged_in()) {
            redirect('/');
            return;
        }
        
        Response::view('auth/signup');
    }

    public function handleLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/login');
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $errors = [];

        if ($email === '') {
            $errors['email'] = 'Vui lòng nhập email.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không đúng định dạng.';
        }

        if ($password === '') {
            $errors['password'] = 'Vui lòng nhập mật khẩu.';
        }

        $user = null;
        if (empty($errors)) {
            try {
                $db = Database::getConnection();
                $stmt = $db->prepare('SELECT id, name, email, password FROM users WHERE email = ?');
                $stmt->execute([$email]);
                $user = $stmt->fetch();
            } catch (\Exception $e) {
                $errors['_global'] = 'Lỗi hệ thống. Vui lòng thử lại sau.';
            }
        }

        if (empty($errors) && (!$user || !password_verify($password, $user['password']))) {
            $errors['password'] = 'Email hoặc mật khẩu không đúng.';
        }

        if (!empty($errors)) {
            flash_set('errors', $errors);
            flash_set('old', ['email' => $email]);
            redirect('/login');
            return;
        }

        // Success: rotate the session ID BEFORE trusting it
        session_regenerate_id(true);
        $_SESSION['user_id']         = $user['id'];
        $_SESSION['user_name']       = $user['name'];
        $_SESSION['login_at']        = time();
        $_SESSION['last_activity_at']= time();
        $_SESSION['user_agent']      = $_SERVER['HTTP_USER_AGENT'] ?? '';

        flash_set('success', 'Đăng nhập thành công.');
        redirect('/resources');
    }

    public function handleSignup(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/signup');
            return;
        }

        $data = [
            'name'             => trim($_POST['name'] ?? ''),
            'email'            => trim($_POST['email'] ?? ''),
            'password'         => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'website'          => trim($_POST['website'] ?? ''), // honeypot
        ];

        $errors = $this->validateSignup($data);

        if (!empty($errors)) {
            flash_set('errors', $errors);
            flash_set('old', ['name' => $data['name'], 'email' => $data['email']]);
            redirect('/signup');
            return;
        }

        try {
            $db = Database::getConnection();
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt = $db->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
            $stmt->execute([$data['name'], $data['email'], $hashedPassword]);

            flash_set('success', 'Tạo tài khoản thành công. Vui lòng đăng nhập.');
            redirect('/login'); // PRG: redirect after successful POST
        } catch (\Exception $e) {
            flash_set('errors', ['_global' => 'Signup failed']);
            flash_set('old', ['name' => $data['name'], 'email' => $data['email']]);
            redirect('/signup');
        }
    }

    private function validateSignup(array $data): array
    {
        $errors = [];

        // Anti-spam first — reject silently with a generic message
        if ($data['website'] !== '') {
            $errors['_global'] = 'Yêu cầu không hợp lệ.';
            return $errors;
        }
        $last = $_SESSION['last_signup_at'] ?? 0;
        if ($last && time() - $last < 5) {
            $errors['_global'] = 'Bạn gửi quá nhanh. Vui lòng thử lại sau vài giây.';
            return $errors;
        }

        // Empty -> format -> length, field by field
        if ($data['name'] === '') {
            $errors['name'] = 'Vui lòng nhập họ tên.';
        } elseif (mb_strlen($data['name']) < 2) {
            $errors['name'] = 'Họ tên phải có ít nhất 2 ký tự.';
        }

        if ($data['email'] === '') {
            $errors['email'] = 'Vui lòng nhập email.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không đúng định dạng.';
        } else {
            try {
                $db = Database::getConnection();
                $checkStmt = $db->prepare('SELECT id FROM users WHERE email = ?');
                $checkStmt->execute([$data['email']]);
                if ($checkStmt->fetch()) {
                    $errors['email'] = 'Email đã được sử dụng.';
                }
            } catch (\Exception $e) {
                $errors['_global'] = 'Lỗi hệ thống.';
            }
        }

        if ($data['password'] === '') {
            $errors['password'] = 'Vui lòng nhập mật khẩu.';
        } elseif (strlen($data['password']) < 6) {
            $errors['password'] = 'Mật khẩu phải có ít nhất 6 ký tự.';
        }

        if ($data['password'] !== $data['confirm_password']) {
            $errors['confirm_password'] = 'Mật khẩu xác nhận không khớp.';
        }

        // Only reset the rate-limit clock when the form is fully valid
        if (empty($errors)) {
            $_SESSION['last_signup_at'] = time();
        }

        return $errors;
    }

    public function logout(): void
    {
        logout_clean();
        session_start(); // fresh session just to carry the one-time flash message
        flash_set('success', 'Đăng xuất thành công.');
        redirect('/login');
    }
}
