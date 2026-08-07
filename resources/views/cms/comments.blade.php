@extends('layouts.admin')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-lg shadow">
    <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
        <div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Pending Comments</h3>
            <p class="text-sm text-gray-500">{{ $pendingCount }} comment(s) awaiting moderation</p>
        </div>
    </div>

    <div class="divide-y divide-gray-200 dark:divide-gray-700">
        @forelse($comments as $comment)
        <div class="p-4">
            <div class="flex justify-between items-start mb-2">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center text-sm font-bold">
                        {{ substr($comment['author_name'] ?? $comment['user_name'] ?? 'A', 0, 1) }}
                    </div>
                    <div>
                        <p class="font-medium text-sm text-gray-900 dark:text-white">
                            {{ $comment['author_name'] ?? $comment['user_name'] ?? 'Anonymous' }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ $comment['author_email'] ?? '' }} · {{ $comment['author_ip'] ?? '' }}
                        </p>
                    </div>
                </div>
                <span class="text-xs text-gray-500">{{ date('M d, H:i', strtotime($comment['created_at'])) }}</span>
            </div>

            <p class="text-sm text-gray-700 dark:text-gray-300 mb-3 ml-11">{{ $comment['content'] }}</p>

            <div class="ml-11 flex items-center gap-4">
                <div class="text-xs text-gray-500">
                    On: <a href="/cms/{{ $comment['post_id'] }}/edit" class="text-blue-600 hover:text-blue-800">{{ $comment['post_title'] ?? 'Unknown Post' }}</a>
                </div>
                <div class="flex gap-2 ml-auto">
                    <form method="POST" action="/cms/comments/{{ $comment['id'] }}/approve">
                        @csrf
                        <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded text-xs hover:bg-green-700">Approve</button>
                    </form>
                    <form method="POST" action="/cms/comments/{{ $comment['id'] }}/reject">
                        @csrf
                        <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700">Reject</button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="p-12 text-center">
            <img src="/images/placeholders/cms-empty.png" alt="No comments" class="mx-auto h-32 w-32 mb-3 opacity-60">
            <p class="text-gray-500">No pending comments. You're all caught up!</p>
        </div>
        @endforelse
    </div>

    @if($pagination['pages'] > 1)
    <div class="p-4 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center">
        <p class="text-sm text-gray-500">Page {{ $pagination['page'] }} of {{ $pagination['pages'] }}</p>
        <div class="flex gap-2">
            @if($pagination['page'] > 1)
                <a href="/cms/comments?page={{ $pagination['page'] - 1 }}" class="px-3 py-1 bg-gray-200 rounded text-sm">← Prev</a>
            @endif
            @if($pagination['page'] < $pagination['pages'])
                <a href="/cms/comments?page={{ $pagination['page'] + 1 }}" class="px-3 py-1 bg-gray-200 rounded text-sm">Next →</a>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
