<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Actions\CreatePostAction;
use App\Actions\HandleMediaUploadAction;
use App\Core\Session;
use App\Core\View;
use App\Enums\PostStatus;
use App\Helpers\Validation;
use App\Services\CmsService;

/**
 * CMS controller for content management operations.
 *
 * Delegates to CmsService for business logic and Action classes
 * for complex multi-step workflows. Uses enums for type safety.
 */
class CmsController
{
    /**
     * @param CmsService $cmsService CMS business logic
     * @param CreatePostAction $createPostAction Post creation workflow
     * @param HandleMediaUploadAction $uploadAction Media upload workflow
     */
    public function __construct(
        private readonly CmsService $cmsService = new CmsService(),
        private readonly CreatePostAction $createPostAction = new CreatePostAction(),
        private readonly HandleMediaUploadAction $uploadAction = new HandleMediaUploadAction(),
    ) {}

    /**
     * Display all posts with author information.
     */
    public function index(): void
    {
        $posts = $this->cmsService->getPostsWithAuthors();
        View::display('cms.index', ['posts' => $posts]);
    }

    /**
     * Display the post creation form.
     */
    public function create(): void
    {
        $db = \App\Core\Database::getInstance();
        $categories = $db->fetchAll(
            "SELECT id, name, slug FROM cms_categories ORDER BY name"
        );
        View::display('cms.create', ['categories' => $categories]);
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
            exit;
        }

        $status = PostStatus::from($_POST['status']);
        $featuredImage = $this->uploadAction->execute($_FILES['featured_image'] ?? null);

        $this->createPostAction->execute(
            title: $_POST['title'],
            content: $_POST['content'],
            authorId: (int) Session::get('user_id'),
            status: $status,
            excerpt: $_POST['excerpt'] ?? null,
            categoryId: !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null,
            featuredImagePath: $featuredImage,
        );

        Response::redirect('/cms');
        exit;
    }

    /**
     * Display the post editing form.
     *
     * @param string $id Post ID
     */
    public function edit(string $id): void
    {
        $post = $this->cmsService->getPostWithAuthor((int) $id);

        if ($post === false) {
            http_response_code(404);
            echo "Post not found";
            return;
        }

        $db = \App\Core\Database::getInstance();
        $categories = $db->fetchAll(
            "SELECT id, name, slug FROM cms_categories ORDER BY name"
        );
        View::display('cms.edit', ['post' => $post, 'categories' => $categories]);
    }

    /**
     * Update an existing post.
     *
     * @param string $id Post ID
     */
    public function update(string $id): void
    {
        $validator = new Validation();
        if (!$validator->validate($_POST, [
            'title' => 'required|min:3|max:255',
            'content' => 'required',
        ])) {
            Session::flash('errors', $validator->errors());
            Response::redirect("/cms/{$id}/edit");
            exit;
        }

        $data = [
            'title' => $_POST['title'],
            'content' => $_POST['content'],
            'excerpt' => $_POST['excerpt'] ?? null,
            'status' => $_POST['status'],
            'published_at' => $_POST['status'] === 'published' ? date('Y-m-d H:i:s') : null,
        ];

        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            $data['featured_image'] = $this->uploadAction->execute($_FILES['featured_image']);
        }

        $this->cmsService->updatePost((int) $id, $data);

        Response::redirect('/cms');
        exit;
    }

    /**
     * Delete a post.
     *
     * @param string $id Post ID
     */
    public function delete(string $id): void
    {
        $db = \App\Core\Database::getInstance();
        $db->delete('cms_posts', 'id = ?', [(int) $id]);
        Response::redirect('/cms');
        exit;
    }
}
