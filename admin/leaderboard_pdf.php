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
@page { 
    size: A4 landscape; 
    margin: 6mm;
}
@media print {
    body { 
        background: #ffffff !important; 
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        font-size: 11px !important;
    }
    .no-print { display: none !important; }
    .print-shadow { box-shadow: none !important; }
    .print-border { border: 1px solid #e9d6ce !important; }
    .print-bg-white { background: white !important; }
    .print-container { max-height: 100vh; overflow: hidden; }
    .print-compact { padding: 0.75rem !important; }
    .print-gap { gap: 0.5rem !important; }
}

:root {
    --primary: #e64c05;
    --primary-dark: #c94104;
    --secondary: #f97316;
    --light-bg: #fafafa;
    --surface: #ffffff;
    --text-primary: #1a1a1a;
    --text-secondary: #6b7280;
    --text-muted: #9ca3af;
    --border: #e5e7eb;
    --accent-light: #fff7ed;
}

body {
    background-color: var(--light-bg);
    color: var(--text-primary);
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
}

.gradient-bg {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
}

.medal-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    border-radius: 50%;
    color: white;
    font-weight: 800;
    font-size: 1.1rem;
    box-shadow: 0 2px 8px rgba(255, 165, 0, 0.4);
}

.stat-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 0.75rem 1rem;
    text-align: center;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1.2;
}

.stat-value-accent {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary);
    line-height: 1.2;
}

.stat-label {
    font-size: 0.65rem;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-top: 0.25rem;
}

.photo-container {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid var(--border);
    background: var(--surface);
}

.photo-label {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
    color: white;
    padding: 0.4rem;
    font-weight: 600;
    font-size: 0.55rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    text-align: center;
}

.section-title {
    font-size: 0.7rem;
    font-weight: 700;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.5rem;
    padding-bottom: 0.25rem;
    border-bottom: 2px solid var(--primary);
    display: inline-block;
}

.info-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
}

.info-label {
    color: var(--text-muted);
    font-weight: 500;
}

.info-value {
    color: var(--text-primary);
    font-weight: 600;
}

.border-custom { border-color: var(--border); }
.text-primary-custom { color: var(--text-primary); }
.text-secondary-custom { color: var(--text-secondary); }
.bg-surface { background-color: var(--surface); }
.bg-light-custom { background-color: var(--light-bg); }
</style>

<div class="no-print mx-auto max-w-7xl px-4 py-6">
    <div class="bg-surface rounded-2xl border border-custom p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="<?= h(url('/admin/leaderboard.php?category=' . urlencode($category))) ?>" 
                   class="inline-flex items-center gap-2 justify-center rounded-xl border border-custom bg-surface px-5 py-2.5 text-sm font-bold text-primary-custom hover:bg-light-custom transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Leaderboard
                </a>
                <div class="text-sm text-secondary-custom hidden md:block">
                    Exporting: <span class="font-bold text-primary-custom"><?= h($category) ?> Category</span>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="text-sm text-secondary-custom">
                    <span class="font-bold">Generated:</span> <?= date('F j, Y, g:i a') ?>
                </div>
                <button onclick="window.print()" 
                        class="inline-flex items-center gap-2 justify-center rounded-xl gradient-bg px-5 py-2.5 text-sm font-bold text-white hover:opacity-90 shadow-md transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print / Save as PDF
                </button>
            </div>
        </div>
    </div>
</div>

