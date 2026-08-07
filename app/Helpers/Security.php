<?php

declare(strict_types=1);

namespace App\Helpers;

class Security
{
    public static function sanitize(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    public static function sanitizeArray(array $data): array
    {
        return array_map(fn($value) => is_array($value) ? self::sanitizeArray($value) : self::sanitize($value), $data);
    }

    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function setCspHeaders(): void
    {
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; " .
               "style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; " .
               "img-src 'self' data: https:; " .
               "font-src 'self' https://fonts.gstatic.com; " .
               "connect-src 'self'; " .
               "frame-ancestors 'none'; " .
               "form-action 'self';";

        header("Content-Security-Policy: {$csp}");
        header("X-Frame-Options: DENY");
        header("X-Content-Type-Options: nosniff");
        header("X-XSS-Protection: 1; mode=block");
        header("Referrer-Policy: strict-origin-when-cross-origin");
    }

    public static function validateFinancial(float $amount): bool
    {
        return $amount >= 0 && $amount <= 999999999.99;
    }

    public static function formatFinancial(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }
}
