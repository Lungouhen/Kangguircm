<?php

declare(strict_types=1);

/**
 * Front Controller - Single Entry Point
 */

require_once __DIR__ . '/../vendor/autoload.php';

// View helpers (esc, badge, fdate, fmoney) for XSS prevention
require_once __DIR__ . '/../resources/views/helpers.php';

// Error reporting
$isProduction = ($_ENV['APP_ENV'] ?? 'development') === 'production';
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Always output session data on shutdown (for Node.js session sync)
register_shutdown_function(function(): void {
    $sessionOutput = [];
    foreach ($_SESSION as $key => $value) {
        if ($key === 'cookie' || $key === '_csrf') continue;
        $sessionOutput[$key] = $value;
    }
    echo '<!-- SESSION_DATA:' . json_encode($sessionOutput) . ':SESSION_DATA -->';
});

// Determine request details
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? $_SERVER['PATH_INFO'] ?? '/';
$uri = parse_url($uri, PHP_URL_PATH) ?: '/';

// Start session
\App\Core\Session::start();

// Security headers (suppress in WASM mode)
try {
    \App\Helpers\Security::setCspHeaders();
} catch (\Throwable $e) {
    // Ignore header errors in WASM mode
}

// Initialize database
try {
    $db = \App\Core\Database::getInstance();
    
    // Auto-run migrations if needed
    $check = @$db->fetch("SELECT COUNT(*) as c FROM sqlite_master WHERE type='table' AND name='users'");
    if (!$check || ($check['c'] ?? 0) == 0) {
        require_once __DIR__ . '/../database/migrate.php';
    }
} catch (\Throwable $e) {
    if (!empty($_ENV['APP_DEBUG'])) {
        echo "<pre>Database Error: " . htmlspecialchars($e->getMessage()) . "</pre>";
    }
    exit;
}

// Load and dispatch routes
try {
    $router = require __DIR__ . '/../routes/web.php';
    $router->dispatch($method, $uri);
} catch (\Throwable $e) {
    if (!empty($_ENV['APP_DEBUG'])) {
        echo "<pre>Error: " . htmlspecialchars($e->getMessage()) . "\n" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Internal server error']);
    }
}
