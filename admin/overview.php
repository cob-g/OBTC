<?php
require __DIR__ . '/../app/bootstrap.php';
require_role('admin');

function truncate_decimals($value, $decimals)
{
    $decimals = max(0, (int) $decimals);
    $factor = pow(10, $decimals);
    if ($factor <= 0) {
        return (float) $value;
    }
    $v = (float) $value;
    if ($v >= 0) {
        return floor($v * $factor) / $factor;
    }
    return ceil($v * $factor) / $factor;
}

function table_exists($table)
{
    try {
        $stmt = db()->prepare('SHOW TABLES LIKE ?');
        $stmt->execute([(string) $table]);
        return $stmt->fetch() ? true : false;
    } catch (Throwable $e) {
        return false;
    }
}

$totalCoaches = 0;
$totalParticipants = 0;
$activeParticipants = 0;
$challengeStartDate = null;
$hasChallengeStart = db_has_column('clients', 'challenge_start_date');

try {
    $stmt = db()->query("SELECT COUNT(*) AS c FROM users WHERE role = 'coach'");
    $totalCoaches = (int) (($stmt->fetch()['c'] ?? 0));
} catch (Throwable $e) {
    $totalCoaches = 0;
}

try {
    $stmt = db()->query('SELECT COUNT(*) AS c FROM clients');
    $totalParticipants = (int) (($stmt->fetch()['c'] ?? 0));
} catch (Throwable $e) {
    $totalParticipants = 0;
}

try {
    $stmt = db()->query('SELECT COUNT(*) AS c FROM clients WHERE start_weight_lbs > 0');
    $activeParticipants = (int) (($stmt->fetch()['c'] ?? 0));
} catch (Throwable $e) {
    $activeParticipants = 0;
}

if ($hasChallengeStart) {
    try {
        $stmt = db()->query('SELECT MIN(challenge_start_date) AS d FROM clients WHERE challenge_start_date IS NOT NULL');
        $challengeStartDate = (string) (($stmt->fetch()['d'] ?? '') ?: '');
        if ($challengeStartDate === '') {
            $challengeStartDate = null;
        }
    } catch (Throwable $e) {
        $challengeStartDate = null;
    }
}

$latestCycle = null;
$latestCycleSummary = null;
$hasHistory = table_exists('challenge_cycles') && table_exists('challenge_clients') && table_exists('challenge_checkins');

if ($hasHistory) {
    try {
        $latestCycle = db()->query('SELECT id, label, started_at, ended_at, created_at FROM challenge_cycles ORDER BY id DESC LIMIT 1')->fetch();
    } catch (Throwable $e) {
        $latestCycle = null;
    }

    if ($latestCycle) {
        $cycleId = (int) $latestCycle['id'];
        try {
            $stmt = db()->prepare(
                'SELECT '
                . 'COUNT(*) AS participants, '
                . 'SUM(CASE WHEN day10_front_photo_path IS NOT NULL AND day10_front_photo_path <> "" AND day10_side_photo_path IS NOT NULL AND day10_side_photo_path <> "" THEN 1 ELSE 0 END) AS has_day10_photos '
                . 'FROM challenge_clients WHERE cycle_id = ?'
            );
            $stmt->execute([$cycleId]);
            $base = $stmt->fetch();
            $participants = (int) ($base['participants'] ?? 0);
            $hasDay10PhotosCount = (int) ($base['has_day10_photos'] ?? 0);

            $stmt = db()->prepare('SELECT COUNT(DISTINCT client_id) AS day10_checkins FROM challenge_checkins WHERE cycle_id = ? AND day_number = 10');
            $stmt->execute([$cycleId]);
            $day10Checkins = (int) (($stmt->fetch()['day10_checkins'] ?? 0));

            $completed = min($hasDay10PhotosCount, $day10Checkins);
            $completionRate = $participants > 0 ? (($completed / $participants) * 100.0) : 0.0;
            $completionRate = truncate_decimals($completionRate, 2);

            $latestCycleSummary = [
                'participants' => $participants,
                'completed' => $completed,
                'completion_rate' => $completionRate,
            ];
        } catch (Throwable $e) {
            $latestCycleSummary = null;
        }
    }
}

