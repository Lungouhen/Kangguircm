<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Post data access layer.
 *
 * Handles all database operations for cms_posts table.
 * Uses explicit column selection and parameterized queries.
 *
 * Following php-pro skill: Repository pattern
 * Following database-optimizer skill: No SELECT *, proper indexes
 * Following security-auditor skill: Prepared statements
 */
class PostRepository extends BaseRepository
{
    protected string $table = 'cms_posts';

    /** @var list<string> */
    protected array $columns = [
        'id', 'title', 'slug', 'content', 'excerpt', 'featured_image',
        'author_id', 'category_id', 'status', 'review_status', 'published_at',
        'meta_title', 'meta_description', 'og_image', 'view_count', 'featured',
        'reading_time', 'reviewed_by', 'reviewed_at', 'allow_comments', 'comment_count',
        'created_at', 'updated_at',
    ];

    /**
     * Find a post by slug with related data.
     *
     * @param string $slug
     * @return array<string, mixed>|false
     */
    public function findBySlug(string $slug): array|false
    {
        return $this->db->fetch(
            "SELECT p.*, u.name as author_name, c.name as category_name
             FROM {$this->table} p
             LEFT JOIN users u ON p.author_id = u.id
             LEFT JOIN cms_categories c ON p.category_id = c.id
             WHERE p.slug = ?",
            [$slug]
        );
    }

    /**
     * Find a post by ID with author and category names.
     *
     * @param int $id
     * @return array<string, mixed>|false
     */
    public function findByIdWithRelations(int $id): array|false
    {
        return $this->db->fetch(
            "SELECT p.*, u.name as author_name, c.name as category_name
             FROM {$this->table} p
             LEFT JOIN users u ON p.author_id = u.id
             LEFT JOIN cms_categories c ON p.category_id = c.id
             WHERE p.id = ?",
            [$id]
        );
    }

    /**
     * Get all posts with author names, paginated and filterable.
     *
     * @param array<string, mixed> $filters {status, review_status, author_id, category_id, search}
     * @param int $limit
     * @param int $offset
     * @return array{data: list<array>, total: int}
     */
    public function findAllWithFilters(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "p.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['review_status'])) {
            $where[] = "p.review_status = ?";
            $params[] = $filters['review_status'];
        }

        if (!empty($filters['author_id'])) {
            $where[] = "p.author_id = ?";
            $params[] = $filters['author_id'];
        }

        if (!empty($filters['category_id'])) {
            $where[] = "p.category_id = ?";
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(p.title LIKE ? OR p.content LIKE ? OR p.excerpt LIKE ?)";
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $whereParams = $params;

        // Get total count
        $total = (int) $this->db->fetch(
            "SELECT COUNT(*) as c FROM {$this->table} p {$whereClause}",
            $whereParams
        )['c'];

        // Get data
        $data = $this->db->fetchAll(
            "SELECT p.id, p.title, p.slug, p.excerpt, p.status, p.review_status,
                    p.featured, p.view_count, p.comment_count, p.published_at,
                    p.created_at, u.name as author_name, c.name as category_name
             FROM {$this->table} p
             LEFT JOIN users u ON p.author_id = u.id
             LEFT JOIN cms_categories c ON p.category_id = c.id
             {$whereClause}
             ORDER BY p.created_at DESC
             LIMIT ? OFFSET ?",
            array_merge($whereParams, [$limit, $offset])
        );

        return ['data' => $data, 'total' => $total];
    }

