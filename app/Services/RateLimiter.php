<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Rate limiting service using in-memory file-based tracking.
 *
 * Prevents abuse by limiting the number of requests per IP
 * within a configurable time window.
 */
class RateLimiter
{
    private readonly string $storagePath;

    public function __construct(?string $storagePath = null)
    {
        $this->storagePath = $storagePath ?? dirname(__DIR__, 2) . '/storage/cache/rate_limits';

        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0750, true);
        }
    }

    /**
     * Check if a request should be allowed.
     *
     * @param string $key Unique identifier (e.g., IP + route)
     * @param int $maxAttempts Maximum allowed attempts in the window
     * @param int $decaySeconds Time window in seconds
     * @return bool True if request is allowed, false if rate limited
     */
    public function attempt(string $key, int $maxAttempts = 60, int $decaySeconds = 60): bool
    {
        $data = $this->getHits($key);
        $now = time();

        // Remove expired hits
        $data = array_filter($data, fn(int $timestamp) => ($now - $timestamp) < $decaySeconds);

        if (count($data) >= $maxAttempts) {
            $this->saveHits($key, $data);
            return false;
        }

        $data[] = $now;
        $this->saveHits($key, $data);

        return true;
    }

    /**
     * Get the number of remaining attempts.
     *
     * @param string $key
     * @param int $maxAttempts
     * @param int $decaySeconds
     * @return int Remaining attempts
     */
    public function remaining(string $key, int $maxAttempts = 60, int $decaySeconds = 60): int
    {
        $data = $this->getHits($key);
        $now = time();

        $data = array_filter($data, fn(int $timestamp) => ($now - $timestamp) < $decaySeconds);

        return max(0, $maxAttempts - count($data));
    }

    /**
     * Create a rate limiter key from request data.
     *
     * @param string $prefix Route or action identifier
     * @return string Rate limiter key
     */
    public static function key(string $prefix = ''): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        return $prefix . ':' . $ip;
    }

    /**
     * Get stored hit timestamps for a key.
     *
     * @param string $key
     * @return list<int>
     */
    private function getHits(string $key): array
    {
        $file = $this->getFilePath($key);

        if (!file_exists($file)) {
            return [];
        }

        $content = file_get_contents($file);
        $data = json_decode($content ?: '[]', true);

        return is_array($data) ? $data : [];
    }

    /**
     * Save hit timestamps for a key.
     *
     * @param string $key
     * @param list<int> $data
     */
    private function saveHits(string $key, array $data): void
    {
        $file = $this->getFilePath($key);
        file_put_contents($file, json_encode(array_values($data)), LOCK_EX);
    }

    /**
     * Get the file path for a rate limiter key.
     *
     * @param string $key
     * @return string
     */
    private function getFilePath(string $key): string
    {
        return $this->storagePath . '/' . md5($key) . '.json';
    }
}
