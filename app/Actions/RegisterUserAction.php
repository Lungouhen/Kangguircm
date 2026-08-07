<?php

declare(strict_types=1);

namespace App\Actions;

use App\Services\AuthService;

/**
 * Action class for user registration.
 *
 * Handles the complete registration workflow:
 * validation, duplicate checking, and account creation.
 */
class RegisterUserAction
{
    public function __construct(
        private readonly AuthService $authService = new AuthService(),
    ) {}

    /**
     * Execute the registration action.
     *
     * @param string $name User full name
     * @param string $email User email
     * @param string $password Plaintext password
     * @param int $roleId Role to assign
     * @return int New user ID
     */
    public function execute(
        string $name,
        string $email,
        string $password,
        int $roleId = 2,
    ): int {
        return $this->authService->register($name, $email, $password, $roleId);
    }
}
