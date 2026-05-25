<?php

declare(strict_types=1);

namespace Controllers;

use Support\Response;
use Support\Database;

class AuthController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login(): void
    {
        if ($this->isLoggedIn()) {
            Response::redirect('/');
            return;
        }
        
        $error = $_GET['error'] ?? null;
        Response::view('auth/login', ['error' => $error]);
    }

    public function signup(): void
    {
        if ($this->isLoggedIn()) {
            Response::redirect('/');
            return;
        }
        
        $error = $_GET['error'] ?? null;
        Response::view('auth/signup', ['error' => $error]);
    }

    public function handleLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::redirect('/login');
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            Response::redirect('/login?error=Empty+email+or+password');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::redirect('/login?error=Invalid+email');
            return;
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare('SELECT id, name, email, password FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                Response::redirect('/?login=success');
                return;
            }

            Response::redirect('/login?error=Invalid+credentials');
        } catch (\Exception $e) {
            Response::redirect('/login?error=Login+failed');
        }
    }

    public function handleSignup(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Response::redirect('/signup');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($name) || empty($email) || empty($password)) {
            Response::redirect('/signup?error=All+fields+required');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::redirect('/signup?error=Invalid+email');
            return;
        }

        if ($password !== $confirmPassword) {
            Response::redirect('/signup?error=Passwords+do+not+match');
            return;
        }

        if (strlen($password) < 6) {
            Response::redirect('/signup?error=Password+must+be+at+least+6+characters');
            return;
        }

        try {
            $db = Database::getConnection();
            
            // Check if email already exists
            $checkStmt = $db->prepare('SELECT id FROM users WHERE email = ?');
            $checkStmt->execute([$email]);
            if ($checkStmt->fetch()) {
                Response::redirect('/signup?error=Email+already+registered');
                return;
            }

            // Insert new user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, $hashedPassword]);

            // Auto-login
            $newUser = $db->prepare('SELECT id, name, email FROM users WHERE email = ?');
            $newUser->execute([$email]);
            $user = $newUser->fetch();

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];

            Response::redirect('/?signup=success');
        } catch (\Exception $e) {
            Response::redirect('/signup?error=Signup+failed');
        }
    }

    public function logout(): void
    {
        session_destroy();
        Response::redirect('/?logout=success');
    }

    private function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }
}
