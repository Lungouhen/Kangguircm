<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= \App\Core\Csrf::token() ?>">
    <title><?= esc($pageTitle ?? 'Multi-Module Platform') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body class="h-full">
    <div class="min-h-full">
        <nav class="bg-gray-800">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <span class="text-white font-bold text-xl">⚡ Platform</span>
                        </div>
                        <div class="ml-10 flex items-baseline space-x-4">
                            <a href="/dashboard" class="text-gray-300 hover:bg-gray-700 hover:text-white rounded-md px-3 py-2 text-sm font-medium">Dashboard</a>
                            <a href="/cms" class="text-gray-300 hover:bg-gray-700 hover:text-white rounded-md px-3 py-2 text-sm font-medium">CMS</a>
                            <a href="/email/subscribers" class="text-gray-300 hover:bg-gray-700 hover:text-white rounded-md px-3 py-2 text-sm font-medium">Email</a>
                            <a href="/hrm/employees" class="text-gray-300 hover:bg-gray-700 hover:text-white rounded-md px-3 py-2 text-sm font-medium">HRM</a>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <span class="text-gray-300 text-sm mr-4">
                            <?= htmlspecialchars(\App\Core\Session::get('user_name', 'Guest')) ?>
                        </span>
                        <a href="/logout" class="text-gray-300 hover:bg-gray-700 hover:text-white rounded-md px-3 py-2 text-sm font-medium">Logout</a>
                    </div>
                </div>
            </div>
        </nav>

        <header class="bg-white shadow">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900">
                    <?= esc($pageTitle ?? 'Dashboard') ?>
                </h1>
            </div>
        </header>

        <main>
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <?php if ($flash = \App\Core\Session::getFlash('success')): ?>
                    <div class="mb-4 rounded-md bg-green-50 p-4">
                        <p class="text-sm text-green-700"><?= esc($flash) ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($error = \App\Core\Session::getFlash('error')): ?>
                    <div class="mb-4 rounded-md bg-red-50 p-4">
                        <p class="text-sm text-red-700"><?= esc($error) ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($errors = \App\Core\Session::getFlash('errors')): ?>
                    <div class="mb-4 rounded-md bg-red-50 p-4">
                        <ul class="list-disc list-inside text-sm text-red-700">
                            <?php foreach ($errors as $fieldErrors): ?>
                                <?php foreach ((array)$fieldErrors as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?= $content ?? '' ?>
            </div>
        </main>
    </div>
    <script src="/js/app.js"></script>
</body>
</html>
