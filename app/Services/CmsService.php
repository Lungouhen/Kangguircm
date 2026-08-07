<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Repositories\PostRepository;
use App\Repositories\TagRepository;
use App\Repositories\RevisionRepository;
use App\Repositories\CommentRepository;

/**
 * CMS Service Layer.
 *
 * Following php-pro skill: Service layer pattern
 * Following backend-developer skill: Business logic separation
 * Following fullstack-developer skill: End-to-end data flow
 */
class CmsService
{
    private Database $db;

    public function __construct(
        private readonly PostRepository $postRepo = new PostRepository(),
        private readonly TagRepository $tagRepo = new TagRepository(),
        private readonly RevisionRepository $revisionRepo = new RevisionRepository(),
        private readonly CommentRepository $commentRepo = new CommentRepository(),
    ) {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new post with tags and initial revision.
     *
     * @param array<string, mixed> $data
     * @param list<int> $tagIds
     * @param int $authorId
     * @return int Post ID
     */
    public function createPost(array $data, array $tagIds = [], int $authorId = 0): int
    {
        // Calculate reading time
        $data['reading_time'] = $this->postRepo->calculateReadingTime($data['content'] ?? '');

        $postId = $this->postRepo->create([
            'title' => $data['title'],
            'slug' => $this->generateUniqueSlug($data['title']),
            'content' => $data['content'] ?? '',
            'excerpt' => $data['excerpt'] ?? null,
            'featured_image' => $data['featured_image'] ?? null,
            'author_id' => $authorId,
            'category_id' => $data['category_id'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'review_status' => $data['review_status'] ?? 'draft',
            'published_at' => ($data['status'] ?? '') === 'published' ? date('Y-m-d H:i:s') : null,
            'meta_title' => $data['meta_title'] ?? $data['title'],
            'meta_description' => $data['meta_description'] ?? null,
            'og_image' => $data['og_image'] ?? $data['featured_image'] ?? null,
            'featured' => (int) ($data['featured'] ?? 0),
            'allow_comments' => (int) ($data['allow_comments'] ?? 1),
            'reading_time' => $data['reading_time'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Set tags
        if (!empty($tagIds)) {
            $this->postRepo->setPostTags($postId, $tagIds);
        }

        // Create initial revision
        $this->revisionRepo->createRevision(
            $postId,
            $data['title'],
            $data['content'] ?? '',
            $data['excerpt'] ?? null,
            $data['status'] ?? 'draft',
            $authorId,
            'Initial creation'
        );

        return $postId;
    }

    /**
     * Update a post with tags and new revision.
     *
     * @param int $postId
     * @param array<string, mixed> $data
     * @param list<int> $tagIds
     * @param int $authorId
     * @param string|null $changeSummary
     * @return int
     */
    public function updatePost(
        int $postId,
        array $data,
        array $tagIds = [],
        int $authorId = 0,
        ?string $changeSummary = null
    ): int {
        // Get current post for revision snapshot
        $current = $this->postRepo->findById($postId);
        if ($current === false) {
            throw new \RuntimeException("Post not found: {$postId}");
        }

        // Calculate reading time
        if (isset($data['content'])) {
            $data['reading_time'] = $this->postRepo->calculateReadingTime($data['content']);
        }

        $result = $this->postRepo->update($postId, array_merge($data, [
            'updated_at' => date('Y-m-d H:i:s'),
        ]));

        // Update tags
        if (!empty($tagIds)) {
            $this->postRepo->setPostTags($postId, $tagIds);
        }

        // Create revision
        $this->revisionRepo->createRevision(
            $postId,
            $data['title'] ?? $current['title'],
            $data['content'] ?? $current['content'],
            $data['excerpt'] ?? $current['excerpt'],
            $data['status'] ?? $current['status'],
            $authorId,
            $changeSummary ?? 'Updated'
        );

        return $result;
    }

    /**
     * Submit a post for review.
     *
     * @param int $postId
     * @param int $authorId
     * @return int
     */
    public function submitForReview(int $postId, int $authorId): int
    {
        return $this->postRepo->update($postId, [
            'review_status' => 'in_review',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Approve a post for publishing.
     *
     * @param int $postId
     * @param int $reviewerId
     * @return int
     */
    public function approvePost(int $postId, int $reviewerId): int
    {
        return $this->postRepo->updateReviewStatus($postId, 'approved', $reviewerId);
    }

    /**
     * Reject a post back to draft.
     *
     * @param int $postId
     * @param int $reviewerId
     * @return int
     */
    public function rejectPost(int $postId, int $reviewerId): int
    {
        return $this->postRepo->updateReviewStatus($postId, 'draft', $reviewerId);
    }

    /**
     * Publish a post directly.
     *
     * @param int $postId
     * @return int
     */
    public function publishPost(int $postId): int
    {
        return $this->postRepo->update($postId, [
            'status' => 'published',
            'review_status' => 'approved',
            'published_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Archive a post.
     *
     * @param int $postId
     * @return int
     */
    public function archivePost(int $postId): int
    {
        return $this->postRepo->update($postId, [
            'status' => 'archived',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Toggle featured status.
     *
     * @param int $postId
     * @return bool New featured status
     */
    public function toggleFeatured(int $postId): bool
    {
        $post = $this->postRepo->findById($postId);
        if ($post === false) {
            throw new \RuntimeException("Post not found: {$postId}");
        }

        $newFeatured = (int) !$post['featured'];
        $this->postRepo->update($postId, [
            'featured' => $newFeatured,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return (bool) $newFeatured;
    }

    /**
     * Get paginated posts with filters.
     *
     * @param int $page
     * @param int $perPage
     * @param array<string, mixed> $filters
     * @return array{data: list<array>, total: int, page: int, pages: int}
     */
    public function getPosts(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;
        $result = $this->postRepo->findAllWithFilters($filters, $perPage, $offset);
        $pages = (int) ceil($result['total'] / $perPage);

        return [
            'data' => $result['data'],
            'total' => $result['total'],
            'page' => $page,
            'pages' => max(1, $pages),
        ];
    }

    /**
     * Get a single post with tags.
     *
     * @param int $id
     * @return array<string, mixed>|false
     */
    public function getPostWithTags(int $id): array|false
    {
        $post = $this->postRepo->findByIdWithRelations($id);
        if ($post === false) {
            return false;
        }

        $post['tags'] = $this->postRepo->getPostTags($id);
        return $post;
    }

    /**
     * Get published posts for public view with tags.
     *
     * @param int $page
     * @param int $perPage
     * @return array{data: list<array>, total: int, page: int, pages: int}
     */
    public function getPublishedPosts(int $page = 1, int $perPage = 12): array
    {
        $offset = ($page - 1) * $perPage;
        $posts = $this->postRepo->findPublished($perPage, $offset);

        // Add tags to each post
        foreach ($posts as &$post) {
            $post['tags'] = $this->postRepo->getPostTags($post['id']);
        }

        $total = $this->postRepo->countByStatus('published');
        $pages = (int) ceil($total / $perPage);

        return [
            'data' => $posts,
            'total' => $total,
            'page' => $page,
            'pages' => max(1, $pages),
        ];
    }

    /**
     * Track a post view.
     *
     * @param int $postId
     * @param string|null $ip
     * @param string|null $userAgent
     * @return void
     */
    public function trackView(int $postId, ?string $ip = null, ?string $userAgent = null): void
    {
        $this->postRepo->incrementViewCount($postId);

        // Log view for analytics
        $this->db->insert('cms_post_views', [
            'post_id' => $postId,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Bulk actions on posts.
     *
     * @param string $action publish|archive|delete
     * @param list<int> $postIds
     * @return int Affected count
     */
    public function bulkAction(string $action, array $postIds): int
    {
        return match ($action) {
            'publish' => $this->postRepo->bulkUpdateStatus($postIds, 'published'),
            'archive' => $this->postRepo->bulkUpdateStatus($postIds, 'archived'),
            'delete' => $this->postRepo->bulkDelete($postIds),
            default => throw new \InvalidArgumentException("Unknown action: {$action}"),
        };
    }

    /**
     * Get all tags with post counts.
     *
     * @return list<array<string, mixed>>
     */
    public function getTags(): array
    {
        return $this->tagRepo->findAllWithCounts();
    }

    /**
     * Create a new tag.
     *
     * @param string $name
     * @param string|null $description
     * @param string $color
     * @return int
     */
    public function createTag(string $name, ?string $description = null, string $color = '#3b82f6'): int
    {
        return $this->tagRepo->createWithSlug($name, $description, $color);
    }

    /**
     * Delete a tag.
     *
     * @param int $id
     * @return int
     */
    public function deleteTag(int $id): int
    {
        return $this->tagRepo->deleteWithAssociations($id);
    }

    /**
     * Get revisions for a post.
     *
     * @param int $postId
     * @return list<array<string, mixed>>
     */
    public function getRevisions(int $postId): array
    {
        return $this->revisionRepo->findByPostId($postId);
    }

    /**
     * Restore a post to a previous revision.
     *
     * @param int $revisionId
     * @return array<string, mixed>|false
     */
    public function restoreRevision(int $revisionId): array|false
    {
        return $this->revisionRepo->restore($revisionId);
    }

    /**
     * Get pending comments for moderation.
     *
     * @param int $page
     * @param int $perPage
     * @return array{data: list<array>, total: int, page: int, pages: int}
     */
    public function getPendingComments(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $result = $this->commentRepo->findPending($perPage, $offset);
        $pages = (int) ceil($result['total'] / $perPage);

        return [
            'data' => $result['data'],
            'total' => $result['total'],
            'page' => $page,
            'pages' => max(1, $pages),
        ];
    }

    /**
     * Approve a comment.
     *
     * @param int $commentId
     * @param int $approvedBy
     * @return int
     */
    public function approveComment(int $commentId, int $approvedBy): int
    {
        return $this->commentRepo->approve($commentId, $approvedBy);
    }

    /**
     * Reject (delete) a comment.
     *
     * @param int $commentId
     * @return int
     */
    public function rejectComment(int $commentId): int
    {
        return $this->commentRepo->reject($commentId);
    }

    /**
     * Create a new comment on a post.
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
        return $this->commentRepo->createComment(
            $postId,
            $content,
            $authorName,
            $authorEmail,
            $userId,
            $authorIp,
            $parentId
        );
    }

    /**
     * Get approved comments for a post.
     *
     * @param int $postId
     * @param int $limit
     * @return list<array<string, mixed>>
     */
    public function getPostComments(int $postId, int $limit = 20): array
    {
        return $this->commentRepo->findApprovedByPostId($postId, $limit);
    }

    /**
     * Get featured published posts.
     *
     * @param int $limit
     * @return list<array<string, mixed>>
     */
    public function getFeaturedPosts(int $limit = 3): array
    {
        return $this->postRepo->findFeatured($limit);
    }

    /**
     * Find a post by slug for public viewing.
     *
     * @param string $slug
     * @return array<string, mixed>|false
     */
    public function getPostBySlug(string $slug): array|false
    {
        $post = $this->postRepo->findBySlug($slug);
        if ($post === false) {
            return false;
        }
        $post['tags'] = $this->postRepo->getPostTags((int) $post['id']);
        return $post;
    }

    /**
     * Get related posts by shared tags or category.
     *
     * @param int $currentPostId
     * @param int $limit
     * @return list<array<string, mixed>>
     */
    public function getRelatedPosts(int $currentPostId, int $limit = 3): array
    {
        // Get current post's tags
        $currentTags = $this->postRepo->getPostTags($currentPostId);
        $tagIds = array_column($currentTags, 'id');

        if (!empty($tagIds)) {
            // Find posts with shared tags
            $placeholders = implode(',', array_fill(0, count($tagIds), '?'));
            return $this->db->fetchAll(
                "SELECT DISTINCT p.id, p.title, p.slug, p.excerpt, p.published_at, p.featured_image
                 FROM cms_posts p
                 INNER JOIN cms_post_tags pt ON p.id = pt.post_id
                 WHERE pt.tag_id IN ({$placeholders})
                   AND p.id != ?
                   AND p.status = 'published'
                 ORDER BY p.published_at DESC
                 LIMIT ?",
                array_merge($tagIds, [$currentPostId, $limit])
            );
        }

        // Fallback: posts in same category
        $currentPost = $this->postRepo->findById($currentPostId);
        if ($currentPost !== false && !empty($currentPost['category_id'])) {
            return $this->db->fetchAll(
                "SELECT id, title, slug, excerpt, published_at, featured_image
                 FROM cms_posts
                 WHERE category_id = ? AND id != ? AND status = 'published'
                 ORDER BY published_at DESC
                 LIMIT ?",
                [$currentPost['category_id'], $currentPostId, $limit]
            );
        }

        // Final fallback: recent published posts
        return $this->db->fetchAll(
            "SELECT id, title, slug, excerpt, published_at, featured_image
             FROM cms_posts
             WHERE id != ? AND status = 'published'
             ORDER BY published_at DESC
             LIMIT ?",
            [$currentPostId, $limit]
        );
    }

    /**
     * Get CMS dashboard statistics.
     *
     * @return array<string, mixed>
     */
    public function getDashboardStats(): array
    {
        return [
            'total_posts' => (int) $this->db->fetch('SELECT COUNT(*) as c FROM cms_posts')['c'],
            'published_posts' => $this->postRepo->countByStatus('published'),
            'draft_posts' => $this->postRepo->countByStatus('draft'),
            'pending_reviews' => (int) $this->db->fetch("SELECT COUNT(*) as c FROM cms_posts WHERE review_status = 'in_review'")['c'],
            'total_tags' => (int) $this->db->fetch('SELECT COUNT(*) as c FROM cms_tags')['c'],
            'pending_comments' => $this->commentRepo->countByStatus('pending'),
            'approved_comments' => $this->commentRepo->countByStatus('approved'),
            'total_views' => (int) $this->db->fetch('SELECT SUM(view_count) as c FROM cms_posts')['c'],
        ];
    }

    /**
     * Generate a URL-friendly slug.
     *
     * @param string $title
     * @return string
     */
    private function generateUniqueSlug(string $title): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $title), '-'));
        $baseSlug = $slug;
        $counter = 1;

        while ($this->postRepo->findBySlug($slug) !== false) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }
}
