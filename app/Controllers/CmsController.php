<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Services\CmsService;
use App\Helpers\Validation;
use App\Helpers\SeoHelper;

/**
 * CMS Controller — upgraded with full feature set.
 *
 * Following php-pro skill: Thin controllers, service delegation
 * Following backend-developer skill: RESTful patterns, validation
 * Following fullstack-developer skill: End-to-end data flow
 */
class CmsController
{
    private readonly CmsService $cms;

    public function __construct()
    {
        $this->cms = new CmsService();
    }

    /**
     * Admin post listing with filters and search.
     */
    public function index(): void
    {
        Session::start();

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $filters = [
            'status' => $_GET['status'] ?? null,
            'review_status' => $_GET['review_status'] ?? null,
            'category_id' => !empty($_GET['category_id']) ? (int) $_GET['category_id'] : null,
            'search' => $_GET['search'] ?? null,
        ];
        $filters = array_filter($filters, fn($v) => $v !== null && $v !== '');

        $result = $this->cms->getPosts($page, 20, $filters);

        View::display('cms.index', [
            'pageTitle' => 'CMS - Posts',
            'posts' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'stats' => $this->cms->getDashboardStats(),
        ]);
    }

    /**
     * Show create post form.
     */
    public function create(): void
    {
        View::display('cms.create', [
            'pageTitle' => 'Create Post',
            'categories' => $this->getCategories(),
            'tags' => $this->cms->getTags(),
            'post' => null,
            'selectedTags' => [],
        ]);
    }

    /**
     * Store a new post.
     */
    public function store(): void
    {
        Session::start();

        $validator = new Validation();
        if (!$validator->validate($_POST, [
            'title' => 'required|min:3|max:255',
            'content' => 'required',
            'status' => 'required|in:draft,published',
        ])) {
            Session::flash('errors', $validator->errors());
            Response::redirect('/cms/create');
            return;
        }

        $tagIds = array_map('intval', $_POST['tags'] ?? []);
        $userId = (int) Session::get('user_id');

        $postId = $this->cms->createPost([
            'title' => $_POST['title'],
            'content' => $_POST['content'],
            'excerpt' => $_POST['excerpt'] ?? null,
            'category_id' => !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null,
            'status' => $_POST['status'],
            'meta_title' => $_POST['meta_title'] ?? null,
            'meta_description' => $_POST['meta_description'] ?? null,
            'og_image' => $_POST['og_image'] ?? null,
            'featured' => (int) ($_POST['featured'] ?? 0),
            'allow_comments' => (int) ($_POST['allow_comments'] ?? 1),
        ], $tagIds, $userId);

        Session::flash('success', 'Post created successfully.');
        Response::redirect('/cms/' . $postId . '/edit');
    }

    /**
     * Show edit post form with revisions sidebar.
     */
    public function edit(string $id): void
    {
        $post = $this->cms->getPostWithTags((int) $id);
        if ($post === false) {
            http_response_code(404);
            echo 'Post not found';
            return;
        }

        $revisions = $this->cms->getRevisions((int) $id);
        $selectedTagIds = array_column($post['tags'] ?? [], 'id');

        View::display('cms.edit', [
            'pageTitle' => 'Edit: ' . $post['title'],
            'post' => $post,
            'categories' => $this->getCategories(),
            'tags' => $this->cms->getTags(),
            'selectedTags' => $selectedTagIds,
            'revisions' => $revisions,
        ]);
    }

    /**
     * Update an existing post.
     */
    public function update(string $id): void
    {
        Session::start();

        $validator = new Validation();
        if (!$validator->validate($_POST, [
            'title' => 'required|min:3|max:255',
            'content' => 'required',
        ])) {
            Session::flash('errors', $validator->errors());
            Response::redirect('/cms/' . $id . '/edit');
            return;
        }

        $tagIds = array_map('intval', $_POST['tags'] ?? []);
        $userId = (int) Session::get('user_id');

        $this->cms->updatePost(
            (int) $id,
            [
                'title' => $_POST['title'],
                'content' => $_POST['content'],
                'excerpt' => $_POST['excerpt'] ?? null,
                'category_id' => !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null,
                'status' => $_POST['status'] ?? 'draft',
                'meta_title' => $_POST['meta_title'] ?? null,
                'meta_description' => $_POST['meta_description'] ?? null,
                'og_image' => $_POST['og_image'] ?? null,
                'featured' => (int) ($_POST['featured'] ?? 0),
                'allow_comments' => (int) ($_POST['allow_comments'] ?? 1),
            ],
            $tagIds,
            $userId,
            $_POST['change_summary'] ?? 'Updated via admin panel'
        );

        Session::flash('success', 'Post updated successfully.');
        Response::redirect('/cms/' . $id . '/edit');
    }

