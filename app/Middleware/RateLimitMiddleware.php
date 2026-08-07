<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\RateLimiter;

/**
 * Rate limiting middleware.
 *
 * Limits requests per IP address to prevent abuse.
 * Returns 429 Too Many Requests when limit is exceeded.
 */
class RateLimitMiddleware
{
    private readonly RateLimiter $limiter;
    private readonly int $maxAttempts;
    private readonly int $decaySeconds;

    /**
     * @param int $maxAttempts Maximum requests per window
     * @param int $decaySeconds Window size in seconds
     */
    public function __construct(int $maxAttempts = 60, int $decaySeconds = 60)
    {
        $this->limiter = new RateLimiter();
        $this->maxAttempts = $maxAttempts;
        $this->decaySeconds = $decaySeconds;
    }

    /**
     * Handle the rate limit check.
     *
     * @return bool True if request is allowed
     */
    public function handle(): bool
    {
        $key = RateLimiter::key('global');

        if (!$this->limiter->attempt($key, $this->maxAttempts, $this->decaySeconds)) {
            http_response_code(429);
            @@header('Retry-After: ' . $this->decaySeconds);
            @@header('X-RateLimit-Limit: ' . $this->maxAttempts);
            @@header('X-RateLimit-Remaining: 0');
            echo json_encode(['error' => 'Too many requests. Please try again later.']);
            return false;
        }

        $remaining = $this->limiter->remaining($key, $this->maxAttempts, $this->decaySeconds);
        @header('X-RateLimit-Limit: ' . $this->maxAttempts);
        @header('X-RateLimit-Remaining: ' . $remaining);

        return true;
    }
}
