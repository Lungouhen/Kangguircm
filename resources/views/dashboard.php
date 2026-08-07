<?php
$pageTitle = 'Dashboard';
ob_start();
?>

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
    <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
        <dt class="truncate text-sm font-medium text-gray-500">Total Users</dt>
        <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900"><?= (int)($stats['total_users'] ?? 0) ?></dd>
    </div>
    <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
        <dt class="truncate text-sm font-medium text-gray-500">CMS Posts</dt>
        <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900"><?= (int)($stats['total_posts'] ?? 0) ?></dd>
    </div>
    <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
        <dt class="truncate text-sm font-medium text-gray-500">Active Subscribers</dt>
        <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900"><?= (int)($stats['total_subscribers'] ?? 0) ?></dd>
    </div>
    <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
        <dt class="truncate text-sm font-medium text-gray-500">Active Employees</dt>
        <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900"><?= (int)($stats['total_employees'] ?? 0) ?></dd>
    </div>
</div>

<div class="mt-8 grid grid-cols-1 gap-5 lg:grid-cols-3">
    <div class="rounded-lg bg-white shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
        <div class="space-y-3">
            <a href="/cms/create" class="block w-full text-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Create New Post</a>
            <a href="/email/subscribers" class="block w-full text-center rounded-md bg-purple-600 px-3 py-2 text-sm font-semibold text-white hover:bg-purple-500">Manage Subscribers</a>
            <a href="/hrm/attendance" class="block w-full text-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white hover:bg-green-500">View Attendance</a>
        </div>
    </div>
    <div class="rounded-lg bg-white shadow p-6 lg:col-span-2">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Welcome, <?= htmlspecialchars($user_name ?? 'User') ?>!</h3>
        <p class="text-gray-600">Your multi-module platform is ready. Use the navigation above to access CMS, Email Marketing, and HRM modules.</p>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layouts/app.php';
?>
