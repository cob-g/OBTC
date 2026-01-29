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
        $lossPct = truncate_decimals($lossPct, 2);

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

uksort($allRowsByCategory, function ($a, $b) {
    return strcasecmp((string) $a, (string) $b);
});

$topByCategory = [];
foreach ($allRowsByCategory as $cat => $list) {
    usort($list, $rowRankCompare);
    $topByCategory[$cat] = array_slice($list, 0, 10);
}

// Calculate summary statistics
$totalLossLbs = array_sum(array_column($rows, 'loss_lbs'));
$avgLossPct = count($rows) > 0 ? array_sum(array_column($rows, 'loss_pct')) / count($rows) : 0;
$avgLossPct = truncate_decimals($avgLossPct, 2);
$activeParticipants = count($rows);
$completionRate = count($rows) > 0 ? (array_sum(array_column($rows, 'has_day10')) / count($rows)) * 100 : 0;

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
            number_format((float) $row['loss_pct'], 2, '.', ''),
            ((int) $row['has_day10']) === 1 ? 'Yes' : 'No',
        ]);
    }

    fclose($out);
    exit;
}

$page_title = 'Leaderboard';
require __DIR__ . '/../partials/layout_top.php';
require __DIR__ . '/../partials/nav.php';

// Get active category from URL or default to first
$activeCategory = $_GET['category'] ?? '';
if ($activeCategory && isset($topByCategory[$activeCategory])) {
    $currentCategory = $activeCategory;
} else {
    $categoryKeys = array_keys($topByCategory);
    $currentCategory = !empty($categoryKeys) ? $categoryKeys[0] : '';
}
?>

