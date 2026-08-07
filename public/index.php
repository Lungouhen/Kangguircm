<?php

declare(strict_types=1);

// Front Controller - Single Entry Point

// Error reporting (controlled by environment)
$isProduction = ($_ENV['APP_ENV'] ?? 'development') === 'production';
if (!$isProduction) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Start session
\App\Core\Session::start();

// Security headers
\App\Helpers\Security::setCspHeaders();

// Database initialization
$db = \App\Core\Database::getInstance();

// Load routes
$router = require __DIR__ . '/../routes/web.php';

// Dispatch request
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$router->dispatch($method, $uri);
