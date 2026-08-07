<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Leave request approval status.
 *
 * Maps to the hrm_leaves.status ENUM column.
 */
enum LeaveStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }

    public function isResolved(): bool
    {
        return $this !== self::Pending;
    }
}
