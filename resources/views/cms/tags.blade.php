@extends('layouts.admin')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Create Tag --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Create New Tag</h3>
        <form method="POST" action="/cms/tags" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                <input type="text" name="name" required
                       class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                <textarea name="description" rows="2"
                          class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Color</label>
                <input type="color" name="color" value="#3b82f6"
                       class="w-full h-10 rounded border-gray-300 dark:border-gray-600">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm font-medium">Create Tag</button>
        </form>
    </div>

    {{-- Tags List --}}
    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">All Tags ({{ count($tags) }})</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @forelse($tags as $tag)
            <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded" style="background:{{ $tag['color'] }}"></span>
                    <div>
                        <p class="font-medium text-sm text-gray-900 dark:text-white">{{ $tag['name'] }}</p>
                        <p class="text-xs text-gray-500">{{ $tag['description'] ?? 'No description' }} · {{ $tag['post_count'] ?? 0 }} posts</p>
                    </div>
                </div>
                <form method="POST" action="/cms/tags/{{ $tag['id'] }}/delete" onsubmit="return confirm('Delete this tag?')">
                    @csrf
                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                </form>
            </div>
            @empty
            <p class="col-span-2 text-gray-500 text-center py-8">No tags created yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
