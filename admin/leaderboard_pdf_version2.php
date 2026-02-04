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

$page_title = 'Elite Champion - Version 3';
require __DIR__ . '/../partials/layout_top.php';
?>

<style>
@page { size: A4 landscape; margin: 0; }
@media print {
    body { background: #0f0f23 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .no-print { display: none !important; }
    .print-page { box-shadow: none !important; margin: 0 !important; }
}
.bg-dark-gradient { background: linear-gradient(135deg, #0f0f23 0%, #1a1a3e 50%, #0f0f23 100%); }
.gold-gradient { background: linear-gradient(135deg, #D4AF37 0%, #FFD700 50%, #D4AF37 100%); }
.gold-text { background: linear-gradient(135deg, #D4AF37 0%, #FFD700 50%, #D4AF37 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
.indigo-glow { box-shadow: 0 0 40px rgba(104, 63, 183, 0.4), inset 0 1px 0 rgba(255,255,255,0.1); }
.gold-glow { box-shadow: 0 0 30px rgba(212, 175, 55, 0.5); }
.shimmer { animation: shimmer 3s ease-in-out infinite; }
@keyframes shimmer { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }
@media print { .shimmer { animation: none; } }
.border-gold { border-color: rgba(212, 175, 55, 0.5); }
.text-gold { color: #D4AF37; }
</style>

<div class="no-print mx-auto max-w-4xl px-4 py-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="<?= h(url('/admin/leaderboard.php?category=' . urlencode($category))) ?>" class="inline-flex items-center justify-center rounded-xl border border-indigo-500/30 bg-indigo-950 px-4 py-2 text-sm font-extrabold text-white hover:bg-indigo-900">Back to Leaderboard</a>
        <button onclick="window.print()" class="gold-gradient inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-extrabold text-zinc-900 hover:opacity-90">Print / Save as PDF</button>
    </div>
</div>

<?php if (!$top1): ?>
    <main class="mx-auto max-w-4xl px-4 py-10">
        <div class="rounded-2xl border border-indigo-500/30 bg-indigo-950 p-6">
            <div class="text-lg font-extrabold tracking-tight text-white">No leaderboard entry found.</div>
            <div class="mt-1 text-sm text-indigo-300">There is no Top 1 participant available for this category.</div>
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
        $startWeight = number_format((float) ($top1['start_weight'] ?? 0), 1, '.', '');
        $endWeight = number_format((float) ($top1['end_weight'] ?? 0), 1, '.', '');
        $daysCompleted = (int) ($top1['days_completed'] ?? 0);

        $day1FrontSrc = !empty($top1['front_photo_path']) ? url('/media/client_photo.php?id=' . (int) $top1['client_id'] . '&photo=day1_front') : '';
        $day10FrontSrc = !empty($top1['day10_front_photo_path']) ? url('/media/client_photo.php?id=' . (int) $top1['client_id'] . '&photo=day10_front') : '';
        $day1SideSrc = !empty($top1['side_photo_path']) ? url('/media/client_photo.php?id=' . (int) $top1['client_id'] . '&photo=day1_side') : '';
        $day10SideSrc = !empty($top1['day10_side_photo_path']) ? url('/media/client_photo.php?id=' . (int) $top1['client_id'] . '&photo=day10_side') : '';
    ?>

    <!-- Premium Dark Layout -->
    <main class="print-page mx-auto" style="width: 297mm; height: 210mm;">
        <div class="relative h-full w-full bg-dark-gradient">
            
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-5">
                <svg width="100%" height="100%">
                    <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                        <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="0.5"/>
                    </pattern>
                    <rect width="100%" height="100%" fill="url(#grid)"/>
                </svg>
            </div>

            <!-- Gold Corner Accents -->
            <div class="absolute left-4 top-4 h-20 w-20">
                <svg viewBox="0 0 80 80" fill="none" class="h-full w-full">
                    <path d="M0 80V0h80" stroke="url(#corner1)" stroke-width="2" fill="none"/>
                    <defs><linearGradient id="corner1" x1="0" y1="0" x2="80" y2="0"><stop stop-color="#D4AF37"/><stop offset="1" stop-color="#D4AF37" stop-opacity="0"/></linearGradient></defs>
                </svg>
            </div>
            <div class="absolute right-4 top-4 h-20 w-20 rotate-90">
                <svg viewBox="0 0 80 80" fill="none" class="h-full w-full">
                    <path d="M0 80V0h80" stroke="url(#corner2)" stroke-width="2" fill="none"/>
                    <defs><linearGradient id="corner2" x1="0" y1="0" x2="80" y2="0"><stop stop-color="#D4AF37"/><stop offset="1" stop-color="#D4AF37" stop-opacity="0"/></linearGradient></defs>
                </svg>
            </div>
            <div class="absolute bottom-4 left-4 h-20 w-20 -rotate-90">
                <svg viewBox="0 0 80 80" fill="none" class="h-full w-full">
                    <path d="M0 80V0h80" stroke="url(#corner3)" stroke-width="2" fill="none"/>
                    <defs><linearGradient id="corner3" x1="0" y1="0" x2="80" y2="0"><stop stop-color="#D4AF37"/><stop offset="1" stop-color="#D4AF37" stop-opacity="0"/></linearGradient></defs>
                </svg>
            </div>
            <div class="absolute bottom-4 right-4 h-20 w-20 rotate-180">
                <svg viewBox="0 0 80 80" fill="none" class="h-full w-full">
                    <path d="M0 80V0h80" stroke="url(#corner4)" stroke-width="2" fill="none"/>
                    <defs><linearGradient id="corner4" x1="0" y1="0" x2="80" y2="0"><stop stop-color="#D4AF37"/><stop offset="1" stop-color="#D4AF37" stop-opacity="0"/></linearGradient></defs>
                </svg>
            </div>

            <div class="relative z-10 flex h-full flex-col p-6">
                <!-- Header -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <!-- Crown Icon -->
                        <div class="shimmer">
                            <svg class="h-12 w-12" viewBox="0 0 48 48" fill="none">
                                <path d="M8 36h32l-4-20-8 8-4-12-4 12-8-8-4 20z" fill="url(#crown-fill)" stroke="#D4AF37" stroke-width="2"/>
                                <circle cx="8" cy="14" r="3" fill="#D4AF37"/>
                                <circle cx="24" cy="8" r="3" fill="#D4AF37"/>
                                <circle cx="40" cy="14" r="3" fill="#D4AF37"/>
                                <rect x="6" y="36" width="36" height="4" rx="1" fill="#D4AF37"/>
                                <defs>
                                    <linearGradient id="crown-fill" x1="8" y1="12" x2="40" y2="36">
                                        <stop stop-color="#FFD700"/><stop offset="1" stop-color="#B8860B"/>
                                    </linearGradient>
                                </defs>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-black tracking-wider gold-text">10 DAYS CHALLENGE</h1>
                            <p class="text-xs font-semibold tracking-[0.3em] text-indigo-300">ELITE TRANSFORMATION PROGRAM</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="gold-gradient gold-glow rounded-lg px-4 py-2">
                            <span class="text-xs font-black text-zinc-900"><?= h(strtoupper($categoryText)) ?></span>
                        </div>
                        <p class="mt-1 text-[10px] font-semibold text-zinc-500"><?= date('F d, Y') ?></p>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="mt-3 flex flex-1 gap-5 min-h-0">
                    
                    <!-- Photos Section -->
                    <div class="flex-1 flex flex-col min-h-0">
                        <!-- Before/After Labels -->
                        <div class="grid grid-cols-2 gap-4 flex-shrink-0">
                            <div class="text-center">
                                <span class="inline-flex items-center gap-2 rounded-full border border-indigo-500/50 bg-indigo-950/80 px-4 py-1">
                                    <span class="h-2 w-2 rounded-full bg-indigo-400"></span>
                                    <span class="text-xs font-bold tracking-widest text-indigo-300">BEFORE</span>
                                </span>
                            </div>
                            <div class="text-center">
                                <span class="inline-flex items-center gap-2 rounded-full border border-gold/50 bg-amber-950/50 px-4 py-1">
                                    <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                                    <span class="text-xs font-bold tracking-widest text-gold">AFTER</span>
                                </span>
                            </div>
                        </div>

                        <!-- Photo Grid -->
                        <div class="grid grid-cols-2 gap-4 mt-2 flex-1 min-h-0">
                            <!-- Day 1 Photos -->
                            <div class="flex flex-col min-h-0">
                                <div class="grid grid-cols-2 gap-2 flex-1 min-h-0">
                                    <div class="indigo-glow overflow-hidden rounded-xl border border-indigo-500/30 flex items-center justify-center bg-indigo-950/50">
                                        <?php if (!empty($day1FrontSrc)): ?>
                                            <img src="<?= h($day1FrontSrc) ?>" alt="Day 1 Front" class="max-h-full max-w-full object-contain" />
                                        <?php else: ?>
                                            <div class="flex h-full w-full flex-col items-center justify-center">
                                                <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                <span class="mt-1 text-[8px] font-bold text-indigo-500">FRONT</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="indigo-glow overflow-hidden rounded-xl border border-indigo-500/30 flex items-center justify-center bg-indigo-950/50">
                                        <?php if (!empty($day1SideSrc)): ?>
                                            <img src="<?= h($day1SideSrc) ?>" alt="Day 1 Side" class="max-h-full max-w-full object-contain" />
                                        <?php else: ?>
                                            <div class="flex h-full w-full flex-col items-center justify-center">
                                                <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                <span class="mt-1 text-[8px] font-bold text-indigo-500">SIDE</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-center mt-1 flex-shrink-0">
                                    <span class="text-[10px] font-semibold text-zinc-500">Day 1 • <?= h($startWeight) ?> lbs</span>
                                </div>
                            </div>

                            <!-- Day 10 Photos -->
                            <div class="flex flex-col min-h-0">
                                <div class="grid grid-cols-2 gap-2 flex-1 min-h-0">
                                    <div class="gold-glow overflow-hidden rounded-xl border border-gold/50 flex items-center justify-center bg-amber-950/30">
                                        <?php if (!empty($day10FrontSrc)): ?>
                                            <img src="<?= h($day10FrontSrc) ?>" alt="Day 10 Front" class="max-h-full max-w-full object-contain" />
                                        <?php else: ?>
                                            <div class="flex h-full w-full flex-col items-center justify-center">
                                                <svg class="h-8 w-8 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                <span class="mt-1 text-[8px] font-bold text-amber-600">FRONT</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="gold-glow overflow-hidden rounded-xl border border-gold/50 flex items-center justify-center bg-amber-950/30">
                                        <?php if (!empty($day10SideSrc)): ?>
                                            <img src="<?= h($day10SideSrc) ?>" alt="Day 10 Side" class="max-h-full max-w-full object-contain" />
                                        <?php else: ?>
                                            <div class="flex h-full w-full flex-col items-center justify-center">
                                                <svg class="h-8 w-8 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                <span class="mt-1 text-[8px] font-bold text-amber-600">SIDE</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-center mt-1 flex-shrink-0">
                                    <span class="text-[10px] font-semibold text-gold">Day 10 • <?= h($endWeight) ?> lbs</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Champion Profile Section -->
                    <div class="w-80 space-y-3">
                        <!-- Rank Badge -->
                        <div class="relative overflow-hidden rounded-2xl border border-gold/30 bg-gradient-to-br from-amber-950/50 to-zinc-950 p-5">
                            <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-gradient-to-br from-amber-500/20 to-transparent blur-2xl"></div>
                            <div class="relative flex items-center gap-4">
                                <div class="flex h-16 w-16 items-center justify-center rounded-full gold-gradient gold-glow">
                                    <span class="text-3xl font-black text-zinc-900">#1</span>
                                </div>
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-widest text-zinc-500">Category Champion</p>
                                    <p class="mt-1 text-lg font-black tracking-tight text-white"><?= h(strtoupper($clientName)) ?></p>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center gap-2 border-t border-zinc-800 pt-3">
                                <svg class="h-4 w-4 text-indigo-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                                <span class="text-xs font-semibold text-zinc-400">Coach:</span>
                                <span class="text-xs font-bold text-indigo-300"><?= h($coachName !== '' ? $coachName : '-') ?></span>
                            </div>
                        </div>

                        <!-- Primary Stats -->
                        <div class="grid grid-cols-2 gap-2">
                            <div class="rounded-xl border border-gold/30 bg-gradient-to-br from-amber-950/30 to-zinc-950 p-3 text-center">
                                <p class="text-[9px] font-bold uppercase tracking-widest text-zinc-500">Lost</p>
                                <p class="mt-1 text-3xl font-black gold-text"><?= h($lossLbsText) ?></p>
                                <p class="text-[10px] font-bold text-gold">POUNDS</p>
                            </div>
                            <div class="rounded-xl border border-indigo-500/30 bg-gradient-to-br from-indigo-950/50 to-zinc-950 p-3 text-center">
                                <p class="text-[9px] font-bold uppercase tracking-widest text-zinc-500">Loss</p>
                                <p class="mt-1 text-3xl font-black text-indigo-300"><?= h($lossPctText) ?>%</p>
                                <p class="text-[10px] font-bold text-indigo-400">PERCENT</p>
                            </div>
                        </div>

                        <!-- Secondary Stats -->
                        <div class="flex gap-2">
                            <div class="flex-1 rounded-lg border border-zinc-800 bg-zinc-900/50 p-2 text-center">
                                <p class="text-[8px] font-bold uppercase text-zinc-600">BMI</p>
                                <p class="text-sm font-black text-zinc-300"><?= h($bmiText !== '' ? $bmiText : '-') ?></p>
                            </div>
                            <div class="flex-1 rounded-lg border border-zinc-800 bg-zinc-900/50 p-2 text-center">
                                <p class="text-[8px] font-bold uppercase text-zinc-600">Inches</p>
                                <p class="text-sm font-black text-zinc-300"><?= h($lostInchesText) ?></p>
                            </div>
                            <div class="flex-1 rounded-lg border border-zinc-800 bg-zinc-900/50 p-2 text-center">
                                <p class="text-[8px] font-bold uppercase text-zinc-600">Days</p>
                                <p class="text-sm font-black text-zinc-300"><?= h((string) $daysCompleted) ?>/10</p>
                            </div>
                        </div>

                        <!-- Progress Visualization -->
                        <div class="rounded-xl border border-zinc-800 bg-zinc-900/30 p-3">
                            <p class="mb-2 text-[9px] font-bold uppercase tracking-widest text-zinc-600">Transformation Progress</p>
                            <div class="relative">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-semibold text-zinc-500"><?= h($startWeight) ?></span>
                                    <span class="font-bold text-gold"><?= h($endWeight) ?></span>
                                </div>
                                <div class="mt-1 h-2 overflow-hidden rounded-full bg-zinc-800">
                                    <div class="gold-gradient h-full rounded-full" style="width: <?= min(100, max(10, (float) $lossPctText * 5)) ?>%;"></div>
                                </div>
                                <div class="mt-1 flex justify-center">
                                    <span class="rounded-full bg-zinc-800 px-2 py-0.5 text-[9px] font-bold text-gold">-<?= h($lossLbsText) ?> lbs</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between border-t border-zinc-800 pt-2 mt-2 flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-1">
                            <div class="h-2 w-2 rounded-full bg-green-500"></div>
                            <span class="text-[9px] font-semibold text-zinc-500">VERIFIED</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-gold" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 2a2 2 0 00-2 2v14l3.5-2 3.5 2 3.5-2 3.5 2V4a2 2 0 00-2-2H5zm2.5 3a1.5 1.5 0 100 3 1.5 1.5 0 000-3zm6.207.293a1 1 0 00-1.414 0l-6 6a1 1 0 101.414 1.414l6-6a1 1 0 000-1.414zM12.5 10a1.5 1.5 0 100 3 1.5 1.5 0 000-3z" clip-rule="evenodd"/></svg>
                        <span class="text-xs font-bold tracking-wider text-zinc-400">ELITE CHAMPION CERTIFICATE</span>
                    </div>
                    <p class="text-[9px] font-semibold text-zinc-600"><?= date('F d, Y') ?></p>
                </div>
            </div>
        </div>
    </main>

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () {
                window.print();
            }, 500);
        });
    </script>
<?php endif; ?>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>
