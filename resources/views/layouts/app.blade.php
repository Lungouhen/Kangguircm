<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ \App\Core\Session::get('csrf_token', '') }}">
    <title>{{ $pageTitle ?? 'Multi-Module Platform' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                            @php $currentPath = $_SERVER['REQUEST_URI'] ?? ''; @endphp
                            <a href="/dashboard" class="@if(str_starts_with($currentPath, '/dashboard')) bg-gray-900 @endif text-gray-300 hover:bg-gray-700 hover:text-white rounded-md px-3 py-2 text-sm font-medium">Dashboard</a>
                            <a href="/cms" class="@if(str_starts_with($currentPath, '/cms')) bg-gray-900 @endif text-gray-300 hover:bg-gray-700 hover:text-white rounded-md px-3 py-2 text-sm font-medium">CMS</a>
                            <a href="/email/subscribers" class="@if(str_starts_with($currentPath, '/email')) bg-gray-900 @endif text-gray-300 hover:bg-gray-700 hover:text-white rounded-md px-3 py-2 text-sm font-medium">Email</a>
                            <a href="/hrm/employees" class="@if(str_starts_with($currentPath, '/hrm')) bg-gray-900 @endif text-gray-300 hover:bg-gray-700 hover:text-white rounded-md px-3 py-2 text-sm font-medium">HRM</a>
                        </div>
                    </div>
                    <div class="flex items-center">
                        @auth
                            <span class="text-gray-300 text-sm mr-4">{{ \App\Core\Session::get('user_name', 'Guest') }}</span>
                            <a href="/logout" class="text-gray-300 hover:bg-gray-700 hover:text-white rounded-md px-3 py-2 text-sm font-medium">Logout</a>
                        @endauth
                        @guest
                            <a href="/login" class="text-gray-300 hover:bg-gray-700 hover:text-white rounded-md px-3 py-2 text-sm font-medium">Login</a>
                        @endguest
                    </div>
                </div>
            </div>
        </nav>

        <header class="bg-white shadow">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ $pageTitle ?? 'Dashboard' }}</h1>
            </div>
        </header>

        <main>
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                @if($flash = \App\Core\Session::getFlash('success'))
                    <div class="mb-4 rounded-md bg-green-50 p-4">
                        <p class="text-sm text-green-700">{{ $flash }}</p>
                    </div>
                @endif

                @if($error = \App\Core\Session::getFlash('error'))
                    <div class="mb-4 rounded-md bg-red-50 p-4">
                        <p class="text-sm text-red-700">{{ $error }}</p>
                    </div>
                @endif

                @if($errors = \App\Core\Session::getFlash('errors'))
                    <div class="mb-4 rounded-md bg-red-50 p-4">
                        <ul class="list-disc list-inside text-sm text-red-700">
                            @foreach($errors as $fieldErrors)
                                @foreach((array)$fieldErrors as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
