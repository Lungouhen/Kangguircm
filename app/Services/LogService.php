<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Structured logging service.
 *
 * Writes JSON-formatted log entries to storage/logs/.
 * Follows backend-developer skill: "Error handling and structured logging"
 */
class LogService
{
    private readonly string $logPath;

    public function __construct(?string $logPath = null)
    {
        $this->logPath = $logPath ?? dirname(__DIR__, 2) . '/storage/logs';

        if (!is_dir($this->logPath)) {
            @mkdir($this->logPath, 0755, true);
        }
    }

    /**
     * Log an info message.
     *
     * @param string $message
     * @param array<string, mixed> $context
     */
    public function info(string $message, array $context = []): void
    {
        $this->write('INFO', $message, $context);
    }

    /**
     * Log a warning.
     *
     * @param string $message
     * @param array<string, mixed> $context
     */
    public function warning(string $message, array $context = []): void
    {
        $this->write('WARNING', $message, $context);
    }

    /**
     * Log an error.
     *
     * @param string $message
     * @param array<string, mixed> $context
     */
    public function error(string $message, array $context = []): void
    {
        $this->write('ERROR', $message, $context);
    }

    /**
     * Log a debug message (only in development).
     *
     * @param string $message
     * @param array<string, mixed> $context
     */
    public function debug(string $message, array $context = []): void
    {
        if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
            $this->write('DEBUG', $message, $context);
        }
    }

    /**
     * Write a log entry.
     *
     * @param string $level Log level
     * @param string $message Log message
     * @param array<string, mixed> $context Additional context
     */
    private function write(string $level, string $message, array $context = []): void
    {
        $entry = [
            'timestamp' => date('c'),
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'request_id' => $this->getRequestId(),
        ];

        $file = $this->logPath . '/app-' . date('Y-m-d') . '.log';
        file_put_contents($file, json_encode($entry) . "\n", FILE_APPEND | LOCK_EX);
    }

    /**
     * Generate or retrieve request ID for tracing.
     *
     * @return string
     */
    private function getRequestId(): string
    {
        static $requestId = null;
        if ($requestId === null) {
            $requestId = bin2hex(random_bytes(8));
        }
        return $requestId;
    }
}
