<?php

declare(strict_types=1);

namespace App\Actions;

use App\Services\HrmService;

/**
 * Action class for processing leave requests (approve/reject).
 */
class ProcessLeaveAction
{
    public function __construct(
        private readonly HrmService $hrmService = new HrmService(),
    ) {}

    /**
     * Approve or reject a leave request.
     *
     * @param int $leaveId Leave request ID
     * @param string $action 'approve' or 'reject'
     * @param int $approvedBy User ID of the decision maker
     * @return int Number of affected rows
     */
    public function execute(int $leaveId, string $action, int $approvedBy): int
    {
        return $this->hrmService->processLeave($leaveId, $action, $approvedBy);
    }
}
