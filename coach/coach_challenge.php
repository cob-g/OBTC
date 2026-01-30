<?php
require __DIR__ . '/../app/bootstrap.php';
require_role('coach');

$user = auth_user();

function ensure_coach_challenge_tables()
{
    db()->exec(
        "CREATE TABLE IF NOT EXISTS coach_challenges (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            start_date DATE NOT NULL,
            duration_days TINYINT UNSIGNED NOT NULL DEFAULT 10,
            status ENUM('active','completed') NOT NULL DEFAULT 'active',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_coach_challenges_status_start (status, start_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    db()->exec(
        "CREATE TABLE IF NOT EXISTS coach_challenge_participants (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            coach_challenge_id INT UNSIGNED NOT NULL,
            coach_user_id INT UNSIGNED NOT NULL,
            height_ft TINYINT UNSIGNED NOT NULL,
            height_in TINYINT UNSIGNED NOT NULL,
            start_weight_lbs DECIMAL(6,2) NOT NULL,
            bmi DECIMAL(6,2) NOT NULL,
            bmi_category VARCHAR(32) NOT NULL,
            registered_at DATETIME NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uniq_coach_challenge_participant (coach_challenge_id, coach_user_id),
            KEY idx_coach_participants_challenge (coach_challenge_id),
            KEY idx_coach_participants_coach (coach_user_id),
            CONSTRAINT fk_coach_participants_challenge FOREIGN KEY (coach_challenge_id) REFERENCES coach_challenges(id)
                ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT fk_coach_participants_user FOREIGN KEY (coach_user_id) REFERENCES users(id)
                ON DELETE RESTRICT ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    db()->exec(
        "CREATE TABLE IF NOT EXISTS coach_checkins (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            coach_challenge_id INT UNSIGNED NOT NULL,
            coach_user_id INT UNSIGNED NOT NULL,
            day_number TINYINT UNSIGNED NOT NULL,
            weight_lbs DECIMAL(6,2) NOT NULL,
            recorded_at DATETIME NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uniq_coach_checkins_day (coach_challenge_id, coach_user_id, day_number),
            KEY idx_coach_checkins_challenge (coach_challenge_id),
            KEY idx_coach_checkins_coach (coach_user_id),
            CONSTRAINT fk_coach_checkins_challenge FOREIGN KEY (coach_challenge_id) REFERENCES coach_challenges(id)
                ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT fk_coach_checkins_user FOREIGN KEY (coach_user_id) REFERENCES users(id)
                ON DELETE RESTRICT ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
}

function coach_global_challenge_start_date()
{
    $now = new DateTimeImmutable('now');
    $dow = (int) $now->format('N');
    if ($dow === 1) {
        return $now->format('Y-m-d');
    }
    if ($dow === 2) {
        return $now->modify('monday this week')->format('Y-m-d');
    }
    return $now->modify('next monday')->format('Y-m-d');
}

function coach_challenge_day_from_start($startDate, $durationDays)
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
    if ($day > (int) $durationDays) {
        return (int) $durationDays + 1;
    }
    return $day;
}

function get_active_coach_challenge()
{
    $row = null;
    try {
        $stmt = db()->query("SELECT * FROM coach_challenges WHERE status = 'active' ORDER BY start_date DESC, id DESC LIMIT 1");
        $row = $stmt->fetch();
    } catch (Throwable $e) {
        $row = null;
    }

    if ($row) {
        return $row;
    }

    $startDate = coach_global_challenge_start_date();
    $name = 'Coach Challenge (' . $startDate . ')';

    $ins = db()->prepare("INSERT INTO coach_challenges (name, start_date, duration_days, status) VALUES (?, ?, 10, 'active')");
    $ins->execute([$name, $startDate]);

    $stmt = db()->query("SELECT * FROM coach_challenges WHERE status = 'active' ORDER BY id DESC LIMIT 1");
    return $stmt->fetch();
}

ensure_coach_challenge_tables();
$challenge = get_active_coach_challenge();

$errors = [];
$success = null;

$participant = null;
try {
    $stmt = db()->prepare('SELECT * FROM coach_challenge_participants WHERE coach_challenge_id = ? AND coach_user_id = ? LIMIT 1');
    $stmt->execute([(int) $challenge['id'], (int) $user['id']]);
    $participant = $stmt->fetch();
} catch (Throwable $e) {
    $participant = null;
}

$currentDay = coach_challenge_day_from_start($challenge['start_date'], (int) $challenge['duration_days']);
$maxDayAllowed = $currentDay === null ? 0 : ($currentDay === 0 ? 0 : ($currentDay > (int) $challenge['duration_days'] ? (int) $challenge['duration_days'] : (int) $currentDay));
$defaultDay = $maxDayAllowed >= 1 ? $maxDayAllowed : 1;

$recordedDays = [];
if ($maxDayAllowed >= 1) {
    try {
        $stmt = db()->prepare('SELECT day_number FROM coach_checkins WHERE coach_challenge_id = ? AND coach_user_id = ? AND day_number <= ?');
        $stmt->execute([(int) $challenge['id'], (int) $user['id'], (int) $maxDayAllowed]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $r) {
            $recordedDays[(int) $r['day_number']] = true;
        }
    } catch (Throwable $e) {
        $recordedDays = [];
    }
}

// Only allow recording for TODAY's day - no backfilling allowed
$canRecordToday = false;
$todayDayNumber = $currentDay !== null && $currentDay >= 1 && $currentDay <= (int) $challenge['duration_days'] ? (int) $currentDay : 0;
if ($todayDayNumber >= 1 && empty($recordedDays[$todayDayNumber])) {
    $canRecordToday = true;
}
$defaultDay = $todayDayNumber >= 1 ? $todayDayNumber : 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Invalid session. Please refresh and try again.';
    } else {
        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'join') {
            $heightFtRaw = trim((string) ($_POST['height_ft'] ?? ''));
            $heightInRaw = trim((string) ($_POST['height_in'] ?? ''));
            $startWeightRaw = trim((string) ($_POST['start_weight_lbs'] ?? ''));
            $coachBmiRaw = trim((string) ($_POST['bmi'] ?? ''));
            $category = trim((string) ($_POST['bmi_category'] ?? ''));

            if ($heightFtRaw === '' || $heightInRaw === '' || $startWeightRaw === '' || $coachBmiRaw === '' || $category === '') {
                $errors[] = 'All fields are required.';
            }

            $heightFt = (int) $heightFtRaw;
            $heightIn = (int) $heightInRaw;
            $startWeight = is_numeric($startWeightRaw) ? (float) $startWeightRaw : 0;

            if ($heightFt < 3 || $heightFt > 8) {
                $errors[] = 'Height (ft) must be between 3 and 8.';
            }
            if ($heightIn < 0 || $heightIn > 11) {
                $errors[] = 'Height (in) must be between 0 and 11.';
            }
            if ($startWeight < 50 || $startWeight > 500) {
                $errors[] = 'Weight must be between 50 and 500 lbs.';
            }

            $coachBmi = null;
            if ($coachBmiRaw !== '') {
                if (!preg_match('/^\d+(\.\d{2})$/', $coachBmiRaw)) {
                    $errors[] = 'BMI must be entered with exactly two decimal places (e.g., 23.45).';
                } elseif (!is_numeric($coachBmiRaw)) {
                    $errors[] = 'BMI must be a valid number.';
                } else {
                    $coachBmi = (float) $coachBmiRaw;
                    if ($coachBmi <= 0 || $coachBmi > 100) {
                        $errors[] = 'BMI must be a reasonable positive value.';
                    }
                }
            }

            if ($category === '') {
                $errors[] = 'BMI category is required.';
            } elseif (!in_array($category, bmi_category_options(), true)) {
                $errors[] = 'Invalid BMI category.';
            }

            $systemBmi = bmi_from_imperial($startWeight, $heightFt, $heightIn);
            if ($systemBmi === null) {
                $errors[] = 'BMI could not be calculated. Please check height and weight.';
            }

            if ($systemBmi !== null && $coachBmi !== null && !$errors) {
                $systemBmiTruncated = floor((float) $systemBmi * 100) / 100;
                $systemBmiFormatted = number_format($systemBmiTruncated, 2, '.', '');
                $coachBmiFormatted = $coachBmiRaw;
                $bmiMatches = ($coachBmiFormatted === $systemBmiFormatted);

                if (!$bmiMatches) {
                    $errors[] = "It looks like the BMI value doesn't match the client's height and weight. Please double-check your calculation and try again.";
                } else {
                    $expectedCategory = bmi_category_suggest($systemBmi);
                    if ($expectedCategory !== null && $category !== $expectedCategory) {
                        $errors[] = "The BMI value is correct, but the selected category doesn't match it. Please review the BMI category before continuing.";
                    }
                }
            }

            if (!$errors) {
                try {
                    $now = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');
                    $ins = db()->prepare('INSERT INTO coach_challenge_participants (coach_challenge_id, coach_user_id, height_ft, height_in, start_weight_lbs, bmi, bmi_category, registered_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                    $ins->execute([
                        (int) $challenge['id'],
                        (int) $user['id'],
                        (int) $heightFt,
                        (int) $heightIn,
                        number_format($startWeight, 2, '.', ''),
                        number_format((float) $coachBmi, 2, '.', ''),
                        (string) $category,
                        $now,
                    ]);

                    $success = 'You have joined the coach challenge.';
                    redirect(url('/coach/coach_challenge.php'));
                } catch (Throwable $e) {
                    $errors[] = 'Failed to join the coach challenge.';
                }
            }
        }

        if ($action === 'record_checkin') {
            if (!$participant) {
                $errors[] = 'Please join the coach challenge first.';
            }

            $dayNumber = (int) ($_POST['day_number'] ?? 0);
            $weight = (float) ($_POST['weight_lbs'] ?? 0);

            if ($dayNumber < 1 || $dayNumber > (int) $challenge['duration_days']) {
                $errors[] = 'Day number must be within the challenge duration.';
            }
            if ($weight < 50 || $weight > 500) {
                $errors[] = 'Weight must be between 50 and 500 lbs.';
            }
            if ($maxDayAllowed < 1) {
                $errors[] = 'Challenge has not started yet.';
            } elseif ($dayNumber > $maxDayAllowed) {
                $errors[] = 'You cannot record a future day weigh-in.';
            } elseif ($dayNumber !== $maxDayAllowed) {
                $errors[] = 'You can only record today\'s weigh-in (Day ' . (string) $maxDayAllowed . '). Missed days cannot be backfilled.';
            }

            if (!$errors) {
                try {
                    $exists = db()->prepare('SELECT id FROM coach_checkins WHERE coach_challenge_id = ? AND coach_user_id = ? AND day_number = ? LIMIT 1');
                    $exists->execute([(int) $challenge['id'], (int) $user['id'], (int) $dayNumber]);
                    $existing = $exists->fetch();
                    if ($existing) {
                        $errors[] = 'This day has already been recorded. Please select the next day.';
                    }
                } catch (Throwable $e) {
                    $errors[] = 'Unable to verify existing check-in for this day.';
                }
            }

            if (!$errors) {
                try {
                    $now = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');
                    $ins = db()->prepare('INSERT INTO coach_checkins (coach_challenge_id, coach_user_id, day_number, weight_lbs, recorded_at) VALUES (?, ?, ?, ?, ?)');
                    $ins->execute([
                        (int) $challenge['id'],
                        (int) $user['id'],
                        (int) $dayNumber,
                        number_format($weight, 2, '.', ''),
                        $now,
                    ]);
                    $success = 'Saved check-in for Day ' . (string) $dayNumber . '.';
                    redirect(url('/coach/coach_challenge.php'));
                } catch (Throwable $e) {
                    $errors[] = 'Failed to save check-in.';
                }
            }
        }
    }
}

