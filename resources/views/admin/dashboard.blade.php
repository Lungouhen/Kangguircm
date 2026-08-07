@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Users</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['users'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-2 text-xs text-green-600">{{ $stats['users_active'] ?? 0 }} active in 30 days</div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Published Posts</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['posts_published'] ?? 0 }} <span class="text-sm text-gray-500">/ {{ $stats['posts'] ?? 0 }}</span></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Active Subscribers</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['subscribers'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
                <div class="mt-2 text-xs text-gray-500">{{ $stats['campaigns_sent'] ?? 0 }} campaigns sent</div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Active Employees</dt>
                            <dd class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['employees'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
                @if(($stats['leaves_pending'] ?? 0) > 0)
                    <div class="mt-2 text-xs text-orange-600 font-medium">{{ $stats['leaves_pending'] }} pending leave requests</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Quick Actions + Recent Activity --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Quick Actions --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Quick Actions</h3>
            <div class="space-y-2">
                <a href="/admin/users" class="flex items-center justify-between px-4 py-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition">
                    <span class="text-sm font-medium">Manage Users</span>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                <a href="/admin/roles" class="flex items-center justify-between px-4 py-3 rounded-lg bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 hover:bg-purple-100 dark:hover:bg-purple-900/40 transition">
                    <span class="text-sm font-medium">Configure Roles</span>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                <a href="/admin/settings" class="flex items-center justify-between px-4 py-3 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 hover:bg-green-100 dark:hover:bg-green-900/40 transition">
                    <span class="text-sm font-medium">System Settings</span>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                <a href="/admin/logs" class="flex items-center justify-between px-4 py-3 rounded-lg bg-gray-50 dark:bg-gray-700/40 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/60 transition">
                    <span class="text-sm font-medium">Audit Logs</span>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        {{-- Recent Activity --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Recent Activity</h3>
                <a href="/admin/logs" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">View all →</a>
            </div>

            @if(empty($recentActivity))
                <div class="text-center py-8">
                    <img src="/images/placeholders/cms-empty.png" alt="No activity" class="mx-auto h-32 w-32 mb-3 opacity-60">
                    <p class="text-gray-500 dark:text-gray-400">No recent activity logged.</p>
                </div>
            @else
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        @foreach($recentActivity as $idx => $log)
                        <li>
                            <div class="relative pb-8">
                                @if($idx < count($recentActivity) - 1)
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                @endif
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs font-bold">
                                            {{ substr($log['user_name'] ?? 'U', 0, 1) }}
                                        </span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-gray-900 dark:text-white">
                                            <span class="font-medium">{{ $log['user_name'] ?? 'Unknown' }}</span>
                                            <span class="text-gray-500 dark:text-gray-400"> {{ $log['action'] }}</span>
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $log['entity'] }}#{{ $log['entity_id'] ?? '-' }}
                                            · {{ date('M d, H:i', strtotime($log['created_at'])) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>

    {{-- System Info --}}
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">System Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div class="flex justify-between border-b border-gray-100 dark:border-gray-700 pb-2">
                <span class="text-gray-500 dark:text-gray-400">PHP Version</span>
                <span class="font-medium text-gray-900 dark:text-white">8.3.32 (WASM)</span>
            </div>
            <div class="flex justify-between border-b border-gray-100 dark:border-gray-700 pb-2">
                <span class="text-gray-500 dark:text-gray-400">Database</span>
                <span class="font-medium text-gray-900 dark:text-white">SQLite</span>
            </div>
            <div class="flex justify-between border-b border-gray-100 dark:border-gray-700 pb-2">
                <span class="text-gray-500 dark:text-gray-400">Template Engine</span>
                <span class="font-medium text-gray-900 dark:text-white">Blade</span>
            </div>
            <div class="flex justify-between border-b border-gray-100 dark:border-gray-700 pb-2">
                <span class="text-gray-500 dark:text-gray-400">Audit Logs (24h)</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ $stats['audit_logs_24h'] ?? 0 }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-100 dark:border-gray-700 pb-2">
                <span class="text-gray-500 dark:text-gray-400">Session Timeout</span>
                <span class="font-medium text-gray-900 dark:text-white">120 min</span>
            </div>
            <div class="flex justify-between border-b border-gray-100 dark:border-gray-700 pb-2">
                <span class="text-gray-500 dark:text-gray-400">Rate Limit</span>
                <span class="font-medium text-gray-900 dark:text-white">100 req/min</span>
            </div>
        </div>
    </div>
</div>
@endsection
