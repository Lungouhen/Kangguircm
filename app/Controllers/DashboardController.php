<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\Database;
use App\Core\Session;
use App\Core\View;

/**
 * Dashboard controller displaying system overview and statistics.
 *
 * Uses explicit column selection (no SELECT *) and aggregates
 * data from multiple modules for the admin dashboard.
 */
class DashboardController
{
    /**
     * Display the main dashboard with system statistics.
     */
    public function index(): void
    {
        Session::start();

        $db = Database::getInstance();

        $stats = [
            'total_users' => (int) $db->fetch(
                "SELECT COUNT(*) AS count FROM users"
            )['count'],
            'total_posts' => (int) $db->fetch(
                "SELECT COUNT(*) AS count FROM cms_posts"
            )['count'],
            'total_subscribers' => (int) $db->fetch(
                "SELECT COUNT(*) AS count FROM email_subscribers WHERE status = 'active'"
            )['count'],
            'total_employees' => (int) $db->fetch(
                "SELECT COUNT(*) AS count FROM hrm_employees WHERE status = 'active'"
            )['count'],
        ];

        View::display('dashboard', [
            'stats' => $stats,
            'user_name' => Session::get('user_name'),
        ]);
    }
}
