@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl">
    <form action="/email/campaigns" method="POST" class="space-y-6">
        @csrf

        <div class="bg-white shadow sm:rounded-lg p-6 space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Campaign Name</label>
                <input type="text" name="name" id="name" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
            </div>
            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700">Email Subject</label>
                <input type="text" name="subject" id="subject" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
            </div>
            <div>
                <label for="template" class="block text-sm font-medium text-gray-700">Email Template (HTML)</label>
                <textarea name="template" id="template" rows="10" required
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border font-mono text-sm"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Target Lists</label>
                <div class="space-y-2">
                    @forelse($lists as $list)
                        <label class="flex items-center">
                            <input type="checkbox" name="lists[]" value="{{ $list['id'] }}"
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">{{ $list['name'] }}</span>
                        </label>
                    @empty
                        <p class="text-sm text-gray-500">No mailing lists available. <a href="/email/lists" class="text-indigo-600">Create one first</a>.</p>
                    @endforelse
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                        <option value="draft">Draft</option>
                        <option value="scheduled">Scheduled</option>
                    </select>
                </div>
                <div>
                    <label for="scheduled_at" class="block text-sm font-medium text-gray-700">Schedule Date/Time</label>
                    <input type="datetime-local" name="scheduled_at" id="scheduled_at"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                </div>
            </div>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="/email/campaigns" class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Create Campaign</button>
        </div>
    </form>
</div>
@endsection
