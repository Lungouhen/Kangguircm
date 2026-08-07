@extends('layouts.blog')

@section('content')
<article class="max-w-4xl mx-auto px-4 py-12">
    {{-- Header --}}
    <header class="mb-8">
        <div class="flex flex-wrap gap-2 mb-4">
            @foreach($post['tags'] ?? [] as $tag)
                <span class="text-xs px-3 py-1 rounded-full bg-blue-50 text-blue-700 font-medium">{{ $tag['name'] }}</span>
            @endforeach
        </div>
        <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $post['title'] }}</h1>
        <div class="flex items-center gap-4 text-sm text-gray-500">
            <span>By <strong class="text-gray-700">{{ $post['author_name'] ?? 'Admin' }}</strong></span>
            <span>·</span>
            <span>{{ date('F j, Y', strtotime($post['published_at'])) }}</span>
            <span>·</span>
            <span>{{ $post['reading_time'] ?? 1 }} min read</span>
            <span>·</span>
            <span>{{ number_format($post['view_count'] ?? 0) }} views</span>
        </div>
    </header>

    {{-- Featured Image --}}
    @if($post['featured_image'])
        <img src="{{ $post['featured_image'] }}" alt="{{ htmlspecialchars($post['title']) }}"
             class="w-full rounded-lg shadow mb-8">
    @endif

    {{-- Excerpt --}}
    @if($post['excerpt'])
        <p class="text-xl text-gray-600 italic mb-8 border-l-4 border-blue-500 pl-4">{{ $post['excerpt'] }}</p>
    @endif

    {{-- Content --}}
    <div class="prose prose-lg max-w-none">
        {!! nl2br(e($post['content'])) !!}
    </div>

    {{-- Tags --}}
    @if(!empty($post['tags']))
        <div class="mt-8 pt-6 border-t">
            <p class="text-sm text-gray-500 mb-2">Tagged in:</p>
            <div class="flex flex-wrap gap-2">
                @foreach($post['tags'] as $tag)
                    <span class="text-sm px-3 py-1 rounded-full bg-gray-100 text-gray-700">{{ $tag['name'] }}</span>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Share --}}
    <div class="mt-8 pt-6 border-t">
        <p class="text-sm text-gray-500 mb-2">Share this article:</p>
        <div class="flex gap-3">
            <a href="https://twitter.com/intent/tweet?url={{ urlencode($seo['url']) }}&text={{ urlencode($seo['title']) }}"
               target="_blank" class="px-3 py-1 bg-blue-500 text-white rounded text-sm hover:bg-blue-600">Twitter</a>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($seo['url']) }}"
               target="_blank" class="px-3 py-1 bg-blue-700 text-white rounded text-sm hover:bg-blue-800">LinkedIn</a>
        </div>
    </div>
</article>

{{-- Related Posts --}}
@if(!empty($relatedPosts))
<section class="max-w-4xl mx-auto px-4 py-8">
    <h2 class="text-2xl font-bold mb-6">Related Articles</h2>
    <div class="grid md:grid-cols-3 gap-4">
        @foreach($relatedPosts as $related)
            <a href="/blog/{{ $related['slug'] }}" class="block bg-white rounded-lg shadow hover:shadow-md transition p-4">
                <h3 class="font-medium text-gray-900 hover:text-blue-600">{{ $related['title'] }}</h3>
                @if($related['excerpt'])
                    <p class="text-sm text-gray-600 mt-2 line-clamp-2">{{ $related['excerpt'] }}</p>
                @endif
            </a>
        @endforeach
    </div>
</section>
@endif

{{-- Comments --}}
@if(($post['allow_comments'] ?? 1))
<section id="comments" class="max-w-4xl mx-auto px-4 py-8 border-t">
    <h2 class="text-2xl font-bold mb-6">Comments ({{ count($comments) }})</h2>

    @if(!empty($comments))
        <div class="space-y-4 mb-8">
            @foreach($comments as $comment)
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center text-sm font-bold">
                                {{ substr($comment['author_name'] ?? 'A', 0, 1) }}
                            </div>
                            <div>
                                <p class="font-medium text-sm">{{ $comment['author_name'] ?? 'Anonymous' }}</p>
                                <p class="text-xs text-gray-500">{{ date('M d, Y', strtotime($comment['created_at'])) }}</p>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-700">{{ $comment['content'] }}</p>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Comment Form --}}
    <form method="POST" action="/blog/{{ $post['id'] }}/comment" class="bg-white border rounded-lg p-6">
        <h3 class="font-medium text-lg mb-4">Leave a Comment</h3>
        @csrf
        <input type="hidden" name="slug" value="{{ $post['slug'] }}">
        <div class="grid md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" name="author_name" required
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="author_email" required
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2">
            </div>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Comment</label>
            <textarea name="content" rows="4" required
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"></textarea>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm font-medium">
            Submit Comment
        </button>
        <p class="text-xs text-gray-500 mt-2">Comments are moderated before appearing publicly.</p>
    </form>
</section>
@endif
@endsection
