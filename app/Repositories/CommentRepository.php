<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Comment data access layer.
 *
 * Following php-pro skill: Repository pattern
 * Following security-auditor skill: Input validation, moderation
 */
class CommentRepository extends BaseRepository
{
    protected string $table = 'cms_comments';

    /** @var list<string> */
    protected array $columns = [
        'id', 'post_id', 'user_id', 'author_name', 'author_email', 'author_ip',
        'content', 'status', 'approved_by', 'approved_at', 'parent_id',
        'created_at', 'updated_at',
    ];

    /**
     * Get comments for a post (approved only, with pagination).
     *
     * @param int $postId
     * @param int $limit
     * @param int $offset
     * @return list<array<string, mixed>>
     */
    public function findApprovedByPostId(int $postId, int $limit = 20, int $offset = 0): array
    {
        return $this->db->fetchAll(
            "SELECT c.*, u.name as user_name
             FROM {$this->table} c
             LEFT JOIN users u ON c.user_id = u.id
             WHERE c.post_id = ? AND c.status = 'approved'
             ORDER BY c.created_at DESC
             LIMIT ? OFFSET ?",
            [$postId, $limit, $offset]
        );
    }

    /**
     * Get all comments for moderation (pending status).
     *
     * @param int $limit
     * @param int $offset
     * @return array{data: list<array>, total: int}
     */
    public function findPending(int $limit = 20, int $offset = 0): array
    {
        $total = (int) $this->db->fetch(
            "SELECT COUNT(*) as c FROM {$this->table} WHERE status = 'pending'"
        )['c'];

        $data = $this->db->fetchAll(
            "SELECT c.*, p.title as post_title, u.name as user_name
             FROM {$this->table} c
             LEFT JOIN cms_posts p ON c.post_id = p.id
             LEFT JOIN users u ON c.user_id = u.id
             WHERE c.status = 'pending'
             ORDER BY c.created_at DESC
             LIMIT ? OFFSET ?",
            [$limit, $offset]
        );

        return ['data' => $data, 'total' => $total];
    }

    /**
     * Get a comment by ID with post info.
     *
     * @param int $id
     * @return array<string, mixed>|false
     */
    public function findByIdWithPost(int $id): array|false
    {
        return $this->db->fetch(
            "SELECT c.*, p.title as post_title, p.slug as post_slug,
                    u.name as user_name
             FROM {$this->table} c
             LEFT JOIN cms_posts p ON c.post_id = p.id
             LEFT JOIN users u ON c.user_id = u.id
             WHERE c.id = ?",
            [$id]
        );
    }

    /**
     * Create a new comment.
     *
     * @param int $postId
     * @param string $content
     * @param string|null $authorName
     * @param string|null $authorEmail
     * @param int|null $userId
     * @param string|null $authorIp
     * @param int|null $parentId
     * @return int
     */
    public function createComment(
        int $postId,
        string $content,
        ?string $authorName = null,
        ?string $authorEmail = null,
        ?int $userId = null,
        ?string $authorIp = null,
        ?int $parentId = null
    ): int {
        return $this->create([
            'post_id' => $postId,
            'content' => $content,
            'author_name' => $authorName,
            'author_email' => $authorEmail,
            'user_id' => $userId,
            'author_ip' => $authorIp,
            'parent_id' => $parentId,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Approve a comment.
     *
     * @param int $id
     * @param int $approvedBy
     * @return int
     */
    public function approve(int $id, int $approvedBy): int
    {
        $result = $this->db->update(
            $this->table,
            [
                'status' => 'approved',
                'approved_by' => $approvedBy,
                'approved_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            'id = ?',
            [$id]
        );

        // Increment post comment count
        if ($result > 0) {
            $comment = $this->findById($id);
            if ($comment !== false) {
                $this->db->query(
                    "UPDATE cms_posts SET comment_count = comment_count + 1 WHERE id = ?",
                    [$comment['post_id']]
                );
            }
        }

        return $result;
    }

    /**
     * Reject (delete) a comment.
     *
     * @param int $id
     * @return int
     */
    public function reject(int $id): int
    {
        $comment = $this->findById($id);
        if ($comment === false) {
            return 0;
        }

        // Decrement post comment count if approved
        if ($comment['status'] === 'approved') {
            $this->db->query(
                "UPDATE cms_posts SET comment_count = MAX(0, comment_count - 1) WHERE id = ?",
                [$comment['post_id']]
            );
        }

        return $this->delete($id);
    }

    /**
     * Bulk approve comments.
     *
     * @param list<int> $ids
     * @param int $approvedBy
     * @return int
     */
    public function bulkApprove(array $ids, int $approvedBy): int
    {
        if (empty($ids)) return 0;

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $result = $this->db->query(
            "UPDATE {$this->table}
             SET status = 'approved', approved_by = ?, approved_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP
             WHERE id IN ({$placeholders}) AND status = 'pending'",
            array_merge([$approvedBy], $ids)
        )->rowCount();

        // Update comment counts
        $posts = $this->db->fetchAll(
            "SELECT post_id, COUNT(*) as cnt FROM {$this->table}
             WHERE id IN ({$placeholders})
             GROUP BY post_id",
            $ids
        );
        foreach ($posts as $post) {
            $this->db->query(
                "UPDATE cms_posts SET comment_count = comment_count + ? WHERE id = ?",
                [(int)$post['cnt'], (int)$post['post_id']]
            );
        }

        return $result;
    }

    /**
     * Bulk delete comments.
     *
     * @param list<int> $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        if (empty($ids)) return 0;

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // Decrement comment counts first
        $posts = $this->db->fetchAll(
            "SELECT post_id, COUNT(*) as cnt FROM {$this->table}
             WHERE id IN ({$placeholders}) AND status = 'approved'
             GROUP BY post_id",
            $ids
        );
        foreach ($posts as $post) {
            $this->db->query(
                "UPDATE cms_posts SET comment_count = MAX(0, comment_count - ?) WHERE id = ?",
                [(int)$post['cnt'], (int)$post['post_id']]
            );
        }

        return $this->db->query(
            "DELETE FROM {$this->table} WHERE id IN ({$placeholders})",
            $ids
        )->rowCount();
    }

    /**
     * Count comments by status.
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
}
