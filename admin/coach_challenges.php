<?php
require __DIR__ . '/../app/bootstrap.php';
require_role('admin');

$errors = [];
$success = null;

$flash = (string) ($_GET['success'] ?? '');
if ($flash === 'end_active') {
    $success = 'Active coach challenge marked as completed.';
}
if ($flash === 'create') {
    $success = 'New coach challenge created and set as active.';
}
if ($flash === 'create_existing') {
    $success = 'An active coach challenge with the same details already exists.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Invalid session. Please refresh and try again.';
    } else {
        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'end_active') {
            try {
                $stmt = db()->prepare("UPDATE coach_challenges SET status = 'completed', updated_at = CURRENT_TIMESTAMP WHERE status = 'active' ORDER BY id DESC LIMIT 1");
                $stmt->execute();
                redirect(url('/admin/coach_challenges.php?success=end_active'));
            } catch (Throwable $e) {
                $errors[] = 'Failed to end active coach challenge.';
            }
        }

        if ($action === 'create') {
            $name = trim((string) ($_POST['name'] ?? ''));
            $startDate = trim((string) ($_POST['start_date'] ?? ''));
            $durationDays = (int) ($_POST['duration_days'] ?? 10);

            if ($name === '') {
                $errors[] = 'Challenge name is required.';
            }
            if ($startDate === '') {
                $errors[] = 'Start date is required.';
            } elseif (!DateTimeImmutable::createFromFormat('Y-m-d', $startDate)) {
                $errors[] = 'Start date must be a valid date (YYYY-MM-DD).';
            }
            if ($durationDays < 1 || $durationDays > 30) {
                $errors[] = 'Duration must be between 1 and 30 days.';
            }

            if (!$errors) {
                try {
                    db()->beginTransaction();

                    $check = db()->prepare("SELECT id FROM coach_challenges WHERE name = ? AND start_date = ? AND duration_days = ? AND status = 'active' ORDER BY id DESC LIMIT 1");
                    $check->execute([$name, $startDate, (int) $durationDays]);
                    $existing = $check->fetch();

                    if ($existing) {
                        db()->commit();
                        redirect(url('/admin/coach_challenges.php?success=create_existing'));
                    }

                    $ins = db()->prepare('INSERT INTO coach_challenges (name, start_date, duration_days, status) VALUES (?, ?, ?, ?)');
                    $ins->execute([$name, $startDate, (int) $durationDays, 'active']);

                    db()->commit();
                    redirect(url('/admin/coach_challenges.php?success=create'));
                } catch (Throwable $e) {
                    if (db()->inTransaction()) {
                        db()->rollBack();
                    }
                    $errors[] = 'Failed to create new coach challenge.';
                }
            }
        }
    }
}

$rows = [];
try {
    $stmt = db()->query('SELECT * FROM coach_challenges ORDER BY start_date DESC, id DESC');
    $rows = $stmt->fetchAll();
} catch (Throwable $e) {
    $rows = [];
}

$page_title = 'Coach Challenges';
require __DIR__ . '/../partials/layout_top.php';
require __DIR__ . '/../partials/nav.php';
?>

<main class="mx-auto max-w-4xl px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold tracking-tight">Coach Challenges</h1>
        <p class="mt-1 text-sm text-zinc-600">Manage global coach challenges and set the active one.</p>
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
        <div class="flex items-center justify-between gap-3">
            <div>
                <div class="text-lg font-extrabold tracking-tight">All Coach Challenges</div>
                <div class="mt-1 text-sm text-zinc-600">Status: active = currently used for weigh-ins; completed = archived.</div>
            </div>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-orange-100 text-xs font-extrabold uppercase tracking-wide text-zinc-600">
                        <th class="py-3 pr-4">Name</th>
                        <th class="py-3 pr-4">Start Date</th>
                        <th class="py-3 pr-4">Duration</th>
                        <th class="py-3 pr-4">Status</th>
                        <th class="py-3 pr-0">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$rows): ?>
                        <tr>
                            <td colspan="5" class="py-6 text-zinc-600">No coach challenges found.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($rows as $r): ?>
                        <tr class="border-b border-orange-50">
                            <td class="py-4 pr-4 font-extrabold text-zinc-900"><?= h((string) $r['name']) ?></td>
                            <td class="py-4 pr-4 text-zinc-700"><?= h((string) $r['start_date']) ?></td>
                            <td class="py-4 pr-4 text-zinc-700"><?= h((string) $r['duration_days']) ?> days</td>
                            <td class="py-4 pr-4">
                                <?php if ($r['status'] === 'active'): ?>
                                    <span class="inline-flex rounded-lg bg-green-100 px-2 py-1 text-xs font-extrabold text-green-800">Active</span>
                                <?php else: ?>
                                    <span class="inline-flex rounded-lg bg-zinc-100 px-2 py-1 text-xs font-extrabold text-zinc-800">Completed</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 pr-0">
                                <?php if ($r['status'] === 'active'): ?>
                                    <form method="post" class="inline" onsubmit="return confirm('End this active coach challenge? Coaches will not be able to log weigh-ins until a new one is created.')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="end_active" />
                                        <button type="submit" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-extrabold text-red-800 hover:bg-red-100">End</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 rounded-2xl border border-orange-100 bg-white p-6">
        <h2 class="text-xl font-extrabold tracking-tight">Create New Coach Challenge</h2>
        <p class="mt-1 text-sm text-zinc-600">Creating a new challenge will automatically set it as active. You may want to end the current active challenge first.</p>

        <form method="post" class="mt-6 space-y-4" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="create" />

            <div>
                <label class="mb-1 block text-sm font-semibold" for="name">Challenge Name</label>
                <input id="name" name="name" type="text" required class="w-full rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm outline-none ring-molten/20 focus:border-molten focus:ring-4" />
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold" for="start_date">Start Date (YYYY-MM-DD)</label>
                <input id="start_date" name="start_date" type="date" required class="w-full rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm outline-none ring-molten/20 focus:border-molten focus:ring-4" />
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold" for="duration_days">Duration (days)</label>
                <input id="duration_days" name="duration_days" type="number" min="1" max="30" value="10" required class="w-full rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm outline-none ring-molten/20 focus:border-molten focus:ring-4" />
            </div>

            <button type="submit" class="w-full rounded-xl bg-molten px-4 py-3 text-sm font-extrabold text-white hover:bg-pumpkin">
                Create and Set Active
            </button>
        </form>
    </div>
</main>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>
