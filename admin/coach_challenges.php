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
    <!-- Page Header with Gradient Banner -->
    <div class="relative mb-8 overflow-hidden rounded-2xl bg-gradient-to-r from-slate-800 via-slate-700 to-indigo_bloom p-6 shadow-lg shadow-slate-300/30">
        <div class="absolute -right-8 -top-8 h-40 w-40 rounded-full bg-white/5"></div>
        <div class="absolute -bottom-6 -right-16 h-32 w-32 rounded-full bg-white/5"></div>
        <div class="relative flex items-center gap-4">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/10 backdrop-blur-sm">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </div>
            <div class="flex-1">
                <h1 class="text-2xl font-extrabold tracking-tight text-white sm:text-3xl">Coach Challenges</h1>
                <p class="mt-1 text-sm text-white/70">Manage global coach challenges and set the active one</p>
            </div>
            <div class="hidden sm:flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 backdrop-blur-sm">
                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
                <span class="text-sm font-bold text-white">Admin Panel</span>
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

    <!-- Challenges List -->
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-slate-100 to-slate-200">
                <svg class="h-6 w-6 text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <div>
                <div class="text-lg font-extrabold tracking-tight text-zinc-900">All Coach Challenges</div>
                <div class="mt-0.5 text-sm text-zinc-500">Active = currently used for weigh-ins • Completed = archived</div>
            </div>
        </div>

        <!-- Challenge Cards -->
        <div class="mt-6 space-y-3">
            <?php if (!$rows): ?>
                <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-gradient-to-br from-slate-50/50 to-zinc-50/50 py-12 px-6 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100">
                        <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-bold text-zinc-700">No Coach Challenges</h3>
                    <p class="mt-1 text-sm text-zinc-500">Create your first coach challenge below to get started.</p>
                </div>
            <?php endif; ?>

            <?php foreach ($rows as $r): ?>
                <?php
                    $isActive = $r['status'] === 'active';
                    $cardBorder = $isActive ? 'border-emerald-200' : 'border-slate-100';
                    $cardBg = $isActive ? 'bg-gradient-to-r from-emerald-50/50 to-green-50/30' : 'bg-white';
                ?>
                <div class="rounded-xl border <?= $cardBorder ?> <?= $cardBg ?> p-4 transition hover:shadow-md">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <div class="flex items-center gap-3 flex-1">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg <?= $isActive ? 'bg-emerald-100' : 'bg-slate-100' ?>">
                                <?php if ($isActive): ?>
                                    <svg class="h-5 w-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                <?php else: ?>
                                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                    </svg>
                                <?php endif; ?>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="font-bold text-zinc-900"><?= h((string) $r['name']) ?></h3>
                                    <?php if ($isActive): ?>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Active
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-600">
                                            Completed
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="mt-1 flex items-center gap-3 text-xs text-zinc-500">
                                    <span class="inline-flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <?= h((string) $r['start_date']) ?>
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <?= h((string) $r['duration_days']) ?> days
                                    </span>
                                </div>
                            </div>
                        </div>

                        <?php if ($isActive): ?>
                            <form method="post" class="flex-shrink-0" onsubmit="return confirm('End this active coach challenge? Coaches will not be able to log weigh-ins until a new one is created.')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="end_active" />
                                <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm font-bold text-red-700 transition hover:bg-red-100 hover:border-red-300">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                                    </svg>
                                    End Challenge
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Create New Challenge -->
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-100 to-purple-100">
                <svg class="h-6 w-6 text-indigo_bloom" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-extrabold tracking-tight text-zinc-900">Create New Coach Challenge</h2>
                <p class="mt-0.5 text-sm text-zinc-500">Creating a new challenge will automatically set it as active</p>
            </div>
        </div>

        <div class="mt-4 rounded-xl bg-amber-50 border border-amber-200 p-3 flex items-start gap-2">
            <svg class="h-5 w-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span class="text-sm text-amber-800">You may want to end the current active challenge first before creating a new one.</span>
        </div>

        <form method="post" class="mt-6 space-y-4" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="create" />

            <div>
                <label class="mb-2 flex items-center gap-2 text-sm font-semibold text-zinc-700" for="name">
                    <svg class="h-4 w-4 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    Challenge Name
                </label>
                <input id="name" name="name" type="text" required placeholder="e.g., January 2026 Coach Challenge" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium outline-none ring-indigo_bloom/20 transition focus:border-indigo_bloom focus:ring-4" />
            </div>

            <div>
                <label class="mb-2 flex items-center gap-2 text-sm font-semibold text-zinc-700" for="start_date">
                    <svg class="h-4 w-4 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Start Date
                </label>
                <input id="start_date" name="start_date" type="date" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium outline-none ring-indigo_bloom/20 transition focus:border-indigo_bloom focus:ring-4" />
            </div>

            <div>
                <label class="mb-2 flex items-center gap-2 text-sm font-semibold text-zinc-700" for="duration_days">
                    <svg class="h-4 w-4 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Duration (days)
                </label>
                <input id="duration_days" name="duration_days" type="number" min="1" max="30" value="10" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium outline-none ring-indigo_bloom/20 transition focus:border-indigo_bloom focus:ring-4" />
            </div>

            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-slate-800 to-indigo_bloom px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-slate-300/30 transition hover:shadow-xl hover:scale-[1.01] active:scale-[0.99]">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Create and Set Active
            </button>
        </form>
    </div>
</main>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>
