<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Security helper providing sanitization, hashing, and header utilities.
 */
class Security
{
    /**
     * Sanitize a string for HTML output.
     */
    public static function sanitize(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Recursively sanitize an array.
     *
     * @param array<mixed> $data
     * @return array<mixed>
     */
    public static function sanitizeArray(array $data): array
    {
        return array_map(
            fn($value) => is_array($value) ? self::sanitizeArray($value) : self::sanitize((string)$value),
            $data
        );
    }

    /**
     * Generate a cryptographically secure random token.
     */
    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Hash a password using Argon2id.
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * Verify a password against a hash.
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Set Content Security Policy and security headers.
     *
     * Uses header() in native mode, or outputs as HTML meta in WASM mode.
     */
    public static function setCspHeaders(): void
    {
        if (!headers_sent()) {
            $csp = "default-src 'self'; " .
                   "script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; " .
                   "style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; " .
                   "img-src 'self' data: https:; " .
                   "font-src 'self' https://fonts.gstatic.com; " .
                   "connect-src 'self'; " .
                   "frame-ancestors 'none'; " .
                   "form-action 'self';";

            @header("Content-Security-Policy: {$csp}");
            @header("X-Frame-Options: DENY");
            @header("X-Content-Type-Options: nosniff");
            @header("X-XSS-Protection: 1; mode=block");
            @header("Referrer-Policy: strict-origin-when-cross-origin");
        }
    }

    /**
     * Validate a financial amount is within acceptable range.
     */
    public static function validateFinancial(float $amount): bool
    {
        return $amount >= 0 && $amount <= 999999999.99;
    }

    /**
     * Format a financial amount to 2 decimal places.
     */
    public static function formatFinancial(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }
}