$participant = null;
try {
    $stmt = db()->prepare('SELECT * FROM coach_challenge_participants WHERE coach_challenge_id = ? AND coach_user_id = ? LIMIT 1');
    $stmt->execute([(int) $challenge['id'], (int) $user['id']]);
    $participant = $stmt->fetch();
} catch (Throwable $e) {
    $participant = null;
}

$latest = null;
$daysDone = 0;
if ($participant) {
    try {
        $stmt = db()->prepare('SELECT COUNT(*) AS days_completed, MAX(day_number) AS max_day, SUBSTRING_INDEX(GROUP_CONCAT(weight_lbs ORDER BY day_number DESC), ",", 1) AS latest_weight FROM coach_checkins WHERE coach_challenge_id = ? AND coach_user_id = ?');
        $stmt->execute([(int) $challenge['id'], (int) $user['id']]);
        $latest = $stmt->fetch();
        $daysDone = $latest ? (int) $latest['days_completed'] : 0;
    } catch (Throwable $e) {
        $latest = null;
    }
}

$latestWeight = $latest && $latest['latest_weight'] !== null ? (float) $latest['latest_weight'] : null;
$loss = ($participant && $latestWeight !== null) ? max(0, (float) $participant['start_weight_lbs'] - $latestWeight) : null;

