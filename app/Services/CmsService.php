<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PostStatus;
use App\Repositories\PostRepository;

/**
 * CMS content management service.
 *
 * Handles post creation, updates, slug generation, and content queries.
 * Encapsulates business logic separate from HTTP and persistence concerns.
 */
class CmsService
{
    /**
     * @param PostRepository $postRepository Post data access
     */
    public function __construct(
        private readonly PostRepository $postRepository = new PostRepository(),
    ) {}

    /**
     * Create a new post with auto-generated slug.
     *
     * @param string $title Post title
     * @param string $content Full post content (HTML)
     * @param int $authorId ID of the author
     * @param PostStatus $status Publication status
     * @param string|null $excerpt Short excerpt
     * @param int|null $categoryId Category ID
     * @param string|null $featuredImagePath Path to uploaded featured image
     * @return int New post ID
     */
    public function createPost(
        string $title,
        string $content,
        int $authorId,
        PostStatus $status = PostStatus::Draft,
        ?string $excerpt = null,
        ?int $categoryId = null,
        ?string $featuredImagePath = null,
    ): int {
        $data = [
            'title' => $title,
            'slug' => $this->generateUniqueSlug($title),
            'content' => $content,
            'excerpt' => $excerpt,
            'featured_image' => $featuredImagePath,
            'author_id' => $authorId,
            'category_id' => $categoryId,
            'status' => $status->value,
            'published_at' => $status === PostStatus::Published ? date('Y-m-d H:i:s') : null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        return $this->postRepository->create($data);
    }

    /**
     * Update an existing post.
     *
     * @param int $id Post ID
     * @param array<string, mixed> $data Fields to update
     * @return int Number of affected rows
     */
    public function updatePost(int $id, array $data): int
    {
        if (isset($data['title'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title'], $id);
        }

        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->postRepository->update($id, $data);
    }

    /**
     * Generate a URL-friendly slug, ensuring uniqueness.
     *
     * @param string $title Source title
     * @param int|null $excludeId Exclude this post ID from uniqueness check
     * @return string URL-safe slug
     */
    public function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $slug = strtolower(trim((string) preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $baseSlug = $slug;
        $counter = 1;

        while ($this->postRepository->slugExists($slug, $excludeId)) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }

    /**
     * Get paginated posts with author names.
     *
     * @param int $limit
     * @param int $offset
     * @return list<array<string, mixed>>
     */
    public function getPostsWithAuthors(int $limit = 50, int $offset = 0): array
    {
        return $this->postRepository->findAllWithAuthor($limit, $offset);
    }

    /**
     * Get a post with its author information.
     *
     * @param int $id
     * @return array<string, mixed>|false
     */
    public function getPostWithAuthor(int $id): array|false
    {
        return $this->postRepository->findByIdWithAuthor($id);
    }

    /**
     * Get total post count by status.
     *
     * @param PostStatus $status
     */
    public function countByStatus(PostStatus $status): int
    {
        return $this->postRepository->countByStatus($status);
    }
}
