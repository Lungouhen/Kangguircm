<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Core\View;
use App\Exceptions\AuthenticationException;
use App\Helpers\Validation;
use App\Services\AuthService;

/**
 * Authentication controller handling login, registration, and logout.
 *
 * Uses AuthService for business logic and follows the thin-controller
 * pattern with delegated responsibilities.
 */
class AuthController
{
    /**
     * @param AuthService $authService Authentication business logic
     */
    public function __construct(
        private readonly AuthService $authService = new AuthService(),
    ) {}

    /**
     * Display the login form.
     */
    public function showLogin(): void
    {
        View::display('auth.login');
    }

    /**
     * Process login attempt.
     */
    public function login(): void
    {
        Session::start();

        $validator = new Validation();
        if (!$validator->validate($_POST, [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ])) {
            Session::flash('errors', $validator->errors());
            header('Location: /login');
            exit;
        }

        try {
            $this->authService->attempt($_POST['email'], $_POST['password']);
            header('Location: /dashboard');
            exit;
        } catch (AuthenticationException $e) {
            Session::flash('error', $e->getMessage());
            header('Location: /login');
            exit;
        }
    }

    /**
     * Display the registration form.
     */
    public function showRegister(): void
    {
        View::display('auth.register');
    }

    /**
     * Process new user registration.
     */
    public function register(): void
    {
        Session::start();

        $validator = new Validation();
        if (!$validator->validate($_POST, [
            'name' => 'required|min:2|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
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

        $this->authService->register(
            name: $_POST['name'],
            email: $_POST['email'],
            password: $_POST['password'],
        );

        Session::flash('success', 'Registration successful. Please login.');
        header('Location: /login');
        exit;
    }

    /**
     * Log the user out and redirect to login.
     */
    public function logout(): void
    {
        $this->authService->logout();
        header('Location: /login');
        exit;
    }
}
