<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\CacheService;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the CacheService.
 */
class CacheServiceTest extends TestCase
{
    private string $cachePath;
    private CacheService $cache;

    protected function setUp(): void
    {
        $this->cachePath = sys_get_temp_dir() . '/cache_test_' . uniqid();
        $this->cache = new CacheService($this->cachePath);
    }

    protected function tearDown(): void
    {
        $this->cache->flush();
        if (is_dir($this->cachePath)) {
            @rmdir($this->cachePath);
        }
    }

    public function test_set_and_get(): void
    {
        $this->cache->set('key1', 'value1');
        $this->assertSame('value1', $this->cache->get('key1'));
    }

    public function test_get_returns_default_for_missing(): void
    {
        $this->assertSame('default', $this->cache->get('missing', 'default'));
    }

    public function test_expired_returns_default(): void
    {
        $this->cache->set('expired', 'old', ttl: -1);
        $this->assertSame('fallback', $this->cache->get('expired', 'fallback'));
    }

    public function test_remember_caches_callback(): void
    {
        $calls = 0;
        $result = $this->cache->remember('computed', function () use (&$calls) {
            $calls++;
            return 'computed_value';
        });

        $this->assertSame('computed_value', $result);
        $this->assertSame(1, $calls);

        // Second call should use cache
        $result2 = $this->cache->remember('computed', function () use (&$calls) {
            $calls++;
            return 'new_value';
        });

        $this->assertSame('computed_value', $result2);
        $this->assertSame(1, $calls); // Not called again
    }

    public function test_forget_removes_key(): void
    {
        $this->cache->set('to_forget', 'value');
        $this->assertTrue($this->cache->forget('to_forget'));
        $this->assertNull($this->cache->get('to_forget'));
    }

    public function test_has_checks_existence(): void
    {
        $this->assertFalse($this->cache->has('nope'));
        $this->cache->set('exists', 'yes');
        $this->assertTrue($this->cache->has('exists'));
    }

    public function test_flush_clears_all(): void
    {
        $this->cache->set('a', 1);
        $this->cache->set('b', 2);
        $deleted = $this->cache->flush();
        $this->assertSame(2, $deleted);
    }

    public function test_caches_complex_data(): void
    {
        $data = ['users' => [['id' => 1, 'name' => 'Alice']], 'count' => 1];
        $this->cache->set('complex', $data);
        $this->assertSame($data, $this->cache->get('complex'));
    }
}
