<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\LogService;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the LogService.
 *
 * Following backend-developer skill: "structured logging"
 */
class LogServiceTest extends TestCase
{
    private string $logPath;
    private LogService $logger;

    protected function setUp(): void
    {
        $this->logPath = sys_get_temp_dir() . '/log_test_' . uniqid();
        $this->logger = new LogService($this->logPath);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->logPath . '/*.log') ?: []);
        if (is_dir($this->logPath)) {
            @rmdir($this->logPath);
        }
    }

    public function test_info_writes_json_log(): void
    {
        $this->logger->info('Test message', ['key' => 'value']);

        $files = glob($this->logPath . '/*.log');
        $this->assertNotEmpty($files);

        $content = file_get_contents($files[0]);
        $entry = json_decode(trim($content), true);

        $this->assertSame('INFO', $entry['level']);
        $this->assertSame('Test message', $entry['message']);
        $this->assertSame('value', $entry['context']['key']);
        $this->assertArrayHasKey('timestamp', $entry);
        $this->assertArrayHasKey('request_id', $entry);
    }

    public function test_error_writes_log(): void
    {
        $this->logger->error('Something failed', ['code' => 500]);

        $files = glob($this->logPath . '/*.log');
        $content = file_get_contents($files[0]);
        $entry = json_decode(trim($content), true);

        $this->assertSame('ERROR', $entry['level']);
    }

    public function test_warning_writes_log(): void
    {
        $this->logger->warning('Low disk space');

        $files = glob($this->logPath . '/*.log');
        $content = file_get_contents($files[0]);
        $entry = json_decode(trim($content), true);

        $this->assertSame('WARNING', $entry['level']);
    }
}
