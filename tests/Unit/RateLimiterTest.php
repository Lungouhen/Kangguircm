<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\RateLimiter;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the RateLimiter service.
 *
 * Verifies rate limiting logic with file-based storage.
 */
class RateLimiterTest extends TestCase
{
    private string $storagePath;
    private RateLimiter $limiter;

    protected function setUp(): void
    {
        $this->storagePath = sys_get_temp_dir() . '/rate_limit_test_' . uniqid();
        $this->limiter = new RateLimiter($this->storagePath);
    }

    protected function tearDown(): void
    {
        // Clean up test files
        if (is_dir($this->storagePath)) {
            array_map('unlink', glob($this->storagePath . '/*'));
            rmdir($this->storagePath);
        }
    }

    public function test_allows_requests_within_limit(): void
    {
        $this->assertTrue($this->limiter->attempt('test-key', maxAttempts: 5, decaySeconds: 60));
        $this->assertTrue($this->limiter->attempt('test-key', maxAttempts: 5, decaySeconds: 60));
        $this->assertTrue($this->limiter->attempt('test-key', maxAttempts: 5, decaySeconds: 60));
    }

    public function test_blocks_requests_exceeding_limit(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->assertTrue($this->limiter->attempt('block-key', maxAttempts: 3, decaySeconds: 60));
        }
        $this->assertFalse($this->limiter->attempt('block-key', maxAttempts: 3, decaySeconds: 60));
    }

    public function test_remaining_decrements(): void
    {
        $this->assertSame(5, $this->limiter->remaining('remaining-key', maxAttempts: 5, decaySeconds: 60));
        $this->limiter->attempt('remaining-key', maxAttempts: 5, decaySeconds: 60);
        $this->assertSame(4, $this->limiter->remaining('remaining-key', maxAttempts: 5, decaySeconds: 60));
    }

    public function test_different_keys_are_independent(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->limiter->attempt('key-a', maxAttempts: 3, decaySeconds: 60);
        }
        $this->assertFalse($this->limiter->attempt('key-a', maxAttempts: 3, decaySeconds: 60));
        $this->assertTrue($this->limiter->attempt('key-b', maxAttempts: 3, decaySeconds: 60));
    }

    public function test_key_generation(): void
    {
        $key = RateLimiter::key('login');
        $this->assertStringStartsWith('login:', $key);
    }
}
