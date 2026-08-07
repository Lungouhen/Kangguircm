<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Core\Session;
use App\Core\Database;
use App\Helpers\Security;

class DashboardController
{
    public function index(): void
    {
        Session::start();

        $db = Database::getInstance();
        
        $stats = [
            'total_users' => $db->fetch("SELECT COUNT(*) as count FROM users")['count'],
            'total_posts' => $db->fetch("SELECT COUNT(*) as count FROM cms_posts")['count'],
            'total_subscribers' => $db->fetch("SELECT COUNT(*) as count FROM email_subscribers WHERE status = 'active'")['count'],
            'total_employees' => $db->fetch("SELECT COUNT(*) as count FROM hrm_employees WHERE status = 'active'")['count']
        ];

        View::display('dashboard', [
            'stats' => $stats,
            'user_name' => Session::get('user_name')
        ]);
    }
}
