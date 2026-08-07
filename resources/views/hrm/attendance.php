<?php
$pageTitle = 'HRM - Attendance';
ob_start();
?>

<div class="mb-6 flex justify-between items-center">
    <p class="text-gray-600">Attendance for <?= htmlspecialchars($today) ?></p>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="lg:col-span-1">
        <div class="bg-white shadow sm:rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Clock In / Out</h3>
            <form action="/hrm/attendance/clock" method="POST" class="space-y-4">
                <?= \App\Core\Csrf::field() ?>
                <div>
                    <label for="employee_id" class="block text-sm font-medium text-gray-700">Employee ID</label>
                    <input type="number" name="employee_id" id="employee_id" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                </div>
                <button type="submit" class="w-full rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white hover:bg-green-500">
                    Clock In / Out
                </button>
            </form>
        </div>

        <div class="mt-6 bg-white shadow sm:rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Navigation</h3>
            <div class="space-y-2">
                <a href="/hrm/employees" class="block rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Employees</a>
                <a href="/hrm/attendance" class="block rounded-md bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700">Attendance</a>
                <a href="/hrm/leaves" class="block rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Leave Requests</a>
            </div>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white shadow sm:rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Clock In</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Clock Out</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <?php if (empty($attendance)): ?>
                        <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">No attendance records for today</td></tr>
                    <?php else: ?>
                        <?php foreach ($attendance as $record): ?>
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= htmlspecialchars($record['name']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($record['employee_code']) ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($record['clock_in'] ?? '-') ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($record['clock_out'] ?? '-') ?></td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium
                                        <?= match($record['status']) {
                                            'present' => 'bg-green-100 text-green-700',
                                            'absent' => 'bg-red-100 text-red-700',
                                            'late' => 'bg-yellow-100 text-yellow-700',
                                            'half_day' => 'bg-blue-100 text-blue-700',
                                        } ?>">
                                        <?= htmlspecialchars($record['status']) ?>
                                    </span>
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
