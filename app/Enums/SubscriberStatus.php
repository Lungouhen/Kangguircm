<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Email subscriber status.
 *
 * Maps to the email_subscribers.status ENUM column.
 */
enum SubscriberStatus: string
{
    case Active = 'active';
    case Unsubscribed = 'unsubscribed';
    case Bounced = 'bounced';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Unsubscribed => 'Unsubscribed',
            self::Bounced => 'Bounced',
        };
    }

    public function canReceiveEmail(): bool
    {
        return $this === self::Active;
    }
}
