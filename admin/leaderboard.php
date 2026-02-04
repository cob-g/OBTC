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
$hasExcludedColumn = db_has_column('clients', 'excluded_from_leaderboard');

$rows = [];
try {
    $selectClient = $hasFullName ? 'c.full_name' : "'' AS full_name";
    $selectExcluded = $hasExcludedColumn ? 'c.excluded_from_leaderboard' : '0 AS excluded_from_leaderboard';
    $whereClause = $hasExcludedColumn ? 'WHERE c.excluded_from_leaderboard = 0' : '';
    
    $sql = 'SELECT c.id, ' . $selectClient . ', ' . $selectExcluded . ', c.start_weight_lbs, c.bmi_category, c.front_photo_path, c.side_photo_path, c.day10_front_photo_path, c.day10_side_photo_path, u.name AS coach_name, u.email AS coach_email, latest.latest_weight, latest.days_completed, latest.has_day10 '
        . 'FROM clients c '
        . 'INNER JOIN users u ON u.id = c.coach_user_id '
        . 'INNER JOIN ( '
        . '   SELECT client_id, COUNT(*) AS days_completed, MAX(CASE WHEN day_number = 10 THEN 1 ELSE 0 END) AS has_day10, '
        . '          SUBSTRING_INDEX(GROUP_CONCAT(weight_lbs ORDER BY day_number DESC), ",", 1) AS latest_weight '
        . '   FROM client_checkins '
        . '   GROUP BY client_id '
        . ') latest ON latest.client_id = c.id '
        . $whereClause . ' '
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
            'excluded_from_leaderboard' => (int) ($r['excluded_from_leaderboard'] ?? 0),
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

<main class="flex-1 overflow-y-auto bg-slate-50">
    <div class="max-w-[1200px] mx-auto p-4 sm:p-6 lg:p-8">
        <!-- Page Heading -->
        <div class="flex flex-col sm:flex-row sm:flex-wrap sm:justify-between sm:items-end gap-4 mb-6 lg:mb-8">
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-indigo_bloom to-purple-600 text-white shadow-lg">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black leading-tight tracking-tight text-slate-900">Top 10 Leaderboard</h1>
                        <p class="text-slate-500 text-xs sm:text-sm font-medium">Ranked by completion, then weight loss %</p>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 sm:gap-3">
                <a href="<?= h(url('/admin/leaderboard_pdf.php?category=' . urlencode((string) $currentCategory))) ?>" target="_blank" rel="noopener" class="flex items-center justify-center rounded-lg h-10 px-3 sm:px-4 bg-white border border-slate-200 text-slate-700 text-xs sm:text-sm font-bold tracking-[0.015em] hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo_bloom transition-colors shadow-sm">
                    <svg class="h-4 w-4 mr-1.5 sm:mr-2 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <span class="hidden sm:inline">Export </span>PDF
                </a>
                <a href="<?= h(url('/admin/leaderboard.php?export=csv')) ?>" class="flex items-center justify-center rounded-lg h-10 px-3 sm:px-4 bg-white border border-slate-200 text-slate-700 text-xs sm:text-sm font-bold tracking-[0.015em] hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo_bloom transition-colors shadow-sm">
                    <svg class="h-4 w-4 mr-1.5 sm:mr-2 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span class="hidden sm:inline">Export </span>CSV
                </a>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6 lg:mb-8">
            <div class="flex flex-col gap-2 rounded-xl p-4 sm:p-5 lg:p-6 bg-white border border-slate-200 shadow-sm">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-100 text-green-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                    <p class="text-slate-500 text-xs sm:text-sm font-medium">Total Group Loss</p>
                </div>
                <p class="text-slate-900 text-xl sm:text-2xl font-bold"><?= h(number_format($totalLossLbs, 1)) ?> <span class="text-sm font-medium text-slate-500">lbs</span></p>
            </div>
            <div class="flex flex-col gap-2 rounded-xl p-4 sm:p-5 lg:p-6 bg-white border border-slate-200 shadow-sm">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo_bloom">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <p class="text-slate-500 text-xs sm:text-sm font-medium">Avg. Loss</p>
                </div>
                <p class="text-slate-900 text-xl sm:text-2xl font-bold"><?= h(number_format($avgLossPct, 2, '.', '')) ?><span class="text-sm font-medium text-slate-500">%</span></p>
            </div>
            <div class="flex flex-col gap-2 rounded-xl p-4 sm:p-5 lg:p-6 bg-white border border-slate-200 shadow-sm">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <p class="text-slate-500 text-xs sm:text-sm font-medium">Participants</p>
                </div>
                <p class="text-slate-900 text-xl sm:text-2xl font-bold"><?= h($activeParticipants) ?></p>
            </div>
            <div class="flex flex-col gap-2 rounded-xl p-4 sm:p-5 lg:p-6 bg-white border border-slate-200 shadow-sm">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-100 text-purple-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-slate-500 text-xs sm:text-sm font-medium">Completion</p>
                </div>
                <p class="text-slate-900 text-xl sm:text-2xl font-bold"><?= h(number_format($completionRate, 0)) ?><span class="text-sm font-medium text-slate-500">%</span></p>
            </div>
        </div>

        <!-- Category Tabs -->
        <div class="mb-6 lg:mb-8 border-b border-slate-200 bg-white rounded-t-xl -mx-4 sm:mx-0 px-4 sm:px-0 sm:rounded-none sm:bg-transparent">
            <div class="flex gap-1 sm:gap-4 lg:gap-8 overflow-x-auto scrollbar-hide pb-px">
                <?php foreach ($topByCategory as $cat => $list): ?>
                    <a class="flex flex-col items-center justify-center border-b-[3px] <?= $cat === $currentCategory ? 'border-indigo_bloom text-indigo_bloom' : 'border-transparent text-slate-500' ?> pb-3 pt-4 px-2 sm:px-3 hover:text-slate-700 whitespace-nowrap transition-colors"
                       href="?category=<?= h(urlencode($cat)) ?>">
                        <p class="text-xs sm:text-sm font-bold tracking-[0.015em]"><?= h($cat) ?></p>
                        <span class="text-[10px] text-slate-400 mt-0.5"><?= count($list) ?> entries</span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (!$topByCategory): ?>
            <div class="text-center py-12 bg-white rounded-xl border border-slate-200">
                <div class="flex flex-col items-center gap-3">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/>
                        </svg>
                    </div>
                    <p class="text-slate-500 text-lg font-medium">No weigh-ins recorded yet</p>
                    <p class="text-slate-400 text-sm">Data will appear here once participants start logging their progress.</p>
                </div>
            </div>
        <?php else: ?>
            <!-- Rank Cards List -->
            <div class="flex flex-col gap-6">
                <?php foreach ($topByCategory[$currentCategory] ?? [] as $i => $row): ?>
                    <?php $rank = $i + 1; ?>
                    
                    <?php if ($rank === 1): ?>
                        <!-- Rank 1 Card - Winner Design -->
                        <div class="group bg-white rounded-xl border-2 border-indigo_bloom shadow-lg overflow-hidden transition-all hover:shadow-xl">
                            <!-- Header -->
                            <div class="flex flex-col sm:flex-row sm:items-center bg-gradient-to-r from-indigo_bloom to-purple-600 p-4 gap-3">
                                <div class="flex items-center gap-4 flex-1">
                                    <div class="flex items-center justify-center h-12 w-12 sm:h-14 sm:w-14 rounded-full bg-white/20 backdrop-blur text-white font-black text-xl sm:text-2xl shadow-lg border-2 border-white/30">
                                        <svg class="h-6 w-6 sm:h-8 sm:w-8 text-yellow-300" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                                        </svg>
                                    </div>
                                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                                        <div>
                                            <span class="text-xs font-bold uppercase tracking-wider text-indigo-200">1st Place</span>
                                            <h3 class="font-bold text-lg sm:text-xl text-white"><?= h($row['client_name']) ?></h3>
                                        </div>
                                        <?php if ($row['has_day10']): ?>
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-green-500/20 text-green-200 text-xs font-bold w-fit">
                                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                Verified
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 sm:gap-6">
                                    <div class="text-center">
                                        <p class="text-[10px] sm:text-xs text-indigo-200 uppercase font-bold">Total Loss</p>
                                        <p class="text-xl sm:text-2xl font-black text-white"><?= h(number_format($row['loss_lbs'], 1)) ?> <span class="text-sm font-medium opacity-80">lbs</span></p>
                                    </div>
                                    <div class="text-center px-4 sm:px-6 py-2 bg-white/20 backdrop-blur rounded-lg border border-white/30">
                                        <p class="text-[10px] sm:text-xs text-white/80 uppercase font-black">Loss %</p>
                                        <p class="text-2xl sm:text-3xl font-black text-white"><?= h(number_format((float) $row['loss_pct'], 2, '.', '')) ?>%</p>
                                    </div>
                                </div>
                            </div>
                            <!-- Content -->
                            <div class="flex flex-col lg:flex-row p-4 sm:p-6 gap-4 sm:gap-6 lg:gap-8">
                                <!-- Photo Grid -->
                                <div class="grid grid-cols-4 gap-2 sm:gap-4 flex-1">
                                    <div class="flex flex-col gap-1 sm:gap-2">
                                        <p class="text-[8px] sm:text-[10px] font-bold uppercase text-slate-400 tracking-widest text-center">Day 1 Front</p>
                                        <div class="aspect-[3/4] bg-slate-100 rounded-lg overflow-hidden border border-slate-200">
                                            <?php if (!empty($row['front_photo_path'])): ?>
                                                <img src="<?= h(url('/media/client_photo.php?id=' . (int) $row['client_id'] . '&photo=day1_front')) ?>" 
                                                     alt="Day 1 front" 
                                                     class="w-full h-full object-contain" />
                                            <?php else: ?>
                                                <div class="flex h-full w-full items-center justify-center">
                                                    <svg class="h-6 w-6 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-1 sm:gap-2">
                                        <p class="text-[8px] sm:text-[10px] font-bold uppercase text-slate-400 tracking-widest text-center">Day 10 Front</p>
                                        <div class="aspect-[3/4] bg-slate-100 rounded-lg overflow-hidden border-2 <?= $row['has_day10'] ? 'border-indigo_bloom' : 'border-slate-200' ?>">
                                            <?php if (!empty($row['day10_front_photo_path'])): ?>
                                                <img src="<?= h(url('/media/client_photo.php?id=' . (int) $row['client_id'] . '&photo=day10_front')) ?>" 
                                                     alt="Day 10 front" 
                                                     class="w-full h-full object-contain" />
                                            <?php else: ?>
                                                <div class="flex h-full w-full items-center justify-center">
                                                    <svg class="h-6 w-6 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-1 sm:gap-2">
                                        <p class="text-[8px] sm:text-[10px] font-bold uppercase text-slate-400 tracking-widest text-center">Day 1 Side</p>
                                        <div class="aspect-[3/4] bg-slate-100 rounded-lg overflow-hidden border border-slate-200">
                                            <?php if (!empty($row['side_photo_path'])): ?>
                                                <img src="<?= h(url('/media/client_photo.php?id=' . (int) $row['client_id'] . '&photo=day1_side')) ?>" 
                                                     alt="Day 1 side" 
                                                     class="w-full h-full object-contain" />
                                            <?php else: ?>
                                                <div class="flex h-full w-full items-center justify-center">
                                                    <svg class="h-6 w-6 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-1 sm:gap-2">
                                        <p class="text-[8px] sm:text-[10px] font-bold uppercase text-slate-400 tracking-widest text-center">Day 10 Side</p>
                                        <div class="aspect-[3/4] bg-slate-100 rounded-lg overflow-hidden border-2 <?= $row['has_day10'] ? 'border-indigo_bloom' : 'border-slate-200' ?>">
                                            <?php if (!empty($row['day10_side_photo_path'])): ?>
                                                <img src="<?= h(url('/media/client_photo.php?id=' . (int) $row['client_id'] . '&photo=day10_side')) ?>" 
                                                     alt="Day 10 side" 
                                                     class="w-full h-full object-contain" />
                                            <?php else: ?>
                                                <div class="flex h-full w-full items-center justify-center">
                                                    <svg class="h-6 w-6 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <!-- Stats Panel -->
                                <div class="w-full lg:w-72 flex flex-col gap-3 sm:gap-4 bg-gradient-to-br from-slate-50 to-indigo-50 p-4 sm:p-6 rounded-xl border border-indigo-100">
                                    <div class="flex justify-between items-center pb-3 border-b border-indigo-100">
                                        <span class="text-xs sm:text-sm font-medium text-slate-500">Coach</span>
                                        <span class="text-sm sm:text-base font-bold text-slate-900"><?= h($row['coach_name']) ?></span>
                                    </div>
                                    <div class="flex justify-between items-center pb-3 border-b border-indigo-100">
                                        <span class="text-xs sm:text-sm font-medium text-slate-500">Start Weight</span>
                                        <span class="text-sm sm:text-base font-bold text-slate-900"><?= h(number_format($row['start_weight'], 1)) ?> lbs</span>
                                    </div>
                                    <div class="flex justify-between items-center pb-3 border-b border-indigo-100">
                                        <span class="text-xs sm:text-sm font-medium text-slate-500">End Weight</span>
                                        <span class="text-sm sm:text-base font-bold text-slate-900"><?= h(number_format($row['end_weight'], 1)) ?> lbs</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs sm:text-sm font-medium text-slate-500">Days Completed</span>
                                        <span class="text-sm sm:text-base font-bold text-indigo_bloom"><?= h($row['days_completed']) ?>/10</span>
                                    </div>
                                    <a href="<?= h(url('/coach/client_details.php?id=' . (int) $row['client_id'])) ?>" class="mt-2 w-full py-2.5 sm:py-3 bg-slate-900 text-white text-xs font-bold rounded-lg hover:bg-slate-800 transition-colors text-center flex items-center justify-center gap-2">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        VIEW FULL PROFILE
                                    </a>
                                    <button onclick="excludeClient(<?= (int) $row['client_id'] ?>, '<?= h($row['client_name']) ?>')" class="mt-2 w-full py-2 border-2 border-red-200 text-red-600 text-xs font-bold rounded-lg hover:bg-red-50 hover:border-red-300 transition-colors text-center flex items-center justify-center gap-2" title="Remove from Top 10 leaderboard">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        EXCLUDE FROM TOP 10
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Rank 2-10 Cards -->
                        <div class="group bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden transition-all hover:border-indigo-300 hover:shadow-md">
                            <!-- Header -->
                            <div class="flex flex-col sm:flex-row sm:items-center p-3 sm:p-4 border-b border-slate-100 gap-3">
                                <div class="flex items-center gap-3 sm:gap-4 flex-1">
                                    <div class="flex items-center justify-center h-10 w-10 sm:h-12 sm:w-12 rounded-full <?= $rank === 2 ? 'bg-slate-200 text-slate-700' : ($rank === 3 ? 'bg-amber-100 text-amber-700' : 'bg-indigo-50 text-indigo_bloom') ?> font-black text-base sm:text-lg">#<?= h($rank) ?></div>
                                    <div class="flex items-center gap-3 flex-1 min-w-0">
                                        <div class="hidden sm:flex h-10 w-10 rounded-full bg-slate-100 items-center justify-center flex-shrink-0">
                                            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h3 class="font-bold text-sm sm:text-base text-slate-900 truncate"><?= h($row['client_name']) ?></h3>
                                            <p class="text-xs text-slate-500 truncate">Coach: <?= h($row['coach_name']) ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 sm:gap-6">
                                    <div class="text-center">
                                        <p class="text-[9px] sm:text-[10px] text-slate-400 uppercase font-bold tracking-tight">Total Loss</p>
                                        <p class="text-lg sm:text-xl font-bold text-slate-900"><?= h(number_format($row['loss_lbs'], 1)) ?> <span class="text-xs text-slate-400">lbs</span></p>
                                    </div>
                                    <div class="text-center px-3 sm:px-4 py-1.5 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-lg border border-indigo-100">
                                        <p class="text-[9px] sm:text-[10px] text-indigo_bloom uppercase font-bold tracking-tight">Loss %</p>
                                        <p class="text-xl sm:text-2xl font-black text-indigo_bloom"><?= h(number_format((float) $row['loss_pct'], 2, '.', '')) ?>%</p>
                                    </div>
                                </div>
                            </div>
                            <!-- Content -->
                            <div class="flex flex-col sm:flex-row p-3 sm:p-5 gap-4 sm:gap-6">
                                <!-- Photos -->
                                <div class="grid grid-cols-4 gap-2 sm:gap-3 flex-1">
                                    <?php 
                                    $photos = [
                                        ['path' => $row['front_photo_path'], 'type' => 'day1_front', 'label' => 'DAY 1'],
                                        ['path' => $row['day10_front_photo_path'], 'type' => 'day10_front', 'label' => 'DAY 10'],
                                        ['path' => $row['side_photo_path'], 'type' => 'day1_side', 'label' => 'DAY 1'],
                                        ['path' => $row['day10_side_photo_path'], 'type' => 'day10_side', 'label' => 'DAY 10'],
                                    ];
                                    ?>
                                    <?php foreach ($photos as $photo): ?>
                                        <div class="aspect-[3/4] bg-slate-100 rounded-lg border <?= strpos($photo['type'], 'day10') !== false && $row['has_day10'] ? 'border-indigo_bloom' : 'border-slate-200' ?> relative overflow-hidden">
                                            <?php if (!empty($photo['path'])): ?>
                                                <img src="<?= h(url('/media/client_photo.php?id=' . (int) $row['client_id'] . '&photo=' . $photo['type'])) ?>" 
                                                     alt="<?= h($photo['label']) ?>" 
                                                     class="w-full h-full object-contain" />
                                            <?php else: ?>
                                                <div class="flex h-full w-full items-center justify-center">
                                                    <svg class="h-5 w-5 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                                                </div>
                                            <?php endif; ?>
                                            <span class="absolute bottom-0.5 sm:bottom-1 left-0.5 sm:left-1 <?= strpos($photo['type'], 'day10') !== false && $row['has_day10'] ? 'bg-indigo_bloom text-white' : 'bg-slate-800/70 text-white' ?> text-[7px] sm:text-[8px] px-1 rounded font-bold">
                                                <?= h($photo['label']) ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <!-- Stats -->
                                <div class="w-full sm:w-48 flex flex-col justify-center gap-2">
                                    <div class="flex justify-between text-xs">
                                        <span class="text-slate-500">Start</span>
                                        <span class="font-bold text-slate-900"><?= h(number_format($row['start_weight'], 1)) ?> lbs</span>
                                    </div>
                                    <div class="flex justify-between text-xs">
                                        <span class="text-slate-500">End</span>
                                        <span class="font-bold text-slate-900"><?= h(number_format($row['end_weight'], 1)) ?> lbs</span>
                                    </div>
                                    <div class="h-2 bg-slate-100 rounded-full mt-2 overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-indigo_bloom to-purple-500 rounded-full transition-all" style="width: <?= h(min(100, $row['days_completed'] * 10)) ?>%"></div>
                                    </div>
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="text-slate-500"><?= h($row['days_completed']) ?> of 10 days</span>
                                        <?php if ($row['has_day10']): ?>
                                            <span class="text-green-600 font-bold flex items-center gap-0.5">
                                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                Done
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="<?= h(url('/coach/client_details.php?id=' . (int) $row['client_id'])) ?>" class="mt-2 w-full py-2 border border-slate-200 text-xs font-bold rounded-lg hover:bg-indigo-50 hover:border-indigo-200 hover:text-indigo_bloom transition-all text-center flex items-center justify-center gap-1.5">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        VIEW DETAILS
                                    </a>                                    <button onclick="excludeClient(<?= (int) $row['client_id'] ?>, '<?= h($row['client_name']) ?>')" class="mt-1 w-full py-2 border border-red-200 text-red-600 text-[11px] font-bold rounded-lg hover:bg-red-50 hover:border-red-300 transition-all text-center flex items-center justify-center gap-1" title="Remove from Top 10 leaderboard">
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        EXCLUDE
                                    </button>                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <?php if (empty($topByCategory[$currentCategory] ?? [])): ?>
                    <div class="text-center py-12 bg-white rounded-xl border border-slate-200">
                        <div class="flex flex-col items-center gap-3">
                            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                                </svg>
                            </div>
                            <p class="text-slate-600 text-base font-medium">No participants in this category</p>
                            <p class="text-slate-400 text-sm">Check back later or select a different BMI category.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
function excludeClient(clientId, clientName) {
    if (!confirm(`Are you sure you want to exclude "${clientName}" from the Top 10 leaderboard?\n\nThis won't delete their data - they just won't appear in the leaderboard rankings.`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('client_id', clientId);
    formData.append('action', 'exclude');
    
    fetch('<?= h(url('/admin/leaderboard_exclude.php')) ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message + '\n\nThe page will now reload.');
            window.location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to exclude client'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while excluding the client.');
    });
}
</script>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>