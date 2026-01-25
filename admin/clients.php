<?php
require __DIR__ . '/../app/bootstrap.php';
require_role('admin');

$clients = [];
try {
    if (db_has_column('clients', 'full_name')) {
        $stmt = db()->query('SELECT c.id, c.full_name, c.gender, c.age, c.height_ft, c.height_in, c.start_weight_lbs, c.waistline_in, c.bmi, c.bmi_category, c.registered_at, u.name AS coach_name, u.email AS coach_email FROM clients c INNER JOIN users u ON u.id = c.coach_user_id ORDER BY c.registered_at DESC, c.id DESC');
        $clients = $stmt->fetchAll();
    } else {
        $stmt = db()->query('SELECT c.id, c.gender, c.age, c.height_ft, c.height_in, c.start_weight_lbs, c.waistline_in, c.bmi, c.bmi_category, c.registered_at, u.name AS coach_name, u.email AS coach_email FROM clients c INNER JOIN users u ON u.id = c.coach_user_id ORDER BY c.registered_at DESC, c.id DESC');
        $clients = $stmt->fetchAll();
    }
} catch (Throwable $e) {
    $clients = [];
}

$page_title = 'Clients (Read-Only)';
require __DIR__ . '/../partials/layout_top.php';
require __DIR__ . '/../partials/nav.php';
?>

<main class="mx-auto max-w-6xl px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold tracking-tight">Clients (Read-Only)</h1>
        <p class="mt-1 text-sm text-zinc-600">Read-only list of all clients across coaches.</p>
    </div>

    <div class="rounded-2xl border border-orange-100 bg-white p-6">
        <div class="text-lg font-extrabold tracking-tight">Client List</div>
        <div class="mt-1 text-sm text-zinc-600">This list is system-level and read-only.</div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-orange-100 text-xs font-extrabold uppercase tracking-wide text-zinc-600">
                        <th class="py-3 pr-4">Client</th>
                        <th class="py-3 pr-4">Coach</th>
                        <th class="py-3 pr-4">Registered</th>
                        <th class="py-3 pr-4">Waistline</th>
                        <th class="py-3 pr-4">BMI</th>
                        <th class="py-3 pr-0">Category</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$clients): ?>
                        <tr>
                            <td colspan="6" class="py-6 text-zinc-600">No clients yet.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($clients as $c): ?>
                        <tr class="border-b border-orange-50">
                            <td class="py-4 pr-4 font-extrabold">
                                <?php $name = isset($c['full_name']) ? trim((string) $c['full_name']) : ''; ?>
                                <?= h($name !== '' ? $name : ('Client #' . (string) $c['id'])) ?>
                            </td>
                            <td class="py-4 pr-4 text-zinc-700"><?= h((string) $c['coach_name']) ?> <span class="text-zinc-500">(<?= h((string) $c['coach_email']) ?>)</span></td>
                            <td class="py-4 pr-4 text-zinc-700"><?= h((string) $c['registered_at']) ?></td>
                            <?php $wst = isset($c['waistline_in']) ? (float) $c['waistline_in'] : 0.0; ?>
                            <td class="py-4 pr-4 text-zinc-700"><?= $wst > 0 ? h((string) $c['waistline_in']) . ' in' : '-' ?></td>
                            <td class="py-4 pr-4 font-extrabold text-molten"><?= h((string) $c['bmi']) ?></td>
                            <td class="py-4 pr-0 text-zinc-800"><?= h((string) $c['bmi_category']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>
