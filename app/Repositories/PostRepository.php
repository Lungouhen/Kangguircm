<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\PostStatus;

/**
 * CMS Post data access layer.
 *
 * Handles all database operations for the cms_posts table.
 * Uses explicit column selection and proper joins.
 */
class PostRepository extends BaseRepository
{
    protected readonly string $table = 'cms_posts';

    /** @var list<string> */
    protected readonly array $columns = [
        'id', 'title', 'slug', 'content', 'excerpt', 'featured_image',
        'author_id', 'category_id', 'status', 'published_at',
        'created_at', 'updated_at',
    ];

    /**
     * Find a post by slug.
     *
     * @param string $slug
     * @return array<string, mixed>|false
     */
    public function findBySlug(string $slug): array|false
    {
        $columnList = implode(', ', $this->columns);
        return $this->db->fetch(
            "SELECT {$columnList} FROM {$this->table} WHERE slug = ?",
            [$slug]
        );
    }

    /**
     * Find a post by ID with author name.
     *
     * @param int $id
     * @return array<string, mixed>|false
     */
    public function findByIdWithAuthor(int $id): array|false
    {
        return $this->db->fetch(
            "SELECT p.id, p.title, p.slug, p.content, p.excerpt,
                    p.featured_image, p.author_id, p.category_id,
                    p.status, p.published_at, p.created_at, p.updated_at,
                    u.name AS author_name
             FROM {$this->table} p
             JOIN users u ON p.author_id = u.id
             WHERE p.id = ?",
            [$id]
        );
    }

    /**
     * Get all posts with author names.
     *
     * @param int $limit
     * @param int $offset
     * @return list<array<string, mixed>>
     */
    public function findAllWithAuthor(int $limit = 50, int $offset = 0): array
    {
        return $this->db->fetchAll(
            "SELECT p.id, p.title, p.slug, p.excerpt, p.status,
                    p.created_at, u.name AS author_name
             FROM {$this->table} p
             JOIN users u ON p.author_id = u.id
             ORDER BY p.created_at DESC
             LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }

    /**
     * Get only published posts (for public-facing pages).
     *
     * @param int $limit
     * @param int $offset
     * @return list<array<string, mixed>>
     */
    public function findPublished(int $limit = 50, int $offset = 0): array
    {
        return $this->db->fetchAll(
            "SELECT p.id, p.title, p.slug, p.excerpt, p.featured_image,
                    p.published_at, u.name AS author_name
             FROM {$this->table} p
             JOIN users u ON p.author_id = u.id
             WHERE p.status = ?
             ORDER BY p.published_at DESC
             LIMIT ? OFFSET ?",
            [PostStatus::Published->value, $limit, $offset]
        );
    }

    /**
     * Check if a slug already exists.
     *
     * @param string $slug
     * @param int|null $excludeId Exclude this post ID from the check
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $result = $this->db->fetch(
                "SELECT id FROM {$this->table} WHERE slug = ? AND id != ?",
                [$slug, $excludeId]
            );
        } else {
            $result = $this->db->fetch(
                "SELECT id FROM {$this->table} WHERE slug = ?",
                [$slug]
            );
        }

        return $result !== false;
    }

    /**
     * Count posts by status.
     *
     * @param PostStatus $status
     */
    public function countByStatus(PostStatus $status): int
    {
        return $this->count('status = ?', [$status->value]);
    }
}