$page_title = 'Admin Overview';
require __DIR__ . '/../partials/layout_top.php';
require __DIR__ . '/../partials/nav.php';
?>

<main class="mx-auto max-w-6xl px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold tracking-tight">Overview</h1>
        <p class="mt-1 text-sm text-zinc-600">Key counts and the latest archived challenge snapshot.</p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-2xl border border-orange-100 bg-white p-5">
            <div class="text-sm font-semibold text-zinc-600">Total Coaches</div>
            <div class="mt-2 text-3xl font-extrabold text-molten"><?= h((string) $totalCoaches) ?></div>
        </div>
        <div class="rounded-2xl border border-orange-100 bg-white p-5">
            <div class="text-sm font-semibold text-zinc-600">Total Participants</div>
            <div class="mt-2 text-3xl font-extrabold text-molten"><?= h((string) $totalParticipants) ?></div>
        </div>
        <div class="rounded-2xl border border-orange-100 bg-white p-5">
            <div class="text-sm font-semibold text-zinc-600">Current Challenge</div>
            <div class="mt-2 text-3xl font-extrabold text-molten"><?= $hasChallengeStart && $challengeStartDate ? h($challengeStartDate) : '-' ?></div>
        </div>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-2xl border border-orange-100 bg-white p-5">
            <div class="text-sm font-semibold text-zinc-600">Active Participants</div>
            <div class="mt-2 text-3xl font-extrabold text-molten"><?= h((string) $activeParticipants) ?></div>
            <div class="mt-1 text-xs text-zinc-500">Clients with baseline set (start weight &gt; 0)</div>
        </div>

        <div class="rounded-2xl border border-orange-100 bg-white p-5 sm:col-span-2">
            <div class="text-sm font-semibold text-zinc-600">Latest Archived Cycle</div>

            <?php if (!$hasHistory): ?>
                <div class="mt-2 text-sm text-zinc-600">No history tables yet. Run Reset Challenge Data at least once.</div>
            <?php elseif (!$latestCycle): ?>
                <div class="mt-2 text-sm text-zinc-600">No archived cycles yet.</div>
            <?php else: ?>
                <?php $label = trim((string) ($latestCycle['label'] ?? '')); ?>
                <div class="mt-2 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <div class="text-lg font-extrabold tracking-tight"><?= $label !== '' ? h($label) : ('Cycle #' . h((string) $latestCycle['id'])) ?></div>
                        <div class="text-xs text-zinc-600"><?= h((string) $latestCycle['started_at']) ?> → <?= h((string) $latestCycle['ended_at']) ?></div>
                    </div>
                    <a class="mt-3 inline-flex items-center justify-center rounded-xl border border-orange-100 bg-white px-4 py-2 text-xs font-extrabold text-zinc-700 hover:bg-orange-50 sm:mt-0" href="<?= h(url('/admin/reports.php?cycle_id=' . (int) $latestCycle['id'])) ?>">View Reports</a>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-orange-100 bg-orange-50 p-4">
                        <div class="text-xs font-extrabold uppercase tracking-wide text-zinc-600">Participants</div>
                        <div class="mt-2 text-2xl font-extrabold text-molten"><?= h((string) (($latestCycleSummary['participants'] ?? 0))) ?></div>
                    </div>
                    <div class="rounded-2xl border border-orange-100 bg-orange-50 p-4">
                        <div class="text-xs font-extrabold uppercase tracking-wide text-zinc-600">Completed</div>
                        <div class="mt-2 text-2xl font-extrabold text-molten"><?= h((string) (($latestCycleSummary['completed'] ?? 0))) ?></div>
                    </div>
                    <div class="rounded-2xl border border-orange-100 bg-orange-50 p-4">
                        <div class="text-xs font-extrabold uppercase tracking-wide text-zinc-600">Completion Rate</div>
                        <div class="mt-2 text-2xl font-extrabold text-molten"><?= h(number_format((float) (($latestCycleSummary['completion_rate'] ?? 0)), 2, '.', '')) ?>%</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>
