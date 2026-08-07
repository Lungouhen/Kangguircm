@extends('layouts.app')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <p class="text-gray-600">Manage employee records</p>
    <a href="/hrm/employees/create" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Add Employee</a>
</div>

<div class="bg-white shadow sm:rounded-lg overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Designation</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Salary</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @forelse($employees as $emp)
                <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $emp['employee_code'] }}</td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">{{ $emp['name'] }}</div>
                        <div class="text-sm text-gray-500">{{ $emp['email'] }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $emp['department'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $emp['designation'] ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ date('M d, Y', strtotime($emp['date_of_joining'])) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 font-medium">${{ number_format((float)$emp['salary'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <img src="/images/placeholders/hrm-empty.png" alt="No employees" class="mx-auto h-48 w-48 mb-4 opacity-75">
                        <p class="text-gray-500">No employees found. Add your first employee!</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
