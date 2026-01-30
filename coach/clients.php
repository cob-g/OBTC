<?php
require __DIR__ . '/../app/bootstrap.php';
require_role('coach');

$user = auth_user();
$page = (int) ($_GET['page'] ?? 1);
if ($page < 1) {
    $page = 1;
}
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
                redirect(url('/coach/clients.php?msg=not_found&page=' . (int) $page));
            }

            try {
                $stmt = db()->prepare('SELECT id FROM clients WHERE id = ? AND coach_user_id = ? LIMIT 1');
                $stmt->execute([(int) $clientId, (int) $user['id']]);
                if (!$stmt->fetch()) {
                    redirect(url('/coach/clients.php?msg=not_found&page=' . (int) $page));
                }

                $del = db()->prepare('DELETE FROM clients WHERE id = ? AND coach_user_id = ?');
                $del->execute([(int) $clientId, (int) $user['id']]);
                redirect(url('/coach/clients.php?msg=deleted&page=' . (int) $page));
            } catch (Throwable $e) {
                redirect(url('/coach/clients.php?msg=delete_failed&page=' . (int) $page));
            }
        }
    }
}
$perPage = 10;
$clients = [];
$totalClients = 0;
$totalPages = 1;

try {
    $countStmt = db()->prepare('SELECT COUNT(*) FROM clients WHERE coach_user_id = ?');
    $countStmt->execute([(int) $user['id']]);
    $totalClients = (int) $countStmt->fetchColumn();

    $totalPages = $perPage > 0 ? (int) ceil($totalClients / $perPage) : 1;
    if ($totalPages < 1) {
        $totalPages = 1;
    }
    if ($page > $totalPages) {
        $page = $totalPages;
    }

    $offset = ($page - 1) * $perPage;

    $select = db_has_column('clients', 'full_name')
        ? 'SELECT id, full_name, gender, age, height_ft, height_in, start_weight_lbs, waistline_in, bmi, bmi_category, registered_at FROM clients WHERE coach_user_id = ? ORDER BY registered_at DESC, id DESC LIMIT ? OFFSET ?'
        : 'SELECT id, gender, age, height_ft, height_in, start_weight_lbs, waistline_in, bmi, bmi_category, registered_at FROM clients WHERE coach_user_id = ? ORDER BY registered_at DESC, id DESC LIMIT ? OFFSET ?';
    $stmt = db()->prepare($select);
    $stmt->bindValue(1, (int) $user['id'], PDO::PARAM_INT);
    $stmt->bindValue(2, (int) $perPage, PDO::PARAM_INT);
    $stmt->bindValue(3, (int) $offset, PDO::PARAM_INT);
    $stmt->execute();
    $clients = $stmt->fetchAll();
} catch (Throwable $e) {
    $clients = [];
}

$page_title = 'Clients';
require __DIR__ . '/../partials/layout_top.php';
require __DIR__ . '/../partials/nav.php';
?>

