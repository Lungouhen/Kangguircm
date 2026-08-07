<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Actions\ProcessLeaveAction;
use App\Core\Session;
use App\Core\View;
use App\Helpers\Validation;
use App\Services\HrmService;

/**
 * HRM controller for human resource management.
 *
 * Manages employees, attendance, leave requests, and payroll.
 * Uses HrmService for business logic and Action classes for workflows.
 */
class HrmController
{
    /**
     * @param HrmService $hrmService HRM business logic
     * @param ProcessLeaveAction $processLeaveAction Leave processing workflow
     */
    public function __construct(
        private readonly HrmService $hrmService = new HrmService(),
        private readonly ProcessLeaveAction $processLeaveAction = new ProcessLeaveAction(),
    ) {}

    /**
     * Display all active employees.
     */
    public function employees(): void
    {
        $employees = $this->hrmService->getActiveEmployees();
        View::display('hrm.employees', ['pageTitle' => 'HRM - Employees', 'employees' => $employees]);
    }

    /**
     * Display the employee creation form.
     */
    public function createEmployee(): void
    {
        View::display('hrm.create-employee', ['pageTitle' => 'Add Employee']);
    }

    /**
     * Store a new employee.
     */
    public function storeEmployee(): void
    {
        $validator = new Validation();
        if (!$validator->validate($_POST, [
            'employee_code' => 'required|min:2|max:50|unique:hrm_employees,employee_code',
            'date_of_joining' => 'required|date',
            'salary' => 'required|numeric',
        ])) {
            Session::flash('errors', $validator->errors());
            Response::redirect('/hrm/employees/create');
            exit;
        }

        try {
            $this->hrmService->createEmployee([
                'user_id' => (int) $_POST['user_id'],
                'employee_code' => $_POST['employee_code'],
                'department' => $_POST['department'] ?? null,
                'designation' => $_POST['designation'] ?? null,
                'date_of_joining' => $_POST['date_of_joining'],
                'date_of_birth' => $_POST['date_of_birth'] ?? null,
                'phone' => $_POST['phone'] ?? null,
                'address' => $_POST['address'] ?? null,
                'emergency_contact' => $_POST['emergency_contact'] ?? null,
                'salary' => $_POST['salary'],
            ]);

            Response::redirect('/hrm/employees');
            exit;
        } catch (\InvalidArgumentException $e) {
            Session::flash('error', $e->getMessage());
            Response::redirect('/hrm/employees/create');
            exit;
        }
    }

    /**
     * Display today's attendance records.
     */
    public function attendance(): void
    {
        $attendance = $this->hrmService->getTodayAttendance();
        View::display('hrm.attendance', ['pageTitle' => 'HRM - Attendance', 
            'attendance' => $attendance,
            'today' => date('Y-m-d'),
        ]);
    }

    /**
     * Process a clock-in/out action.
     */
    public function clockIn(): void
    {
        $validator = new Validation();
        if (!$validator->validate($_POST, [
            'employee_id' => 'required|numeric',
        ])) {
            Session::flash('errors', $validator->errors());
            Response::redirect('/hrm/attendance');
            exit;
        }

        $this->hrmService->clockInOut((int) $_POST['employee_id']);

        Response::redirect('/hrm/attendance');
        exit;
    }

    /**
     * Display all leave requests.
     */
    public function leaves(): void
    {
        $leaves = $this->hrmService->getAllLeaveRequests();
        View::display('hrm.leaves', ['pageTitle' => 'HRM - Leaves', 'leaves' => $leaves]);
    }

    /**
     * Submit a new leave request.
     */
    public function requestLeave(): void
    {
        $validator = new Validation();
        if (!$validator->validate($_POST, [
            'employee_id' => 'required|numeric',
            'leave_type' => 'required|in:sick,casual,earned,maternity,paternity',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'reason' => 'required|min:10',
        ])) {
            Session::flash('errors', $validator->errors());
            Response::redirect('/hrm/leaves');
            exit;
        }

        $this->hrmService->requestLeave(
            employeeId: (int) $_POST['employee_id'],
            leaveType: $_POST['leave_type'],
            startDate: $_POST['start_date'],
            endDate: $_POST['end_date'],
            reason: $_POST['reason'],
        );

        Response::redirect('/hrm/leaves');
        exit;
    }

    /**
     * Approve or reject a leave request.
     *
     * @param string $id Leave request ID
     */
    public function approveLeave(string $id): void
    {
        Session::start();
        $action = $_POST['action'] ?? 'approve';

        $this->processLeaveAction->execute(
            leaveId: (int) $id,
            action: $action,
            approvedBy: (int) Session::get('user_id'),
        );

        Response::redirect('/hrm/leaves');
        exit;
    }
}
