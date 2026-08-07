<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Attendance record status.
 *
 * Maps to the hrm_attendance.status ENUM column.
 */
enum AttendanceStatus: string
{
    case Present = 'present';
    case Absent = 'absent';
    case Late = 'late';
    case HalfDay = 'half_day';

    public function label(): string
    {
        return match ($this) {
            self::Present => 'Present',
            self::Absent => 'Absent',
            self::Late => 'Late',
            self::HalfDay => 'Half Day',
        };
    }
}
