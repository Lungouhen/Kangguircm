@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl">
    <form action="/hrm/employees" method="POST" class="space-y-6">
        @csrf

        <div class="bg-white shadow sm:rounded-lg p-6 space-y-4">
            <h3 class="text-lg font-medium text-gray-900">Employee Information</h3>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700">User Account ID</label>
                    <input type="number" name="user_id" id="user_id" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                </div>
                <div>
                    <label for="employee_code" class="block text-sm font-medium text-gray-700">Employee Code</label>
                    <input type="text" name="employee_code" id="employee_code" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="department" class="block text-sm font-medium text-gray-700">Department</label>
                    <input type="text" name="department" id="department"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                </div>
                <div>
                    <label for="designation" class="block text-sm font-medium text-gray-700">Designation</label>
                    <input type="text" name="designation" id="designation"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="date_of_joining" class="block text-sm font-medium text-gray-700">Date of Joining</label>
                    <input type="date" name="date_of_joining" id="date_of_joining" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                </div>
                <div>
                    <label for="salary" class="block text-sm font-medium text-gray-700">Monthly Salary ($)</label>
                    <input type="number" name="salary" id="salary" step="0.01" min="0" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                </div>
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                <input type="text" name="phone" id="phone"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                <textarea name="address" id="address" rows="2"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border"></textarea>
            </div>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="/hrm/employees" class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Add Employee</button>
        </div>
    </form>
</div>
@endsection
