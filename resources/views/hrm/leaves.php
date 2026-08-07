<?php
$pageTitle = 'HRM - Leave Requests';
ob_start();
?>

<div class="mb-6 flex justify-between items-center">
    <p class="text-gray-600">Manage leave requests</p>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="lg:col-span-1">
        <div class="bg-white shadow sm:rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Request Leave</h3>
            <form action="/hrm/leaves" method="POST" class="space-y-4">
                <?= \App\Core\Csrf::field() ?>
                <div>
                    <label for="employee_id" class="block text-sm font-medium text-gray-700">Employee ID</label>
                    <input type="number" name="employee_id" id="employee_id" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                </div>
                <div>
                    <label for="leave_type" class="block text-sm font-medium text-gray-700">Leave Type</label>
                    <select name="leave_type" id="leave_type" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                        <option value="sick">Sick Leave</option>
                        <option value="casual">Casual Leave</option>
                        <option value="earned">Earned Leave</option>
                        <option value="maternity">Maternity Leave</option>
                        <option value="paternity">Paternity Leave</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                        <input type="date" name="start_date" id="start_date" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                        <input type="date" name="end_date" id="end_date" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                    </div>
                </div>
                <div>
                    <label for="reason" class="block text-sm font-medium text-gray-700">Reason</label>
                    <textarea name="reason" id="reason" rows="3" required
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border"></textarea>
                </div>
                <button type="submit" class="w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Submit Request</button>
            </form>
        </div>

        <div class="mt-6 bg-white shadow sm:rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Navigation</h3>
            <div class="space-y-2">
                <a href="/hrm/employees" class="block rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Employees</a>
                <a href="/hrm/attendance" class="block rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Attendance</a>
                <a href="/hrm/leaves" class="block rounded-md bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700">Leave Requests</a>
            </div>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white shadow sm:rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dates</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <?php if (empty($leaves)): ?>
                        <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">No leave requests found</td></tr>
                    <?php else: ?>
                        <?php foreach ($leaves as $leave): ?>
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= esc($leave['name']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500"><?= esc($leave['leave_type']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?= date('M d', strtotime($leave['start_date'])) ?> - <?= date('M d, Y', strtotime($leave['end_date'])) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500"><?= (int)$leave['days_count'] ?></td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium
                                        <?= match($leave['status']) {
                                            'approved' => 'bg-green-100 text-green-700',
                                            'rejected' => 'bg-red-100 text-red-700',
                                            'pending' => 'bg-yellow-100 text-yellow-700',
                                        } ?>">
                                        <?= esc($leave['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($leave['status'] === 'pending'): ?>
                                        <form action="/hrm/leaves/<?= (int)$leave['id'] ?>/approve" method="POST" class="flex space-x-1">
                                            <?= \App\Core\Csrf::field() ?>
                                            <button type="submit" name="action" value="approve" class="text-xs text-green-600 hover:text-green-800">Approve</button>
                                            <span class="text-gray-300">|</span>
                                            <button type="submit" name="action" value="reject" class="text-xs text-red-600 hover:text-red-800">Reject</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-400">
                                            <?= $leave['approved_by_name'] ? 'By ' . esc($leave['approved_by_name']) : '' ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
