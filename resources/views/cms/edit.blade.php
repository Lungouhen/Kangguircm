@extends('layouts.admin')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Main Content --}}
    <div class="lg:col-span-2">
        <form method="POST" action="/cms/{{ $post['id'] }}/update" enctype="multipart/form-data">
            @csrf

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-4 p-6">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                    <input type="text" name="title" value="{{ htmlspecialchars($post['title'] ?? '') }}" required
                           class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Content</label>
                    <textarea name="content" rows="16" required
                              class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 font-mono text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white">{{ htmlspecialchars($post['content'] ?? '') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Markdown or HTML supported. Reading time: ~{{ $post['reading_time'] ?? 1 }} min</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Excerpt</label>
                    <textarea name="excerpt" rows="2"
                              class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">{{ htmlspecialchars($post['excerpt'] ?? '') }}</textarea>
                </div>
            </div>

            {{-- SEO Section --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-4 p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    SEO &amp; Social
                </h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meta Title <span class="text-gray-400">(leave blank to use post title)</span></label>
                        <input type="text" name="meta_title" value="{{ htmlspecialchars($post['meta_title'] ?? '') }}" maxlength="70"
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Meta Description</label>
                        <textarea name="meta_description" rows="2" maxlength="160"
                                  class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">{{ htmlspecialchars($post['meta_description'] ?? '') }}</textarea>
                        <p class="text-xs text-gray-500">Recommended: 150-160 characters</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">OG Image URL</label>
                        <input type="url" name="og_image" value="{{ htmlspecialchars($post['og_image'] ?? '') }}"
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                </div>
            </div>

            {{-- Change Summary --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-4 p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Change Summary</h3>
                <input type="text" name="change_summary" placeholder="Brief description of changes (for revision history)"
                       class="w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm font-medium">Save Changes</button>
                <a href="/cms" class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-4 py-2 rounded text-sm">Cancel</a>
            </div>
        </form>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">
        {{-- Status Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Status</h3>
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Publish Status:</span>
                    <span class="font-medium capitalize">{{ $post['status'] }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Review Status:</span>
                    <span class="font-medium capitalize">{{ $post['review_status'] ?? 'draft' }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Featured:</span>
                    <span class="font-medium">{{ $post['featured'] ? 'Yes ★' : 'No' }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Views:</span>
                    <span class="font-medium">{{ number_format($post['view_count'] ?? 0) }}</span>
                </div>
            </div>

            <div class="mt-4 space-y-2">
                @if($post['review_status'] === 'draft')
                    <form method="POST" action="/cms/{{ $post['id'] }}/submit-review">
                        @csrf
                        <button type="submit" class="w-full bg-yellow-500 text-white px-3 py-2 rounded text-sm hover:bg-yellow-600">Submit for Review</button>
                    </form>
                @endif
                @if($post['review_status'] === 'in_review')
                    <form method="POST" action="/cms/{{ $post['id'] }}/approve" class="mb-2">
                        @csrf
                        <button type="submit" class="w-full bg-green-600 text-white px-3 py-2 rounded text-sm hover:bg-green-700">Approve &amp; Publish</button>
                    </form>
                    <form method="POST" action="/cms/{{ $post['id'] }}/reject">
                        @csrf
                        <button type="submit" class="w-full bg-red-600 text-white px-3 py-2 rounded text-sm hover:bg-red-700">Reject</button>
                    </form>
                @endif
                <form method="POST" action="/cms/{{ $post['id'] }}/toggle-featured">
                    @csrf
                    <button type="submit" class="w-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-3 py-2 rounded text-sm hover:bg-gray-300">
                        {{ $post['featured'] ? 'Unfeature' : 'Feature this post' }}
                    </button>
                </form>
            </div>
        </div>

        {{-- Category & Tags --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Category &amp; Tags</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Category</label>
                    <select name="category_id" form="updateForm"
                            class="w-full rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-2 py-1 text-sm">
                        <option value="">No category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat['id'] }}" {{ ($post['category_id'] ?? 0) == $cat['id'] ? 'selected' : '' }}>
                                {{ $cat['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Tags</label>
                    <div class="space-y-1 max-h-40 overflow-y-auto">
                        @foreach($tags as $tag)
                            <label class="flex items-center text-sm">
                                <input type="checkbox" name="tags[]" value="{{ $tag['id'] }}" {{ in_array($tag['id'], $selectedTags) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2">
                                <span class="inline-block w-3 h-3 rounded mr-1" style="background:{{ $tag['color'] }}"></span>
                                {{ $tag['name'] }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Options --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Options</h3>
            <label class="flex items-center mb-2">
                <input type="checkbox" name="featured" value="1" {{ ($post['featured'] ?? 0) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2">
                <span class="text-sm text-gray-700 dark:text-gray-300">Featured post</span>
            </label>
            <label class="flex items-center">
                <input type="checkbox" name="allow_comments" value="1" {{ ($post['allow_comments'] ?? 1) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2">
                <span class="text-sm text-gray-700 dark:text-gray-300">Allow comments</span>
            </label>
        </div>

        {{-- Revision History --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Revision History</h3>
            @if(empty($revisions))
                <p class="text-sm text-gray-500">No revisions yet.</p>
            @else
                <ul class="space-y-2 max-h-60 overflow-y-auto">
                    @foreach($revisions as $rev)
                        <li class="border-b border-gray-100 dark:border-gray-700 pb-2 last:border-0">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <p class="text-xs font-medium text-gray-900 dark:text-white">{{ $rev['change_summary'] ?? 'Edit' }}</p>
                                    <p class="text-xs text-gray-500">{{ $rev['author_name'] ?? 'Unknown' }} · {{ date('M d, H:i', strtotime($rev['created_at'])) }}</p>
                                </div>
                                <form method="POST" action="/cms/restore-revision/{{ $rev['id'] }}" class="ml-2">
                                    @csrf
                                    <button type="submit" class="text-xs text-blue-600 hover:text-blue-800" title="Restore this version"></button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Danger Zone --}}
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
            <h3 class="text-sm font-medium text-red-900 dark:text-red-300 mb-3">Danger Zone</h3>
            <form method="POST" action="/cms/{{ $post['id'] }}/delete" onsubmit="return confirm('Delete this post permanently?')">
                @csrf
                <button type="submit" class="w-full bg-red-600 text-white px-3 py-2 rounded text-sm hover:bg-red-700">Delete Post</button>
            </form>
        </div>

        {{-- Preview Link --}}
        @if($post['status'] === 'published')
            <a href="/blog/{{ $post['slug'] }}" target="_blank" class="block bg-green-600 text-white text-center px-4 py-2 rounded hover:bg-green-700 text-sm font-medium">
                View Published Post →
            </a>
        @endif
    </div>
</div>

@push('scripts')
<script>
// Add category_id to main form
document.querySelector('select[name="category_id"]').setAttribute('form', '');
document.querySelector('select[name="category_id"]').setAttribute('name', 'category_id');
// Move sidebar inputs into the main form
document.querySelectorAll('input[name="tags[]"], input[name="featured"], input[name="allow_comments"]').forEach(el => {
    el.closest('div').closest('form') || el.setAttribute('form', '');
});
</script>
@endpush
@endsection
