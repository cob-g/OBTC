<?php
require __DIR__ . '/../app/bootstrap.php';
require_role('coach');

$user = auth_user();
$totalClients = 0;
$challengeDayLabel = '-';
$avgWeightLossLabel = '-';

function dashboard_challenge_start_date_for_client(array $client)
{
    if (!empty($client['challenge_start_date'])) {
        return (string) $client['challenge_start_date'];
    }

    $registeredAt = isset($client['registered_at']) ? (string) $client['registered_at'] : '';
    if ($registeredAt === '') {
        return null;
    }

    try {
        $dt = new DateTimeImmutable($registeredAt);
    } catch (Throwable $e) {
        return null;
    }

    $dow = (int) $dt->format('N');
    if ($dow === 1) {
        return $dt->format('Y-m-d');
    }
    if ($dow === 2) {
        return $dt->modify('monday this week')->format('Y-m-d');
    }
    return $dt->modify('next monday')->format('Y-m-d');
}

function dashboard_challenge_day_from_start($startDate)
{
    if (!$startDate) {
        return null;
    }

    try {
        $start = new DateTimeImmutable((string) $startDate . ' 00:00:00');
    } catch (Throwable $e) {
        return null;
    }

    $today = new DateTimeImmutable('today');
    if ($today < $start) {
        return 0;
    }

    $diffDays = (int) $start->diff($today)->format('%a');
    $day = $diffDays + 1;
    if ($day > 10) {
        return 11;
    }
    return $day;
}

try {
    $stmt = db()->prepare('SELECT COUNT(*) AS c FROM clients WHERE coach_user_id = ?');
    $stmt->execute([(int) $user['id']]);
    $row = $stmt->fetch();
    $totalClients = $row ? (int) $row['c'] : 0;
} catch (Throwable $e) {
    $totalClients = 0;
}

$clients = [];
try {
    $select = 'SELECT id, registered_at, start_weight_lbs' . (db_has_column('clients', 'challenge_start_date') ? ', challenge_start_date' : '') . ' FROM clients WHERE coach_user_id = ?';
    $stmt = db()->prepare($select);
    $stmt->execute([(int) $user['id']]);
    $clients = $stmt->fetchAll();
} catch (Throwable $e) {
    $clients = [];
}

if ($clients) {
    $maxActiveDay = 0;
    $activeClientIds = [];
    foreach ($clients as $c) {
        $startDate = dashboard_challenge_start_date_for_client($c);
        $day = dashboard_challenge_day_from_start($startDate);
        if ($day !== null && $day >= 1 && $day <= 10) {
            $maxActiveDay = max($maxActiveDay, (int) $day);
            $activeClientIds[] = (int) $c['id'];
        }
        if ($day === 0) {
            $maxActiveDay = max($maxActiveDay, 0);
        }
    }

    if ($maxActiveDay === 0) {
        $challengeDayLabel = 'Upcoming';
    } elseif ($maxActiveDay >= 1 && $maxActiveDay <= 10) {
        $challengeDayLabel = 'Day ' . (string) $maxActiveDay;
    }

    if ($activeClientIds) {
        $placeholders = implode(',', array_fill(0, count($activeClientIds), '?'));
        try {
            $sql = 'SELECT client_id, SUBSTRING_INDEX(GROUP_CONCAT(weight_lbs ORDER BY day_number DESC), ",", 1) AS latest_weight FROM client_checkins WHERE coach_user_id = ? AND client_id IN (' . $placeholders . ') GROUP BY client_id';
            $stmt = db()->prepare($sql);
            $stmt->execute(array_merge([(int) $user['id']], $activeClientIds));
            $latestRows = $stmt->fetchAll();
            $latestById = [];
            foreach ($latestRows as $r) {
                $latestById[(int) $r['client_id']] = $r['latest_weight'] !== null ? (float) $r['latest_weight'] : null;
            }

            $sumLoss = 0.0;
            $countLoss = 0;
            foreach ($clients as $c) {
                $id = (int) $c['id'];
                if (!in_array($id, $activeClientIds, true)) {
                    continue;
                }
                if ((float) $c['start_weight_lbs'] <= 0) {
                    continue;
                }
                $latest = $latestById[$id] ?? null;
                if ($latest === null) {
                    continue;
                }
                $loss = max(0, (float) $c['start_weight_lbs'] - (float) $latest);
                $sumLoss += $loss;
                $countLoss++;
            }

            if ($countLoss > 0) {
                $avg = $sumLoss / $countLoss;
                $avgWeightLossLabel = number_format((float) $avg, 2, '.', '');
            }
        } catch (Throwable $e) {
            $avgWeightLossLabel = '-';
        }
    }
}

