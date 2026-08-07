<?php

declare(strict_types=1);

namespace App\Core;

/**
 * HTTP Response helper.
 *
 * Handles redirects and responses in both native PHP and PHP-WASM environments.
 */
class Response
{
    /**
     * Redirect to a URL.
     *
     * In native PHP: uses header() + exit
     * In WASM mode: outputs a redirect instruction the server can parse
     */
    public static function redirect(string $url, int $statusCode = 302): never
    {
        // Try native header first
        if (!headers_sent()) {
            @header("Location: {$url}", true, $statusCode);
            @header("X-Redirect: {$url}");
        }

        // Always output a parseable redirect marker for the WASM server
        echo "<!-- REDIRECT:{$url} -->";

        // Also include a fallback for browsers
        echo "<script>window.location.href='{$url}';</script>";
        echo "<meta http-equiv=\"refresh\" content=\"0;url={$url}\">";

        exit;
    }

    /**
     * Send a JSON response.
     */
    public static function json(array $data, int $statusCode = 200): never
    {
        if (!headers_sent()) {
            @header('Content-Type: application/json');
            @http_response_code($statusCode);
        }
        echo json_encode($data);
        exit;
    }

    /**
     * Send a plain text response.
     */
    public static function text(string $content, int $statusCode = 200): never
    {
        if (!headers_sent()) {
            @header('Content-Type: text/plain');
            @http_response_code($statusCode);
        }
        echo $content;
        exit;
    }
}
