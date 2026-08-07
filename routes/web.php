<?php

declare(strict_types=1);

use App\Core\Router;
use App\Controllers\{AuthController, DashboardController, CmsController, EmailController, HrmController, AdminController};
use App\Middleware\{AuthMiddleware, AdminMiddleware, CsrfMiddleware, RateLimitMiddleware, LoginRateLimitMiddleware};

$router = new Router();

// Public routes (rate-limited)
$router->get('/', fn() => \App\Core\Response::redirect('/login'));
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login'], [LoginRateLimitMiddleware::class, CsrfMiddleware::class]);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register'], [RateLimitMiddleware::class, CsrfMiddleware::class]);
$router->get('/logout', [AuthController::class, 'logout']);

// Authenticated routes
$router->group('', function(Router $router) {

    // Dashboard
    $router->get('/dashboard', [DashboardController::class, 'index'], [AuthMiddleware::class]);

    // CMS Module
    $router->group('/cms', function(Router $router) {
        $router->get('/', [CmsController::class, 'index'], [AuthMiddleware::class]);
        $router->get('/create', [CmsController::class, 'create'], [AuthMiddleware::class]);
        $router->post('/', [CmsController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class]);
        $router->get('/{id}/edit', [CmsController::class, 'edit'], [AuthMiddleware::class]);
        $router->put('/{id}', [CmsController::class, 'update'], [AuthMiddleware::class, CsrfMiddleware::class]);
        $router->delete('/{id}', [CmsController::class, 'delete'], [AuthMiddleware::class, CsrfMiddleware::class]);
    });

    // Email Marketing Module
    $router->group('/email', function(Router $router) {
        $router->get('/subscribers', [EmailController::class, 'subscribers'], [AuthMiddleware::class]);
        $router->post('/subscribers', [EmailController::class, 'addSubscriber'], [AuthMiddleware::class, CsrfMiddleware::class]);
        $router->get('/lists', [EmailController::class, 'lists'], [AuthMiddleware::class]);
        $router->post('/lists', [EmailController::class, 'createList'], [AuthMiddleware::class, CsrfMiddleware::class]);
        $router->get('/campaigns', [EmailController::class, 'campaigns'], [AuthMiddleware::class]);
        $router->get('/campaigns/create', [EmailController::class, 'createCampaign'], [AuthMiddleware::class]);
        $router->post('/campaigns', [EmailController::class, 'storeCampaign'], [AuthMiddleware::class, CsrfMiddleware::class]);
    });

    // HRM Module
    $router->group('/hrm', function(Router $router) {
        $router->get('/employees', [HrmController::class, 'employees'], [AuthMiddleware::class]);
        $router->get('/employees/create', [HrmController::class, 'createEmployee'], [AuthMiddleware::class]);
        $router->post('/employees', [HrmController::class, 'storeEmployee'], [AuthMiddleware::class, CsrfMiddleware::class]);
        $router->get('/attendance', [HrmController::class, 'attendance'], [AuthMiddleware::class]);
        $router->post('/attendance/clock', [HrmController::class, 'clockIn'], [AuthMiddleware::class, CsrfMiddleware::class]);
        $router->get('/leaves', [HrmController::class, 'leaves'], [AuthMiddleware::class]);
        $router->post('/leaves', [HrmController::class, 'requestLeave'], [AuthMiddleware::class, CsrfMiddleware::class]);
        $router->post('/leaves/{id}/approve', [HrmController::class, 'approveLeave'], [AuthMiddleware::class, CsrfMiddleware::class]);
    });

    // Admin Panel (admin role only)
    $router->group('/admin', function(Router $router) {
        $router->get('/', [AdminController::class, 'dashboard']);
        $router->get('/users', [AdminController::class, 'users']);
        $router->get('/roles', [AdminController::class, 'roles']);
        $router->get('/settings', [AdminController::class, 'settings']);
        $router->post('/settings/update', [AdminController::class, 'updateSetting'], [CsrfMiddleware::class]);
        $router->get('/logs', [AdminController::class, 'logs']);
    }, [AdminMiddleware::class]);

}, [AuthMiddleware::class]);

return $router;
