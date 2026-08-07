<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Leave request types.
 *
 * Maps to the hrm_leaves.leave_type ENUM column.
 */
enum LeaveType: string
{
    case Sick = 'sick';
    case Casual = 'casual';
    case Earned = 'earned';
    case Maternity = 'maternity';
    case Paternity = 'paternity';

    public function label(): string
    {
        return match ($this) {
            self::Sick => 'Sick Leave',
            self::Casual => 'Casual Leave',
            self::Earned => 'Earned Leave',
            self::Maternity => 'Maternity Leave',
            self::Paternity => 'Paternity Leave',
        };
    }
}
