<?php
$pageTitle = 'Email Marketing - Campaigns';
ob_start();
?>

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
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            <?php if (empty($campaigns)): ?>
                <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">No campaigns found</td></tr>
            <?php else: ?>
                <?php foreach ($campaigns as $campaign): ?>
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= esc($campaign['name']) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?= esc($campaign['subject']) ?></td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium
                                <?= match($campaign['status']) {
                                    'sent' => 'bg-green-100 text-green-700',
                                    'sending' => 'bg-blue-100 text-blue-700',
                                    'scheduled' => 'bg-yellow-100 text-yellow-700',
                                    'draft' => 'bg-gray-100 text-gray-700',
                                } ?>">
                                <?= esc($campaign['status']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <?= $campaign['scheduled_at'] ? date('M d, Y H:i', strtotime($campaign['scheduled_at'])) : '-' ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?= date('M d, Y', strtotime($campaign['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
