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

$page_title = 'Champion Certificate - Version 2';
require __DIR__ . '/../partials/layout_top.php';
?>

<style>
@page { size: A4 landscape; margin: 0; }
@media print {
    body { background: #ffffff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .no-print { display: none !important; }
    .print-page { box-shadow: none !important; margin: 0 !important; border-radius: 0 !important; }
}
.gradient-fire { background: linear-gradient(135deg, #E74B05 0%, #F26E10 50%, #FFB347 100%); }
.gradient-indigo { background: linear-gradient(135deg, #683FB7 0%, #8B5CF6 100%); }
.text-shadow { text-shadow: 2px 2px 4px rgba(0,0,0,0.3); }
.stat-glow { box-shadow: 0 0 30px rgba(231, 75, 5, 0.3); }
.trophy-float { animation: float 3s ease-in-out infinite; }
@keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-10px); } }
@media print { .trophy-float { animation: none; } }
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
        $startWeight = number_format((float) ($top1['start_weight'] ?? 0), 1, '.', '');
        $endWeight = number_format((float) ($top1['end_weight'] ?? 0), 1, '.', '');

        $day1FrontSrc = !empty($top1['front_photo_path']) ? url('/media/client_photo.php?id=' . (int) $top1['client_id'] . '&photo=day1_front') : '';
        $day10FrontSrc = !empty($top1['day10_front_photo_path']) ? url('/media/client_photo.php?id=' . (int) $top1['client_id'] . '&photo=day10_front') : '';
        $day1SideSrc = !empty($top1['side_photo_path']) ? url('/media/client_photo.php?id=' . (int) $top1['client_id'] . '&photo=day1_side') : '';
        $day10SideSrc = !empty($top1['day10_side_photo_path']) ? url('/media/client_photo.php?id=' . (int) $top1['client_id'] . '&photo=day10_side') : '';
    ?>

    <!-- Certificate Style Layout -->
    <main class="print-page mx-auto" style="width: 297mm; height: 210mm; padding: 8mm;">
        <div class="relative h-full w-full overflow-hidden rounded-3xl border-4 border-orange-400" style="background: linear-gradient(180deg, #FFF7ED 0%, #FFFFFF 30%, #FFFFFF 70%, #FFF7ED 100%);">
            
            <!-- Decorative Corner Elements -->
            <div class="absolute left-0 top-0 h-32 w-32">
                <svg viewBox="0 0 100 100" class="h-full w-full text-orange-200">
                    <path d="M0 0 L100 0 L100 20 Q50 20 20 50 L20 100 L0 100 Z" fill="currentColor"/>
                </svg>
            </div>
            <div class="absolute right-0 top-0 h-32 w-32 rotate-90">
                <svg viewBox="0 0 100 100" class="h-full w-full text-orange-200">
                    <path d="M0 0 L100 0 L100 20 Q50 20 20 50 L20 100 L0 100 Z" fill="currentColor"/>
                </svg>
            </div>
            <div class="absolute bottom-0 left-0 h-32 w-32 -rotate-90">
                <svg viewBox="0 0 100 100" class="h-full w-full text-orange-200">
                    <path d="M0 0 L100 0 L100 20 Q50 20 20 50 L20 100 L0 100 Z" fill="currentColor"/>
                </svg>
            </div>
            <div class="absolute bottom-0 right-0 h-32 w-32 rotate-180">
                <svg viewBox="0 0 100 100" class="h-full w-full text-orange-200">
                    <path d="M0 0 L100 0 L100 20 Q50 20 20 50 L20 100 L0 100 Z" fill="currentColor"/>
                </svg>
            </div>

            <div class="relative z-10 flex h-full flex-col p-6">
                <!-- Header Section -->
                <div class="text-center">
                    <div class="inline-flex items-center gap-3">
                        <!-- Trophy Icon -->
                        <div class="trophy-float">
                            <svg class="h-14 w-14 text-orange-500" viewBox="0 0 64 64" fill="none">
                                <path d="M16 8h32v8c0 12-6 20-16 24-10-4-16-12-16-24V8z" fill="url(#trophy-gold)" stroke="#B45309" stroke-width="2"/>
                                <path d="M16 14c-6 0-10 4-10 10s4 10 10 10" stroke="#B45309" stroke-width="2" fill="none"/>
                                <path d="M48 14c6 0 10 4 10 10s-4 10-10 10" stroke="#B45309" stroke-width="2" fill="none"/>
                                <rect x="24" y="44" width="16" height="8" rx="2" fill="#92400E"/>
                                <rect x="20" y="52" width="24" height="6" rx="2" fill="#78350F"/>
                                <ellipse cx="32" cy="20" rx="10" ry="5" fill="url(#trophy-shine)" opacity="0.6"/>
                                <defs>
                                    <linearGradient id="trophy-gold" x1="16" y1="8" x2="48" y2="40">
                                        <stop stop-color="#FCD34D"/><stop offset="1" stop-color="#F59E0B"/>
                                    </linearGradient>
                                    <radialGradient id="trophy-shine" cx="32" cy="16" r="12">
                                        <stop stop-color="#FEF3C7"/><stop offset="1" stop-color="#FCD34D" stop-opacity="0"/>
                                    </radialGradient>
                                </defs>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl font-black tracking-tight text-zinc-800">10 DAYS CHALLENGE</h1>
                            <p class="text-sm font-bold uppercase tracking-widest text-orange-600">Category Champion</p>
                        </div>
                        <div class="trophy-float">
                            <svg class="h-14 w-14 text-orange-500" viewBox="0 0 64 64" fill="none">
                                <path d="M16 8h32v8c0 12-6 20-16 24-10-4-16-12-16-24V8z" fill="url(#trophy-gold2)" stroke="#B45309" stroke-width="2"/>
                                <path d="M16 14c-6 0-10 4-10 10s4 10 10 10" stroke="#B45309" stroke-width="2" fill="none"/>
                                <path d="M48 14c6 0 10 4 10 10s-4 10-10 10" stroke="#B45309" stroke-width="2" fill="none"/>
                                <rect x="24" y="44" width="16" height="8" rx="2" fill="#92400E"/>
                                <rect x="20" y="52" width="24" height="6" rx="2" fill="#78350F"/>
                                <ellipse cx="32" cy="20" rx="10" ry="5" fill="url(#trophy-shine2)" opacity="0.6"/>
                                <defs>
                                    <linearGradient id="trophy-gold2" x1="16" y1="8" x2="48" y2="40">
                                        <stop stop-color="#FCD34D"/><stop offset="1" stop-color="#F59E0B"/>
                                    </linearGradient>
                                    <radialGradient id="trophy-shine2" cx="32" cy="16" r="12">
                                        <stop stop-color="#FEF3C7"/><stop offset="1" stop-color="#FCD34D" stop-opacity="0"/>
                                    </radialGradient>
                                </defs>
                            </svg>
                        </div>
                    </div>

                    <!-- Champion Badge -->
                    <div class="mt-3 inline-flex items-center gap-2 rounded-full gradient-fire px-6 py-2 shadow-lg">
                        <span class="text-2xl font-black text-white text-shadow">#1</span>
                        <span class="text-lg font-bold uppercase tracking-wide text-white"><?= h(strtoupper($categoryText)) ?> CATEGORY</span>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="mt-3 flex flex-1 gap-5 min-h-0">
                    <!-- Left: Photos Grid -->
                    <div class="flex-1 flex flex-col min-h-0">
                        <div class="grid grid-cols-2 gap-3 flex-1 min-h-0">
                            <!-- Day 1 Column -->
                            <div class="flex flex-col min-h-0">
                                <div class="rounded-lg gradient-indigo px-3 py-1 text-center flex-shrink-0">
                                    <span class="text-xs font-black uppercase tracking-wide text-white">DAY 1 - BEFORE</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 mt-2 flex-1 min-h-0">
                                    <div class="overflow-hidden rounded-xl border-2 border-purple-300 bg-purple-50 flex items-center justify-center">
                                        <?php if (!empty($day1FrontSrc)): ?>
                                            <img src="<?= h($day1FrontSrc) ?>" alt="Day 1 Front" class="max-h-full max-w-full object-contain" />
                                        <?php else: ?>
                                            <div class="flex h-full w-full flex-col items-center justify-center text-purple-300">
                                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                <span class="mt-1 text-[9px] font-bold">FRONT</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="overflow-hidden rounded-xl border-2 border-purple-300 bg-purple-50 flex items-center justify-center">
                                        <?php if (!empty($day1SideSrc)): ?>
                                            <img src="<?= h($day1SideSrc) ?>" alt="Day 1 Side" class="max-h-full max-w-full object-contain" />
                                        <?php else: ?>
                                            <div class="flex h-full w-full flex-col items-center justify-center text-purple-300">
                                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                <span class="mt-1 text-[9px] font-bold">SIDE</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Day 10 Column -->
                            <div class="flex flex-col min-h-0">
                                <div class="rounded-lg gradient-fire px-3 py-1 text-center flex-shrink-0">
                                    <span class="text-xs font-black uppercase tracking-wide text-white">DAY 10 - AFTER</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 mt-2 flex-1 min-h-0">
                                    <div class="overflow-hidden rounded-xl border-2 border-orange-300 bg-orange-50 flex items-center justify-center">
                                        <?php if (!empty($day10FrontSrc)): ?>
                                            <img src="<?= h($day10FrontSrc) ?>" alt="Day 10 Front" class="max-h-full max-w-full object-contain" />
                                        <?php else: ?>
                                            <div class="flex h-full w-full flex-col items-center justify-center text-orange-300">
                                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                <span class="mt-1 text-[9px] font-bold">FRONT</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="overflow-hidden rounded-xl border-2 border-orange-300 bg-orange-50 flex items-center justify-center">
                                        <?php if (!empty($day10SideSrc)): ?>
                                            <img src="<?= h($day10SideSrc) ?>" alt="Day 10 Side" class="max-h-full max-w-full object-contain" />
                                        <?php else: ?>
                                            <div class="flex h-full w-full flex-col items-center justify-center text-orange-300">
                                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                <span class="mt-1 text-[9px] font-bold">SIDE</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Stats & Info -->
                    <div class="w-72 space-y-3">
                        <!-- Champion Name Card -->
                        <div class="rounded-2xl bg-white p-4 shadow-lg ring-2 ring-orange-200">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-zinc-400">Champion</p>
                            <p class="mt-1 text-xl font-black tracking-tight text-zinc-800"><?= h(strtoupper($clientName)) ?></p>
                            <div class="mt-2 flex items-center gap-2 text-sm">
                                <span class="text-zinc-400">Coach:</span>
                                <span class="font-bold text-indigo-600"><?= h($coachName !== '' ? $coachName : '-') ?></span>
                            </div>
                        </div>

                        <!-- Stats Grid -->
                        <div class="grid grid-cols-2 gap-2">
                            <div class="stat-glow rounded-xl gradient-fire p-3 text-center">
                                <p class="text-[9px] font-bold uppercase tracking-wide text-orange-100">Weight Lost</p>
                                <p class="text-2xl font-black text-white text-shadow"><?= h($lossLbsText) ?></p>
                                <p class="text-xs font-bold text-orange-100">LBS</p>
                            </div>
                            <div class="stat-glow rounded-xl gradient-fire p-3 text-center">
                                <p class="text-[9px] font-bold uppercase tracking-wide text-orange-100">Loss Percent</p>
                                <p class="text-2xl font-black text-white text-shadow"><?= h($lossPctText) ?>%</p>
                                <p class="text-xs font-bold text-orange-100">TOTAL</p>
                            </div>
                        </div>

                        <!-- Additional Stats -->
                        <div class="grid grid-cols-3 gap-2">
                            <div class="rounded-lg bg-zinc-100 p-2 text-center">
                                <p class="text-[8px] font-bold uppercase text-zinc-400">BMI</p>
                                <p class="text-sm font-black text-zinc-700"><?= h($bmiText !== '' ? $bmiText : '-') ?></p>
                            </div>
                            <div class="rounded-lg bg-zinc-100 p-2 text-center">
                                <p class="text-[8px] font-bold uppercase text-zinc-400">Inches</p>
                                <p class="text-sm font-black text-zinc-700"><?= h($lostInchesText) ?></p>
                            </div>
                            <div class="rounded-lg bg-zinc-100 p-2 text-center">
                                <p class="text-[8px] font-bold uppercase text-zinc-400">Days</p>
                                <p class="text-sm font-black text-zinc-700"><?= h((string) (int) ($top1['days_completed'] ?? 0)) ?>/10</p>
                            </div>
                        </div>

                        <!-- Weight Journey -->
                        <div class="rounded-xl border border-zinc-200 bg-white p-3">
                            <p class="mb-2 text-[9px] font-bold uppercase tracking-wide text-zinc-400">Weight Journey</p>
                            <div class="flex items-center justify-between">
                                <div class="text-center">
                                    <p class="text-lg font-black text-zinc-400"><?= h($startWeight) ?></p>
                                    <p class="text-[8px] font-bold text-zinc-300">START</p>
                                </div>
                                <div class="flex flex-1 items-center justify-center px-2">
                                    <div class="h-0.5 flex-1 bg-gradient-to-r from-zinc-300 via-orange-400 to-green-400"></div>
                                    <svg class="mx-1 h-4 w-4 text-orange-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                </div>
                                <div class="text-center">
                                    <p class="text-lg font-black text-green-600"><?= h($endWeight) ?></p>
                                    <p class="text-[8px] font-bold text-green-400">FINISH</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between border-t border-orange-100 pt-2 mt-2 flex-shrink-0">
                    <p class="text-[10px] font-semibold text-zinc-400">10 Days Weekly Challenge • <?= date('F Y') ?></p>
                    <div class="flex items-center gap-1">
                        <svg class="h-4 w-4 text-orange-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <span class="text-[10px] font-bold text-zinc-500">Certified Champion</span>
                    </div>
                    <p class="text-[10px] font-semibold text-zinc-400">Generated: <?= date('M d, Y') ?></p>
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
