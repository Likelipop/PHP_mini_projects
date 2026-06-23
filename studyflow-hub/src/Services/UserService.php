<?php

declare(strict_types=1);

namespace StudyFlow\Services;

use StudyFlow\Repositories\UserRepository;
use StudyFlow\Core\Session;

class UserService
{
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function register(array $data): array
    {
        $errors = [];
        $username = trim($data['username'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';

        // 1. Username Validation Sequence (Emptiness -> Format -> Length -> Uniqueness)
        if ($username === '') {
            $errors['username'] = 'Tên đăng nhập không được để trống.';
        } elseif (!preg_match('/^[a-z0-9._]+$/', $username)) {
            $errors['username'] = 'Tên đăng nhập chỉ được chứa chữ cái thường (a-z), số (0-9), dấu chấm (.) và dấu gạch dưới (_).';
        } elseif (strlen($username) < 3 || strlen($username) > 30) {
            $errors['username'] = 'Tên đăng nhập phải có độ dài từ 3 đến 30 ký tự.';
        } elseif ($this->userRepository->findByUsername($username)) {
            $errors['username'] = 'Tên đăng nhập đã tồn tại.';
        }

        // 2. Email Validation Sequence (Emptiness -> Format -> Uniqueness)
        if ($email === '') {
            $errors['email'] = 'Email không được để trống.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không đúng định dạng.';
        } elseif ($this->userRepository->findByEmail($email)) {
            $errors['email'] = 'Email đã được sử dụng.';
        }

        // 3. Password Validation Sequence (Emptiness -> Length)
        if ($password === '') {
            $errors['password'] = 'Mật khẩu không được để trống.';
        } elseif (strlen($password) < 6) {
            $errors['password'] = 'Mật khẩu phải từ 6 ký tự trở lên.';
        }

        // 4. Confirm Password Validation Sequence (Emptiness -> Match)
        if ($confirmPassword === '') {
            $errors['confirm_password'] = 'Xác nhận mật khẩu không được để trống.';
        } elseif ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Mật khẩu xác nhận không khớp.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $userId = $this->userRepository->create([
            'username' => $username,
            'email' => $email,
            'password' => $password,
        ]);

        return ['success' => true, 'user_id' => $userId];
    }

    public function login(string $username, string $password): array
    {
        $username = trim($username);
        $user = $this->userRepository->findByUsername($username);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'error' => 'Tên đăng nhập hoặc mật khẩu không chính xác.'];
        }

        // Initialize hardened session variables
        Session::start();
        Session::set('user_id', $user['id']);
        Session::set('username', $user['username']);
        Session::set('user_agent', $_SERVER['HTTP_USER_AGENT'] ?? '');
        Session::set('last_activity_at', time());

        return ['success' => true];
    }

    public function logout(): void
    {
        Session::logout();
    }

    public function getUserStats(int $userId): array
    {
        return $this->userRepository->getUserStats($userId);
    }
}
