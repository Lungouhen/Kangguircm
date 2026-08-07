<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/images/favicon.png">
    <title>{{ $pageTitle ?? 'Admin Panel' }} | Multi-Module Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: { 50: '#eff6ff', 100: '#dbeafe', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8' },
                    }
                }
            }
        }
    </script>
    <style>
        .sidebar-link.active { background: #1e3a8a; border-left: 3px solid #3b82f6; }
        .sidebar-link:hover { background: #1e40af; }
    </style>
</head>
<body class="h-full bg-gray-50 dark:bg-gray-900">
    <div class="flex h-full">
        {{-- Sidebar Navigation --}}
        <aside class="w-64 bg-gray-900 text-white flex flex-col shadow-xl">
            <div class="flex items-center h-16 px-6 border-b border-gray-700">
                <img src="/images/logo.png" alt="Logo" class="h-8 w-8 mr-3 rounded">
                <div>
                    <div class="font-bold text-lg">Admin Panel</div>
                    <div class="text-xs text-gray-400">v1.0.0</div>
                </div>
            </div>

            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                @php $currentPath = $_SERVER['REQUEST_URI'] ?? ''; @endphp

                <a href="/admin" class="sidebar-link @if(str_starts_with($currentPath, '/admin') && !str_contains($currentPath, '/users') && !str_contains($currentPath, '/roles') && !str_contains($currentPath, '/settings') && !str_contains($currentPath, '/logs')) active @endif flex items-center px-3 py-2 text-sm font-medium rounded-md">
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>

                <a href="/admin/users" class="sidebar-link @if(str_contains($currentPath, '/users')) active @endif flex items-center px-3 py-2 text-sm font-medium rounded-md">
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Users
                </a>

                <a href="/admin/roles" class="sidebar-link @if(str_contains($currentPath, '/roles')) active @endif flex items-center px-3 py-2 text-sm font-medium rounded-md">
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Roles & Permissions
                </a>

                <div class="pt-4 mt-4 border-t border-gray-700">
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Modules</p>
                </div>

                <a href="/cms" class="sidebar-link flex items-center px-3 py-2 text-sm font-medium text-gray-300 rounded-md">
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    CMS
                </a>

                <a href="/email/subscribers" class="sidebar-link flex items-center px-3 py-2 text-sm font-medium text-gray-300 rounded-md">
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Email Marketing
                </a>

                <a href="/hrm/employees" class="sidebar-link flex items-center px-3 py-2 text-sm font-medium text-gray-300 rounded-md">
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    HRM
                </a>

                <div class="pt-4 mt-4 border-t border-gray-700">
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">System</p>
                </div>

                <a href="/admin/settings" class="sidebar-link @if(str_contains($currentPath, '/settings')) active @endif flex items-center px-3 py-2 text-sm font-medium rounded-md">
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Settings
                </a>

                <a href="/admin/logs" class="sidebar-link @if(str_contains($currentPath, '/logs')) active @endif flex items-center px-3 py-2 text-sm font-medium rounded-md">
                    <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Audit Logs
                </a>
            </nav>

            <div class="p-4 border-t border-gray-700">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="h-8 w-8 rounded-full bg-primary-600 flex items-center justify-center text-white font-bold text-sm">
                            {{ substr(\App\Core\Session::get('user_name', 'A'), 0, 1) }}
                        </div>
                    </div>
                    <div class="ml-3 flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ \App\Core\Session::get('user_name', 'Admin') }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ \App\Core\Session::get('user_email', 'admin@example.com') }}</p>
                    </div>
                </div>
                <a href="/logout" class="mt-3 block w-full text-center text-xs text-gray-400 hover:text-white">Logout</a>
            </div>
        </aside>

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            {{-- Top Bar --}}
            <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between h-16 px-6">
                    <div class="flex items-center">
                        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">{{ $pageTitle ?? 'Dashboard' }}</h2>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="/dashboard" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                            ← Back to App
                        </a>
                    </div>
                </div>
            </header>

            {{-- Page Content --}}
            <main class="flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-900 p-6">
                @if($flash = \App\Core\Session::getFlash('success'))
                    <div class="mb-4 rounded-md bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 p-4">
                        <p class="text-sm text-green-700 dark:text-green-300">{{ $flash }}</p>
                    </div>
                @endif

                @if($error = \App\Core\Session::getFlash('error'))
                    <div class="mb-4 rounded-md bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 p-4">
                        <p class="text-sm text-red-700 dark:text-red-300">{{ $error }}</p>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
