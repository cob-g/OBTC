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

$hasFullName = db_has_column('clients', 'full_name');

$rows = [];
try {
    $selectClient = $hasFullName ? 'c.full_name' : "'' AS full_name";
    $sql = 'SELECT c.id, ' . $selectClient . ', c.start_weight_lbs, c.bmi_category, c.front_photo_path, c.side_photo_path, c.day10_front_photo_path, c.day10_side_photo_path, u.name AS coach_name, u.email AS coach_email, latest.latest_weight, latest.days_completed, latest.has_day10 '
        . 'FROM clients c '
        . 'INNER JOIN users u ON u.id = c.coach_user_id '
        . 'INNER JOIN ( '
        . '   SELECT client_id, COUNT(*) AS days_completed, MAX(CASE WHEN day_number = 10 THEN 1 ELSE 0 END) AS has_day10, '
        . '          SUBSTRING_INDEX(GROUP_CONCAT(weight_lbs ORDER BY day_number DESC), ",", 1) AS latest_weight '
        . '   FROM client_checkins '
        . '   GROUP BY client_id '
        . ') latest ON latest.client_id = c.id '
        . 'ORDER BY latest.has_day10 DESC, latest.days_completed DESC, c.id DESC';

    $stmt = db()->query($sql);
    $raw = $stmt->fetchAll();

    foreach ($raw as $r) {
        $start = (float) ($r['start_weight_lbs'] ?? 0);
        $end = (float) ($r['latest_weight'] ?? 0);
        if ($start <= 0 || $end <= 0) {
            continue;
        }

        $lossLbs = max(0, $start - $end);
        $lossPct = $start > 0 ? (($lossLbs / $start) * 100.0) : 0.0;
        $lossPct = truncate_decimals($lossPct, 3);

        $name = trim((string) ($r['full_name'] ?? ''));
        if ($name === '') {
            $name = 'Client #' . (string) $r['id'];
        }

        $rows[] = [
            'client_id' => (int) $r['id'],
            'client_name' => $name,
            'coach_name' => (string) ($r['coach_name'] ?? ''),
            'coach_email' => (string) ($r['coach_email'] ?? ''),
            'bmi_category' => (string) ($r['bmi_category'] ?? ''),
            'front_photo_path' => (string) ($r['front_photo_path'] ?? ''),
            'side_photo_path' => (string) ($r['side_photo_path'] ?? ''),
            'day10_front_photo_path' => (string) ($r['day10_front_photo_path'] ?? ''),
            'day10_side_photo_path' => (string) ($r['day10_side_photo_path'] ?? ''),
            'days_completed' => (int) ($r['days_completed'] ?? 0),
            'has_day10' => (int) ($r['has_day10'] ?? 0),
            'start_weight' => $start,
            'end_weight' => $end,
            'loss_lbs' => $lossLbs,
            'loss_pct' => $lossPct,
        ];
    }
} catch (Throwable $e) {
    $rows = [];
}

$rowRankCompare = function ($a, $b) {
    $cmp = ($b['has_day10'] <=> $a['has_day10']);
    if ($cmp !== 0) {
        return $cmp;
    }
    $cmp = ($b['days_completed'] <=> $a['days_completed']);
    if ($cmp !== 0) {
        return $cmp;
    }
    $cmp = ($b['loss_pct'] <=> $a['loss_pct']);
    if ($cmp !== 0) {
        return $cmp;
    }
    $cmp = ($b['loss_lbs'] <=> $a['loss_lbs']);
    if ($cmp !== 0) {
        return $cmp;
    }
    return ($a['client_id'] <=> $b['client_id']);
};

$categories = [];
foreach ($rows as $r) {
    $cat = trim((string) ($r['bmi_category'] ?? ''));
    if ($cat === '') {
        $cat = 'Uncategorized';
    }
    if (!isset($categories[$cat])) {
        $categories[$cat] = [];
    }
    $categories[$cat][] = $r;
}

uksort($categories, function ($a, $b) {
    return strcasecmp((string) $a, (string) $b);
});

$topByCategory = [];
foreach ($categories as $cat => $list) {
    usort($list, $rowRankCompare);
    $topByCategory[$cat] = array_slice($list, 0, 10);
}

$exportRows = [];
foreach ($topByCategory as $cat => $list) {
    foreach ($list as $idx => $r) {
        $exportRows[] = ['category' => (string) $cat, 'rank' => (int) ($idx + 1)] + $r;
    }
}

if (isset($_GET['export']) && (string) $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="leaderboard_top10.csv"');
    header('Cache-Control: no-store, max-age=0');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['BMI Category', 'Rank', 'Client', 'Coach', 'Coach Email', 'Days Completed', 'Start Weight (lbs)', 'Latest Weight (lbs)', 'Weight Loss (lbs)', 'Weight Loss (%)', 'Has Day 10']);
    foreach ($exportRows as $row) {
        fputcsv($out, [
            $row['category'],
            (int) $row['rank'],
            $row['client_name'],
            $row['coach_name'],
            $row['coach_email'],
            $row['bmi_category'],
            (int) $row['days_completed'],
            number_format((float) $row['start_weight'], 2, '.', ''),
            number_format((float) $row['end_weight'], 2, '.', ''),
            number_format((float) $row['loss_lbs'], 2, '.', ''),
            number_format((float) $row['loss_pct'], 3, '.', ''),
            ((int) $row['has_day10']) === 1 ? 'Yes' : 'No',
        ]);
    }
    fclose($out);
    exit;
}

$page_title = 'Leaderboard';
require __DIR__ . '/../partials/layout_top.php';
require __DIR__ . '/../partials/nav.php';
?>

