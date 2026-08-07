@extends('layouts.app')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <p class="text-gray-600">Manage email campaigns</p>
    <a href="/email/campaigns/create" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Create Campaign</a>
</div>

<div class="bg-white shadow sm:rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Campaign</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Scheduled</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($campaigns as $campaign)
                <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $campaign['name'] }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $campaign['subject'] }}</td>
                    <td class="px-6 py-4">
                        @if($campaign['status'] === 'sent')
                            <span class="inline-flex items-center rounded-md bg-green-100 px-2 py-1 text-xs font-medium text-green-700">sent</span>
                        @elseif($campaign['status'] === 'sending')
                            <span class="inline-flex items-center rounded-md bg-blue-100 px-2 py-1 text-xs font-medium text-blue-700">sending</span>
                        @elseif($campaign['status'] === 'scheduled')
                            <span class="inline-flex items-center rounded-md bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-700">scheduled</span>
                        @else
                            <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">draft</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        @if($campaign['scheduled_at'])
                            {{ date('M d, Y H:i', strtotime($campaign['scheduled_at'])) }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-6 py-12 text-center text-gray-500">No campaigns found</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