<main class="mx-auto max-w-6xl px-4 py-8">
    <!-- Page Header with Gradient Banner -->
    <div class="relative mb-8 overflow-hidden rounded-2xl bg-gradient-to-r from-molten via-pumpkin to-amber-500 p-6 shadow-lg shadow-orange-200/50">
        <div class="absolute -right-8 -top-8 h-40 w-40 rounded-full bg-white/10"></div>
        <div class="absolute -bottom-6 -right-16 h-32 w-32 rounded-full bg-white/10"></div>
        <div class="relative flex items-center gap-4">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-sm">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h1 class="text-2xl font-extrabold tracking-tight text-white sm:text-3xl">My Clients</h1>
                <p class="mt-1 text-sm text-white/80">Manage and track your registered clients</p>
            </div>
            <div class="hidden sm:flex items-center gap-2 rounded-xl bg-white/20 px-4 py-2 backdrop-blur-sm">
                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
                <span class="text-sm font-bold text-white"><?= h((string) $totalClients) ?> Total</span>
            </div>
        </div>
    </div>

    <!-- Success Alert -->
    <?php if ($success): ?>
        <div class="mb-6 flex items-center gap-3 rounded-2xl border border-green-200 bg-gradient-to-r from-green-50 to-emerald-50 p-4 shadow-sm">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-500/10">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <div class="text-sm font-bold text-green-800"><?= h($success) ?></div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Error Alert -->
    <?php if ($errors): ?>
        <div class="mb-6 rounded-2xl border border-red-200 bg-gradient-to-r from-red-50 to-rose-50 p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-500/10">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="text-sm font-extrabold text-red-800">Please fix the following:</div>
            </div>
            <ul class="mt-3 ml-13 list-disc pl-5 text-sm text-red-700">
                <?php foreach ($errors as $e): ?>
                    <li><?= h((string) $e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="rounded-2xl border border-orange-100 bg-white p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-amber-100 to-orange-100">
                    <svg class="h-6 w-6 text-molten" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-lg font-extrabold tracking-tight text-zinc-900">Client List</div>
                    <div class="mt-0.5 text-sm text-zinc-500">Track progress and manage daily weigh-ins</div>
                </div>
            </div>
            <a href="<?= h(url('/coach/pre_registration.php?step=1')) ?>" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-molten to-pumpkin px-4 py-2.5 text-sm font-bold text-white shadow-md shadow-orange-200/50 transition hover:shadow-lg hover:scale-[1.02] active:scale-[0.98]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Add Client
            </a>
        </div>

        <!-- Client Cards -->
        <div class="mt-6 space-y-4">
            <?php if (!$clients): ?>
                <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-orange-200 bg-gradient-to-br from-orange-50/50 to-amber-50/50 py-12 px-6 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-orange-100">
                        <svg class="h-8 w-8 text-orange-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-bold text-zinc-700">No Clients Yet</h3>
                    <p class="mt-1 text-sm text-zinc-500">Use "Add Client" to pre-register your first challenger.</p>
                    <a href="<?= h(url('/coach/pre_registration.php?step=1')) ?>" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-molten to-pumpkin px-4 py-2 text-sm font-bold text-white shadow-md shadow-orange-200/50 hover:shadow-lg">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Get Started
                    </a>
                </div>
            <?php endif; ?>

            <?php foreach ($clients as $c): ?>
                <?php 
                    $name = isset($c['full_name']) ? trim((string) $c['full_name']) : '';
                    if ($name === '') {
                        $name = 'Client #' . (string) $c['id'];
                    }
                    $weight = isset($c['start_weight_lbs']) ? (float) $c['start_weight_lbs'] : 0.0;
                    $bmi = isset($c['bmi']) ? (float) $c['bmi'] : 0.0;
                    $waistline = isset($c['waistline_in']) ? (float) $c['waistline_in'] : 0.0;
                    $category = isset($c['bmi_category']) ? trim((string) $c['bmi_category']) : '';
                    
                    // BMI category colors
                    $categoryColors = [
                        'Underweight' => 'bg-blue-100 text-blue-700',
                        'Normal weight' => 'bg-emerald-100 text-emerald-700',
                        'Overweight' => 'bg-amber-100 text-amber-700',
                        'Obese' => 'bg-red-100 text-red-700',
                    ];
                    $categoryClass = $categoryColors[$category] ?? 'bg-slate-100 text-slate-700';
                ?>
                <div class="rounded-2xl border border-orange-100 bg-gradient-to-r from-white to-orange-50/30 p-5 transition hover:shadow-md hover:border-orange-200">
                    <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                        <!-- Client Info -->
                        <div class="flex items-center gap-4 flex-1 min-w-0">
                            <!-- Avatar -->
                            <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-molten to-pumpkin text-white font-bold text-lg shadow-md">
                                <?= strtoupper(substr($name, 0, 1)) ?>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="font-extrabold text-zinc-900 truncate"><?= h($name) ?></h3>
                                    <?php if ($category !== ''): ?>
                                        <span class="inline-flex items-center gap-1 rounded-full <?= $categoryClass ?> px-2.5 py-0.5 text-xs font-bold">
                                            <?= h($category) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="mt-1 flex items-center gap-3 text-xs text-zinc-500 flex-wrap">
                                    <span class="inline-flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <?= h((string) $c['gender']) ?>, <?= h((string) $c['age']) ?>y
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h10v10H7V7z"/></svg>
                                        <?= h((string) $c['height_ft']) ?>'<?= h((string) $c['height_in']) ?>"
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <?= h((string) $c['registered_at']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Stats & Actions -->
                        <div class="flex items-center gap-3 flex-wrap">
                            <!-- Stats Pills -->
                            <div class="flex items-center gap-2">
                                <?php if ($weight > 0): ?>
                                    <div class="flex flex-col items-center rounded-xl bg-blue-50 px-3 py-2">
                                        <span class="text-xs text-blue-600 font-medium">Weight</span>
                                        <span class="text-sm font-bold text-blue-700"><?= h(number_format($weight, 1)) ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($waistline > 0): ?>
                                    <div class="flex flex-col items-center rounded-xl bg-amber-50 px-3 py-2">
                                        <span class="text-xs text-amber-600 font-medium">Waist</span>
                                        <span class="text-sm font-bold text-amber-700"><?= h(number_format($waistline, 1)) ?>"</span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($bmi > 0): ?>
                                    <div class="flex flex-col items-center rounded-xl bg-purple-50 px-3 py-2">
                                        <span class="text-xs text-purple-600 font-medium">BMI</span>
                                        <span class="text-sm font-bold text-purple-700"><?= h(number_format($bmi, 1)) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex items-center gap-2">
                                <a class="inline-flex items-center gap-1.5 rounded-xl border border-orange-200 bg-gradient-to-r from-orange-50 to-amber-50 px-3 py-2 text-xs font-bold text-zinc-700 transition hover:bg-orange-100 hover:border-orange-300" href="<?= h(url('/coach/client_details.php?id=' . (int) $c['id'])) ?>">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    View
                                </a>
                                <form method="post" onsubmit="return confirm('Delete this client? This will remove their check-ins and consent logs. This cannot be undone.');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete_client" />
                                    <input type="hidden" name="client_id" value="<?= h((string) $c['id']) ?>" />
                                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-700 transition hover:bg-red-100 hover:border-red-300">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4 rounded-xl bg-gradient-to-r from-orange-50 to-amber-50 p-4">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-molten" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="text-sm font-bold text-zinc-700">Page <span class="text-molten"><?= h((string) $page) ?></span> of <span class="text-molten"><?= h((string) $totalPages) ?></span></span>
                </div>
                <div class="flex items-center gap-2">
                    <?php if ($page > 1): ?>
                        <a class="inline-flex items-center gap-1.5 rounded-xl border border-orange-200 bg-white px-4 py-2 text-sm font-bold text-zinc-700 shadow-sm transition hover:bg-orange-50 hover:border-orange-300" href="<?= h(url('/coach/clients.php?page=' . (int) ($page - 1))) ?>">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Previous
                        </a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-molten to-pumpkin px-4 py-2 text-sm font-bold text-white shadow-md shadow-orange-200/50 transition hover:shadow-lg" href="<?= h(url('/coach/clients.php?page=' . (int) ($page + 1))) ?>">
                            Next
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>
