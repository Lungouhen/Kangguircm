@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl">
    <form action="/cms/{{ $post['id'] }}/update" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="bg-white shadow sm:rounded-lg p-6">
            <div class="space-y-4">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" name="title" id="title" required value="{{ $post['title'] }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                </div>

                <div>
                    <label for="excerpt" class="block text-sm font-medium text-gray-700">Excerpt</label>
                    <textarea name="excerpt" id="excerpt" rows="2"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">{{ $post['excerpt'] ?? '' }}</textarea>
                </div>

                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700">Content</label>
                    <textarea name="content" id="content" rows="12" required
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">{{ $post['content'] }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                        <select name="category_id" id="category_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                            <option value="">No Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat['id'] }}" @if($cat['id'] == $post['category_id']) selected @endif>
                                    {{ $cat['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" id="status"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                            <option value="draft" @if($post['status'] === 'draft') selected @endif>Draft</option>
                            <option value="published" @if($post['status'] === 'published') selected @endif>Published</option>
                            <option value="archived" @if($post['status'] === 'archived') selected @endif>Archived</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-between">
            <form action="/cms/{{ $post['id'] }}/delete" method="POST" onsubmit="return confirm('Delete this post?')">
                @csrf
                <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500">Delete Post</button>
            </form>

            <div class="flex space-x-3">
                <a href="/cms" class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Update Post</button>
            </div>
        </div>
    </form>
</div>
@endsection
