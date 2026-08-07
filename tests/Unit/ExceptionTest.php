<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\AuthenticationException;
use App\Exceptions\AuthorizationException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use PHPUnit\Framework\TestCase;

/**
 * Tests for custom exception classes.
 */
class ExceptionTest extends TestCase
{
    public function test_authentication_exception_defaults(): void
    {
        $e = new AuthenticationException();
        $this->assertSame('Unauthorized', $e->getMessage());
        $this->assertSame(401, $e->getCode());
    }

    public function test_authentication_exception_custom_message(): void
    {
        $e = new AuthenticationException('Invalid credentials');
        $this->assertSame('Invalid credentials', $e->getMessage());
    }

    public function test_authorization_exception_defaults(): void
    {
        $e = new AuthorizationException();
        $this->assertSame('Forbidden', $e->getMessage());
        $this->assertSame(403, $e->getCode());
    }

    public function test_not_found_exception_defaults(): void
    {
        $e = new NotFoundException();
        $this->assertSame('Resource not found', $e->getMessage());
        $this->assertSame(404, $e->getCode());
    }

    public function test_validation_exception_stores_errors(): void
    {
        $errors = ['email' => ['Email is required'], 'name' => ['Name is too short']];
        $e = new ValidationException($errors);

        $this->assertSame($errors, $e->errors());
        $this->assertSame('Validation failed', $e->getMessage());
    }

    public function test_validation_exception_flat_errors(): void
    {
        $errors = ['email' => ['Email is required'], 'name' => ['Name is too short', 'Name must be unique']];
        $e = new ValidationException($errors);

        $flat = $e->flatErrors();
        $this->assertCount(3, $flat);
        $this->assertContains('Email is required', $flat);
        $this->assertContains('Name is too short', $flat);
        $this->assertContains('Name must be unique', $flat);
    }
}
