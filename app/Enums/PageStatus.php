<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Page publication status.
 *
 * Maps to the cms_pages.status ENUM column.
 */
enum PageStatus: string
{
    case Draft = 'draft';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Published',
        };
    }
}
