<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Employee employment status.
 *
 * Maps to the hrm_employees.status ENUM column.
 */
enum EmployeeStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Terminated = 'terminated';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Terminated => 'Terminated',
        };
    }

    public function canClockIn(): bool
    {
        return $this === self::Active;
    }
}
