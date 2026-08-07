@extends('layouts.app')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <p class="text-gray-600">Manage mailing lists</p>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="lg:col-span-1">
        <div class="bg-white shadow sm:rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Create List</h3>
            <form action="/email/lists" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">List Name</label>
                    <input type="text" name="name" id="name" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border"></textarea>
                </div>
                <button type="submit" class="w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Create List</button>
            </form>
        </div>
        <div class="mt-6 bg-white shadow sm:rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Navigation</h3>
            <div class="space-y-2">
                <a href="/email/subscribers" class="block rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Subscribers</a>
                <a href="/email/lists" class="block rounded-md bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700">Mailing Lists</a>
                <a href="/email/campaigns" class="block rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Campaigns</a>
            </div>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white shadow sm:rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subscribers</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($lists as $list)
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $list['name'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $list['description'] ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $list['subscriber_count'] ?? 0 }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ date('M d, Y', strtotime($list['created_at'])) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-12 text-center text-gray-500">No mailing lists found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
