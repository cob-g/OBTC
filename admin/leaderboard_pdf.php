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
$hasDay10Waist = db_has_column('clients', 'day10_waistline_in');

$rows = [];
try {
    $selectClient = $hasFullName ? 'c.full_name' : "'' AS full_name";
    $sql = 'SELECT c.id, ' . $selectClient . ', c.start_weight_lbs, c.waistline_in, c.bmi, c.bmi_category, c.front_photo_path, c.side_photo_path, c.day10_front_photo_path, c.day10_side_photo_path, u.name AS coach_name, latest.latest_weight, latest.days_completed, latest.has_day10 '
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
        $lossPct = truncate_decimals($lossPct, 2);

        $name = trim((string) ($r['full_name'] ?? ''));
        if ($name === '') {
            $name = 'Client #' . (string) $r['id'];
        }

        $rows[] = [
            'client_id' => (int) $r['id'],
            'client_name' => $name,
            'coach_name' => (string) ($r['coach_name'] ?? ''),
            'bmi' => (string) ($r['bmi'] ?? ''),
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
            'start_waist' => (float) ($r['waistline_in'] ?? 0),
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

$allRowsByCategory = [];
foreach (bmi_category_options() as $opt) {
    $catKey = trim((string) $opt);
    if ($catKey === '') {
        continue;
    }
    if (!isset($allRowsByCategory[$catKey])) {
        $allRowsByCategory[$catKey] = [];
    }
}

foreach ($rows as $r) {
    $cat = trim((string) ($r['bmi_category'] ?? ''));
    if ($cat === '') {
        $cat = 'Uncategorized';
    }

    if (strcasecmp($cat, 'Underweight') === 0) {
        $cat = 'Normal';
    }

    if (!isset($allRowsByCategory[$cat])) {
        $allRowsByCategory[$cat] = [];
    }
    $allRowsByCategory[$cat][] = $r;
}

foreach ($allRowsByCategory as $cat => $list) {
    usort($list, $rowRankCompare);
    $allRowsByCategory[$cat] = $list;
}

$category = trim((string) ($_GET['category'] ?? ''));
if ($category === '' || !isset($allRowsByCategory[$category])) {
    $keys = array_keys($allRowsByCategory);
    $category = !empty($keys) ? (string) $keys[0] : '';
}

$top1 = null;
if ($category !== '') {
    $top1 = $allRowsByCategory[$category][0] ?? null;
}

$client = null;
$endWaist = null;
if ($top1) {
    try {
        $cols = ['waistline_in'];
        if ($hasDay10Waist) {
            $cols[] = 'day10_waistline_in';
        }
        $stmt = db()->prepare('SELECT ' . implode(', ', $cols) . ' FROM clients WHERE id = ? LIMIT 1');
        $stmt->execute([(int) $top1['client_id']]);
        $client = $stmt->fetch();
        if ($client && $hasDay10Waist) {
            $endWaist = (float) ($client['day10_waistline_in'] ?? 0);
        }
    } catch (Throwable $e) {
        $client = null;
    }
}

$startWaist = $top1 ? (float) ($top1['start_waist'] ?? 0) : 0.0;
$lostInches = null;
if ($startWaist > 0 && $endWaist !== null && $endWaist > 0) {
    $lostInches = max(0, $startWaist - $endWaist);
}

$page_title = 'Leaderboard Export';
require __DIR__ . '/../partials/layout_top.php';
?>

<style>
@page { size: A4 landscape; margin: 10mm; }
@media print {
    body { background: #ffffff !important; }
    .no-print { display: none !important; }
    .print-shadow { box-shadow: none !important; }
}
</style>

<div class="no-print mx-auto max-w-4xl px-4 py-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="<?= h(url('/admin/leaderboard.php?category=' . urlencode($category))) ?>" class="inline-flex items-center justify-center rounded-xl border border-orange-100 bg-white px-4 py-2 text-sm font-extrabold text-zinc-700 hover:bg-orange-50">Back to Leaderboard</a>
        <button onclick="window.print()" class="inline-flex items-center justify-center rounded-xl bg-molten px-4 py-2 text-sm font-extrabold text-white hover:bg-pumpkin">Print / Save as PDF</button>
    </div>
</div>

<?php if (!$top1): ?>
    <main class="mx-auto max-w-4xl px-4 py-10">
        <div class="rounded-2xl border border-orange-100 bg-white p-6">
            <div class="text-lg font-extrabold tracking-tight">No leaderboard entry found.</div>
            <div class="mt-1 text-sm text-zinc-600">There is no Top 1 participant available for this category.</div>
        </div>
    </main>
<?php else: ?>
    <?php
        $bmiText = trim((string) ($top1['bmi'] ?? ''));
        $coachName = trim((string) ($top1['coach_name'] ?? ''));
        $clientName = trim((string) ($top1['client_name'] ?? ''));
        $categoryText = trim((string) ($top1['bmi_category'] ?? $category));
        if ($categoryText === '') {
            $categoryText = $category;
        }
        $lossPctText = number_format((float) ($top1['loss_pct'] ?? 0), 2, '.', '');
        $lossLbsText = number_format((float) ($top1['loss_lbs'] ?? 0), 1, '.', '');
        $lostInchesText = $lostInches === null ? 'N/A' : number_format((float) $lostInches, 1, '.', '');

        $day1FrontSrc = !empty($top1['front_photo_path']) ? url('/media/client_photo.php?id=' . (int) $top1['client_id'] . '&photo=day1_front') : '';
        $day10FrontSrc = !empty($top1['day10_front_photo_path']) ? url('/media/client_photo.php?id=' . (int) $top1['client_id'] . '&photo=day10_front') : '';
        $day1SideSrc = !empty($top1['side_photo_path']) ? url('/media/client_photo.php?id=' . (int) $top1['client_id'] . '&photo=day1_side') : '';
        $day10SideSrc = !empty($top1['day10_side_photo_path']) ? url('/media/client_photo.php?id=' . (int) $top1['client_id'] . '&photo=day10_side') : '';
    ?>

    <main class="mx-auto max-w-[1200px] px-6 py-4">
        <div class="print-shadow overflow-hidden rounded-2xl border border-[#e5e4dc] bg-white">
            <div class="grid grid-cols-2 gap-0 border-b border-[#e5e4dc]">
                <div class="p-6">
                    <div class="text-[12px] font-black uppercase tracking-wide text-[#181711]">Name: <span class="font-black"><?= h(strtoupper($clientName)) ?></span></div>
                    <div class="mt-1 text-[12px] font-black uppercase tracking-wide text-[#181711]">BMI (KG/M2): <span class="font-black"><?= h($bmiText !== '' ? $bmiText : '-') ?></span></div>
                </div>
                <div class="p-6 text-right">
                    <div class="text-[12px] font-black uppercase tracking-wide text-[#181711]">Team Captain: <span class="font-black"><?= h(strtoupper($coachName !== '' ? $coachName : '-')) ?></span></div>
                    <div class="mt-1 text-[12px] font-black uppercase tracking-wide text-[#181711]"><?= h(strtoupper($categoryText)) ?> Category</div>
                </div>
            </div>

            <div class="p-6">
                <div class="flex items-center justify-between gap-6">
                    <div class="text-5xl font-black tracking-tight text-orange-600">#1</div>
                    <div class="flex items-end gap-3">
                        <div class="text-right">
                            <div class="text-[11px] font-black uppercase tracking-wide text-[#888263]">Lost Inches</div>
                            <div class="text-3xl font-black tracking-tight text-[#181711]"><?= h($lostInchesText) ?></div>
                        </div>
                        <div class="text-3xl font-black tracking-tight text-[#181711]">|</div>
                        <div class="text-right">
                            <div class="text-[11px] font-black uppercase tracking-wide text-[#888263]">Lost Lbs</div>
                            <div class="text-3xl font-black tracking-tight text-[#181711]"><?= h($lossLbsText) ?></div>
                        </div>
                        <div class="text-3xl font-black tracking-tight text-[#181711]">|</div>
                        <div class="text-right">
                            <div class="text-[11px] font-black uppercase tracking-wide text-[#888263]">Loss %</div>
                            <div class="text-3xl font-black tracking-tight text-orange-600"><?= h($lossPctText) ?>%</div>
                        </div>
                    </div>
                </div>

                <div class="mt-2 text-center text-[11px] font-bold text-[#888263]">
                    Category: <?= h($categoryText) ?> • Days Completed: <?= h((string) (int) ($top1['days_completed'] ?? 0)) ?>/10
                </div>

                <div class="mt-5 grid grid-cols-4 gap-4">
                    <?php
                        $pdfPhotos = [
                            ['src' => $day1FrontSrc, 'label' => 'FRONT BEFORE (DAY 1)'],
                            ['src' => $day10FrontSrc, 'label' => 'FRONT AFTER (DAY 10)'],
                            ['src' => $day1SideSrc, 'label' => 'SIDE BEFORE (DAY 1)'],
                            ['src' => $day10SideSrc, 'label' => 'SIDE AFTER (DAY 10)'],
                        ];
                    ?>

                    <?php foreach ($pdfPhotos as $p): ?>
                        <div class="flex flex-col gap-2">
                            <div class="text-center text-[10px] font-black uppercase tracking-wide text-[#888263]"><?= h($p['label']) ?></div>
                            <div class="aspect-[3/4] rounded-lg border border-[#e5e4dc] bg-[#f4f4f0] overflow-hidden">
                                <?php if (!empty($p['src'])): ?>
                                    <img src="<?= h($p['src']) ?>" alt="<?= h($p['label']) ?>" class="w-full h-full object-contain" />
                                <?php else: ?>
                                    <div class="flex h-full w-full items-center justify-center">
                                        <span class="material-symbols-outlined text-[#888263]">image</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () {
                window.print();
            }, 300);
        });
    </script>
<?php endif; ?>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>
