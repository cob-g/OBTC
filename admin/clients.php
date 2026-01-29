<?php
require __DIR__ . '/../app/bootstrap.php';
require_role('admin');

$errors = [];
$success = null;

if (isset($_GET['msg'])) {
    $msg = (string) $_GET['msg'];
    if ($msg === 'deleted') {
        $success = 'Client deleted.';
    } elseif ($msg === 'delete_failed') {
        $errors[] = 'Failed to delete client.';
    } elseif ($msg === 'not_found') {
        $errors[] = 'Client not found.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Invalid session. Please refresh and try again.';
    } else {
        $action = (string) ($_POST['action'] ?? '');
        if ($action === 'delete_client') {
            $clientId = (int) ($_POST['client_id'] ?? 0);
            if ($clientId <= 0) {
                redirect(url('/admin/clients.php?msg=not_found'));
            }

            try {
                $stmt = db()->prepare('SELECT id FROM clients WHERE id = ? LIMIT 1');
                $stmt->execute([(int) $clientId]);
                if (!$stmt->fetch()) {
                    redirect(url('/admin/clients.php?msg=not_found'));
                }

                $del = db()->prepare('DELETE FROM clients WHERE id = ?');
                $del->execute([(int) $clientId]);
                redirect(url('/admin/clients.php?msg=deleted'));
            } catch (Throwable $e) {
                redirect(url('/admin/clients.php?msg=delete_failed'));
            }
        }
    }
}

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

    <?php if ($success): ?>
        <div class="mb-5 rounded-2xl border border-green-200 bg-green-50 p-4 text-sm font-semibold text-green-800">
            <?= h($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4">
            <div class="text-sm font-extrabold text-red-800">Please fix the following:</div>
            <ul class="mt-2 list-disc pl-5 text-sm text-red-700">
                <?php foreach ($errors as $e): ?>
                    <li><?= h((string) $e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

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
                        <th class="py-3 pl-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$clients): ?>
                        <tr>
                            <td colspan="7" class="py-6 text-zinc-600">No clients yet.</td>
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
                            <td class="py-4 pl-4 text-right">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    <a class="inline-flex items-center justify-center rounded-xl border border-orange-100 bg-white px-3 py-2 text-xs font-extrabold text-zinc-700 hover:bg-orange-50" href="<?= h(url('/coach/client_details.php?id=' . (int) $c['id'])) ?>">View</a>
                                    <form method="post" onsubmit="return confirm('Delete this client? This will remove their check-ins and consent logs. This cannot be undone.');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete_client" />
                                        <input type="hidden" name="client_id" value="<?= h((string) $c['id']) ?>" />
                                        <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-extrabold text-red-700 hover:bg-red-100">Delete</button>
                                    </form>
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
