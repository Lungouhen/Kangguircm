<?php
$pageTitle = 'CMS - Posts';
ob_start();
?>

<div class="mb-6 flex justify-between items-center">
    <p class="text-gray-600">Manage your content posts</p>
    <a href="/cms/create" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
        Create Post
    </a>
</div>

<div class="overflow-hidden bg-white shadow sm:rounded-md">
    <ul role="list" class="divide-y divide-gray-200">
        <?php if (empty($posts)): ?>
            <li class="px-6 py-12 text-center text-gray-500">No posts found. Create your first post!</li>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <li>
                    <a href="/cms/<?= (int)$post['id'] ?>/edit" class="block hover:bg-gray-50">
                        <div class="px-4 py-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <p class="truncate text-sm font-medium text-indigo-600"><?= htmlspecialchars($post['title']) ?></p>
                                    <span class="ml-3 inline-flex items-center rounded-md px-2 py-1 text-xs font-medium
                                        <?= match($post['status']) {
                                            'published' => 'bg-green-100 text-green-700',
                                            'draft' => 'bg-yellow-100 text-yellow-700',
                                            'archived' => 'bg-gray-100 text-gray-700',
                                        } ?>">
                                        <?= htmlspecialchars($post['status']) ?>
                                    </span>
                                </div>
                                <div class="ml-2 flex flex-shrink-0">
                                    <p class="text-sm text-gray-500">By <?= htmlspecialchars($post['author_name']) ?></p>
                                </div>
                            </div>
                            <div class="mt-2 sm:flex sm:justify-between">
                                <div class="sm:flex">
                                    <p class="flex items-center text-sm text-gray-500">
                                        /<?= htmlspecialchars($post['slug']) ?>
                                    </p>
                                </div>
                                <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                    <p><?= date('M d, Y', strtotime($post['created_at'])) ?></p>
                                </div>
                            </div>
                        </div>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
