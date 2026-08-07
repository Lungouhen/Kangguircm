<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Email campaign lifecycle status.
 *
 * Maps to the email_campaigns.status ENUM column.
 */
enum CampaignStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Sending = 'sending';
    case Sent = 'sent';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Scheduled => 'Scheduled',
            self::Sending => 'Sending',
            self::Sent => 'Sent',
        };
    }

    public function isTerminal(): bool
    {
        return $this === self::Sent;
    }
}
