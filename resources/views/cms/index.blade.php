@extends('layouts.admin')

@section('content')
{{-- Stats Cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">Total Posts</div>
        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_posts'] ?? 0 }}</div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">Published</div>
        <div class="text-2xl font-bold text-green-600">{{ $stats['published_posts'] ?? 0 }}</div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">Pending Review</div>
        <div class="text-2xl font-bold text-yellow-600">{{ $stats['pending_reviews'] ?? 0 }}</div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">Total Views</div>
        <div class="text-2xl font-bold text-blue-600">{{ number_format($stats['total_views'] ?? 0) }}</div>
    </div>
</div>

{{-- Actions Bar --}}
<div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-4">
    <div class="p-4 flex flex-wrap gap-3 items-center">
        <a href="/cms/create" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm font-medium">+ New Post</a>
        <a href="/cms/tags" class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-4 py-2 rounded hover:bg-gray-300 text-sm">Tags</a>
        <a href="/cms/comments" class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-4 py-2 rounded hover:bg-gray-300 text-sm relative">
            Comments
            @if(($stats['pending_comments'] ?? 0) > 0)
                <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">{{ $stats['pending_comments'] }}</span>
            @endif
        </a>
        <a href="/blog" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm" target="_blank">View Blog →</a>
    </div>

    {{-- Filters --}}
    <form method="GET" action="/cms" class="p-4 pt-0 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search posts..."
               class="flex-1 min-w-48 rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
        <select name="status" class="rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2">
            <option value="">All Statuses</option>
            <option value="draft" {{ ($filters['status'] ?? '') === 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="published" {{ ($filters['status'] ?? '') === 'published' ? 'selected' : '' }}>Published</option>
            <option value="archived" {{ ($filters['status'] ?? '') === 'archived' ? 'selected' : '' }}>Archived</option>
        </select>
        <select name="review_status" class="rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2">
            <option value="">All Reviews</option>
            <option value="draft" {{ ($filters['review_status'] ?? '') === 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="in_review" {{ ($filters['review_status'] ?? '') === 'in_review' ? 'selected' : '' }}>In Review</option>
            <option value="approved" {{ ($filters['review_status'] ?? '') === 'approved' ? 'selected' : '' }}>Approved</option>
        </select>
        <button type="submit" class="bg-gray-800 dark:bg-gray-700 text-white px-4 py-2 rounded text-sm">Filter</button>
        @if(!empty($filters))
            <a href="/cms" class="bg-red-100 text-red-700 px-3 py-2 rounded text-sm">Clear</a>
        @endif
    </form>
</div>

{{-- Bulk Actions Form --}}
<form method="POST" action="/cms/bulk" id="bulkForm">
    @csrf
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr>
                    <th class="px-4 py-3"><input type="checkbox" id="selectAll"></th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Review</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Author</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Views</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($posts as $post)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                    <td class="px-4 py-4"><input type="checkbox" name="post_ids[]" value="{{ $post['id'] }}" class="bulk-check rounded border-gray-300"></td>
                    <td class="px-4 py-4">
                        <a href="/cms/{{ $post['id'] }}/edit" class="text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400">
                            {{ $post['title'] }}
                        </a>
                        @if($post['featured'])
                            <span class="ml-2 text-yellow-500">★</span>
                        @endif
                    </td>
                    <td class="px-4 py-4">
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                            @if($post['status'] === 'published') bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300
                            @elseif($post['status'] === 'draft') bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300
                            @else bg-orange-100 text-orange-700 @endif">
                            {{ $post['status'] }}
                        </span>
                    </td>
                    <td class="px-4 py-4">
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                            @if($post['review_status'] === 'approved') bg-green-100 text-green-700
                            @elseif($post['review_status'] === 'in_review') bg-yellow-100 text-yellow-700
                            @else bg-gray-100 text-gray-700 @endif">
                            {{ $post['review_status'] ?? 'draft' }}
                        </span>
                    </td>
                    <td class="px-4 py-4 text-sm text-gray-500">{{ $post['author_name'] ?? '-' }}</td>
                    <td class="px-4 py-4 text-sm text-gray-500">{{ number_format($post['view_count'] ?? 0) }}</td>
                    <td class="px-4 py-4 text-sm text-gray-500">{{ date('M d, Y', strtotime($post['created_at'])) }}</td>
                    <td class="px-4 py-4 text-sm">
                        <div class="flex gap-2">
                            <a href="/cms/{{ $post['id'] }}/edit" class="text-blue-600 hover:text-blue-800">Edit</a>
                            @if($post['review_status'] === 'draft')
                                <form method="POST" action="/cms/{{ $post['id'] }}/submit-review" class="inline">
                                    @csrf
                                    <button type="submit" class="text-yellow-600 hover:text-yellow-800 text-xs">Submit</button>
                                </form>
                            @endif
                            @if($post['review_status'] === 'in_review')
                                <form method="POST" action="/cms/{{ $post['id'] }}/approve" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-800 text-xs">Approve</button>
                                </form>
                                <form method="POST" action="/cms/{{ $post['id'] }}/reject" class="inline">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Reject</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center">
                        <img src="/images/placeholders/cms-empty.png" alt="No posts" class="mx-auto h-32 w-32 mb-3 opacity-60">
                        <p class="text-gray-500">No posts found. Create your first post!</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if(!empty($posts))
        <div class="p-4 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-500">Bulk actions:</span>
                <select name="action" class="rounded border-gray-300 text-sm">
                    <option value="">Select action...</option>
                    <option value="publish">Publish selected</option>
                    <option value="archive">Archive selected</option>
                    <option value="delete">Delete selected</option>
                </select>
                <button type="submit" class="bg-gray-800 text-white px-3 py-1 rounded text-sm">Apply</button>
            </div>
            <div class="text-sm text-gray-500">
                Page {{ $pagination['page'] }} of {{ $pagination['pages'] }}
                ({{ $pagination['total'] }} total)
            </div>
        </div>
        @endif
    </div>
</form>

<script>
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.bulk-check').forEach(cb => cb.checked = this.checked);
});
</script>
@endsection
