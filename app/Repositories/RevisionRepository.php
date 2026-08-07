<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Revision data access layer.
 *
 * Tracks post changes for version history and rollback.
 *
 * Following php-pro skill: Repository pattern
 * Following backend-developer skill: Audit trail
 */
class RevisionRepository extends BaseRepository
{
    protected string $table = 'cms_revisions';

    /** @var list<string> */
    protected array $columns = [
        'id', 'post_id', 'title', 'content', 'excerpt', 'status',
        'author_id', 'change_summary', 'created_at',
    ];

    /**
     * Get all revisions for a post.
     *
     * @param int $postId
     * @param int $limit
     * @return list<array<string, mixed>>
     */
    public function findByPostId(int $postId, int $limit = 50): array
    {
        return $this->db->fetchAll(
            "SELECT r.*, u.name as author_name
             FROM {$this->table} r
             LEFT JOIN users u ON r.author_id = u.id
             WHERE r.post_id = ?
             ORDER BY r.created_at DESC
             LIMIT ?",
            [$postId, $limit]
        );
    }

    /**
     * Get a specific revision by ID.
     *
     * @param int $id
     * @return array<string, mixed>|false
     */
    public function findById(int $id): array|false
    {
        return $this->db->fetch(
            "SELECT r.*, u.name as author_name
             FROM {$this->table} r
             LEFT JOIN users u ON r.author_id = u.id
             WHERE r.id = ?",
            [$id]
        );
    }

    /**
     * Create a revision for a post.
     *
     * @param int $postId
     * @param string $title
     * @param string|null $content
     * @param string|null $excerpt
     * @param string|null $status
     * @param int $authorId
     * @param string|null $changeSummary
     * @return int
     */
    public function createRevision(
        int $postId,
        string $title,
        ?string $content = null,
        ?string $excerpt = null,
        ?string $status = null,
        int $authorId = 0,
        ?string $changeSummary = null
    ): int {
        return $this->create([
            'post_id' => $postId,
            'title' => $title,
            'content' => $content,
            'excerpt' => $excerpt,
            'status' => $status,
            'author_id' => $authorId,
            'change_summary' => $changeSummary,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Restore a revision to the current post.
     *
     * @param int $revisionId
     * @return array<string, mixed>|false The restored revision data
     */
    public function restore(int $revisionId): array|false
    {
        $revision = $this->findById($revisionId);
        if ($revision === false) {
            return false;
        }

        // Create a new revision before restoring (audit trail)
        $this->createRevision(
            $revision['post_id'],
            $revision['title'],
            $revision['content'],
            $revision['excerpt'],
            $revision['status'],
            $revision['author_id'],
            'Restored from revision #' . $revisionId
        );

        // Update the post with revision data
        $this->db->update(
            'cms_posts',
            [
                'title' => $revision['title'],
                'content' => $revision['content'],
                'excerpt' => $revision['excerpt'],
                'status' => $revision['status'] ?? 'draft',
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            'id = ?',
            [$revision['post_id']]
        );

        return $revision;
    }

    /**
     * Get revision count for a post.
     *
     * @param int $postId
     * @return int
     */
    public function countByPostId(int $postId): int
    {
        return (int) $this->db->fetch(
            "SELECT COUNT(*) as c FROM {$this->table} WHERE post_id = ?",
            [$postId]
        )['c'];
    }

    /**
     * Delete old revisions for a post (keep last N).
     *
     * @param int $postId
     * @param int $keepCount
     * @return int Number deleted
     */
    public function pruneOld(int $postId, int $keepCount = 10): int
    {
        $ids = $this->db->fetchAll(
            "SELECT id FROM {$this->table}
             WHERE post_id = ?
             ORDER BY created_at DESC
             LIMIT 1000 OFFSET ?",
            [$postId, $keepCount]
        );

        if (empty($ids)) {
            return 0;
        }

        $idsToDelete = array_column($ids, 'id');
        $placeholders = implode(',', array_fill(0, count($idsToDelete), '?'));
        return $this->db->query(
            "DELETE FROM {$this->table} WHERE id IN ({$placeholders})",
            $idsToDelete
        )->rowCount();
    }
}
