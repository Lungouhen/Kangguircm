<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Employee data access layer.
 *
 * Handles all database operations for the hrm_employees table.
 */
class EmployeeRepository extends BaseRepository
{
    protected string $table = 'hrm_employees';

    /** @var list<string> */
    protected array $columns = [
        'id', 'user_id', 'employee_code', 'department', 'designation',
        'date_of_joining', 'date_of_birth', 'phone', 'address',
        'emergency_contact', 'salary', 'bank_details', 'status',
        'created_at', 'updated_at',
    ];

    /**
     * Find an employee by their employee code.
     *
     * @param string $code
     * @return array<string, mixed>|false
     */
    public function findByCode(string $code): array|false
    {
        $columnList = implode(', ', $this->columns);
        return $this->db->fetch(
            "SELECT {$columnList} FROM {$this->table} WHERE employee_code = ?",
            [$code]
        );
    }

    /**
     * Find employee by ID with linked user information.
     *
     * @param int $id
     * @return array<string, mixed>|false
     */
    public function findByIdWithUser(int $id): array|false
    {
        return $this->db->fetch(
            "SELECT e.id, e.user_id, e.employee_code, e.department,
                    e.designation, e.date_of_joining, e.salary, e.status,
                    u.name, u.email
             FROM {$this->table} e
             JOIN users u ON e.user_id = u.id
             WHERE e.id = ?",
            [$id]
        );
    }

    /**
     * Get all active employees with user information.
     *
     * @param int $limit
     * @param int $offset
     * @return list<array<string, mixed>>
     */
    public function findAllActiveWithUser(int $limit = 50, int $offset = 0): array
    {
        return $this->db->fetchAll(
            "SELECT e.id, e.employee_code, e.department, e.designation,
                    e.date_of_joining, e.salary, e.status,
                    u.name, u.email
             FROM {$this->table} e
             JOIN users u ON e.user_id = u.id
             WHERE e.status = 'active'
             ORDER BY e.created_at DESC
             LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }

    /**
     * Check if an employee code already exists.
     *
     * @param string $code
     */
    public function codeExists(string $code): bool
    {
        $result = $this->db->fetch(
            "SELECT id FROM {$this->table} WHERE employee_code = ?",
            [$code]
        );
        return $result !== false;
    }
}