    /**
     * Delete a post.
     */
    public function delete(string $id): void
    {
        Session::start();
        $this->cms->bulkAction('delete', [(int) $id]);
        Session::flash('success', 'Post deleted.');
        Response::redirect('/cms');
    }

    /**
     * Submit a post for review workflow.
     */
    public function submitForReview(string $id): void
    {
        Session::start();
        $this->cms->submitForReview((int) $id, (int) Session::get('user_id'));
        Session::flash('success', 'Post submitted for review.');
        Response::redirect('/cms/' . $id . '/edit');
    }

    /**
     * Approve a post (admin only).
     */
    public function approve(string $id): void
    {
        Session::start();
        $this->cms->approvePost((int) $id, (int) Session::get('user_id'));
        Session::flash('success', 'Post approved and published.');
        Response::redirect('/cms');
    }

    /**
     * Reject a post back to draft.
     */
    public function reject(string $id): void
    {
        Session::start();
        $this->cms->rejectPost((int) $id, (int) Session::get('user_id'));
        Session::flash('success', 'Post rejected.');
        Response::redirect('/cms');
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeatured(string $id): void
    {
        Session::start();
        $featured = $this->cms->toggleFeatured((int) $id);
        Session::flash('success', $featured ? 'Post marked as featured.' : 'Post unfeatured.');
        Response::redirect('/cms');
    }

    /**
     * Restore a post to a previous revision.
     */
    public function restoreRevision(string $revisionId): void
    {
        Session::start();
        $result = $this->cms->restoreRevision((int) $revisionId);
        if ($result !== false) {
            Session::flash('success', 'Post restored to revision #' . $revisionId);
            Response::redirect('/cms/' . $result['post_id'] . '/edit');
        } else {
            Session::flash('error', 'Revision not found.');
            Response::redirect('/cms');
        }
    }

    /**
     * Handle bulk actions.
     */
    public function bulkAction(): void
    {
        Session::start();

        $action = $_POST['action'] ?? '';
        $postIds = array_map('intval', $_POST['post_ids'] ?? []);

        if (empty($postIds) || !in_array($action, ['publish', 'archive', 'delete'])) {
            Session::flash('error', 'Invalid bulk action.');
            Response::redirect('/cms');
            return;
        }

        $count = $this->cms->bulkAction($action, $postIds);
        Session::flash('success', ucfirst($action) . 'd ' . $count . ' post(s).');
        Response::redirect('/cms');
    }

    /**
     * Admin tags management page.
     */
    public function tags(): void
    {
        View::display('cms.tags', [
            'pageTitle' => 'CMS - Tags',
            'tags' => $this->cms->getTags(),
        ]);
    }

    /**
     * Create a new tag.
     */
    public function createTag(): void
    {
        Session::start();

        $validator = new Validation();
        if (!$validator->validate($_POST, ['name' => 'required|min:1|max:50'])) {
            Session::flash('error', 'Tag name is required.');
            Response::redirect('/cms/tags');
            return;
        }

        $this->cms->createTag(
            $_POST['name'],
            $_POST['description'] ?? null,
            $_POST['color'] ?? '#3b82f6'
        );

        Session::flash('success', 'Tag created.');
        Response::redirect('/cms/tags');
    }

    /**
     * Delete a tag.
     */
    public function deleteTag(string $id): void
    {
        Session::start();
        $this->cms->deleteTag((int) $id);
        Session::flash('success', 'Tag deleted.');
        Response::redirect('/cms/tags');
    }

    /**
     * Admin comments moderation page.
     */
    public function comments(): void
    {
        Session::start();

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $result = $this->cms->getPendingComments($page, 20);

        View::display('cms.comments', [
            'pageTitle' => 'CMS - Comment Moderation',
            'comments' => $result['data'],
            'pagination' => $result,
            'pendingCount' => $result['total'],
        ]);
    }

    /**
     * Approve a comment.
     */
    public function approveComment(string $id): void
    {
        Session::start();
        $this->cms->approveComment((int) $id, (int) Session::get('user_id'));
        Session::flash('success', 'Comment approved.');
        Response::redirect('/cms/comments');
    }

    /**
     * Reject a comment.
     */
    public function rejectComment(string $id): void
    {
        Session::start();
        $this->cms->rejectComment((int) $id);
        Session::flash('success', 'Comment rejected.');
        Response::redirect('/cms/comments');
    }

    /**
     * Public blog listing page with SEO.
     */
    public function blog(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $result = $this->cms->getPublishedPosts($page, 12);

        // Track page view via cache service
        $cache = new \App\Services\CacheService();
        $cache->set('blog_page_' . $page, $result, 300);

        View::display('cms.blog', [
            'pageTitle' => 'Blog',
            'posts' => $result['data'],
            'pagination' => $result,
            'featuredPosts' => $this->cms->getFeaturedPosts(),
            'seo' => [
                'title' => 'Blog - Latest Articles',
                'description' => 'Read our latest articles on technology, tutorials, and announcements.',
                'url' => ($_ENV['APP_URL'] ?? 'http://localhost:3000') . '/blog',
                'type' => 'website',
            ],
        ]);
    }

    /**
     * Public single post view with SEO, comments, related posts.
     */
    public function showPost(string $slug): void
    {
        $post = $this->cms->getPostBySlug($slug);
        if ($post === false || $post['status'] !== 'published') {
            http_response_code(404);
            echo 'Post not found';
            return;
        }

        // Track view
        $this->cms->trackView(
            (int) $post['id'],
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        );

        // Get comments and related posts
        $comments = $this->cms->getPostComments((int) $post['id'], 10);
        $relatedPosts = $this->cms->getRelatedPosts((int) $post['id'], 3);

        // Build SEO data
        $appUrl = $_ENV['APP_URL'] ?? 'http://localhost:3000';
        $seo = [
            'title' => $post['meta_title'] ?? $post['title'],
            'description' => $post['meta_description'] ?? SeoHelper::truncateForMeta(strip_tags($post['excerpt'] ?? $post['content'])),
            'url' => $appUrl . '/blog/' . $post['slug'],
            'image' => $post['og_image'] ?? $post['featured_image'] ?? '',
            'type' => 'article',
            'author' => $post['author_name'] ?? '',
            'published_at' => $post['published_at'] ?? '',
        ];

        View::display('cms.show', [
            'pageTitle' => $seo['title'],
            'post' => $post,
            'comments' => $comments,
            'relatedPosts' => $relatedPosts,
            'seo' => $seo,
        ]);
    }

    /**
     * Submit a comment on a post (public).
     */
    public function submitComment(string $postId): void
    {
        Session::start();

        $validator = new Validation();
        if (!$validator->validate($_POST, [
            'content' => 'required|min:3|max:2000',
            'author_name' => 'required|min:2|max:100',
            'author_email' => 'required|email',
        ])) {
            Session::flash('errors', $validator->errors());
            Response::redirect('/blog/' . ($_POST['slug'] ?? ''));
            return;
        }

        $userId = Session::has('user_id') ? (int) Session::get('user_id') : null;

        $this->cms->createComment(
            (int) $postId,
            $_POST['content'],
            $_POST['author_name'],
            $_POST['author_email'],
            $userId,
            $_SERVER['REMOTE_ADDR'] ?? null
        );

        Session::flash('success', 'Comment submitted for moderation.');
        Response::redirect('/blog/' . ($_POST['slug'] ?? '') . '#comments');
    }

    /**
     * Get categories helper.
     *
     * @return list<array<string, mixed>>
     */
    private function getCategories(): array
    {
        $db = \App\Core\Database::getInstance();
        return $db->fetchAll("SELECT id, name, slug FROM cms_categories ORDER BY name");
    }
}
