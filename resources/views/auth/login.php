<?php
$pageTitle = 'Login - Multi-Module Platform';
\App\Helpers\Security::setCspHeaders();
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full">
    <div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <h2 class="mt-10 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">
                Sign in to your account
            </h2>
        </div>

        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
            <?php if ($error = \App\Core\Session::getFlash('error')): ?>
                <div class="mb-4 rounded-md bg-red-50 p-4">
                    <p class="text-sm text-red-700"><?= esc($error) ?></p>
                </div>
            <?php endif; ?>

            <?php if ($success = \App\Core\Session::getFlash('success')): ?>
                <div class="mb-4 rounded-md bg-green-50 p-4">
                    <p class="text-sm text-green-700"><?= esc($success) ?></p>
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

            <form class="space-y-6" action="/login" method="POST">
                <?= \App\Core\Csrf::field() ?>

                <div>
                    <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email address</label>
                    <div class="mt-2">
                        <input id="email" name="email" type="email" autocomplete="email" required 
                               class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 px-3">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium leading-6 text-gray-900">Password</label>
                    <div class="mt-2">
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                               class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 px-3">
                    </div>
                </div>

                <div>
                    <button type="submit" 
                            class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        Sign in
                    </button>
                </div>
            </form>

            <p class="mt-10 text-center text-sm text-gray-500">
                Don't have an account?
                <a href="/register" class="font-semibold leading-6 text-indigo-600 hover:text-indigo-500">Register here</a>
            </p>
        </div>
    </div>
</body>
</html>
