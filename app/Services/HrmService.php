<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Helpers\Security;
use App\Repositories\EmployeeRepository;

/**
 * Human Resource Management service.
 *
 * Handles employee management, attendance tracking, leave workflows,
 * and payroll calculations.
 */
class HrmService
{
    private readonly Database $db;

    /**
     * @param EmployeeRepository $employeeRepository Employee data access
     */
    public function __construct(
        private readonly EmployeeRepository $employeeRepository = new EmployeeRepository(),
    ) {
        $this->db = Database::getInstance();
    }

    /**
     * Register a new employee.
     *
     * @param array<string, mixed> $data Employee data
     * @return int New employee ID
     * @throws \InvalidArgumentException If salary is out of valid range
     */
    public function createEmployee(array $data): int
    {
        $salary = (float) ($data['salary'] ?? 0);

        if (!Security::validateFinancial($salary)) {
            throw new \InvalidArgumentException('Invalid salary amount');
        }

        $data['salary'] = Security::formatFinancial($salary);
        $data['status'] = 'active';
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->employeeRepository->create($data);
    }

    /**
     * Clock in or out for an employee.
     *
     * If no record exists for today, creates a clock-in record.
     * If a clock-in exists, adds the clock-out time.
     *
     * @param int $employeeId
     * @return void
     */
    public function clockInOut(int $employeeId): void
    {
        $today = date('Y-m-d');
        $time = date('H:i:s');

        $existing = $this->db->fetch(
            "SELECT id, clock_out FROM hrm_attendance WHERE employee_id = ? AND date = ?",
            [$employeeId, $today]
        );

        if ($existing !== false && $existing['clock_out'] === null) {
            // Clock out
            $this->db->update(
                'hrm_attendance',
                ['clock_out' => $time, 'updated_at' => date('Y-m-d H:i:s')],
                'id = ?',
                [$existing['id']]
            );
        } else {
            // Clock in
            $this->db->insert('hrm_attendance', [
                'employee_id' => $employeeId,
                'date' => $today,
                'clock_in' => $time,
                'status' => 'present',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * Submit a leave request.
     *
     * @param int $employeeId
     * @param string $leaveType One of: sick, casual, earned, maternity, paternity
     * @param string $startDate Start date (Y-m-d)
     * @param string $endDate End date (Y-m-d)
     * @param string $reason Reason for leave
     * @return int New leave request ID
     */
    public function requestLeave(
        int $employeeId,
        string $leaveType,
        string $startDate,
        string $endDate,
        string $reason,
    ): int {
        $startTimestamp = strtotime($startDate);
        $endTimestamp = strtotime($endDate);
        $days = (int) ceil(($endTimestamp - $startTimestamp) / 86400) + 1;

        return $this->db->insert('hrm_leaves', [
            'employee_id' => $employeeId,
            'leave_type' => $leaveType,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days_count' => $days,
            'reason' => $reason,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Approve or reject a leave request.
     *
     * @param int $leaveId
     * @param string $action 'approve' or 'reject'
     * @param int $approvedBy User ID of the approver
     * @return int Number of affected rows
     */
    public function processLeave(int $leaveId, string $action, int $approvedBy): int
    {
        $status = $action === 'approve' ? 'approved' : 'rejected';

        return $this->db->update('hrm_leaves', [
            'status' => $status,
            'approved_by' => $approvedBy,
            'approved_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$leaveId]);
    }

    /**
     * Get today's attendance records.
     *
     * @return list<array<string, mixed>>
     */
    public function getTodayAttendance(): array
    {
        $today = date('Y-m-d');
        return $this->db->fetchAll(
            "SELECT a.id, a.employee_id, a.date, a.clock_in, a.clock_out, a.status,
                    e.employee_code, u.name
             FROM hrm_attendance a
             JOIN hrm_employees e ON a.employee_id = e.id
             JOIN users u ON e.user_id = u.id
             WHERE a.date = ?
             ORDER BY a.clock_in DESC",
            [$today]
        );
    }

    /**
     * Get all leave requests with employee info.
     *
     * @return list<array<string, mixed>>
     */
    public function getAllLeaveRequests(): array
    {
        return $this->db->fetchAll(
            "SELECT l.id, l.employee_id, l.leave_type, l.start_date, l.end_date,
                    l.days_count, l.reason, l.status, l.created_at,
                    e.employee_code, u.name, u2.name AS approved_by_name
             FROM hrm_leaves l
             JOIN hrm_employees e ON l.employee_id = e.id
             JOIN users u ON e.user_id = u.id
             LEFT JOIN users u2 ON l.approved_by = u2.id
             ORDER BY l.created_at DESC"
        );
    }

    /**
     * Get active employees with user information.
     *
     * @return list<array<string, mixed>>
     */
    public function getActiveEmployees(): array
    {
        return $this->employeeRepository->findAllActiveWithUser();
    }
}
