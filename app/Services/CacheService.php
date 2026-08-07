<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Cache service for performance optimization.
 *
 * File-based caching with TTL support. Reduces database
 * queries and expensive computations.
 *
 * Following performance-engineer skill: "Cache strategies"
 */
class CacheService
{
    private readonly string $cachePath;
    private readonly int $defaultTtl;

    /**
     * @param string|null $cachePath Path to cache directory
     * @param int $defaultTtl Default TTL in seconds (5 minutes)
     */
    public function __construct(?string $cachePath = null, int $defaultTtl = 300)
    {
        $this->cachePath = $cachePath ?? dirname(__DIR__, 2) . '/storage/cache';
        $this->defaultTtl = $defaultTtl;

        if (!is_dir($this->cachePath)) {
            @mkdir($this->cachePath, 0755, true);
        }
    }

    /**
     * Get a cached value.
     *
     * @param string $key Cache key
     * @param mixed $default Default if not found or expired
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $file = $this->getFilePath($key);

        if (!file_exists($file)) {
            return $default;
        }

        $data = json_decode((string) file_get_contents($file), true);

        if (!is_array($data) || !isset($data['expires_at'], $data['value'])) {
            return $default;
        }

        if (time() > $data['expires_at']) {
            @unlink($file);
            return $default;
        }

        return $data['value'];
    }

    /**
     * Store a value in cache.
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache (must be JSON-serializable)
     * @param int|null $ttl Time-to-live in seconds
     * @return bool
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $file = $this->getFilePath($key);
        $data = [
            'value' => $value,
            'expires_at' => time() + ($ttl ?? $this->defaultTtl),
            'created_at' => time(),
        ];

        return file_put_contents($file, json_encode($data), LOCK_EX) !== false;
    }

    /**
     * Get a value or compute it if not cached.
     *
     * @param string $key Cache key
     * @param callable $callback Function to compute value
     * @param int|null $ttl Time-to-live
     * @return mixed
     */
    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl);

        return $value;
    }

    /**
     * Delete a cached value.
     *
     * @param string $key
     * @return bool
     */
    public function forget(string $key): bool
    {
        $file = $this->getFilePath($key);
        if (file_exists($file)) {
            return @unlink($file);
        }
        return true;
    }

    /**
     * Clear all cached data.
     *
     * @return int Number of files deleted
     */
    public function flush(): int
    {
        $count = 0;
        foreach (glob($this->cachePath . '/*.json') ?: [] as $file) {
            if (@unlink($file)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Check if a key exists and is not expired.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Get file path for a cache key.
     *
     * @param string $key
     * @return string
     */
    private function getFilePath(string $key): string
    {
        return $this->cachePath . '/' . md5($key) . '.json';
    }
}
