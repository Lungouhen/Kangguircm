@extends('layouts.admin')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Audit Logs ({{ $pagination['total'] }})</h3>
            <form method="GET" action="/admin/logs" class="flex space-x-2">
                <input type="text" name="filter" value="{{ $filter ?? '' }}" placeholder="Filter by action..."
                       class="rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-3 py-1 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <button type="submit" class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-3 py-1 rounded text-sm hover:bg-gray-300 dark:hover:bg-gray-600">Filter</button>
                @if($filter)
                    <a href="/admin/logs" class="bg-red-100 text-red-700 px-3 py-1 rounded text-sm hover:bg-red-200">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Entity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($logs as $log)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ date('M d, H:i', strtotime($log['created_at'])) }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="h-6 w-6 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs font-bold mr-2">
                                {{ substr($log['user_name'] ?? 'U', 0, 1) }}
                            </div>
                            <span class="text-sm text-gray-900 dark:text-white">{{ $log['user_name'] ?? 'Unknown' }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                            @if(str_contains($log['action'], 'create')) bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300
                            @elseif(str_contains($log['action'], 'update')) bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300
                            @elseif(str_contains($log['action'], 'delete')) bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300
                            @else bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300
                            @endif">
                            {{ $log['action'] }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                        {{ $log['entity'] }}@if($log['entity_id'])#{{ $log['entity_id'] }}@endif
                    </td>
                    <td class="px-6 py-4 text-xs text-gray-500 dark:text-gray-400 font-mono">{{ $log['ip_address'] }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center">
                        <img src="/images/placeholders/cms-empty.png" alt="No logs" class="mx-auto h-32 w-32 mb-3 opacity-60">
                        <p class="text-gray-500 dark:text-gray-400">No audit logs found.</p>
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
                <a href="/admin/logs?page={{ $pagination['page'] - 1 }}@if($filter)&filter={{ $filter }}@endif" class="px-3 py-1 text-sm bg-gray-200 dark:bg-gray-700 rounded">← Prev</a>
            @endif
            @if($pagination['page'] < $pagination['pages'])
                <a href="/admin/logs?page={{ $pagination['page'] + 1 }}@if($filter)&filter={{ $filter }}@endif" class="px-3 py-1 text-sm bg-gray-200 dark:bg-gray-700 rounded">Next →</a>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
