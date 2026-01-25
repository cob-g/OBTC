<?php
require __DIR__ . '/../app/bootstrap.php';
require_role('coach');

$user = auth_user();
$clients = [];

try {
    $select = db_has_column('clients', 'full_name')
        ? 'SELECT id, full_name, gender, age, height_ft, height_in, start_weight_lbs, waistline_in, bmi, bmi_category, registered_at FROM clients WHERE coach_user_id = ? ORDER BY registered_at DESC, id DESC'
        : 'SELECT id, gender, age, height_ft, height_in, start_weight_lbs, waistline_in, bmi, bmi_category, registered_at FROM clients WHERE coach_user_id = ? ORDER BY registered_at DESC, id DESC';
    $stmt = db()->prepare($select);
    $stmt->execute([(int) $user['id']]);
    $clients = $stmt->fetchAll();
} catch (Throwable $e) {
    $clients = [];
}

$page_title = 'Clients';
require __DIR__ . '/../partials/layout_top.php';
require __DIR__ . '/../partials/nav.php';
?>

<main class="mx-auto max-w-6xl px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold tracking-tight">Clients</h1>
        <p class="mt-1 text-sm text-zinc-600">Registered clients for this coach.</p>
    </div>

    <div class="rounded-2xl border border-orange-100 bg-white p-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <div class="text-lg font-extrabold tracking-tight">Client List</div>
                <div class="mt-1 text-sm text-zinc-600">This is read-only for now; daily weigh-ins and progress views come next.</div>
            </div>
            <a href="<?= h(url('/coach/pre_registration.php?step=1')) ?>" class="rounded-xl bg-molten px-4 py-3 text-sm font-extrabold text-white hover:bg-pumpkin">Add Client</a>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-orange-100 text-xs font-extrabold uppercase tracking-wide text-zinc-600">
                        <th class="py-3 pr-4">Client</th>
                        <th class="py-3 pr-4">Registered</th>
                        <th class="py-3 pr-4">Stats</th>
                        <th class="py-3 pr-4">BMI</th>
                        <th class="py-3 pr-0">Category</th>
                        <th class="py-3 pl-4 text-right">Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$clients): ?>
                        <tr>
                            <td colspan="6" class="py-6 text-zinc-600">No clients yet. Use “Add Client” to pre-register.</td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($clients as $c): ?>
                        <tr class="border-b border-orange-50">
                            <td class="py-4 pr-4 font-extrabold text-zinc-900">
                                <?php $name = isset($c['full_name']) ? trim((string) $c['full_name']) : ''; ?>
                                <?= h($name !== '' ? $name : ('Client #' . (string) $c['id'])) ?>
                            </td>
                            <td class="py-4 pr-4 text-zinc-700"><?= h((string) $c['registered_at']) ?></td>
                            <td class="py-4 pr-4 text-zinc-700">
                                <?= h((string) $c['gender']) ?>, <?= h((string) $c['age']) ?>y
                                • <?= h((string) $c['height_ft']) ?>ft <?= h((string) $c['height_in']) ?>in
                                <?php $w = isset($c['start_weight_lbs']) ? (float) $c['start_weight_lbs'] : 0.0; ?>
                                • <?= $w > 0 ? h((string) $c['start_weight_lbs']) . ' lbs' : '-' ?>
                            </td>
                            <?php $b = isset($c['bmi']) ? (float) $c['bmi'] : 0.0; ?>
                            <td class="py-4 pr-4 font-extrabold text-molten"><?= $b > 0 ? h((string) $c['bmi']) : '-' ?></td>
                            <?php $cat = isset($c['bmi_category']) ? trim((string) $c['bmi_category']) : ''; ?>
                            <td class="py-4 pr-0 text-zinc-800"><?= $cat !== '' ? h($cat) : '-' ?></td>
                            <td class="py-4 pl-4 text-right">
                                <a class="inline-flex items-center justify-center rounded-xl border border-orange-100 bg-white px-3 py-2 text-xs font-extrabold text-zinc-700 hover:bg-orange-50" href="<?= h(url('/coach/client_details.php?id=' . (int) $c['id'])) ?>">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>
