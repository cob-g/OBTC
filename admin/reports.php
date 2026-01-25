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
        $row = $stmt->fetch();
        return $row ? true : false;
    } catch (Throwable $e) {
        return false;
    }
}

function csv_string_from_rows($rows)
{
    $out = fopen('php://temp', 'w+');
    $first = true;
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        if ($first) {
            fputcsv($out, array_keys($row));
            $first = false;
        }
        fputcsv($out, array_values($row));
    }
    rewind($out);
    $csv = stream_get_contents($out);
    fclose($out);
    return (string) $csv;
}

$hasHistory = table_exists('challenge_cycles') && table_exists('challenge_clients') && table_exists('challenge_checkins');

$cycles = [];
$selectedCycle = null;
$cycleSummary = null;
$dailyAverages = [];
$topPerformers = [];

$cyclePage = max(1, (int) ($_GET['page'] ?? 1));
$cyclesPerPage = 10;
$totalCycles = 0;
$totalPages = 1;

$cycleId = (int) ($_GET['cycle_id'] ?? 0);
$export = (string) ($_GET['export'] ?? '');

if ($cycleId > 0 && $export !== '') {
    $hasHistory = table_exists('challenge_cycles') && table_exists('challenge_clients') && table_exists('challenge_checkins');
    if ($hasHistory) {
        try {
            $stmt = db()->prepare('SELECT id FROM challenge_cycles WHERE id = ? LIMIT 1');
            $stmt->execute([$cycleId]);
            $cycleRow = $stmt->fetch();
        } catch (Throwable $e) {
            $cycleRow = null;
        }

        if ($cycleRow) {
            try {
                $timestamp = date('Ymd_His');

                if ($export === 'cycle_summary_csv') {
                    $stmt = db()->prepare(
                        'SELECT '
                        . 'c.client_id, c.full_name, c.coach_user_id, c.gender, c.age, c.height_ft, c.height_in, '
                        . 'c.start_weight_lbs, c.waistline_in, c.bmi, c.bmi_category, '
                        . 'c.front_photo_path, c.side_photo_path, c.day10_front_photo_path, c.day10_side_photo_path, '
                        . 'c.challenge_start_date, c.registered_at, '
                        . 'd10.weight_lbs AS day10_weight '
                        . 'FROM challenge_clients c '
                        . 'LEFT JOIN challenge_checkins d10 ON d10.cycle_id = c.cycle_id AND d10.client_id = c.client_id AND d10.day_number = 10 '
                        . 'WHERE c.cycle_id = ? '
                        . 'ORDER BY c.client_id ASC'
                    );
                    $stmt->execute([$cycleId]);
                    $rows = $stmt->fetchAll();

                    $exportRows = [];
                    foreach ($rows as $r) {
                        $start = isset($r['start_weight_lbs']) ? (float) $r['start_weight_lbs'] : 0.0;
                        $end = isset($r['day10_weight']) ? (float) $r['day10_weight'] : 0.0;
                        $lossLbs = 0.0;
                        $lossPct = 0.0;
                        if ($start > 0 && $end > 0) {
                            $lossLbs = max(0.0, $start - $end);
                            $lossPct = ($lossLbs / $start) * 100.0;
                        }
                        $exportRows[] = [
                            'client_id' => (int) $r['client_id'],
                            'full_name' => (string) ($r['full_name'] ?? ''),
                            'coach_user_id' => (int) $r['coach_user_id'],
                            'gender' => (string) ($r['gender'] ?? ''),
                            'age' => isset($r['age']) ? (int) $r['age'] : null,
                            'height_ft' => isset($r['height_ft']) ? (int) $r['height_ft'] : null,
                            'height_in' => isset($r['height_in']) ? (int) $r['height_in'] : null,
                            'start_weight_lbs' => $start > 0 ? number_format($start, 2, '.', '') : '',
                            'day10_weight_lbs' => $end > 0 ? number_format($end, 2, '.', '') : '',
                            'loss_lbs' => $lossLbs > 0 ? number_format($lossLbs, 2, '.', '') : '',
                            'loss_pct' => $lossPct > 0 ? number_format($lossPct, 3, '.', '') : '',
                            'waistline_in' => isset($r['waistline_in']) ? number_format((float) $r['waistline_in'], 2, '.', '') : '',
                            'bmi' => isset($r['bmi']) ? number_format((float) $r['bmi'], 2, '.', '') : '',
                            'bmi_category' => (string) ($r['bmi_category'] ?? ''),
                            'has_day1_photos' => ((string) ($r['front_photo_path'] ?? '') !== '' && (string) ($r['side_photo_path'] ?? '') !== '') ? 1 : 0,
                            'has_day10_photos' => ((string) ($r['day10_front_photo_path'] ?? '') !== '' && (string) ($r['day10_side_photo_path'] ?? '') !== '') ? 1 : 0,
                            'challenge_start_date' => (string) ($r['challenge_start_date'] ?? ''),
                            'registered_at' => (string) ($r['registered_at'] ?? ''),
                        ];
                    }

                    $csv = csv_string_from_rows($exportRows);
                    header('Content-Type: text/csv; charset=UTF-8');
                    header('Content-Disposition: attachment; filename="cycle_' . (int) $cycleId . '_summary_' . $timestamp . '.csv"');
                    header('Cache-Control: no-store, max-age=0');
                    echo $csv;
                    exit;
                }

                if ($export === 'cycle_checkins_csv') {
                    $stmt = db()->prepare(
                        'SELECT '
                        . 'cc.id, cc.client_id, c.full_name, cc.coach_user_id, cc.day_number, cc.weight_lbs, cc.recorded_at, cc.created_at '
                        . 'FROM challenge_checkins cc '
                        . 'LEFT JOIN challenge_clients c ON c.cycle_id = cc.cycle_id AND c.client_id = cc.client_id '
                        . 'WHERE cc.cycle_id = ? '
                        . 'ORDER BY cc.client_id ASC, cc.day_number ASC, cc.id ASC'
                    );
                    $stmt->execute([$cycleId]);
                    $rows = $stmt->fetchAll();

                    $exportRows = [];
                    foreach ($rows as $r) {
                        $exportRows[] = [
                            'id' => (int) $r['id'],
                            'client_id' => (int) $r['client_id'],
                            'full_name' => (string) ($r['full_name'] ?? ''),
                            'coach_user_id' => (int) $r['coach_user_id'],
                            'day_number' => (int) $r['day_number'],
                            'weight_lbs' => number_format((float) $r['weight_lbs'], 2, '.', ''),
                            'recorded_at' => (string) $r['recorded_at'],
                            'created_at' => (string) $r['created_at'],
                        ];
                    }

                    $csv = csv_string_from_rows($exportRows);
                    header('Content-Type: text/csv; charset=UTF-8');
                    header('Content-Disposition: attachment; filename="cycle_' . (int) $cycleId . '_checkins_' . $timestamp . '.csv"');
                    header('Cache-Control: no-store, max-age=0');
                    echo $csv;
                    exit;
                }
            } catch (Throwable $e) {
            }
        }
    }
}

