<?php

declare(strict_types=1);

namespace App\Core;

class View
{
    private static string $viewPath = __DIR__ . '/../../resources/views/';
    private static array $shared = [];

    public static function share(string $key, mixed $value): void
    {
        self::$shared[$key] = $value;
    }

    public static function render(string $view, array $data = []): string
    {
        $file = self::$viewPath . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($file)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        // Register escape function for views (XSS prevention)
        $e = ['App\Helpers\Html', 'e'];
        
        extract(array_merge(self::$shared, $data));
        
        ob_start();
        require $file;
        return ob_get_clean();
    }

    /**
     * Escape a value for safe HTML output (XSS prevention).
     * Shortcut for use in views: <?= View::esc($value) ?>
     *
     * @param mixed $value
     * @return string
     */
    public static function esc(mixed $value): string
    {
        if ($value === null) return '';
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function display(string $view, array $data = []): void
    {
        echo self::render($view, $data);
    }

    public static function escape(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}