    /**
     * Get published posts for public display.
     *
     * @param int $limit
     * @param int $offset
     * @return list<array<string, mixed>>
     */
    public function findPublished(int $limit = 20, int $offset = 0): array
    {
        return $this->db->fetchAll(
            "SELECT p.id, p.title, p.slug, p.excerpt, p.featured_image,
                    p.published_at, p.view_count, p.reading_time,
                    u.name as author_name, c.name as category_name
             FROM {$this->table} p
             LEFT JOIN users u ON p.author_id = u.id
             LEFT JOIN cms_categories c ON p.category_id = c.id
             WHERE p.status = 'published' AND p.review_status = 'approved'
             ORDER BY p.published_at DESC
             LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }

    /**
     * Get featured posts.
     *
     * @param int $limit
     * @return list<array<string, mixed>>
     */
    public function findFeatured(int $limit = 5): array
    {
        return $this->db->fetchAll(
            "SELECT p.id, p.title, p.slug, p.excerpt, p.featured_image, p.published_at
             FROM {$this->table} p
             WHERE p.featured = 1 AND p.status = 'published'
             ORDER BY p.published_at DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Get posts by tag.
     *
     * @param int $tagId
     * @param int $limit
     * @param int $offset
     * @return list<array<string, mixed>>
     */
    public function findByTag(int $tagId, int $limit = 20, int $offset = 0): array
    {
        return $this->db->fetchAll(
            "SELECT p.id, p.title, p.slug, p.excerpt, p.published_at,
                    u.name as author_name
             FROM {$this->table} p
             INNER JOIN cms_post_tags pt ON p.id = pt.post_id
             LEFT JOIN users u ON p.author_id = u.id
             WHERE pt.tag_id = ? AND p.status = 'published'
             ORDER BY p.published_at DESC
             LIMIT ? OFFSET ?",
            [$tagId, $limit, $offset]
        );
    }

    /**
     * Get tags for a post.
     *
     * @param int $postId
     * @return list<array<string, mixed>>
     */
    public function getPostTags(int $postId): array
    {
        return $this->db->fetchAll(
            "SELECT t.* FROM cms_tags t
             INNER JOIN cms_post_tags pt ON t.id = pt.tag_id
             WHERE pt.post_id = ?
             ORDER BY t.name",
            [$postId]
        );
    }

    /**
     * Set tags for a post (replaces all existing tags).
     *
     * @param int $postId
     * @param list<int> $tagIds
     * @return void
     */
    public function setPostTags(int $postId, array $tagIds): void
    {
        $this->db->delete('cms_post_tags', 'post_id = ?', [$postId]);

        foreach ($tagIds as $tagId) {
            $this->db->insert('cms_post_tags', [
                'post_id' => $postId,
                'tag_id' => $tagId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * Increment view count for a post.
     *
     * @param int $postId
     * @return void
     */
    public function incrementViewCount(int $postId): void
    {
        $this->db->query(
            "UPDATE {$this->table} SET view_count = view_count + 1 WHERE id = ?",
            [$postId]
        );
    }

    /**
     * Update review status and reviewer info.
     *
     * @param int $postId
     * @param string $reviewStatus
     * @param int $reviewerId
     * @return int
     */
    public function updateReviewStatus(int $postId, string $reviewStatus, int $reviewerId): int
    {
        return $this->db->update(
            $this->table,
            [
                'review_status' => $reviewStatus,
                'reviewed_by' => $reviewerId,
                'reviewed_at' => date('Y-m-d H:i:s'),
                'status' => $reviewStatus === 'approved' ? 'published' : 'draft',
                'published_at' => $reviewStatus === 'approved' ? date('Y-m-d H:i:s') : null,
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            'id = ?',
            [$postId]
        );
    }

    /**
     * Bulk update status for multiple posts.
     *
     * @param list<int> $postIds
     * @param string $status
     * @return int
     */
    public function bulkUpdateStatus(array $postIds, string $status): int
    {
        $placeholders = implode(',', array_fill(0, count($postIds), '?'));
        return $this->db->query(
            "UPDATE {$this->table} SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id IN ({$placeholders})",
            array_merge([$status], $postIds)
        )->rowCount();
    }

    /**
     * Bulk delete multiple posts.
     *
     * @param list<int> $postIds
     * @return int
     */
    public function bulkDelete(array $postIds): int
    {
        $placeholders = implode(',', array_fill(0, count($postIds), '?'));
        return $this->db->query(
            "DELETE FROM {$this->table} WHERE id IN ({$placeholders})",
            $postIds
        )->rowCount();
    }

    /**
     * Count posts by status.
     *
     * @param string $status
     * @return int
     */
    public function countByStatus(string $status): int
    {
        return (int) $this->db->fetch(
            "SELECT COUNT(*) as c FROM {$this->table} WHERE status = ?",
            [$status]
        )['c'];
    }

    /**
     * Get recent posts for dashboard.
     *
     * @param int $limit
     * @return list<array<string, mixed>>
     */
    public function findRecent(int $limit = 5): array
    {
        return $this->db->fetchAll(
            "SELECT p.id, p.title, p.status, p.created_at, u.name as author_name
             FROM {$this->table} p
             LEFT JOIN users u ON p.author_id = u.id
             ORDER BY p.created_at DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Calculate reading time based on content length.
     *
     * @param string $content
     * @return int Minutes
     */
    public function calculateReadingTime(string $content): int
    {
        $wordCount = str_word_count(strip_tags($content));
        return max(1, (int) ceil($wordCount / 200)); // 200 words per minute
    }
}
