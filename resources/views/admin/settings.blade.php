@extends('layouts.admin')

@section('content')
<form action="/admin/settings/update" method="POST" class="space-y-6">
    @csrf

    @foreach($settings as $groupName => $items)
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white capitalize">{{ str_replace('_', ' ', $groupName) }}</h3>
        </div>
        <div class="p-6 space-y-4">
            @foreach($items as $setting)
            <div>
                <label for="setting_{{ $setting['key'] }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ ucfirst(str_replace('_', ' ', $setting['key'])) }}
                </label>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $setting['description'] ?? '' }}</p>
                <input type="text"
                       id="setting_{{ $setting['key'] }}"
                       name="settings[{{ $setting['key'] }}]"
                       value="{{ htmlspecialchars($setting['value'] ?? '') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <input type="hidden" name="keys[]" value="{{ $setting['key'] }}">
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    <div class="flex justify-end">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700">
            Save Settings
        </button>
    </div>
</form>
@endsection
