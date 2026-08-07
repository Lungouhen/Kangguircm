<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Response;
use App\Core\Session;

/**
 * Admin access middleware.
 *
 * Only users with role_id = 1 (admin) can access admin panel routes.
 * Following security-auditor skill: RBAC enforcement.
 */
class AdminMiddleware
{
    public function handle(): bool
    {
        Session::start();

        if (!Session::has('user_id')) {
            Response::redirect('/login');
            return false;
        }

        $roleId = (int) Session::get('role_id', 0);

        if ($roleId !== 1) {
            http_response_code(403);
            echo 'Access denied. Admin privileges required.';
            return false;
        }

        return true;
    }
}
