<?php

declare(strict_types=1);

namespace App\Core;

/**
 * View rendering engine with Blade template support.
 *
 * Automatically detects .blade.php files and routes them through
 * the Blade compiler. Falls back to plain .php views for legacy files.
 *
 * Following php-pro skill: strict types, PHPDoc, clean architecture.
 */
class View
{
    /** @var string Base path for view files */
    private static string $viewPath = '';

    /** @var array<string, mixed> Data shared across all views */
    private static array $shared = [];

    /** @var bool Whether Blade has been configured */
    private static bool $bladeConfigured = false;

    /**
     * Initialize the view engine.
     *
     * @param string $viewPath Absolute path to views directory
     */
    public static function configure(string $viewPath): void
    {
        self::$viewPath = rtrim($viewPath, '/');

        if (!self::$bladeConfigured) {
            Blade::configure(self::$viewPath);
            self::$bladeConfigured = true;
        }
    }

    /**
     * Share data with all views.
     *
     * @param string $key
     * @param mixed $value
     */
    public static function share(string $key, mixed $value): void
    {
        self::$shared[$key] = $value;
        Blade::share($key, $value);
    }

    /**
     * Render a view and return HTML output.
     *
     * Automatically uses Blade if a .blade.php file exists,
     * otherwise falls back to the plain PHP view.
     *
     * @param string $view Dot-notation view name (e.g., 'dashboard' or 'cms.index')
     * @param array<string, mixed> $data Variables available in the view
     * @return string Rendered HTML
     */
    public static function render(string $view, array $data = []): string
    {
        self::ensureConfigured();

        $relative = str_replace('.', '/', $view);
        $bladePath = self::$viewPath . '/' . $relative . '.blade.php';
        $phpPath = self::$viewPath . '/' . $relative . '.php';

        // Prefer Blade template if it exists
        if (file_exists($bladePath)) {
            return Blade::render($view, array_merge(self::$shared, $data));
        }

        // Fall back to plain PHP view
        if (file_exists($phpPath)) {
            return self::renderPhp($phpPath, array_merge(self::$shared, $data));
        }

        throw new \RuntimeException("View not found: {$view}");
    }

    /**
     * Render a view and echo it directly.
     *
     * @param string $view
     * @param array<string, mixed> $data
     */
    public static function display(string $view, array $data = []): void
    {
        echo self::render($view, $data);
    }

    /**
     * Escape a value for safe HTML output.
     *
     * @param mixed $value
     * @return string
     */
    public static function esc(mixed $value): string
    {
        if ($value === null) return '';
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Render a plain PHP view file (legacy support).
     *
     * @param string $file Absolute path to the PHP file
     * @param array<string, mixed> $data
     * @return string
     */
    private static function renderPhp(string $file, array $data): string
    {
        extract($data, EXTR_SKIP);

        ob_start();
        require $file;
        return ob_get_clean();
    }

    /**
     * Ensure the view engine is configured.
     */
    private static function ensureConfigured(): void
    {
        if (self::$viewPath === '') {
            self::configure(dirname(__DIR__, 2) . '/resources/views');
        }
    }
}
