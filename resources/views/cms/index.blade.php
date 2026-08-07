@extends('layouts.app')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <p class="text-gray-600">Manage your content posts</p>
    <a href="/cms/create" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Create Post</a>
</div>

<div class="overflow-hidden bg-white shadow sm:rounded-md">
    <ul role="list" class="divide-y divide-gray-200">
        @forelse($posts as $post)
            <li>
                <a href="/cms/{{ $post['id'] }}/edit" class="block hover:bg-gray-50">
                    <div class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <p class="truncate text-sm font-medium text-indigo-600">{{ $post['title'] }}</p>
                                @if($post['status'] === 'published')
                                    <span class="ml-3 inline-flex items-center rounded-md bg-green-100 px-2 py-1 text-xs font-medium text-green-700">published</span>
                                @elseif($post['status'] === 'draft')
                                    <span class="ml-3 inline-flex items-center rounded-md bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-700">draft</span>
                                @else
                                    <span class="ml-3 inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">{{ $post['status'] }}</span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-500">By {{ $post['author_name'] }}</p>
                        </div>
                        <div class="mt-2 sm:flex sm:justify-between">
                            <p class="text-sm text-gray-500">/{{ $post['slug'] }}</p>
                            <p class="text-sm text-gray-500">{{ date('M d, Y', strtotime($post['created_at'])) }}</p>
                        </div>
                    </div>
                </a>
            </li>
        @empty
            <li class="px-6 py-12 text-center text-gray-500">No posts found. Create your first post!</li>
        @endforelse
    </ul>
</div>
@endsection
