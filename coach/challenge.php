<?php
require __DIR__ . '/../app/bootstrap.php';
require_role('coach');

$user = auth_user();

function challenge_start_date_for_client(array $client)
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

function challenge_day_from_start($startDate)
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

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Invalid session. Please refresh and try again.';
    } else {
        $action = (string) ($_POST['action'] ?? '');
        if ($action === 'record_checkin') {
            $clientId = (int) ($_POST['client_id'] ?? 0);
            $dayNumber = (int) ($_POST['day_number'] ?? 0);
            $weight = (float) ($_POST['weight_lbs'] ?? 0);

            if ($clientId <= 0) {
                $errors[] = 'Invalid client.';
            }
            if ($dayNumber < 1 || $dayNumber > 10) {
                $errors[] = 'Day number must be between 1 and 10.';
            }
            if ($weight < 50 || $weight > 500) {
                $errors[] = 'Weight must be between 50 and 500 lbs.';
            }

            if (!$errors) {
                try {
                    $stmt = db()->prepare('SELECT id, coach_user_id, registered_at' . (db_has_column('clients', 'challenge_start_date') ? ', challenge_start_date' : '') . ' FROM clients WHERE id = ? LIMIT 1');
                    $stmt->execute([$clientId]);
                    $clientRow = $stmt->fetch();
                } catch (Throwable $e) {
                    $clientRow = null;
                }

                if (!$clientRow || (int) $clientRow['coach_user_id'] !== (int) $user['id']) {
                    $errors[] = 'Client not found.';
                } else {
                    $startDate = challenge_start_date_for_client($clientRow);
                    $currentDay = challenge_day_from_start($startDate);
                    if ($currentDay === 0) {
                        $errors[] = 'Challenge has not started yet for this client.';
                    } elseif ($currentDay === 11) {
                        $errors[] = 'This challenge is already completed. Past days cannot be recorded.';
                    } elseif ($currentDay !== null && $dayNumber !== (int) $currentDay) {
                        $errors[] = 'You can only record today\'s weigh-in (Day ' . (string) $currentDay . '). Missed days cannot be backfilled.';
                    }

                    if (!$errors) {
                        try {
                            $dup = db()->prepare('SELECT 1 FROM client_checkins WHERE client_id = ? AND coach_user_id = ? AND day_number = ? LIMIT 1');
                            $dup->execute([$clientId, (int) $user['id'], $dayNumber]);
                            if ($dup->fetchColumn()) {
                                $errors[] = 'A weigh-in for this day is already recorded and cannot be overwritten.';
                            }
                        } catch (Throwable $e) {
                            $errors[] = 'Failed to validate existing check-ins.';
                        }
                    }
                }
            }

            if (!$errors) {
                try {
                    $now = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');
                    $ins = db()->prepare('INSERT INTO client_checkins (client_id, coach_user_id, day_number, weight_lbs, recorded_at) VALUES (?, ?, ?, ?, ?)');
                    $ins->execute([$clientId, (int) $user['id'], $dayNumber, number_format($weight, 2, '.', ''), $now]);
                    $success = 'Saved check-in for Day ' . (string) $dayNumber . '.';
                } catch (Throwable $e) {
                    $errors[] = 'Failed to save check-in.';
                }
            }
        }
    }
}

$page = (int) ($_GET['page'] ?? 1);
if ($page < 1) {
    $page = 1;
}
$perPage = 10;
$clients = [];
$totalClients = 0;
$totalPages = 1;

try {
    $countStmt = db()->prepare('SELECT COUNT(*) FROM clients WHERE coach_user_id = ?');
    $countStmt->execute([(int) $user['id']]);
    $totalClients = (int) $countStmt->fetchColumn();

    $totalPages = $perPage > 0 ? (int) ceil($totalClients / $perPage) : 1;
    if ($totalPages < 1) {
        $totalPages = 1;
    }
    if ($page > $totalPages) {
        $page = $totalPages;
    }

    $offset = ($page - 1) * $perPage;

    $select = 'SELECT id, coach_user_id, registered_at, gender, age, height_ft, height_in, start_weight_lbs, bmi, bmi_category' .
        (db_has_column('clients', 'full_name') ? ', full_name' : '') .
        (db_has_column('clients', 'challenge_start_date') ? ', challenge_start_date' : '') .
        ' FROM clients WHERE coach_user_id = ? ORDER BY registered_at DESC, id DESC LIMIT ? OFFSET ?';
    $stmt = db()->prepare($select);
    $stmt->bindValue(1, (int) $user['id'], PDO::PARAM_INT);
    $stmt->bindValue(2, (int) $perPage, PDO::PARAM_INT);
    $stmt->bindValue(3, (int) $offset, PDO::PARAM_INT);
    $stmt->execute();
    $clients = $stmt->fetchAll();
} catch (Throwable $e) {
    $clients = [];
}

