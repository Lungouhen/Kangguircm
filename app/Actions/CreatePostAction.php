<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PostStatus;
use App\Services\CmsService;

/**
 * Action class for creating a CMS post.
 *
 * Encapsulates the complete post creation workflow:
 * validation, slug generation, and persistence.
 */
class CreatePostAction
{
    public function __construct(
        private readonly CmsService $cmsService = new CmsService(),
    ) {}

    /**
     * Execute the post creation action.
     *
     * @param string $title Post title
     * @param string $content Post content
     * @param int $authorId Author user ID
     * @param PostStatus $status Publication status
     * @param string|null $excerpt Optional excerpt
     * @param int|null $categoryId Optional category
     * @param string|null $featuredImagePath Optional image path
     * @return int New post ID
     */
    public function execute(
        string $title,
        string $content,
        int $authorId,
        PostStatus $status = PostStatus::Draft,
        ?string $excerpt = null,
        ?int $categoryId = null,
        ?string $featuredImagePath = null,
    ): int {
        return $this->cmsService->createPost(
            title: $title,
            content: $content,
            authorId: $authorId,
            status: $status,
            excerpt: $excerpt,
            categoryId: $categoryId,
            featuredImagePath: $featuredImagePath,
        );
    }
}