<?php if (!$top1): ?>
    <main class="mx-auto max-w-4xl px-4 py-10">
        <div class="bg-surface rounded-2xl border border-custom p-8 text-center shadow-sm">
            <div class="mx-auto w-16 h-16 bg-light-custom rounded-full flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-secondary-custom" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="text-xl font-bold text-primary-custom mb-2">No leaderboard entry found</div>
            <div class="text-secondary-custom">There is no Top 1 participant available for this category.</div>
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
        $daysCompleted = (int) ($top1['days_completed'] ?? 0);
        $completionPercentage = min(100, ($daysCompleted / 10) * 100);

        $day1FrontSrc = !empty($top1['front_photo_path']) ? url('/media/client_photo.php?id=' . (int) $top1['client_id'] . '&photo=day1_front') : '';
        $day10FrontSrc = !empty($top1['day10_front_photo_path']) ? url('/media/client_photo.php?id=' . (int) $top1['client_id'] . '&photo=day10_front') : '';
        $day1SideSrc = !empty($top1['side_photo_path']) ? url('/media/client_photo.php?id=' . (int) $top1['client_id'] . '&photo=day1_side') : '';
        $day10SideSrc = !empty($top1['day10_side_photo_path']) ? url('/media/client_photo.php?id=' . (int) $top1['client_id'] . '&photo=day10_side') : '';
    ?>

    <main class="mx-auto max-w-6xl px-3 pb-4 print-container">
        <div class="bg-surface rounded-xl border border-custom shadow-sm overflow-hidden print-shadow print-border print-bg-white">
            
            <!-- Compact Header -->
            <div class="gradient-bg px-5 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="medal-badge">#1</div>
                        <div>
                            <div class="text-white/80 text-xs font-semibold uppercase tracking-wide"><?= h($categoryText) ?> Category Winner</div>
                            <div class="text-xl font-bold text-white"><?= h($clientName) ?></div>
                        </div>
                    </div>
                    <div class="flex items-center gap-6 text-white text-sm">
                        <div class="text-center">
                            <div class="text-2xl font-bold"><?= h($lossPctText) ?>%</div>
                            <div class="text-xs text-white/80">Weight Loss</div>
                        </div>
                        <div class="h-10 w-px bg-white/20"></div>
                        <div class="text-right">
                            <div class="text-xs text-white/80">Coach: <span class="text-white font-semibold"><?= h($coachName !== '' ? $coachName : '-') ?></span></div>
                            <div class="text-xs text-white/80">BMI: <span class="text-white font-semibold"><?= h($bmiText !== '' ? $bmiText : '-') ?></span> · <?= $daysCompleted ?>/10 Days</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="p-5 print-compact">
                <div class="grid grid-cols-12 gap-5 print-gap">
                    
                    <!-- Left: Photos Section -->
                    <div class="col-span-8">
                        <div class="section-title">10-Day Transformation</div>
                        <div class="grid grid-cols-4 gap-3">
                            <!-- Front Before -->
                            <div class="photo-container aspect-[3/4]">
                                <?php if (!empty($day1FrontSrc)): ?>
                                    <img src="<?= h($day1FrontSrc) ?>" alt="Front Day 1" class="w-full h-full object-cover" />
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center bg-gray-50">
                                        <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    </div>
                                <?php endif; ?>
                                <div class="photo-label">Front · Day 1</div>
                            </div>
                            
                            <!-- Front After -->
                            <div class="photo-container aspect-[3/4]">
                                <?php if (!empty($day10FrontSrc)): ?>
                                    <img src="<?= h($day10FrontSrc) ?>" alt="Front Day 10" class="w-full h-full object-cover" />
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center bg-gray-50">
                                        <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    </div>
                                <?php endif; ?>
                                <div class="photo-label">Front · Day 10</div>
                            </div>
                            
                            <!-- Side Before -->
                            <div class="photo-container aspect-[3/4]">
                                <?php if (!empty($day1SideSrc)): ?>
                                    <img src="<?= h($day1SideSrc) ?>" alt="Side Day 1" class="w-full h-full object-cover" />
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center bg-gray-50">
                                        <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    </div>
                                <?php endif; ?>
                                <div class="photo-label">Side · Day 1</div>
                            </div>
                            
                            <!-- Side After -->
                            <div class="photo-container aspect-[3/4]">
                                <?php if (!empty($day10SideSrc)): ?>
                                    <img src="<?= h($day10SideSrc) ?>" alt="Side Day 10" class="w-full h-full object-cover" />
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center bg-gray-50">
                                        <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    </div>
                                <?php endif; ?>
                                <div class="photo-label">Side · Day 10</div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Stats Panel -->
                    <div class="col-span-4">
                        <div class="section-title">Results</div>
                        <div class="space-y-3">
                            <!-- Main Stat - Loss Percentage -->
                            <div class="bg-gradient-to-br from-orange-50 to-orange-100 border border-orange-200 rounded-lg p-4 text-center">
                                <div class="stat-value-accent text-3xl"><?= h($lossPctText) ?>%</div>
                                <div class="stat-label">Total Weight Loss</div>
                            </div>
                            
                            <!-- Stats Grid -->
                            <div class="grid grid-cols-2 gap-2">
                                <div class="stat-card">
                                    <div class="stat-value"><?= h($lossLbsText) ?></div>
                                    <div class="stat-label">Lbs Lost</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-value"><?= h($lostInchesText) ?></div>
                                    <div class="stat-label">Inches Lost</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-value"><?= number_format((float) ($top1['start_weight'] ?? 0), 1) ?></div>
                                    <div class="stat-label">Start Weight</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-value"><?= number_format((float) ($top1['end_weight'] ?? 0), 1) ?></div>
                                    <div class="stat-label">End Weight</div>
                                </div>
                            </div>
                            
                            <!-- Progress -->
                            <div class="stat-card">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="stat-label" style="margin-top:0">Progress</span>
                                    <span class="text-xs font-bold text-primary"><?= $daysCompleted ?>/10</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="h-2 rounded-full gradient-bg" style="width: <?= $completionPercentage ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Minimal Footer -->
            <div class="border-t border-custom px-5 py-2 flex items-center justify-between text-xs text-gray-400">
                <span><?= h($categoryText) ?> · <?= h($coachName) ?></span>
                <span><?= date('M j, Y') ?></span>
            </div>
        </div>
    </main>

<?php endif; ?>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>