@extends('layouts.blog')

@section('content')
{{-- Hero --}}
<div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-16">
    <div class="max-w-7xl mx-auto px-4">
        <h1 class="text-4xl font-bold mb-3">Blog</h1>
        <p class="text-blue-100 text-lg">Latest articles, tutorials, and announcements.</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 py-12">
    {{-- Featured Posts --}}
    @if(!empty($featuredPosts))
    <div class="mb-12">
        <h2 class="text-2xl font-bold mb-6">Featured</h2>
        <div class="grid md:grid-cols-3 gap-6">
            @foreach($featuredPosts as $featured)
            <a href="/blog/{{ $featured['slug'] }}" class="group block bg-white rounded-lg shadow hover:shadow-lg transition">
                @if($featured['featured_image'])
                    <img src="{{ $featured['featured_image'] }}" alt="" class="w-full h-48 object-cover rounded-t-lg">
                @else
                    <div class="w-full h-48 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-t-lg flex items-center justify-center">
                        <span class="text-4xl">📝</span>
                    </div>
                @endif
                <div class="p-5">
                    <h3 class="font-bold text-lg group-hover:text-blue-600">{{ $featured['title'] }}</h3>
                    @if($featured['excerpt'])
                        <p class="text-sm text-gray-600 mt-2 line-clamp-2">{{ $featured['excerpt'] }}</p>
                    @endif
                    <div class="mt-3 text-xs text-gray-500">{{ date('M d, Y', strtotime($featured['published_at'])) }}</div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Posts Grid --}}
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($posts as $post)
        <a href="/blog/{{ $post['slug'] }}" class="group block bg-white rounded-lg shadow hover:shadow-lg transition border border-gray-100">
            @if($post['featured_image'])
                <img src="{{ $post['featured_image'] }}" alt="" class="w-full h-48 object-cover rounded-t-lg">
            @else
                <div class="w-full h-48 bg-gradient-to-br from-gray-50 to-gray-100 rounded-t-lg flex items-center justify-center">
                    <span class="text-4xl">📄</span>
                </div>
            @endif
            <div class="p-5">
                <div class="flex flex-wrap gap-1 mb-2">
                    @foreach($post['tags'] ?? [] as $tag)
                        <span class="text-xs px-2 py-1 rounded-full bg-blue-50 text-blue-700">{{ $tag['name'] }}</span>
                    @endforeach
                </div>
                <h3 class="font-bold text-lg group-hover:text-blue-600">{{ $post['title'] }}</h3>
                @if($post['excerpt'])
                    <p class="text-sm text-gray-600 mt-2 line-clamp-2">{{ $post['excerpt'] }}</p>
                @endif
                <div class="mt-4 flex items-center justify-between text-xs text-gray-500">
                    <span>{{ $post['author_name'] ?? 'Admin' }}</span>
                    <span>{{ date('M d, Y', strtotime($post['published_at'])) }}</span>
                </div>
            </div>
        </a>
        @empty
        <div class="md:col-span-2 lg:col-span-3 text-center py-12">
            <p class="text-gray-500">No posts published yet. Check back soon!</p>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if(($pagination['pages'] ?? 0) > 1)
    <div class="mt-12 flex justify-center gap-2">
        @if($pagination['page'] > 1)
            <a href="/blog?page={{ $pagination['page'] - 1 }}" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">← Previous</a>
        @endif
        <span class="px-4 py-2 text-gray-600">Page {{ $pagination['page'] }} of {{ $pagination['pages'] }}</span>
        @if($pagination['page'] < $pagination['pages'])
            <a href="/blog?page={{ $pagination['page'] + 1 }}" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Next →</a>
        @endif
    </div>
    @endif
</div>
@endsection
