<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Core\Session;
use App\Core\Database;
use App\Helpers\{Validation, Security};

class CmsController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index(): void
    {
        $posts = $this->db->fetchAll(
            "SELECT p.*, u.name as author_name 
             FROM cms_posts p 
             JOIN users u ON p.author_id = u.id 
             ORDER BY p.created_at DESC"
        );

        View::display('cms.index', ['posts' => $posts]);
    }

    public function create(): void
    {
        $categories = $this->db->fetchAll("SELECT * FROM cms_categories ORDER BY name");
        View::display('cms.create', ['categories' => $categories]);
    }

    public function store(): void
    {
        Session::start();

        $validator = new Validation();
        if (!$validator->validate($_POST, [
            'title' => 'required|min:3|max:255',
            'content' => 'required',
            'status' => 'required|in:draft,published'
        ])) {
            Session::flash('errors', $validator->errors());
            header('Location: /cms/create');
            exit;
        }

        $slug = $this->generateSlug($_POST['title']);
        $featuredImage = $this->handleUpload($_FILES['featured_image'] ?? null);

        $this->db->insert('cms_posts', [
            'title' => $_POST['title'],
            'slug' => $slug,
            'content' => $_POST['content'],
            'excerpt' => $_POST['excerpt'] ?? null,
            'featured_image' => $featuredImage,
            'author_id' => Session::get('user_id'),
            'category_id' => $_POST['category_id'] ?: null,
            'status' => $_POST['status'],
            'published_at' => $_POST['status'] === 'published' ? date('Y-m-d H:i:s') : null
        ]);

        header('Location: /cms');
        exit;
    }

    public function edit(string $id): void
    {
        $post = $this->db->fetch("SELECT * FROM cms_posts WHERE id = ?", [$id]);
        
        if (!$post) {
            http_response_code(404);
            echo "Post not found";
            return;
        }

        $categories = $this->db->fetchAll("SELECT * FROM cms_categories ORDER BY name");
        View::display('cms.edit', ['post' => $post, 'categories' => $categories]);
    }

    public function update(string $id): void
    {
        $validator = new Validation();
        if (!$validator->validate($_POST, [
            'title' => 'required|min:3|max:255',
            'content' => 'required'
        ])) {
            Session::flash('errors', $validator->errors());
            header("Location: /cms/{$id}/edit");
            exit;
        }

        $data = [
            'title' => $_POST['title'],
            'slug' => $this->generateSlug($_POST['title']),
            'content' => $_POST['content'],
            'excerpt' => $_POST['excerpt'] ?? null,
            'status' => $_POST['status'],
            'published_at' => $_POST['status'] === 'published' ? date('Y-m-d H:i:s') : null
        ];

        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            $data['featured_image'] = $this->handleUpload($_FILES['featured_image']);
        }

        $this->db->update('cms_posts', $data, 'id = ?', [$id]);

        header('Location: /cms');
        exit;
    }

    public function delete(string $id): void
    {
        $this->db->delete('cms_posts', 'id = ?', [$id]);
        header('Location: /cms');
        exit;
    }

    private function generateSlug(string $title): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $baseSlug = $slug;
        $counter = 1;

        while ($this->db->fetch("SELECT id FROM cms_posts WHERE slug = ?", [$slug])) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }

    private function handleUpload(?array $file): ?string
    {
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedMimes)) {
            return null;
        }

        $maxSize = (int)($_ENV['MAX_UPLOAD_SIZE'] ?? 10485760);
        if ($file['size'] > $maxSize) {
            return null;
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $uploadPath = $_ENV['UPLOAD_PATH'] ?? 'public/uploads';
        $destination = $uploadPath . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return null;
        }

        $this->db->insert('cms_media', [
            'filename' => $filename,
            'original_name' => $file['name'],
            'mime_type' => $mimeType,
            'size' => $file['size'],
            'path' => $destination,
            'uploaded_by' => Session::get('user_id')
        ]);

        return $destination;
    }
}