<main class="flex-1 overflow-y-auto">
    <div class="max-w-[1200px] mx-auto p-8">
        <!-- Page Heading -->
        <div class="flex flex-wrap justify-between items-end gap-3 mb-8">
            <div class="flex flex-col gap-2">
                <p class="text-[#181711] text-4xl font-black leading-tight tracking-[-0.033em]">Top 10 Leaderboard</p>
                <p class="text-[#888263] text-base font-normal leading-normal">Ranking based on Day 10 completion, days completed, then weight loss percentage (2 decimals, no rounding).</p>
            </div>
            <div class="flex gap-3">
                <a href="<?= h(url('/admin/leaderboard_pdf.php?category=' . urlencode((string) $currentCategory))) ?>" target="_blank" rel="noopener" class="flex items-center justify-center rounded-lg h-10 px-4 bg-white border border-[#e5e4dc] text-[#181711] text-sm font-bold tracking-[0.015em] hover:bg-orange-50">
                    <span class="material-symbols-outlined text-lg mr-2">picture_as_pdf</span>
                    Export PDF
                </a>
                <a href="<?= h(url('/admin/leaderboard.php?export=csv')) ?>" class="flex items-center justify-center rounded-lg h-10 px-4 bg-white border border-[#e5e4dc] text-[#181711] text-sm font-bold tracking-[0.015em] hover:bg-orange-50">
                    <span class="material-symbols-outlined text-lg mr-2">download</span>
                    Export CSV
                </a>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="flex flex-wrap gap-4 mb-8">
            <div class="flex min-w-[200px] flex-1 flex-col gap-2 rounded-xl p-6 bg-white border border-[#e5e4dc] shadow-sm">
                <p class="text-[#888263] text-sm font-medium leading-normal">Total Group Loss</p>
                <p class="text-[#181711] tracking-light text-2xl font-bold leading-tight"><?= h(number_format($totalLossLbs, 1)) ?> lbs</p>
                <div class="flex items-center gap-1 text-[#078814]">
                    <span class="material-symbols-outlined text-sm">trending_up</span>
                    <span class="text-xs font-bold">All categories</span>
                </div>
            </div>
            <div class="flex min-w-[200px] flex-1 flex-col gap-2 rounded-xl p-6 bg-white border border-[#e5e4dc] shadow-sm">
                <p class="text-[#888263] text-sm font-medium leading-normal">Avg. Loss %</p>
                <p class="text-[#181711] tracking-light text-2xl font-bold leading-tight"><?= h(number_format($avgLossPct, 2, '.', '')) ?>%</p>
                <div class="flex items-center gap-1 text-[#078814]">
                    <span class="material-symbols-outlined text-sm">trending_up</span>
                    <span class="text-xs font-bold">Average per participant</span>
                </div>
            </div>
            <div class="flex min-w-[200px] flex-1 flex-col gap-2 rounded-xl p-6 bg-white border border-[#e5e4dc] shadow-sm">
                <p class="text-[#888263] text-sm font-medium leading-normal">Active Participants</p>
                <p class="text-[#181711] tracking-light text-2xl font-bold leading-tight"><?= h($activeParticipants) ?></p>
                <p class="text-[#888263] text-xs font-medium"><?= h(number_format($completionRate, 0)) ?>% Day 10 completion</p>
            </div>
        </div>

        <!-- Category Tabs -->
        <div class="mb-8 border-b border-[#e5e4dc]">
            <div class="flex gap-8">
                <?php foreach ($topByCategory as $cat => $list): ?>
                    <a class="flex flex-col items-center justify-center border-b-[3px] <?= $cat === $currentCategory ? 'border-orange-500 text-orange-600' : 'border-transparent text-[#888263]' ?> pb-3 pt-4 hover:text-[#181711]"
                       href="?category=<?= h(urlencode($cat)) ?>">
                        <p class="text-sm font-bold tracking-[0.015em]"><?= h($cat) ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (!$topByCategory): ?>
            <div class="text-center py-12">
                <p class="text-[#888263] text-lg">No weigh-ins yet.</p>
            </div>
        <?php else: ?>
            <!-- Rank Cards List -->
            <div class="flex flex-col gap-6">
                <?php foreach ($topByCategory[$currentCategory] ?? [] as $i => $row): ?>
                    <?php $rank = $i + 1; ?>
                    
                    <?php if ($rank === 1): ?>
                        <!-- Rank 1 Card - Special Design -->
                        <div class="group bg-white rounded-xl border-2 border-orange-500 shadow-lg overflow-hidden transition-all hover:translate-y-[-2px]">
                            <div class="flex items-center bg-orange-50 p-4 border-b border-orange-200">
                                <div class="flex items-center gap-4 flex-1">
                                    <div class="flex items-center justify-center size-12 rounded-full bg-orange-500 text-white font-black text-xl shadow-md">#<?= h($rank) ?></div>
                                    <div class="flex items-center gap-3">
                                        <div class="size-10 rounded-full bg-gray-200 border-2 border-white flex items-center justify-center">
                                            <span class="material-symbols-outlined text-gray-600">person</span>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-lg text-[#181711]"><?= h($row['client_name']) ?></h3>
                                            <p class="text-xs text-[#888263]"><?= h($row['has_day10'] ? 'Verified • ' : '') ?>ID: #<?= h($row['client_id']) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-8 pr-4">
                                    <div class="text-center">
                                        <p class="text-xs text-[#888263] uppercase font-bold">Total Loss</p>
                                        <p class="text-2xl font-black text-[#181711]"><?= h(number_format($row['loss_lbs'], 1)) ?> <span class="text-sm font-medium">lbs</span></p>
                                    </div>
                                    <div class="text-center px-6 py-2 bg-orange-500 rounded-lg">
                                        <p class="text-xs text-white uppercase font-black opacity-90">Loss %</p>
                                        <p class="text-3xl font-black text-white"><?= h(number_format((float) $row['loss_pct'], 2, '.', '')) ?>%</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex p-6 gap-8">
                                <!-- Photo Grid -->
                                <div class="grid grid-cols-4 gap-4 flex-1">
                                    <div class="flex flex-col gap-2">
                                        <p class="text-[10px] font-bold uppercase text-[#888263] tracking-widest text-center">Front Before (Day 1)</p>
                                        <div class="aspect-[3/4] bg-[#f4f4f0] rounded-lg overflow-hidden border border-[#e5e4dc]">
                                            <?php if (!empty($row['front_photo_path'])): ?>
                                                <img src="<?= h(url('/media/client_photo.php?id=' . (int) $row['client_id'] . '&photo=day1_front')) ?>" 
                                                     alt="Day 1 front" 
                                                     class="w-full h-full object-cover" />
                                            <?php else: ?>
                                                <div class="flex h-full w-full items-center justify-center">
                                                    <span class="material-symbols-outlined text-[#888263]">image</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <p class="text-[10px] font-bold uppercase text-[#888263] tracking-widest text-center">Front After (Day 10)</p>
                                        <div class="aspect-[3/4] bg-[#f4f4f0] rounded-lg overflow-hidden border <?= $row['has_day10'] ? 'border-orange-500' : 'border-[#e5e4dc]' ?>">
                                            <?php if (!empty($row['day10_front_photo_path'])): ?>
                                                <img src="<?= h(url('/media/client_photo.php?id=' . (int) $row['client_id'] . '&photo=day10_front')) ?>" 
                                                     alt="Day 10 front" 
                                                     class="w-full h-full object-cover" />
                                            <?php else: ?>
                                                <div class="flex h-full w-full items-center justify-center">
                                                    <span class="material-symbols-outlined text-[#888263]">image</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <p class="text-[10px] font-bold uppercase text-[#888263] tracking-widest text-center">Side Before (Day 1)</p>
                                        <div class="aspect-[3/4] bg-[#f4f4f0] rounded-lg overflow-hidden border border-[#e5e4dc]">
                                            <?php if (!empty($row['side_photo_path'])): ?>
                                                <img src="<?= h(url('/media/client_photo.php?id=' . (int) $row['client_id'] . '&photo=day1_side')) ?>" 
                                                     alt="Day 1 side" 
                                                     class="w-full h-full object-cover" />
                                            <?php else: ?>
                                                <div class="flex h-full w-full items-center justify-center">
                                                    <span class="material-symbols-outlined text-[#888263]">image</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <p class="text-[10px] font-bold uppercase text-[#888263] tracking-widest text-center">Side After (Day 10)</p>
                                        <div class="aspect-[3/4] bg-[#f4f4f0] rounded-lg overflow-hidden border <?= $row['has_day10'] ? 'border-orange-500' : 'border-[#e5e4dc]' ?>">
                                            <?php if (!empty($row['day10_side_photo_path'])): ?>
                                                <img src="<?= h(url('/media/client_photo.php?id=' . (int) $row['client_id'] . '&photo=day10_side')) ?>" 
                                                     alt="Day 10 side" 
                                                     class="w-full h-full object-cover" />
                                            <?php else: ?>
                                                <div class="flex h-full w-full items-center justify-center">
                                                    <span class="material-symbols-outlined text-[#888263]">image</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <!-- Detailed Stats -->
                                <div class="w-72 flex flex-col justify-center gap-4 bg-orange-50 p-6 rounded-xl border border-orange-100">
                                    <div class="flex justify-between items-center pb-3 border-b border-orange-100">
                                        <span class="text-sm font-medium text-[#888263]">Coach</span>
                                        <span class="text-lg font-bold"><?= h($row['coach_name']) ?></span>
                                    </div>
                                    <div class="flex justify-between items-center pb-3 border-b border-orange-100">
                                        <span class="text-sm font-medium text-[#888263]">Start Weight</span>
                                        <span class="text-lg font-bold"><?= h(number_format($row['start_weight'], 1)) ?> lbs</span>
                                    </div>
                                    <div class="flex justify-between items-center pb-3 border-b border-orange-100">
                                        <span class="text-sm font-medium text-[#888263]">End Weight</span>
                                        <span class="text-lg font-bold"><?= h(number_format($row['end_weight'], 1)) ?> lbs</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-[#888263]">Days Completed</span>
                                        <span class="text-lg font-bold text-orange-600"><?= h($row['days_completed']) ?>/10</span>
                                    </div>
                                    <a href="<?= h(url('/coach/client_details.php?id=' . (int) $row['client_id'])) ?>" class="mt-4 w-full py-2 bg-[#181711] text-white text-xs font-bold rounded-lg hover:bg-black transition-colors text-center">
                                        VIEW FULL PROFILE
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Rank 2-10 Cards - Simplified Design -->
                        <div class="group bg-white rounded-xl border border-[#e5e4dc] shadow-sm overflow-hidden transition-all hover:border-orange-300">
                            <div class="flex items-center p-4 border-b border-[#e5e4dc]">
                                <div class="flex items-center gap-4 flex-1">
                                    <div class="flex items-center justify-center size-10 rounded-full <?= $rank === 2 ? 'bg-gray-200' : 'bg-orange-100' ?> text-[#181711] font-black text-lg">#<?= h($rank) ?></div>
                                    <div class="flex items-center gap-3">
                                        <div class="size-8 rounded-full bg-gray-200 flex items-center justify-center">
                                            <span class="material-symbols-outlined text-gray-600 text-sm">person</span>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-base text-[#181711]"><?= h($row['client_name']) ?></h3>
                                            <p class="text-xs text-[#888263]"><?= h($row['coach_name']) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-8">
                                    <div class="text-center">
                                        <p class="text-[10px] text-[#888263] uppercase font-bold tracking-tighter">Total Loss</p>
                                        <p class="text-xl font-bold"><?= h(number_format($row['loss_lbs'], 1)) ?> lbs</p>
                                    </div>
                                    <div class="text-center px-4 py-1.5 bg-orange-100 rounded-lg">
                                        <p class="text-[10px] text-orange-800 uppercase font-black tracking-tighter">Loss %</p>
                                        <p class="text-2xl font-black text-orange-800"><?= h(number_format((float) $row['loss_pct'], 2, '.', '')) ?>%</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex p-5 gap-6">
                                <div class="grid grid-cols-4 gap-3 flex-1">
                                    <!-- Before/After Photos -->
                                    <?php 
                                    $photos = [
                                        ['path' => $row['front_photo_path'], 'type' => 'day1_front', 'label' => 'BEFORE'],
                                        ['path' => $row['day10_front_photo_path'], 'type' => 'day10_front', 'label' => 'AFTER'],
                                        ['path' => $row['side_photo_path'], 'type' => 'day1_side', 'label' => 'BEFORE'],
                                        ['path' => $row['day10_side_photo_path'], 'type' => 'day10_side', 'label' => 'AFTER'],
                                    ];
                                    ?>
                                    <?php foreach ($photos as $photo): ?>
                                        <div class="aspect-[3/4] bg-[#f4f4f0] rounded border <?= strpos($photo['label'], 'AFTER') !== false && $row['has_day10'] ? 'border-orange-500' : 'border-[#e5e4dc]' ?> relative">
                                            <?php if (!empty($photo['path'])): ?>
                                                <img src="<?= h(url('/media/client_photo.php?id=' . (int) $row['client_id'] . '&photo=' . $photo['type'])) ?>" 
                                                     alt="<?= h($photo['label']) ?>" 
                                                     class="w-full h-full object-cover" />
                                            <?php else: ?>
                                                <div class="flex h-full w-full items-center justify-center">
                                                    <span class="material-symbols-outlined text-[#888263]">image</span>
                                                </div>
                                            <?php endif; ?>
                                            <span class="absolute bottom-1 left-1 <?= strpos($photo['label'], 'AFTER') !== false && $row['has_day10'] ? 'bg-orange-500 text-white' : 'bg-black/50 text-white' ?> text-[8px] px-1 rounded">
                                                <?= h($photo['label']) ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="w-48 flex flex-col justify-center gap-2">
                                    <div class="flex justify-between text-xs">
                                        <span class="text-[#888263]">Start</span>
                                        <span class="font-bold"><?= h(number_format($row['start_weight'], 1)) ?> lbs</span>
                                    </div>
                                    <div class="flex justify-between text-xs">
                                        <span class="text-[#888263]">End</span>
                                        <span class="font-bold"><?= h(number_format($row['end_weight'], 1)) ?> lbs</span>
                                    </div>
                                    <div class="h-1 bg-gray-100 rounded-full mt-2">
                                        <div class="h-full bg-orange-500 rounded-full" style="width: <?= h(min(100, $row['days_completed'] * 10)) ?>%"></div>
                                    </div>
                                    <div class="text-xs text-[#888263] text-center mt-1">
                                        <?= h($row['days_completed']) ?> of 10 days
                                    </div>
                                    <a href="<?= h(url('/coach/client_details.php?id=' . (int) $row['client_id'])) ?>" class="mt-2 w-full py-1.5 border border-[#e5e4dc] text-[10px] font-bold rounded-lg hover:bg-orange-50 transition-all text-center">
                                        VIEW DETAILS
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php if (empty($topByCategory[$currentCategory] ?? [])): ?>
                    <div class="text-center py-12">
                        <p class="text-[#888263] text-lg">No participants in this category yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>