<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Admin panel service.
 *
 * Following backend-developer skill: audit logging, structured data access,
 * cache strategy for admin dashboard metrics.
 */
class AdminService
{
    private readonly Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get admin dashboard statistics.
     *
     * @return array<string, mixed>
     */
    public function getDashboardStats(): array
    {
        return [
            'users' => (int) $this->db->fetch('SELECT COUNT(*) as c FROM users')['c'],
            'users_active' => (int) $this->db->fetch("SELECT COUNT(*) as c FROM users WHERE updated_at > datetime('now', '-30 days')")['c'],
            'posts' => (int) $this->db->fetch('SELECT COUNT(*) as c FROM cms_posts')['c'],
            'posts_published' => (int) $this->db->fetch("SELECT COUNT(*) as c FROM cms_posts WHERE status = 'published'")['c'],
            'subscribers' => (int) $this->db->fetch("SELECT COUNT(*) as c FROM email_subscribers WHERE status = 'active'")['c'],
            'employees' => (int) $this->db->fetch("SELECT COUNT(*) as c FROM hrm_employees WHERE status = 'active'")['c'],
            'campaigns_sent' => (int) $this->db->fetch("SELECT COUNT(*) as c FROM email_campaigns WHERE status = 'sent'")['c'],
            'leaves_pending' => (int) $this->db->fetch("SELECT COUNT(*) as c FROM hrm_leaves WHERE status = 'pending'")['c'],
            'audit_logs_24h' => (int) $this->db->fetch("SELECT COUNT(*) as c FROM audit_logs WHERE created_at > datetime('now', '-1 day')")['c'],
            'storage_used' => '0 MB',
        ];
    }

    /**
     * Get recent activity for dashboard.
     *
     * @param int $limit
     * @return list<array<string, mixed>>
     */
    public function getRecentActivity(int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT al.*, u.name as user_name
             FROM audit_logs al
             JOIN users u ON al.user_id = u.id
             ORDER BY al.created_at DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Get user list with pagination.
     *
     * @param int $page
     * @param int $perPage
     * @return array{data: list<array>, total: int, page: int, pages: int}
     */
    public function getUsers(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $total = (int) $this->db->fetch('SELECT COUNT(*) as c FROM users')['c'];
        $pages = (int) ceil($total / $perPage);

        $data = $this->db->fetchAll(
            "SELECT u.id, u.name, u.email, u.role_id, u.created_at, u.updated_at,
                    r.name as role_name
             FROM users u
             LEFT JOIN roles r ON u.role_id = r.id
             ORDER BY u.created_at DESC
             LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );

        return ['data' => $data, 'total' => $total, 'page' => $page, 'pages' => $pages];
    }

    /**
     * Get role list with permissions.
     *
     * @return list<array<string, mixed>>
     */
    public function getRoles(): array
    {
        return $this->db->fetchAll("SELECT * FROM roles ORDER BY id");
    }

    /**
     * Get system settings grouped.
     *
     * @return array<string, list<array>>
     */
    public function getSettings(): array
    {
        $rows = $this->db->fetchAll("SELECT * FROM system_settings ORDER BY group_name, key");
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['group_name']][] = $row;
        }
        return $grouped;
    }

    /**
     * Update a system setting and log the change.
     *
     * @param string $key
     * @param string $value
     * @param int $userId
     * @return void
     */
    public function updateSetting(string $key, string $value, int $userId): void
    {
        $old = $this->db->fetch("SELECT value FROM system_settings WHERE key = ?", [$key]);
        $oldValue = $old ? $old['value'] : null;

        $this->db->update(
            'system_settings',
            ['value' => $value, 'updated_by' => $userId, 'updated_at' => date('Y-m-d H:i:s')],
            'key = ?',
            [$key]
        );

        $this->logAction($userId, 'setting.update', 'system_settings', 0, $oldValue, $value);
    }

    /**
     * Get audit logs with pagination.
     *
     * @param int $page
     * @param int $perPage
     * @param string|null $filter
     * @return array{data: list<array>, total: int, page: int, pages: int}
     */
    public function getAuditLogs(int $page = 1, int $perPage = 50, ?string $filter = null): array
    {
        $where = $filter ? "WHERE al.action LIKE ?" : '';
        $params = $filter ? ['%' . $filter . '%'] : [];

        $total = (int) $this->db->fetch("SELECT COUNT(*) as c FROM audit_logs al $where", $params)['c'];
        $pages = (int) ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $data = $this->db->fetchAll(
            "SELECT al.*, u.name as user_name
             FROM audit_logs al
             LEFT JOIN users u ON al.user_id = u.id
             $where
             ORDER BY al.created_at DESC
             LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        return ['data' => $data, 'total' => $total, 'page' => $page, 'pages' => $pages];
    }

    /**
     * Log an admin action.
     *
     * @param int $userId
     * @param string $action
     * @param string $entity
     * @param int $entityId
     * @param string|null $oldValues
     * @param string|null $newValues
     * @return void
     */
    public function logAction(
        int $userId,
        string $action,
        string $entity = '',
        int $entityId = 0,
        ?string $oldValues = null,
        ?string $newValues = null
    ): void {
        $this->db->insert('audit_logs', [
            'user_id' => $userId,
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get unread notification count for a user.
     *
     * @param int $userId
     * @return int
     */
    public function getUnreadNotificationsCount(int $userId): int
    {
        return (int) $this->db->fetch(
            "SELECT COUNT(*) as c FROM notifications WHERE user_id = ? AND is_read = 0",
            [$userId]
        )['c'];
    }

    /**
     * Get notifications for a user.
     *
     * @param int $userId
     * @param int $limit
     * @return list<array<string, mixed>>
     */
    public function getNotifications(int $userId, int $limit = 20): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
    }
}
