<?php
require __DIR__ . '/../app/bootstrap.php';
require_role('admin');

$logs = [];
try {
    $stmt = db()->query('SELECT l.id, l.client_id, l.coach_user_id, l.consent_text, l.ip_address, l.user_agent, l.created_at, u.name AS coach_name, u.email AS coach_email FROM consent_logs l INNER JOIN users u ON u.id = l.coach_user_id ORDER BY l.created_at DESC, l.id DESC');
    $logs = $stmt->fetchAll();
} catch (Throwable $e) {
    $logs = [];
}

$page_title = 'Privacy Logs';
require __DIR__ . '/../partials/layout_top.php';
require __DIR__ . '/../partials/nav.php';
?>

<main class="mx-auto max-w-6xl px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold tracking-tight">Privacy & Consent Logs</h1>
        <p class="mt-1 text-sm text-zinc-600">Timestamped consent confirmations for compliance tracking.</p>
    </div>

    <div class="rounded-2xl border border-orange-100 bg-white p-6">
        <div class="text-lg font-extrabold tracking-tight">Consent Log</div>
        <div class="mt-1 text-sm text-zinc-600">Each entry is created when a coach completes pre-registration with the required consent checkbox.</div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-orange-100 text-xs font-extrabold uppercase tracking-wide text-zinc-600">
                        <th class="py-3 pr-4">Timestamp</th>
                        <th class="py-3 pr-4">Client</th>
                        <th class="py-3 pr-4">Coach</th>
                        <th class="py-3 pr-4">IP</th>
                        <th class="py-3 pr-0">Consent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$logs): ?>
                        <tr>
                            <td colspan="5" class="py-6 text-zinc-600">No consent logs yet.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($logs as $l): ?>
                        <tr class="border-b border-orange-50 align-top">
                            <td class="py-4 pr-4 whitespace-nowrap text-zinc-700"><?= h((string) $l['created_at']) ?></td>
                            <td class="py-4 pr-4 font-extrabold">Client #<?= h((string) $l['client_id']) ?></td>
                            <td class="py-4 pr-4 text-zinc-700"><?= h((string) $l['coach_name']) ?> <span class="text-zinc-500">(<?= h((string) $l['coach_email']) ?>)</span></td>
                            <td class="py-4 pr-4 text-zinc-700"><?= h((string) ($l['ip_address'] ?? '-')) ?></td>
                            <td class="py-4 pr-0 text-zinc-700">
                                <div class="max-w-xl">
                                    <?= h((string) $l['consent_text']) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>
