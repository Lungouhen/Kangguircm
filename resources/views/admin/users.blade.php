@extends('layouts.admin')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Users ({{ $pagination['total'] }})</h3>
            <a href="/register" class="text-sm bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700">+ Add User</a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Last Active</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs font-bold mr-3">
                                {{ substr($user['name'], 0, 1) }}
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $user['name'] }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user['email'] }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                            @if($user['role_name'] === 'admin') bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300
                            @elseif($user['role_name'] === 'editor') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300
                            @elseif($user['role_name'] === 'hr_manager') bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300
                            @else bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300
                            @endif">
                            {{ $user['role_name'] ?? 'user' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ date('M d, Y', strtotime($user['created_at'])) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ date('M d, Y', strtotime($user['updated_at'])) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center">
                        <img src="/images/placeholders/hrm-empty.png" alt="No users" class="mx-auto h-32 w-32 mb-3 opacity-60">
                        <p class="text-gray-500 dark:text-gray-400">No users registered yet.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($pagination['pages'] > 1)
    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center">
        <p class="text-sm text-gray-500 dark:text-gray-400">Page {{ $pagination['page'] }} of {{ $pagination['pages'] }}</p>
        <div class="flex space-x-2">
            @if($pagination['page'] > 1)
                <a href="/admin/users?page={{ $pagination['page'] - 1 }}" class="px-3 py-1 text-sm bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-300 dark:hover:bg-gray-600">← Prev</a>
            @endif
            @if($pagination['page'] < $pagination['pages'])
                <a href="/admin/users?page={{ $pagination['page'] + 1 }}" class="px-3 py-1 text-sm bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-300 dark:hover:bg-gray-600">Next →</a>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