if ($hasHistory) {
    try {
        $stmt = db()->query('SELECT COUNT(*) AS c FROM challenge_cycles');
        $totalCycles = (int) (($stmt->fetch()['c'] ?? 0));
        $totalPages = max(1, (int) ceil($totalCycles / $cyclesPerPage));
    } catch (Throwable $e) {
        $totalCycles = 0;
        $totalPages = 1;
    }

    if ($cycleId > 0) {
        try {
            $stmt = db()->prepare('SELECT COUNT(*) AS c FROM challenge_cycles WHERE id > ?');
            $stmt->execute([$cycleId]);
            $before = (int) (($stmt->fetch()['c'] ?? 0));
            $cyclePage = (int) floor($before / $cyclesPerPage) + 1;
        } catch (Throwable $e) {
            // keep requested page
        }
    }

    $cyclePage = max(1, min($cyclePage, $totalPages));
    $offset = ($cyclePage - 1) * $cyclesPerPage;

    try {
        $stmt = db()->prepare('SELECT id, label, started_at, ended_at, created_at FROM challenge_cycles ORDER BY id DESC LIMIT ? OFFSET ?');
        $stmt->bindValue(1, (int) $cyclesPerPage, PDO::PARAM_INT);
        $stmt->bindValue(2, (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
        $cycles = $stmt->fetchAll();
    } catch (Throwable $e) {
        $cycles = [];
    }

    if ($cycleId > 0) {
        try {
            $stmt = db()->prepare('SELECT id, label, started_at, ended_at, created_at FROM challenge_cycles WHERE id = ? LIMIT 1');
            $stmt->execute([$cycleId]);
            $selectedCycle = $stmt->fetch();
        } catch (Throwable $e) {
            $selectedCycle = null;
        }

        if ($selectedCycle) {
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

                $stmt = db()->prepare(
                    'SELECT cc.client_id, '
                    . 'MAX(CASE WHEN cc.day_number = 10 THEN cc.weight_lbs END) AS day10_weight, '
                    . 'c.start_weight_lbs AS start_weight '
                    . 'FROM challenge_clients c '
                    . 'LEFT JOIN challenge_checkins cc ON cc.cycle_id = c.cycle_id AND cc.client_id = c.client_id '
                    . 'WHERE c.cycle_id = ? '
                    . 'GROUP BY cc.client_id, c.start_weight_lbs'
                );
                $stmt->execute([$cycleId]);
                $lossRows = $stmt->fetchAll();
                $sumLossPct = 0.0;
                $countLossPct = 0;
                foreach ($lossRows as $r) {
                    $start = (float) ($r['start_weight'] ?? 0);
                    $end = (float) ($r['day10_weight'] ?? 0);
                    if ($start <= 0 || $end <= 0) {
                        continue;
                    }
                    $loss = max(0, $start - $end);
                    $pct = ($loss / $start) * 100.0;
                    $sumLossPct += $pct;
                    $countLossPct++;
                }
                $avgLossPct = $countLossPct > 0 ? ($sumLossPct / $countLossPct) : 0.0;
                $avgLossPct = truncate_decimals($avgLossPct, 3);

                $cycleSummary = [
                    'participants' => $participants,
                    'completed' => $completed,
                    'completion_rate' => $completionRate,
                    'avg_loss_pct' => $avgLossPct,
                ];
            } catch (Throwable $e) {
                $cycleSummary = null;
            }

            try {
                $stmt = db()->prepare(
                    'SELECT day_number, AVG(weight_lbs) AS avg_weight '
                    . 'FROM challenge_checkins '
                    . 'WHERE cycle_id = ? '
                    . 'GROUP BY day_number '
                    . 'ORDER BY day_number ASC'
                );
                $stmt->execute([$cycleId]);
                $dailyAverages = $stmt->fetchAll();
            } catch (Throwable $e) {
                $dailyAverages = [];
            }

            try {
                $stmt = db()->prepare(
                    'SELECT c.client_id, c.full_name, c.start_weight_lbs, d10.weight_lbs AS day10_weight '
                    . 'FROM challenge_clients c '
                    . 'INNER JOIN challenge_checkins d10 ON d10.cycle_id = c.cycle_id AND d10.client_id = c.client_id AND d10.day_number = 10 '
                    . 'WHERE c.cycle_id = ? '
                    . 'ORDER BY (CASE WHEN c.start_weight_lbs > 0 THEN ((c.start_weight_lbs - d10.weight_lbs) / c.start_weight_lbs) ELSE 0 END) DESC '
                    . 'LIMIT 10'
                );
                $stmt->execute([$cycleId]);
                $topPerformers = $stmt->fetchAll();
            } catch (Throwable $e) {
                $topPerformers = [];
            }
        }
    }
}

$page_title = 'Reports';
require __DIR__ . '/../partials/layout_top.php';
require __DIR__ . '/../partials/nav.php';
?>

<main class="mx-auto max-w-6xl px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold tracking-tight">Reports & Analytics</h1>
        <p class="mt-1 text-sm text-zinc-600">Historical challenge cycles, completion rate, and trends.</p>
    </div>

    <div class="rounded-2xl border border-orange-100 bg-white p-6">
        <?php if (!$hasHistory): ?>
            <div class="text-sm font-semibold text-zinc-700">Status</div>
            <div class="mt-2 text-sm text-zinc-600">No challenge history yet. Run at least one reset to create archive tables.</div>
        <?php else: ?>
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <div class="lg:col-span-1">
                    <div class="text-lg font-extrabold tracking-tight">Challenge Cycles</div>
                    <div class="mt-1 text-sm text-zinc-600">Select a cycle to view analytics.</div>

                    <div class="mt-4 space-y-2">
                        <?php if (!$cycles): ?>
                            <div class="rounded-2xl border border-orange-100 bg-orange-50 p-4 text-sm text-zinc-700">No archived cycles yet.</div>
                        <?php endif; ?>

                        <?php foreach ($cycles as $c): ?>
                            <?php $active = $cycleId > 0 && (int) $c['id'] === $cycleId; ?>
                            <a href="<?= h(url('/admin/reports.php?cycle_id=' . (int) $c['id'] . '&page=' . (int) $cyclePage)) ?>" class="block rounded-2xl border <?= $active ? 'border-molten bg-orange-50' : 'border-orange-100 bg-white hover:bg-orange-50' ?> p-4">
                                <?php $label = trim((string) ($c['label'] ?? '')); ?>
                                <div class="text-sm font-extrabold text-zinc-900"><?= $label !== '' ? h($label) : ('Cycle #' . h((string) $c['id'])) ?></div>
                                <div class="mt-1 text-xs text-zinc-600"><?= h((string) $c['started_at']) ?> → <?= h((string) $c['ended_at']) ?></div>
                            </a>
                        <?php endforeach; ?>

                        <?php if ($totalPages > 1): ?>
                            <?php
                                $prevPage = max(1, $cyclePage - 1);
                                $nextPage = min($totalPages, $cyclePage + 1);
                                $base = '/admin/reports.php?page=';
                                $q = $cycleId > 0 ? ('&cycle_id=' . (int) $cycleId) : '';
                            ?>
                            <div class="mt-3 flex items-center justify-between">
                                <a class="rounded-xl border border-orange-100 bg-white px-3 py-2 text-xs font-extrabold text-zinc-700 hover:bg-orange-50 <?= $cyclePage <= 1 ? 'pointer-events-none opacity-50' : '' ?>" href="<?= h(url($base . (int) $prevPage . $q)) ?>">Prev</a>
                                <div class="text-xs text-zinc-600">Page <?= h((string) $cyclePage) ?> of <?= h((string) $totalPages) ?></div>
                                <a class="rounded-xl border border-orange-100 bg-white px-3 py-2 text-xs font-extrabold text-zinc-700 hover:bg-orange-50 <?= $cyclePage >= $totalPages ? 'pointer-events-none opacity-50' : '' ?>" href="<?= h(url($base . (int) $nextPage . $q)) ?>">Next</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <?php if (!$selectedCycle): ?>
                        <div class="rounded-2xl border border-orange-100 bg-orange-50 p-6">
                            <div class="text-sm font-semibold text-zinc-700">Select a cycle</div>
                            <div class="mt-2 text-sm text-zinc-600">Pick a cycle from the left to view completion rate and trends.</div>
                        </div>
                    <?php else: ?>
                        <div class="flex flex-col gap-1">
                            <?php $label = trim((string) ($selectedCycle['label'] ?? '')); ?>
                            <div class="text-lg font-extrabold tracking-tight"><?= $label !== '' ? h($label) : ('Cycle #' . h((string) $selectedCycle['id'])) ?></div>
                            <div class="text-sm text-zinc-600"><?= h((string) $selectedCycle['started_at']) ?> → <?= h((string) $selectedCycle['ended_at']) ?></div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <?php $pageParam = $cyclePage > 1 ? ('&page=' . (int) $cyclePage) : ''; ?>
                            <a href="<?= h(url('/admin/reports.php?cycle_id=' . (int) $cycleId . $pageParam . '&export=cycle_summary_csv')) ?>" class="inline-flex items-center justify-center rounded-xl border border-orange-100 bg-white px-4 py-2 text-xs font-extrabold text-zinc-700 hover:bg-orange-50">Download Cycle Summary (CSV)</a>
                            <a href="<?= h(url('/admin/reports.php?cycle_id=' . (int) $cycleId . $pageParam . '&export=cycle_checkins_csv')) ?>" class="inline-flex items-center justify-center rounded-xl border border-orange-100 bg-white px-4 py-2 text-xs font-extrabold text-zinc-700 hover:bg-orange-50">Download Check-ins (CSV)</a>
                        </div>

                        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="rounded-2xl border border-orange-100 bg-white p-5">
                                <div class="text-sm font-semibold text-zinc-600">Participants</div>
                                <div class="mt-2 text-3xl font-extrabold text-molten"><?= h((string) (($cycleSummary['participants'] ?? 0))) ?></div>
                            </div>
                            <div class="rounded-2xl border border-orange-100 bg-white p-5">
                                <div class="text-sm font-semibold text-zinc-600">Completed</div>
                                <div class="mt-2 text-3xl font-extrabold text-molten"><?= h((string) (($cycleSummary['completed'] ?? 0))) ?></div>
                            </div>
                            <div class="rounded-2xl border border-orange-100 bg-white p-5">
                                <div class="text-sm font-semibold text-zinc-600">Completion Rate</div>
                                <div class="mt-2 text-3xl font-extrabold text-molten"><?= h(number_format((float) (($cycleSummary['completion_rate'] ?? 0)), 2, '.', '')) ?>%</div>
                            </div>
                            <div class="rounded-2xl border border-orange-100 bg-white p-5">
                                <div class="text-sm font-semibold text-zinc-600">Avg Loss %</div>
                                <div class="mt-2 text-3xl font-extrabold text-molten"><?= h(number_format((float) (($cycleSummary['avg_loss_pct'] ?? 0)), 3, '.', '')) ?>%</div>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
                            <div class="rounded-2xl border border-orange-100 bg-white p-6">
                                <div class="text-lg font-extrabold tracking-tight">Average Weight by Day</div>
                                <div class="mt-1 text-sm text-zinc-600">Computed from archived check-ins.</div>

                                <div class="mt-5 overflow-x-auto">
                                    <table class="min-w-full text-left text-sm">
                                        <thead>
                                            <tr class="border-b border-orange-100 text-xs font-extrabold uppercase tracking-wide text-zinc-600">
                                                <th class="py-3 pr-4">Day</th>
                                                <th class="py-3 pr-0">Avg Weight (lbs)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!$dailyAverages): ?>
                                                <tr><td colspan="2" class="py-6 text-zinc-600">No check-ins archived for this cycle.</td></tr>
                                            <?php endif; ?>
                                            <?php foreach ($dailyAverages as $row): ?>
                                                <tr class="border-b border-orange-50">
                                                    <td class="py-4 pr-4 font-extrabold">Day <?= h((string) $row['day_number']) ?></td>
                                                    <td class="py-4 pr-0 text-zinc-700"><?= h(number_format((float) $row['avg_weight'], 2, '.', '')) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-orange-100 bg-white p-6">
                                <div class="text-lg font-extrabold tracking-tight">Top 10 Performers</div>
                                <div class="mt-1 text-sm text-zinc-600">Ranked by weight loss percentage (approx).</div>

                                <div class="mt-5 overflow-x-auto">
                                    <table class="min-w-full text-left text-sm">
                                        <thead>
                                            <tr class="border-b border-orange-100 text-xs font-extrabold uppercase tracking-wide text-zinc-600">
                                                <th class="py-3 pr-4">Client</th>
                                                <th class="py-3 pr-4">Loss %</th>
                                                <th class="py-3 pr-0">Day 10</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!$topPerformers): ?>
                                                <tr><td colspan="3" class="py-6 text-zinc-600">No Day 10 check-ins archived.</td></tr>
                                            <?php endif; ?>
                                            <?php foreach ($topPerformers as $row): ?>
                                                <?php
                                                    $sw = (float) ($row['start_weight_lbs'] ?? 0);
                                                    $dw = (float) ($row['day10_weight'] ?? 0);
                                                    $pct = 0.0;
                                                    if ($sw > 0 && $dw > 0) {
                                                        $pct = truncate_decimals((max(0, $sw - $dw) / $sw) * 100.0, 3);
                                                    }
                                                    $nm = trim((string) ($row['full_name'] ?? ''));
                                                    if ($nm === '') {
                                                        $nm = 'Client #' . (string) $row['client_id'];
                                                    }
                                                ?>
                                                <tr class="border-b border-orange-50">
                                                    <td class="py-4 pr-4 font-extrabold text-zinc-900"><?= h($nm) ?></td>
                                                    <td class="py-4 pr-4 font-extrabold text-molten"><?= h(number_format((float) $pct, 3, '.', '')) ?>%</td>
                                                    <td class="py-4 pr-0 text-zinc-700"><?= h(number_format((float) $dw, 2, '.', '')) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>