$checkinsByClient = [];
if ($clients) {
    $ids = array_map(fn ($c) => (int) $c['id'], $clients);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    try {
        $sql = 'SELECT client_id, COUNT(*) AS days_completed, MAX(day_number) AS max_day, GROUP_CONCAT(day_number ORDER BY day_number ASC) AS day_numbers, SUBSTRING_INDEX(GROUP_CONCAT(weight_lbs ORDER BY day_number DESC), ",", 1) AS latest_weight FROM client_checkins WHERE coach_user_id = ? AND client_id IN (' . $placeholders . ') GROUP BY client_id';
        $stmt = db()->prepare($sql);
        $stmt->execute(array_merge([(int) $user['id']], $ids));
        $rows = $stmt->fetchAll();
        foreach ($rows as $r) {
            $checkinsByClient[(int) $r['client_id']] = $r;
        }
    } catch (Throwable $e) {
        $checkinsByClient = [];
    }
}

$page_title = 'Challenge';
require __DIR__ . '/../partials/layout_top.php';
require __DIR__ . '/../partials/nav.php';
?>

<main class="mx-auto max-w-6xl px-4 py-8">
    <!-- Page Header with Gradient Banner -->
    <div class="relative mb-8 overflow-hidden rounded-2xl bg-gradient-to-r from-molten via-pumpkin to-amber-500 p-6 shadow-lg shadow-orange-200/50">
        <div class="absolute -right-8 -top-8 h-40 w-40 rounded-full bg-white/10"></div>
        <div class="absolute -bottom-6 -right-16 h-32 w-32 rounded-full bg-white/10"></div>
        <div class="relative flex items-center gap-4">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-sm">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </div>
            <div class="flex-1">
                <h1 class="text-2xl font-extrabold tracking-tight text-white sm:text-3xl">Challenge Check-In</h1>
                <p class="mt-1 text-sm text-white/80">Record daily weigh-ins and track your clients' 10-day journey</p>
            </div>
            <div class="hidden sm:flex items-center gap-2 rounded-xl bg-white/20 px-4 py-2 backdrop-blur-sm">
                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
                <span class="text-sm font-bold text-white">10-Day Program</span>
            </div>
        </div>
    </div>

    <!-- Success Alert -->
    <?php if ($success): ?>
        <div class="mb-6 flex items-center gap-3 rounded-2xl border border-green-200 bg-gradient-to-r from-green-50 to-emerald-50 p-4 shadow-sm">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-500/10">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <div class="text-sm font-bold text-green-800"><?= h($success) ?></div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Error Alert -->
    <?php if ($errors): ?>
        <div class="mb-6 rounded-2xl border border-red-200 bg-gradient-to-r from-red-50 to-rose-50 p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-500/10">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="text-sm font-extrabold text-red-800">Please fix the following:</div>
            </div>
            <ul class="mt-3 ml-13 list-disc pl-5 text-sm text-red-700">
                <?php foreach ($errors as $e): ?>
                    <li><?= h((string) $e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Active Clients Section -->
    <div class="rounded-2xl border border-orange-100 bg-white p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-amber-100 to-orange-100">
                    <svg class="h-6 w-6 text-molten" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-lg font-extrabold tracking-tight text-zinc-900">Active Challenge Clients</div>
                    <div class="mt-0.5 text-sm text-zinc-500">Challenge starts on Monday • Late registrations have grace period</div>
                </div>
            </div>
            <a href="<?= h(url('/coach/clients.php')) ?>" class="inline-flex items-center gap-2 rounded-xl border border-orange-200 bg-gradient-to-r from-orange-50 to-amber-50 px-4 py-2.5 text-sm font-bold text-zinc-700 transition hover:border-orange-300 hover:shadow-sm">
                <svg class="h-4 w-4 text-molten" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                View All Clients
            </a>
        </div>

        <!-- Client Cards Grid -->
        <div class="mt-6 space-y-4">
            <?php if (!$clients): ?>
                <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-orange-200 bg-gradient-to-br from-orange-50/50 to-amber-50/50 py-12 px-6 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-orange-100">
                        <svg class="h-8 w-8 text-orange-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-bold text-zinc-700">No Clients Yet</h3>
                    <p class="mt-1 text-sm text-zinc-500">Pre-register a challenger to get started with the 10-day program.</p>
                    <a href="<?= h(url('/coach/pre_registration.php')) ?>" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-molten to-pumpkin px-4 py-2 text-sm font-bold text-white shadow-md shadow-orange-200/50 hover:shadow-lg">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Register New Client
                    </a>
                </div>
            <?php endif; ?>

            <?php foreach ($clients as $c): ?>
                <?php
                    $name = isset($c['full_name']) ? trim((string) $c['full_name']) : '';
                    if ($name === '') {
                        $name = 'Client #' . (string) $c['id'];
                    }
                    $startDate = challenge_start_date_for_client($c);
                    $day = challenge_day_from_start($startDate);
                    $dayLabel = $day === null ? '-' : ($day === 0 ? 'Upcoming' : ($day === 11 ? 'Completed' : ('Day ' . (string) $day)));
                    $agg = $checkinsByClient[(int) $c['id']] ?? null;
                    $daysDone = $agg ? (int) $agg['days_completed'] : 0;
                    $latestWeight = $agg && $agg['latest_weight'] !== null ? (float) $agg['latest_weight'] : null;
                    $loss = $latestWeight === null ? null : max(0, (float) $c['start_weight_lbs'] - $latestWeight);
                    $maxDayAllowed = $day === null ? 0 : ($day === 0 ? 0 : ($day === 11 ? 10 : (int) $day));
                    $recordedDayNumbers = [];
                    if ($agg && isset($agg['day_numbers']) && (string) $agg['day_numbers'] !== '') {
                        $recordedDayNumbers = array_map('intval', array_filter(explode(',', (string) $agg['day_numbers']), fn ($v) => $v !== ''));
                    }

                    $currentDayNumber = $day === null ? 0 : ($day === 0 ? 0 : ($day === 11 ? 0 : (int) $day));
                    $canRecordToday = $currentDayNumber >= 1 && $currentDayNumber <= 10 && !in_array($currentDayNumber, $recordedDayNumbers, true);
                    
                    // Status color classes
                    $statusBg = $day === 0 ? 'bg-blue-100 text-blue-700' : ($day === 11 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700');
                    $progressPercent = ($daysDone / 10) * 100;
                ?>
                <div class="rounded-2xl border border-orange-100 bg-gradient-to-r from-white to-orange-50/30 p-5 transition hover:shadow-md hover:border-orange-200">
                    <!-- Client Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <div class="flex items-center gap-4 flex-1 min-w-0">
                            <!-- Avatar -->
                            <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-molten to-pumpkin text-white font-bold text-lg shadow-md">
                                <?= strtoupper(substr($name, 0, 1)) ?>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="font-extrabold text-zinc-900 truncate"><?= h($name) ?></h3>
                                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold <?= $statusBg ?>">
                                        <?php if ($day === 0): ?>
                                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                                        <?php elseif ($day === 11): ?>
                                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        <?php else: ?>
                                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/></svg>
                                        <?php endif; ?>
                                        <?= h($dayLabel) ?>
                                    </span>
                                </div>
                                <div class="mt-1 flex items-center gap-3 text-xs text-zinc-500">
                                    <span class="inline-flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <?= h((string) $c['gender']) ?>, <?= h((string) $c['age']) ?>y
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h10v10H7V7z"/></svg>
                                        <?= h((string) $c['height_ft']) ?>'<?= h((string) $c['height_in']) ?>"
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <?= h((string) ($startDate ?? 'Not set')) ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Stats Pills -->
                        <div class="flex items-center gap-2 flex-wrap">
                            <div class="flex flex-col items-center rounded-xl bg-blue-50 px-3 py-2">
                                <span class="text-xs text-blue-600 font-medium">Start</span>
                                <span class="text-sm font-bold text-blue-700"><?= h(number_format((float) $c['start_weight_lbs'], 1)) ?></span>
                            </div>
                            <div class="flex flex-col items-center rounded-xl bg-purple-50 px-3 py-2">
                                <span class="text-xs text-purple-600 font-medium">Latest</span>
                                <span class="text-sm font-bold text-purple-700"><?= $latestWeight === null ? '-' : h(number_format((float) $latestWeight, 1)) ?></span>
                            </div>
                            <div class="flex flex-col items-center rounded-xl bg-emerald-50 px-3 py-2">
                                <span class="text-xs text-emerald-600 font-medium">Lost</span>
                                <span class="text-sm font-bold text-emerald-700"><?= $loss === null ? '-' : h(number_format((float) $loss, 1)) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- 10-Day Progress Timeline -->
                    <div class="mt-4 pt-4 border-t border-orange-100">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-zinc-600">10-Day Progress</span>
                            <span class="text-xs font-bold text-molten"><?= h((string) $daysDone) ?>/10 Days Complete</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <?php
                                    $isCompleted = in_array($i, $recordedDayNumbers, true);
                                    $isCurrent = $i === $currentDayNumber;
                                    $isPast = $day !== null && $day !== 0 && $i < $currentDayNumber && !$isCompleted;
                                    $dayClass = $isCompleted ? 'bg-emerald-500 text-white' : ($isCurrent ? 'bg-molten text-white ring-2 ring-molten/30' : ($isPast ? 'bg-red-100 text-red-400' : 'bg-zinc-100 text-zinc-400'));
                                ?>
                                <div class="flex-1 flex flex-col items-center gap-1">
                                    <div class="w-full h-8 rounded-lg flex items-center justify-center text-xs font-bold transition-all <?= $dayClass ?>">
                                        <?php if ($isCompleted): ?>
                                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        <?php else: ?>
                                            <?= $i ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- Record Check-in Form -->
                    <div class="mt-4 pt-4 border-t border-orange-100">
                        <?php if ($day === 0): ?>
                            <div class="flex items-center gap-2 rounded-xl bg-blue-50 px-4 py-3">
                                <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-sm font-medium text-blue-700">Challenge starts soon! Check-ins will be available when the challenge begins.</span>
                            </div>
                        <?php elseif ($day === 11): ?>
                            <div class="flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3">
                                <svg class="h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                </svg>
                                <span class="text-sm font-medium text-emerald-700">Congratulations! This client has completed their 10-day challenge!</span>
                            </div>
                        <?php elseif (!$canRecordToday): ?>
                            <div class="flex items-center gap-2 rounded-xl bg-zinc-50 px-4 py-3">
                                <svg class="h-5 w-5 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-sm font-medium text-zinc-600">Day <?= (int) $currentDayNumber ?> weigh-in already recorded. Come back tomorrow!</span>
                            </div>
                        <?php else: ?>
                            <form method="post" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3" novalidate>
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="record_checkin" />
                                <input type="hidden" name="client_id" value="<?= h((string) $c['id']) ?>" />

                                <div class="flex items-center gap-2 flex-1">
                                    <div class="flex items-center gap-2 rounded-xl border border-orange-200 bg-gradient-to-r from-orange-50 to-amber-50 px-3 py-2">
                                        <svg class="h-4 w-4 text-molten" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <select name="day_number" class="bg-transparent text-sm font-bold text-zinc-700 outline-none cursor-pointer">
                                            <option value="<?= (int) $currentDayNumber ?>" selected>Day <?= (int) $currentDayNumber ?></option>
                                        </select>
                                    </div>

                                    <div class="flex-1 relative">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                            <svg class="h-4 w-4 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                                            </svg>
                                        </div>
                                        <input name="weight_lbs" type="number" min="50" max="500" step="0.01" value="" placeholder="Enter weight (lbs)" class="w-full rounded-xl border border-orange-200 bg-white pl-10 pr-4 py-2.5 text-sm font-semibold text-zinc-700 placeholder-zinc-400 outline-none ring-molten/20 transition focus:border-molten focus:ring-4" />
                                    </div>
                                </div>

                                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-molten to-pumpkin px-5 py-2.5 text-sm font-bold text-white shadow-md shadow-orange-200/50 transition hover:shadow-lg hover:scale-[1.02] active:scale-[0.98]">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Record Weigh-In
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4 rounded-xl bg-gradient-to-r from-orange-50 to-amber-50 p-4">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-molten" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span class="text-sm font-bold text-zinc-700">Page <span class="text-molten"><?= h((string) $page) ?></span> of <span class="text-molten"><?= h((string) $totalPages) ?></span></span>
                </div>
                <div class="flex items-center gap-2">
                    <?php if ($page > 1): ?>
                        <a class="inline-flex items-center gap-1.5 rounded-xl border border-orange-200 bg-white px-4 py-2 text-sm font-bold text-zinc-700 shadow-sm transition hover:bg-orange-50 hover:border-orange-300" href="<?= h(url('/coach/challenge.php?page=' . (int) ($page - 1))) ?>">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Previous
                        </a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-molten to-pumpkin px-4 py-2 text-sm font-bold text-white shadow-md shadow-orange-200/50 transition hover:shadow-lg" href="<?= h(url('/coach/challenge.php?page=' . (int) ($page + 1))) ?>">
                            Next
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>
