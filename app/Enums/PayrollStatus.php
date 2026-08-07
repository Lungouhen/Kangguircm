<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Payroll payment status.
 *
 * Maps to the hrm_payroll.payment_status ENUM column.
 */
enum PayrollStatus: string
{
    case Pending = 'pending';
    case Processed = 'processed';
    case Paid = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Processed => 'Processed',
            self::Paid => 'Paid',
        };
    }
}
