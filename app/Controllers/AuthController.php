<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Core\Session;
use App\Models\User;
use App\Helpers\{Validation, Security};

class AuthController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function showLogin(): void
    {
        View::display('auth.login');
    }

    public function login(): void
    {
        Session::start();

        $validator = new Validation();
        if (!$validator->validate($_POST, [
            'email' => 'required|email',
            'password' => 'required|min:6'
        ])) {
            Session::flash('errors', $validator->errors());
            header('Location: /login');
            exit;
        }

        $user = $this->userModel->findByEmail($_POST['email']);

        if (!$user || !$this->userModel->verifyPassword($_POST['password'], $user['password'])) {
            Session::flash('error', 'Invalid credentials');
            header('Location: /login');
            exit;
        }

        Session::regenerate();
        Session::set('user_id', $user['id']);
        Session::set('user_name', $user['name']);
        Session::set('user_email', $user['email']);
        Session::set('role_id', $user['role_id']);

        header('Location: /dashboard');
        exit;
    }

    public function showRegister(): void
    {
        View::display('auth.register');
    }

    public function register(): void
    {
        Session::start();

        $validator = new Validation();
        if (!$validator->validate($_POST, [
            'name' => 'required|min:2|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8'
        ])) {
            Session::flash('errors', $validator->errors());
            header('Location: /register');
            exit;
        }

        if ($_POST['password'] !== $_POST['password_confirmation']) {
            Session::flash('error', 'Password confirmation does not match');
            header('Location: /register');
            exit;
        }

        $this->userModel->create([
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'password' => $_POST['password'],
            'role_id' => 2 // Default to 'user' role
        ]);

        Session::flash('success', 'Registration successful. Please login.');
        header('Location: /login');
        exit;
    }

    public function logout(): void
    {
        Session::start();
        Session::destroy();
        header('Location: /login');
        exit;
    }
}
