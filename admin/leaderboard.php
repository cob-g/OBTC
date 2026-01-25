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
    $sql = 'SELECT c.id, ' . $selectClient . ', c.start_weight_lbs, c.bmi_category, u.name AS coach_name, u.email AS coach_email, d10.weight_lbs AS day10_weight, d10.recorded_at AS day10_recorded_at '
        . 'FROM clients c '
        . 'INNER JOIN users u ON u.id = c.coach_user_id '
        . 'INNER JOIN client_checkins d10 ON d10.client_id = c.id AND d10.day_number = 10 '
        . 'ORDER BY d10.recorded_at DESC, c.id DESC';

    $stmt = db()->query($sql);
    $raw = $stmt->fetchAll();

    foreach ($raw as $r) {
        $start = (float) ($r['start_weight_lbs'] ?? 0);
        $end = (float) ($r['day10_weight'] ?? 0);
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
            'start_weight' => $start,
            'end_weight' => $end,
            'loss_lbs' => $lossLbs,
            'loss_pct' => $lossPct,
            'day10_recorded_at' => (string) ($r['day10_recorded_at'] ?? ''),
        ];
    }
} catch (Throwable $e) {
    $rows = [];
}

usort($rows, function ($a, $b) {
    $cmp = ($b['loss_pct'] <=> $a['loss_pct']);
    if ($cmp !== 0) {
        return $cmp;
    }
    $cmp = ($b['loss_lbs'] <=> $a['loss_lbs']);
    if ($cmp !== 0) {
        return $cmp;
    }
    return ($a['client_id'] <=> $b['client_id']);
});

$top = array_slice($rows, 0, 10);

if (isset($_GET['export']) && (string) $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="leaderboard_top10.csv"');
    header('Cache-Control: no-store, max-age=0');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['Rank', 'Client', 'Coach', 'Coach Email', 'BMI Category', 'Start Weight (lbs)', 'Day 10 Weight (lbs)', 'Weight Loss (lbs)', 'Weight Loss (%)', 'Day 10 Recorded At']);
    $rank = 1;
    foreach ($top as $row) {
        fputcsv($out, [
            $rank,
            $row['client_name'],
            $row['coach_name'],
            $row['coach_email'],
            $row['bmi_category'],
            number_format((float) $row['start_weight'], 2, '.', ''),
            number_format((float) $row['end_weight'], 2, '.', ''),
            number_format((float) $row['loss_lbs'], 2, '.', ''),
            number_format((float) $row['loss_pct'], 3, '.', ''),
            $row['day10_recorded_at'],
        ]);
        $rank++;
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
        <p class="mt-1 text-sm text-zinc-600">Top 10 leaderboard ranked by weight loss percentage (3 decimals, no rounding).</p>
    </div>

    <div class="rounded-2xl border border-orange-100 bg-white p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="text-lg font-extrabold tracking-tight">Top 10 Results</div>
                <div class="mt-1 text-sm text-zinc-600">Uses Day 10 weigh-in as final weight. Clients without Day 10 weigh-in are excluded.</div>
            </div>
            <a href="<?= h(url('/admin/leaderboard.php?export=csv')) ?>" class="inline-flex items-center justify-center rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm font-extrabold text-zinc-700 hover:bg-orange-50">Export CSV</a>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-orange-100 text-xs font-extrabold uppercase tracking-wide text-zinc-600">
                        <th class="py-3 pr-4">Rank</th>
                        <th class="py-3 pr-4">Client</th>
                        <th class="py-3 pr-4">Coach</th>
                        <th class="py-3 pr-4">Start</th>
                        <th class="py-3 pr-4">Day 10</th>
                        <th class="py-3 pr-4">Loss</th>
                        <th class="py-3 pr-4">Loss %</th>
                        <th class="py-3 pr-0">Category</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$top): ?>
                        <tr>
                            <td colspan="8" class="py-6 text-zinc-600">No Day 10 weigh-ins yet.</td>
                        </tr>
                    <?php endif; ?>

                    <?php $rank = 1; ?>
                    <?php foreach ($top as $row): ?>
                        <tr class="border-b border-orange-50">
                            <td class="py-4 pr-4 font-extrabold text-zinc-900"><?= h((string) $rank) ?></td>
                            <td class="py-4 pr-4 font-extrabold text-zinc-900"><?= h((string) $row['client_name']) ?></td>
                            <td class="py-4 pr-4 text-zinc-700"><?= h((string) $row['coach_name']) ?> <span class="text-zinc-500">(<?= h((string) $row['coach_email']) ?>)</span></td>
                            <td class="py-4 pr-4 text-zinc-700"><?= h(number_format((float) $row['start_weight'], 2, '.', '')) ?></td>
                            <td class="py-4 pr-4 text-zinc-700"><?= h(number_format((float) $row['end_weight'], 2, '.', '')) ?></td>
                            <td class="py-4 pr-4 font-extrabold text-molten"><?= h(number_format((float) $row['loss_lbs'], 2, '.', '')) ?></td>
                            <td class="py-4 pr-4 font-extrabold text-molten"><?= h(number_format((float) $row['loss_pct'], 3, '.', '')) ?></td>
                            <td class="py-4 pr-0 text-zinc-800"><?= h((string) $row['bmi_category']) ?></td>
                        </tr>
                        <?php $rank++; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>
