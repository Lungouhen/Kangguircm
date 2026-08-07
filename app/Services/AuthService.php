<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Exceptions\AuthenticationException;
use App\Repositories\UserRepository;

/**
 * Authentication service handling login, registration, and session management.
 *
 * Follows the Service layer pattern to separate business logic
 * from HTTP concerns (controllers) and data access (repositories).
 */
class AuthService
{
    /**
     * @param UserRepository $userRepository User data access
     */
    public function __construct(
        private readonly UserRepository $userRepository = new UserRepository(),
    ) {}

    /**
     * Attempt to authenticate a user with email and password.
     *
     * @param string $email User email address
     * @param string $password Plaintext password
     * @return array<string, mixed> User data (without password)
     * @throws AuthenticationException When credentials are invalid
     */
    public function attempt(string $email, string $password): array
    {
        $user = $this->userRepository->findByEmail($email);

        if ($user === false) {
            throw new AuthenticationException('Invalid credentials');
        }

        if (!$this->userRepository->verifyPassword($password, $user['password'])) {
            throw new AuthenticationException('Invalid credentials');
        }

        $this->createSession($user);

        return $this->sanitizeUser($user);
    }

    /**
     * Register a new user account.
     *
     * @param string $name Full name
     * @param string $email Email address (must be unique)
     * @param string $password Plaintext password (will be hashed)
     * @param int $roleId Role ID to assign (default: 2 = user)
     * @return int New user ID
     */
    public function register(string $name, string $email, string $password, int $roleId = 2): int
    {
        return $this->userRepository->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role_id' => $roleId,
        ]);
    }

    /**
     * Destroy the current user session.
     */
    public function logout(): void
    {
        Session::destroy();
    }

    /**
     * Get the currently authenticated user's ID.
     *
     * @return int|null User ID or null if not authenticated
     */
    public function currentUserId(): ?int
    {
        return Session::has('user_id') ? (int) Session::get('user_id') : null;
    }

    /**
     * Check if a user is currently authenticated.
     */
    public function isAuthenticated(): bool
    {
        return Session::has('user_id');
    }

    /**
     * Create a session for the authenticated user.
     *
     * @param array<string, mixed> $user User record from database
     */
    private function createSession(array $user): void
    {
        Session::regenerate();
        Session::set('user_id', $user['id']);
        Session::set('user_name', $user['name']);
        Session::set('user_email', $user['email']);
        Session::set('role_id', $user['role_id']);
    }

    /**
     * Remove sensitive fields from user data.
     *
     * @param array<string, mixed> $user
     * @return array<string, mixed>
     */
    private function sanitizeUser(array $user): array
    {
        unset($user['password']);
        return $user;
    }
}
