<?php

declare(strict_types=1);

namespace App\Core;

class Csrf
{
    public static function generate(): string
    {
        $token = bin2hex(random_bytes(32));
        Session::set('csrf_token', $token);
        return $token;
    }

    public static function token(): string
    {
        $token = Session::get('csrf_token');
        if (!$token) {
            $token = self::generate();
        }
        return $token;
    }

    public static function field(): string
    {
        $tokenName = $_ENV['CSRF_TOKEN_NAME'] ?? 'csrf_token';
        return '<input type="hidden" name="' . $tokenName . '" value="' . self::token() . '">';
    }

    public static function validate(?string $token = null): bool
    {
        $token = $token ?? ($_POST[$_ENV['CSRF_TOKEN_NAME'] ?? 'csrf_token'] ?? null);
        $sessionToken = Session::get('csrf_token');
        
        if (!$token || !$sessionToken) {
            return false;
        }
        
        return hash_equals($sessionToken, $token);
    }

    public static function middleware(): bool
    {
        if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE'])) {
            return self::validate();
        }
        return true;
    }
}
