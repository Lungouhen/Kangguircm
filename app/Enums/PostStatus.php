<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Post publication status.
 *
 * Maps to the cms_posts.status ENUM column.
 */
enum PostStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';

    /**
     * Get a human-readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Published',
            self::Archived => 'Archived',
        };
    }

    /**
     * Whether this status represents a publicly visible post.
     */
    public function isPublic(): bool
    {
        return $this === self::Published;
    }
}