<main class="mx-auto max-w-6xl px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold tracking-tight">Leaderboard</h1>
        <p class="mt-1 text-sm text-zinc-600">Top 10 leaderboard ranked by Day 10 completion, days completed, then weight loss percentage (3 decimals, no rounding).</p>    
    </div>

    <div class="rounded-2xl border border-orange-100 bg-white p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="text-lg font-extrabold tracking-tight">Top 10 Results</div>
                <div class="mt-1 text-sm text-zinc-600">Uses latest weigh-in for weight loss percentage. Day 10 completion and consistency are prioritized. Results are grouped per BMI category.</div>
            </div>
            <a href="<?= h(url('/admin/leaderboard.php?export=csv')) ?>" class="inline-flex items-center justify-center rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm font-extrabold text-zinc-700 hover:bg-orange-50">Export CSV</a>
        </div>

        <?php if (!$topByCategory): ?>
            <div class="mt-6 text-sm text-zinc-600">No weigh-ins yet.</div>
        <?php endif; ?>

        <?php foreach ($topByCategory as $cat => $list): ?>
            <div class="mt-8">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <div class="text-lg font-extrabold tracking-tight"><?= h((string) $cat) ?></div>
                        <div class="mt-1 text-sm text-zinc-600">Top 10 for this category.</div>
                    </div>

                    <?php $winner = $list[0] ?? null; ?>
                    <?php if ($winner): ?>
                        <div class="w-full rounded-2xl border border-orange-100 bg-orange-50 p-4 lg:w-[420px]">
                            <div class="text-sm font-extrabold text-zinc-800">#1 Progress Photos</div>
                            <div class="mt-1 text-sm text-zinc-600"><?= h((string) $winner['client_name']) ?></div>

                            <div class="mt-4 grid grid-cols-2 gap-3">
                                <div class="overflow-hidden rounded-xl bg-white">
                                    <div class="aspect-[3/4] w-full">
                                        <?php if (!empty($winner['front_photo_path'])): ?>
                                            <img src="<?= h(url('/media/client_photo.php?id=' . (int) $winner['client_id'] . '&photo=day1_front')) ?>" alt="Day 1 front" class="h-full w-full object-cover" />
                                        <?php else: ?>
                                            <div class="flex h-full w-full items-center justify-center text-sm text-zinc-600">Day 1 front not available</div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="overflow-hidden rounded-xl bg-white">
                                    <div class="aspect-[3/4] w-full">
                                        <?php if (!empty($winner['day10_front_photo_path'])): ?>
                                            <img src="<?= h(url('/media/client_photo.php?id=' . (int) $winner['client_id'] . '&photo=day10_front')) ?>" alt="Day 10 front" class="h-full w-full object-cover" />
                                        <?php else: ?>
                                            <div class="flex h-full w-full items-center justify-center text-sm text-zinc-600">Day 10 front not available</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 grid grid-cols-2 gap-3">
                                <div class="overflow-hidden rounded-xl bg-white">
                                    <div class="aspect-[3/4] w-full">
                                        <?php if (!empty($winner['side_photo_path'])): ?>
                                            <img src="<?= h(url('/media/client_photo.php?id=' . (int) $winner['client_id'] . '&photo=day1_side')) ?>" alt="Day 1 side" class="h-full w-full object-cover" />
                                        <?php else: ?>
                                            <div class="flex h-full w-full items-center justify-center text-sm text-zinc-600">Day 1 side not available</div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="overflow-hidden rounded-xl bg-white">
                                    <div class="aspect-[3/4] w-full">
                                        <?php if (!empty($winner['day10_side_photo_path'])): ?>
                                            <img src="<?= h(url('/media/client_photo.php?id=' . (int) $winner['client_id'] . '&photo=day10_side')) ?>" alt="Day 10 side" class="h-full w-full object-cover" />
                                        <?php else: ?>
                                            <div class="flex h-full w-full items-center justify-center text-sm text-zinc-600">Day 10 side not available</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mt-5 overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-orange-100 text-xs font-extrabold uppercase tracking-wide text-zinc-600">
                                <th class="py-3 pr-4">Rank</th>
                                <th class="py-3 pr-4">Client</th>
                                <th class="py-3 pr-4">Coach</th>
                                <th class="py-3 pr-4">Days</th>
                                <th class="py-3 pr-4">Start</th>
                                <th class="py-3 pr-4">Latest</th>
                                <th class="py-3 pr-4">Loss</th>
                                <th class="py-3 pr-0">Loss %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$list): ?>
                                <tr>
                                    <td colspan="8" class="py-6 text-zinc-600">No weigh-ins yet for this category.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($list as $i => $row): ?>
                                <tr class="border-b border-orange-50">
                                    <td class="py-4 pr-4 font-extrabold text-zinc-900"><?= h((string) ($i + 1)) ?></td>
                                    <td class="py-4 pr-4 font-extrabold text-zinc-900"><?= h((string) $row['client_name']) ?></td>
                                    <td class="py-4 pr-4 text-zinc-700"><?= h((string) $row['coach_name']) ?> <span class="text-zinc-500">(<?= h((string) $row['coach_email']) ?>)</span></td>
                                    <td class="py-4 pr-4 text-zinc-700"><?= h((string) ((int) $row['days_completed'])) ?>/10</td>
                                    <td class="py-4 pr-4 text-zinc-700"><?= h(number_format((float) $row['start_weight'], 2, '.', '')) ?></td>
                                    <td class="py-4 pr-4 text-zinc-700"><?= h(number_format((float) $row['end_weight'], 2, '.', '')) ?></td>
                                    <td class="py-4 pr-4 font-extrabold text-molten"><?= h(number_format((float) $row['loss_lbs'], 2, '.', '')) ?></td>
                                    <td class="py-4 pr-0 font-extrabold text-molten"><?= h(number_format((float) $row['loss_pct'], 3, '.', '')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>
