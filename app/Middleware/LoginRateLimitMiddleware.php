<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\RateLimiter;

/**
 * Login-specific rate limiting middleware.
 *
 * Applies stricter limits to authentication endpoints
 * to prevent brute force attacks.
 */
class LoginRateLimitMiddleware
{
    private readonly RateLimiter $limiter;

    public function __construct()
    {
        $this->limiter = new RateLimiter();
    }

    /**
     * Handle login rate limit check (5 attempts per minute).
     *
     * @return bool True if request is allowed
     */
    public function handle(): bool
    {
        $key = RateLimiter::key('login');

        if (!$this->limiter->attempt($key, maxAttempts: 5, decaySeconds: 60)) {
            http_response_code(429);
            header('Retry-After: 60');
            echo json_encode(['error' => 'Too many login attempts. Please try again in 60 seconds.']);
            return false;
        }

        return true;
    }
}