$page_title = 'Coach Dashboard';
require __DIR__ . '/../partials/layout_top.php';
require __DIR__ . '/../partials/nav.php';
?>

<main class="mx-auto max-w-6xl px-4 sm:px-6 py-6 sm:py-8">
    <!-- Coach Welcome Header -->
    <div class="mb-6 sm:mb-8 rounded-2xl bg-gradient-to-r from-molten via-pumpkin to-amber-500 p-4 sm:p-6 shadow-lg overflow-hidden relative">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <defs>
                    <pattern id="coachPattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                        <circle cx="10" cy="10" r="1.5" fill="white"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#coachPattern)"/>
            </svg>
        </div>
        
        <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <!-- Trophy Icon -->
                <div class="flex h-14 w-14 sm:h-16 sm:w-16 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-sm border border-white/30 shadow-lg">
                    <svg class="h-8 w-8 sm:h-10 sm:w-10 text-white" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 10c0 8 4 16 12 16s12-8 12-16" fill="currentColor" opacity="0.9"/>
                        <rect x="18" y="34" width="12" height="6" rx="2" fill="currentColor" opacity="0.7"/>
                        <rect x="16" y="40" width="16" height="4" rx="2" fill="currentColor" opacity="0.5"/>
                        <path d="M12 14c-4 0-6 4-6 8s2 8 6 8" stroke="currentColor" stroke-width="2" fill="none" opacity="0.7"/>
                        <path d="M36 14c4 0 6 4 6 8s-2 8-6 8" stroke="currentColor" stroke-width="2" fill="none" opacity="0.7"/>
                        <polygon points="24,14 25.5,18 30,18 26.5,21 28,25 24,22 20,25 21.5,21 18,18 22.5,18" fill="#FFD700"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl sm:text-2xl lg:text-3xl font-black text-white tracking-tight">Coach Dashboard</h1>
                        <!-- <span class="hidden sm:inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide bg-white/20 text-white border border-white/30">
                            Pro
                        </span> -->
                    </div>
                    <p class="mt-0.5 text-sm text-white/80">Welcome back, <span class="font-semibold text-white"><?= h($user['name']) ?></span>! Ready to inspire your challengers?</p>
                </div>
            </div>
            
            <!-- Quick Stats Pills -->
            <div class="flex items-center gap-2 sm:gap-3">
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/20 backdrop-blur-sm border border-white/30">
                    <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span class="text-sm font-bold text-white"><?= h((string) $totalClients) ?> Clients</span>
                </div>
                <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/20 backdrop-blur-sm border border-white/30">
                    <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span class="text-sm font-bold text-white"><?= h((string) $challengeDayLabel) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-3 sm:gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="group rounded-2xl border border-orange-100 bg-white p-4 sm:p-5 hover:shadow-lg hover:border-orange-200 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-zinc-600">Total Clients</div>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-orange-100 to-amber-50 group-hover:from-orange-200 group-hover:to-amber-100 transition-colors">
                    <svg class="h-5 w-5 text-molten" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 text-3xl sm:text-4xl font-black text-zinc-900"><?= h((string) $totalClients) ?></div>
            <div class="mt-1 flex items-center gap-1.5 text-xs font-medium text-zinc-500">
                <span class="flex h-2 w-2 rounded-full bg-green-500"></span>
                Active participants
            </div>
        </div>
        <div class="group rounded-2xl border border-orange-100 bg-white p-4 sm:p-5 hover:shadow-lg hover:border-orange-200 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-zinc-600">Challenge Day</div>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-orange-100 to-amber-50 group-hover:from-orange-200 group-hover:to-amber-100 transition-colors">
                    <svg class="h-5 w-5 text-molten" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 text-3xl sm:text-4xl font-black text-zinc-900"><?= h((string) $challengeDayLabel) ?></div>
            <div class="mt-1 flex items-center gap-1.5 text-xs font-medium text-zinc-500">
                <svg class="h-3.5 w-3.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                </svg>
                Current progress
            </div>
        </div>
        <div class="group rounded-2xl border border-orange-100 bg-white p-4 sm:p-5 hover:shadow-lg hover:border-orange-200 transition-all duration-300 sm:col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-zinc-600">Weekly Progress</div>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-orange-100 to-amber-50 group-hover:from-orange-200 group-hover:to-amber-100 transition-colors">
                    <svg class="h-5 w-5 text-molten" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-end gap-2">
                <span class="text-3xl sm:text-4xl font-black text-zinc-900">
                    <?php
                        $dayNum = 0;
                        if (preg_match('/Day (\d+)/', $challengeDayLabel, $m)) {
                            $dayNum = (int)$m[1];
                        }
                    ?>
                    <?= $dayNum ?: '0' ?>
                </span>
                <span class="text-xl font-bold text-zinc-300 mb-1">/10</span>
            </div>
            <div class="mt-3 h-2.5 w-full rounded-full bg-zinc-100 overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r from-molten to-amber-500 transition-all duration-500 ease-out" style="width: <?= ($dayNum > 10 ? 10 : $dayNum) * 10 ?>%"></div>
            </div>
            <div class="mt-2 flex items-center justify-between text-xs">
                <span class="font-medium text-zinc-500">
                    <?php $weekNum = $dayNum ? ceil($dayNum / 2.5) : 1; ?>
                    Week <?= $weekNum ?> in progress
                </span>
                <span class="font-bold text-molten"><?= ($dayNum > 10 ? 100 : $dayNum * 10) ?>%</span>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="mt-6 sm:mt-8 flex flex-col gap-3 sm:flex-row">
        <a class="group inline-flex items-center justify-center gap-2.5 rounded-xl bg-gradient-to-r from-indigo_bloom to-purple-600 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo_bloom/25 hover:shadow-xl hover:shadow-indigo_bloom/30 hover:-translate-y-0.5 transition-all duration-200" href="<?= h(url('/coach/pre_registration.php')) ?>">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            Pre-Register Challenger
            <svg class="h-4 w-4 opacity-50 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        <a class="group inline-flex items-center justify-center gap-2.5 rounded-xl bg-gradient-to-r from-molten to-pumpkin px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-molten/25 hover:shadow-xl hover:shadow-molten/30 hover:-translate-y-0.5 transition-all duration-200" href="<?= h(url('/coach/challenge.php')) ?>">
            <svg class="h-5 w-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <polygon points="8,5 20,12 8,19" fill="white"/>
            </svg>
            View Active Challenge
            <svg class="h-4 w-4 opacity-50 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    <!-- 10-Day Progress Timeline -->
    <div class="mt-6 sm:mt-8 rounded-2xl border border-orange-100 bg-white p-4 sm:p-6 shadow-sm hover:shadow-md transition-shadow">
        <div class="mb-4 sm:mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-orange-100 to-amber-50">
                    <svg class="h-5 w-5 text-molten" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-zinc-900">10-Day Progress Timeline</h2>
                    <p class="text-xs text-zinc-500">Track your challengers' journey</p>
                </div>
            </div>
            <?php if ($dayNum > 0 && $dayNum <= 10): ?>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-50 border border-amber-200">
                    <span class="flex h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
                    <span class="text-xs font-bold text-amber-700">Day <?= $dayNum ?> Active</span>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="overflow-x-auto pb-2 -mx-4 sm:mx-0 px-4 sm:px-0">
            <div class="flex items-center justify-between min-w-[600px] sm:min-w-[700px]">
                <?php for ($day = 1; $day <= 10; $day++): ?>
                    <?php
                        $isCompleted = $dayNum > 0 && $day < $dayNum;
                        $isCurrent = $dayNum > 0 && $day === $dayNum;
                        $isUpcoming = $dayNum === 0 || $day > $dayNum;
                        $isFinalPhoto = $day === 10;
                    ?>
                    
                    <div class="flex flex-1 items-center">
                        <!-- Day Circle -->
                        <div class="flex flex-col items-center group">
                            <div class="relative flex h-10 w-10 sm:h-12 sm:w-12 items-center justify-center rounded-full <?= $isCompleted ? 'bg-gradient-to-br from-green-500 to-emerald-600 shadow-lg shadow-green-500/30' : ($isCurrent ? 'bg-gradient-to-br from-zinc-800 to-zinc-900 ring-4 ring-amber-400/50 shadow-lg' : 'bg-zinc-100 group-hover:bg-zinc-200') ?> transition-all duration-300">
                                <?php if ($isCompleted): ?>
                                    <svg class="h-5 w-5 sm:h-6 sm:w-6 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                <?php elseif ($isCurrent): ?>
                                    <?php if ($isFinalPhoto): ?>
                                        <svg class="h-5 w-5 sm:h-6 sm:w-6 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 5a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-1.121-1.121A2 2 0 0011.172 3H8.828a2 2 0 00-1.414.586L6.293 4.707A1 1 0 015.586 5H4zm6 9a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                                        </svg>
                                    <?php else: ?>
                                        <span class="text-sm sm:text-base font-black text-white"><?= $day ?></span>
                                    <?php endif; ?>
                                <?php elseif ($isFinalPhoto): ?>
                                    <svg class="h-5 w-5 sm:h-6 sm:w-6 text-zinc-400 group-hover:text-zinc-500 transition-colors" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 5a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-1.121-1.121A2 2 0 0011.172 3H8.828a2 2 0 00-1.414.586L6.293 4.707A1 1 0 015.586 5H4zm6 9a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                                    </svg>
                                <?php else: ?>
                                    <span class="text-xs sm:text-sm font-bold text-zinc-400 group-hover:text-zinc-500 transition-colors"><?= $day ?></span>
                                <?php endif; ?>
                                
                                <?php if ($isCurrent): ?>
                                    <span class="absolute -top-1 -right-1 flex h-4 w-4">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-4 w-4 bg-amber-500 border-2 border-white"></span>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Day Label -->
                            <div class="mt-2 text-center">
                                <div class="text-xs font-bold <?= $isCompleted ? 'text-green-600' : ($isCurrent ? 'text-zinc-900' : 'text-zinc-400') ?>">
                                    Day <?= $day ?>
                                </div>
                                <div class="text-[10px] font-semibold <?= $isCompleted ? 'text-green-600' : ($isCurrent ? 'text-amber-600' : 'text-zinc-400') ?>">
                                    <?php if ($isCompleted): ?>
                                        ✓ Done
                                    <?php elseif ($isCurrent): ?>
                                        <span class="uppercase tracking-wide">Today</span>
                                    <?php elseif ($isFinalPhoto): ?>
                                        📸 Final
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Connecting Line -->
                        <?php if ($day < 10): ?>
                            <div class="flex-1 mx-1 sm:mx-2 h-1 rounded-full <?= $isCompleted ? 'bg-gradient-to-r from-green-500 to-emerald-500' : 'bg-zinc-200' ?> transition-all duration-300"></div>
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <?php if ($dayNum === 0): ?>
            <div class="mt-4 rounded-xl bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 px-4 py-4 flex items-start gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 flex-shrink-0">
                    <svg class="h-4 w-4 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-amber-800">Challenge Starting Soon!</p>
                    <p class="text-xs text-amber-700 mt-0.5">The timeline will activate once clients begin their 10-day journey.</p>
                </div>
            </div>
        <?php elseif ($dayNum > 10): ?>
            <div class="mt-4 rounded-xl bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 px-4 py-4 flex items-start gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-100 flex-shrink-0">
                    <svg class="h-4 w-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-green-800">🎉 Challenge Complete!</p>
                    <p class="text-xs text-green-700 mt-0.5">All 10 days have been completed. Amazing work, Coach!</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Quick Tips Card (New) -->
    <div class="mt-4 sm:mt-6 rounded-2xl border border-orange-100 bg-gradient-to-br from-white to-orange-50/30 p-4 sm:p-5">
        <div class="flex items-center gap-2 mb-3">
            <svg class="h-5 w-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <h3 class="text-sm font-bold text-zinc-800">Coach Tips</h3>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="flex items-center gap-2 text-xs text-zinc-600">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-orange-100 text-molten font-bold text-[10px]">1</span>
                <span>Remind clients to log weight daily</span>
            </div>
            <div class="flex items-center gap-2 text-xs text-zinc-600">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-orange-100 text-molten font-bold text-[10px]">2</span>
                <span>Day 10 photos are mandatory</span>
            </div>
            <div class="flex items-center gap-2 text-xs text-zinc-600">
                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-orange-100 text-molten font-bold text-[10px]">3</span>
                <span>Check leaderboard for rankings</span>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>
