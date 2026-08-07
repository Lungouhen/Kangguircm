<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Session management.
 *
 * In PHP-WASM mode, sessions are managed by Node.js express-session.
 * This class provides a compatible interface using $_SESSION superglobal.
 */
class Session
{
    private static bool $started = false;

    /**
     * Start the session (or ensure it's active).
     */
    public static function start(): void
    {
        if (self::$started) return;

        // In WASM mode, $_SESSION is already populated by the server wrapper
        if (!empty($_SESSION)) {
            self::$started = true;
            return;
        }

        // In native PHP mode, use standard session handling
        if (php_sapi_name() !== 'cli' && session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        self::$started = true;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (php_sapi_name() !== 'cli' && session_status() === PHP_SESSION_ACTIVE) {
            @session_destroy();
        }
    }

    public static function regenerate(): void
    {
        if (php_sapi_name() !== 'cli' && session_status() === PHP_SESSION_ACTIVE) {
            @session_regenerate_id(true);
        }
    }

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public static function reset(): void
    {
        self::$started = false;
    }
}