$page_title = 'Coach Challenge';
require __DIR__ . '/../partials/layout_top.php';
require __DIR__ . '/../partials/nav.php';
?>

<main class="mx-auto max-w-4xl px-4 py-8">
    <!-- Page Header with Gradient Banner -->
    <div class="relative mb-8 overflow-hidden rounded-2xl bg-gradient-to-r from-molten via-pumpkin to-amber-500 p-6 shadow-lg shadow-orange-200/50">
        <div class="absolute -right-8 -top-8 h-40 w-40 rounded-full bg-white/10"></div>
        <div class="absolute -bottom-6 -right-16 h-32 w-32 rounded-full bg-white/10"></div>
        <div class="relative flex items-center gap-4">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-sm">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h1 class="text-2xl font-extrabold tracking-tight text-white sm:text-3xl">Coach Challenge</h1>
                <p class="mt-1 text-sm text-white/80"><?= h((string) $challenge['name']) ?> • Starts <?= h((string) $challenge['start_date']) ?></p>
            </div>
            <div class="hidden sm:flex items-center gap-2 rounded-xl bg-white/20 px-4 py-2 backdrop-blur-sm">
                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
                <span class="text-sm font-bold text-white"><?= h((string) $challenge['duration_days']) ?>-Day Program</span>
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

    <div class="rounded-2xl border border-orange-100 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-amber-100 to-orange-100">
                <svg class="h-6 w-6 text-molten" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
            </div>
            <div>
                <div class="text-lg font-extrabold tracking-tight text-zinc-900">My Participation</div>
                <div class="mt-0.5 text-sm text-zinc-500"><?= $participant ? ('Days logged: ' . h((string) $daysDone) . '/' . h((string) $challenge['duration_days']) . ' • Keep going!') : 'Join the coach challenge to start logging weigh-ins.' ?></div>
            </div>
        </div>

        <?php if ($participant): ?>
            <!-- Stats Cards -->
            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 to-indigo-50 p-4">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                        </svg>
                        <span class="text-xs font-bold uppercase tracking-wide text-blue-600">Start Weight</span>
                    </div>
                    <div class="mt-2 text-2xl font-extrabold text-blue-700"><?= h((string) $participant['start_weight_lbs']) ?> <span class="text-sm font-bold">lbs</span></div>
                </div>
                <div class="rounded-2xl border border-purple-100 bg-gradient-to-br from-purple-50 to-violet-50 p-4">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-purple-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span class="text-xs font-bold uppercase tracking-wide text-purple-600">Latest Weight</span>
                    </div>
                    <div class="mt-2 text-2xl font-extrabold text-purple-700"><?= $latestWeight === null ? '-' : h(number_format((float) $latestWeight, 2, '.', '')) ?> <span class="text-sm font-bold"><?= $latestWeight !== null ? 'lbs' : '' ?></span></div>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-green-50 p-4">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                        </svg>
                        <span class="text-xs font-bold uppercase tracking-wide text-emerald-600">Total Loss</span>
                    </div>
                    <div class="mt-2 text-2xl font-extrabold text-emerald-700"><?= $loss === null ? '-' : h(number_format((float) $loss, 2, '.', '')) ?> <span class="text-sm font-bold"><?= $loss !== null ? 'lbs' : '' ?></span></div>
                </div>
            </div>

            <!-- Progress Timeline -->
            <div class="mt-6 pt-4 border-t border-orange-100">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-bold text-zinc-700">Challenge Progress</span>
                    <span class="text-sm font-bold text-molten"><?= h((string) $daysDone) ?>/<?= h((string) $challenge['duration_days']) ?> Days</span>
                </div>
                <div class="flex items-center gap-1">
                    <?php for ($i = 1; $i <= (int) $challenge['duration_days']; $i++): ?>
                        <?php
                            $isCompleted = !empty($recordedDays[$i]);
                            $isCurrent = $i === $todayDayNumber;
                            $isPast = $todayDayNumber > 0 && $i < $todayDayNumber && !$isCompleted;
                            $dayClass = $isCompleted ? 'bg-emerald-500 text-white' : ($isCurrent ? 'bg-molten text-white ring-2 ring-molten/30' : ($isPast ? 'bg-red-100 text-red-400' : 'bg-zinc-100 text-zinc-400'));
                        ?>
                        <div class="flex-1 flex flex-col items-center gap-1">
                            <div class="w-full h-9 rounded-lg flex items-center justify-center text-xs font-bold transition-all <?= $dayClass ?>">
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
            <div class="mt-6 pt-4 border-t border-orange-100">
                <?php if ($maxDayAllowed < 1): ?>
                    <div class="flex items-center gap-2 rounded-xl bg-blue-50 px-4 py-3">
                        <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm font-medium text-blue-700">Challenge starts soon! Check-ins will be available when the challenge begins.</span>
                    </div>
                <?php elseif ($currentDay > (int) $challenge['duration_days']): ?>
                    <div class="flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3">
                        <svg class="h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                        <span class="text-sm font-medium text-emerald-700">Congratulations! You have completed this coach challenge!</span>
                    </div>
                <?php elseif (!$canRecordToday): ?>
                    <div class="flex items-center gap-2 rounded-xl bg-zinc-50 px-4 py-3">
                        <svg class="h-5 w-5 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm font-medium text-zinc-600">Day <?= (int) $todayDayNumber ?> weigh-in already recorded. Come back tomorrow!</span>
                    </div>
                <?php else: ?>
                    <form method="post" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3" novalidate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="record_checkin" />

                        <div class="flex items-center gap-2 flex-1">
                            <div class="flex items-center gap-2 rounded-xl border border-orange-200 bg-gradient-to-r from-orange-50 to-amber-50 px-3 py-2">
                                <svg class="h-4 w-4 text-molten" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <select name="day_number" class="bg-transparent text-sm font-bold text-zinc-700 outline-none cursor-pointer">
                                    <option value="<?= (int) $todayDayNumber ?>">Day <?= (int) $todayDayNumber ?></option>
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
        <?php else: ?>
            <!-- Join Form -->
            <div class="mt-6 pt-4 border-t border-orange-100">
                <div class="flex items-center gap-2 mb-4">
                    <svg class="h-5 w-5 text-molten" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    <span class="text-sm font-bold text-zinc-700">Enter your details to join this challenge</span>
                </div>

                <form method="post" class="space-y-4" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="join" />

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 flex items-center gap-2 text-sm font-semibold text-zinc-700" for="height_ft">
                                <svg class="h-4 w-4 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h10v10H7V7z"/></svg>
                                Height (ft)
                            </label>
                            <input id="height_ft" name="height_ft" type="number" min="3" max="8" required placeholder="e.g., 5" class="w-full rounded-xl border border-orange-200 bg-white px-4 py-3 text-sm font-medium outline-none ring-molten/20 transition focus:border-molten focus:ring-4" />
                        </div>
                        <div>
                            <label class="mb-2 flex items-center gap-2 text-sm font-semibold text-zinc-700" for="height_in">
                                <svg class="h-4 w-4 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h10v10H7V7z"/></svg>
                                Height (in)
                            </label>
                            <input id="height_in" name="height_in" type="number" min="0" max="11" required placeholder="e.g., 8" class="w-full rounded-xl border border-orange-200 bg-white px-4 py-3 text-sm font-medium outline-none ring-molten/20 transition focus:border-molten focus:ring-4" />
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 flex items-center gap-2 text-sm font-semibold text-zinc-700" for="start_weight_lbs">
                            <svg class="h-4 w-4 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                            Starting Weight (lbs)
                        </label>
                        <input id="start_weight_lbs" name="start_weight_lbs" type="number" min="50" max="500" step="0.01" required placeholder="e.g., 180" class="w-full rounded-xl border border-orange-200 bg-white px-4 py-3 text-sm font-medium outline-none ring-molten/20 transition focus:border-molten focus:ring-4" />
                    </div>

                    <div>
                        <label class="mb-2 flex items-center gap-2 text-sm font-semibold text-zinc-700" for="bmi">
                            <svg class="h-4 w-4 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            BMI (Coach-entered)
                        </label>
                        <input id="bmi" name="bmi" type="number" min="5" max="100" step="0.01" required placeholder="e.g., 23.45" class="w-full rounded-xl border border-orange-200 bg-white px-4 py-3 text-sm font-medium outline-none ring-molten/20 transition focus:border-molten focus:ring-4" />
                    </div>

                    <div>
                        <label class="mb-2 flex items-center gap-2 text-sm font-semibold text-zinc-700" for="bmi_category">
                            <svg class="h-4 w-4 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                            BMI Category
                        </label>
                        <select id="bmi_category" name="bmi_category" required class="w-full rounded-xl border border-orange-200 bg-white px-4 py-3 text-sm font-medium outline-none ring-molten/20 transition focus:border-molten focus:ring-4">
                            <option value="" disabled selected>Select category</option>
                            <?php foreach (bmi_category_options() as $opt): ?>
                                <option value="<?= h($opt) ?>"><?= h($opt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-molten to-pumpkin px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-orange-200/50 transition hover:shadow-xl hover:scale-[1.01] active:scale-[0.99]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        Join Coach Challenge
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>
