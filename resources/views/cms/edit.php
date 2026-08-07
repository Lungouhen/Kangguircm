<?php
$pageTitle = 'Edit Post - CMS';
ob_start();
?>

<div class="mx-auto max-w-3xl">
    <form action="/cms/<?= (int)$post['id'] ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
        <?= \App\Core\Csrf::field() ?>
        <input type="hidden" name="_method" value="PUT">

        <div class="bg-white shadow sm:rounded-lg p-6">
            <div class="space-y-4">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" name="title" id="title" required value="<?= htmlspecialchars($post['title']) ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                </div>

                <div>
                    <label for="excerpt" class="block text-sm font-medium text-gray-700">Excerpt</label>
                    <textarea name="excerpt" id="excerpt" rows="2"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border"><?= htmlspecialchars($post['excerpt'] ?? '') ?></textarea>
                </div>

                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700">Content</label>
                    <textarea name="content" id="content" rows="12" required
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border"><?= htmlspecialchars($post['content']) ?></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                        <select name="category_id" id="category_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                            <option value="">No Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= (int)$cat['id'] ?>" <?= $cat['id'] == $post['category_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" id="status"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                            <option value="draft" <?= $post['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="published" <?= $post['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                            <option value="archived" <?= $post['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="featured_image" class="block text-sm font-medium text-gray-700">Featured Image</label>
                    <?php if ($post['featured_image']): ?>
                        <p class="text-sm text-gray-500 mb-2">Current: <?= htmlspecialchars($post['featured_image']) ?></p>
                    <?php endif; ?>
                    <input type="file" name="featured_image" id="featured_image" accept="image/*"
                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                </div>
            </div>
        </div>

        <div class="flex justify-between">
            <form action="/cms/<?= (int)$post['id'] ?>" method="POST" onsubmit="return confirm('Are you sure?')">
                <?= \App\Core\Csrf::field() ?>
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500">Delete Post</button>
            </form>

            <div class="flex space-x-3">
                <a href="/cms" class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Update Post</button>
            </div>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
