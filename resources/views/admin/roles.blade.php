@extends('layouts.admin')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
@foreach($roles as $role)
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 border-l-4
        @if($role['name'] === 'admin') border-purple-500
        @elseif($role['name'] === 'editor') border-blue-500
        @elseif($role['name'] === 'hr_manager') border-orange-500
        @else border-gray-400
        @endif">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white capitalize">{{ str_replace('_', ' ', $role['name']) }}</h3>
            <span class="text-xs text-gray-500 dark:text-gray-400">ID: {{ $role['id'] }}</span>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">{{ $role['description'] ?? 'No description' }}</p>
        <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Permissions:</p>
            <div class="flex flex-wrap gap-1">
                @php $rolePerms = json_decode($role['permissions'] ?? '[]', true); if (!is_array($rolePerms)) $rolePerms = []; @endphp
                @foreach($rolePerms as $perm)
                    <span class="px-2 py-1 text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded">{{ $perm }}</span>
                @endforeach
                @if(empty($rolePerms))
                    <span class="text-xs text-gray-400">None</span>
                @endif
            </div>
        </div>
    </div>
@endforeach
</div>

<div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Permission Matrix</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Permission</th>
                    @foreach($roles as $role)
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase capitalize">{{ $role['name'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @php
                    $allPerms = [];
                    foreach($roles as $r) {
                        $p = json_decode($r['permissions'] ?? '[]', true);
                        if (is_array($p)) $allPerms = array_merge($allPerms, $p);
                    }
                    $allPerms = array_values(array_unique($allPerms));
                    sort($allPerms);
                @endphp
                @foreach($allPerms as $perm)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                    <td class="px-4 py-2 text-sm font-mono text-gray-900 dark:text-white">{{ $perm }}</td>
                    @foreach($roles as $role)
                        @php $has = in_array($perm, json_decode($role['permissions'] ?? '[]', true) ?: []); @endphp
                        <td class="px-4 py-2 text-center">
                            @if($has || in_array('*', json_decode($role['permissions'] ?? '[]', true) ?: []))
                                <svg class="h-5 w-5 text-green-500 mx-auto" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            @else
                                <svg class="h-5 w-5 text-gray-300 dark:text-gray-600 mx-auto" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                            @endif
                        </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
