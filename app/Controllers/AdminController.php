<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\AdminService;
use App\Helpers\Validation;
use App\Core\Database;

/**
 * Admin panel controller.
 *
 * Following backend-developer skill: RESTful patterns, structured responses.
 * Following fullstack-developer skill: end-to-end data flow.
 * Following api-designer skill: consistent naming, pagination.
 */
class AdminController
{
    private readonly AdminService $admin;

    public function __construct()
    {
        $this->admin = new AdminService();
    }

    /**
     * Admin dashboard with metrics and recent activity.
     */
    public function dashboard(): void
    {
        $stats = $this->admin->getDashboardStats();
        $activity = $this->admin->getRecentActivity(10);

        View::display('admin.dashboard', [
            'pageTitle' => 'Admin Dashboard',
            'stats' => $stats,
            'recentActivity' => $activity,
        ]);
    }

    /**
     * Users management listing.
     */
    public function users(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $result = $this->admin->getUsers($page, 20);

        View::display('admin.users', [
            'pageTitle' => 'User Management',
            'users' => $result['data'],
            'pagination' => $result,
        ]);
    }

    /**
     * Roles and permissions management.
     */
    public function roles(): void
    {
        $roles = $this->admin->getRoles();

        View::display('admin.roles', [
            'pageTitle' => 'Roles & Permissions',
            'roles' => $roles,
        ]);
    }

    /**
     * System settings panel.
     */
    public function settings(): void
    {
        $settings = $this->admin->getSettings();

        View::display('admin.settings', [
            'pageTitle' => 'System Settings',
            'settings' => $settings,
        ]);
    }

    /**
     * Update system settings (bulk).
     */
    public function updateSetting(): void
    {
        Session::start();

        $settings = $_POST['settings'] ?? [];
        $keys = $_POST['keys'] ?? [];
        $userId = (int) Session::get('user_id');

        if (empty($settings) || empty($keys)) {
            Session::flash('error', 'No settings provided.');
            Response::redirect('/admin/settings');
            return;
        }

        foreach ($keys as $key) {
            if (isset($settings[$key])) {
                $this->admin->updateSetting($key, $settings[$key], $userId);
            }
        }

        Session::flash('success', count($keys) . ' setting(s) updated successfully.');
        Response::redirect('/admin/settings');
    }

    /**
     * Audit logs viewer.
     */
    public function logs(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $filter = $_GET['filter'] ?? null;
        $result = $this->admin->getAuditLogs($page, 50, $filter);

        View::display('admin.logs', [
            'pageTitle' => 'Audit Logs',
            'logs' => $result['data'],
            'pagination' => $result,
            'filter' => $filter,
        ]);
    }

    /**
     * Mark notification as read.
     */
    public function markNotificationRead(string $id): void
    {
        Session::start();
        $userId = (int) Session::get('user_id');

        $db = \App\Core\Database::getInstance();
        $db->update('notifications', [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s'),
        ], 'id = ? AND user_id = ?', [(int) $id, $userId]);

        Response::redirect($_SERVER['HTTP_REFERER'] ?? '/admin');
    }
}
