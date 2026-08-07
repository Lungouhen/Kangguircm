<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Core\Session;
use App\Core\Database;
use App\Helpers\{Validation, Security};

class HrmController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function employees(): void
    {
        $employees = $this->db->fetchAll(
            "SELECT e.*, u.name, u.email 
             FROM hrm_employees e 
             JOIN users u ON e.user_id = u.id 
             WHERE e.status = 'active'
             ORDER BY e.created_at DESC"
        );

        View::display('hrm.employees', ['employees' => $employees]);
    }

    public function createEmployee(): void
    {
        View::display('hrm.create-employee');
    }

    public function storeEmployee(): void
    {
        $validator = new Validation();
        if (!$validator->validate($_POST, [
            'employee_code' => 'required|min:2|max:50|unique:hrm_employees,employee_code',
            'date_of_joining' => 'required|date',
            'salary' => 'required|numeric'
        ])) {
            Session::flash('errors', $validator->errors());
            header('Location: /hrm/employees/create');
            exit;
        }

        if (!Security::validateFinancial((float)$_POST['salary'])) {
            Session::flash('error', 'Invalid salary amount');
            header('Location: /hrm/employees/create');
            exit;
        }

        $this->db->insert('hrm_employees', [
            'user_id' => $_POST['user_id'],
            'employee_code' => $_POST['employee_code'],
            'department' => $_POST['department'] ?? null,
            'designation' => $_POST['designation'] ?? null,
            'date_of_joining' => $_POST['date_of_joining'],
            'date_of_birth' => $_POST['date_of_birth'] ?? null,
            'phone' => $_POST['phone'] ?? null,
            'address' => $_POST['address'] ?? null,
            'emergency_contact' => $_POST['emergency_contact'] ?? null,
            'salary' => Security::formatFinancial((float)$_POST['salary']),
            'status' => 'active'
        ]);

        header('Location: /hrm/employees');
        exit;
    }

    public function attendance(): void
    {
        $today = date('Y-m-d');
        $attendance = $this->db->fetchAll(
            "SELECT a.*, e.employee_code, u.name 
             FROM hrm_attendance a 
             JOIN hrm_employees e ON a.employee_id = e.id 
             JOIN users u ON e.user_id = u.id 
             WHERE a.date = ?
             ORDER BY a.clock_in DESC",
            [$today]
        );

        View::display('hrm.attendance', ['attendance' => $attendance, 'today' => $today]);
    }

    public function clockIn(): void
    {
        $validator = new Validation();
        if (!$validator->validate($_POST, [
            'employee_id' => 'required|numeric'
        ])) {
            Session::flash('errors', $validator->errors());
            header('Location: /hrm/attendance');
            exit;
        }

        $employeeId = (int)$_POST['employee_id'];
        $today = date('Y-m-d');
        $time = date('H:i:s');

        $existing = $this->db->fetch(
            "SELECT id FROM hrm_attendance WHERE employee_id = ? AND date = ?",
            [$employeeId, $today]
        );

        if ($existing) {
            $this->db->update(
                'hrm_attendance',
                ['clock_out' => $time, 'updated_at' => date('Y-m-d H:i:s')],
                'id = ?',
                [$existing['id']]
            );
        } else {
            $this->db->insert('hrm_attendance', [
                'employee_id' => $employeeId,
                'date' => $today,
                'clock_in' => $time,
                'status' => 'present'
            ]);
        }

        header('Location: /hrm/attendance');
        exit;
    }

    public function leaves(): void
    {
        $leaves = $this->db->fetchAll(
            "SELECT l.*, e.employee_code, u.name, u2.name as approved_by_name
             FROM hrm_leaves l 
             JOIN hrm_employees e ON l.employee_id = e.id 
             JOIN users u ON e.user_id = u.id 
             LEFT JOIN users u2 ON l.approved_by = u2.id
             ORDER BY l.created_at DESC"
        );

        View::display('hrm.leaves', ['leaves' => $leaves]);
    }

    public function requestLeave(): void
    {
        $validator = new Validation();
        if (!$validator->validate($_POST, [
            'employee_id' => 'required|numeric',
            'leave_type' => 'required|in:sick,casual,earned,maternity,paternity',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'reason' => 'required|min:10'
        ])) {
            Session::flash('errors', $validator->errors());
            header('Location: /hrm/leaves');
            exit;
        }

        $startDate = strtotime($_POST['start_date']);
        $endDate = strtotime($_POST['end_date']);
        $days = ceil(($endDate - $startDate) / 86400) + 1;

        $this->db->insert('hrm_leaves', [
            'employee_id' => $_POST['employee_id'],
            'leave_type' => $_POST['leave_type'],
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date'],
            'days_count' => $days,
            'reason' => $_POST['reason'],
            'status' => 'pending'
        ]);

        header('Location: /hrm/leaves');
        exit;
    }

    public function approveLeave(string $id): void
    {
        $action = $_POST['action'] ?? 'approve';
        $status = $action === 'approve' ? 'approved' : 'rejected';

        $this->db->update('hrm_leaves', [
            'status' => $status,
            'approved_by' => Session::get('user_id'),
            'approved_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$id]);

        header('Location: /hrm/leaves');
        exit;
    }
}
