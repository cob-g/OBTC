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

$availableDays = [];
if ($maxDayAllowed >= 1) {
    for ($i = 1; $i <= (int) $maxDayAllowed; $i++) {
        if (empty($recordedDays[$i])) {
            $availableDays[] = $i;
        }
    }
}

if ($availableDays) {
    $defaultDay = (int) $availableDays[0];
}

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
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold tracking-tight">Coach Challenge</h1>
        <p class="mt-1 text-sm text-zinc-600"><?= h((string) $challenge['name']) ?> • Starts <?= h((string) $challenge['start_date']) ?> • <?= h((string) $challenge['duration_days']) ?> days</p>
    </div>

    <?php if ($success): ?>
        <div class="mb-5 rounded-2xl border border-green-200 bg-green-50 p-4 text-sm font-semibold text-green-800">
            <?= h($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4">
            <div class="text-sm font-extrabold text-red-800">Please fix the following:</div>
            <ul class="mt-2 list-disc pl-5 text-sm text-red-700">
                <?php foreach ($errors as $e): ?>
                    <li><?= h((string) $e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="rounded-2xl border border-orange-100 bg-white p-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="text-lg font-extrabold tracking-tight">My Participation</div>
                <div class="mt-1 text-sm text-zinc-600"><?= $participant ? ('Days logged: ' . h((string) $daysDone) . '/' . h((string) $challenge['duration_days'])) : 'Join the coach challenge to start logging weigh-ins.' ?></div>
            </div>
        </div>

        <?php if ($participant): ?>
            <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div class="rounded-2xl border border-orange-100 bg-orange-50 p-4">
                    <div class="text-xs font-extrabold uppercase tracking-wide text-zinc-600">Start Weight</div>
                    <div class="mt-1 text-xl font-extrabold text-zinc-900"><?= h((string) $participant['start_weight_lbs']) ?> lbs</div>
                </div>
                <div class="rounded-2xl border border-orange-100 bg-orange-50 p-4">
                    <div class="text-xs font-extrabold uppercase tracking-wide text-zinc-600">Latest Weight</div>
                    <div class="mt-1 text-xl font-extrabold text-zinc-900"><?= $latestWeight === null ? '-' : h(number_format((float) $latestWeight, 2, '.', '')) . ' lbs' ?></div>
                </div>
                <div class="rounded-2xl border border-orange-100 bg-orange-50 p-4">
                    <div class="text-xs font-extrabold uppercase tracking-wide text-zinc-600">Loss</div>
                    <div class="mt-1 text-xl font-extrabold text-zinc-900"><?= $loss === null ? '-' : h(number_format((float) $loss, 2, '.', '')) . ' lbs' ?></div>
                </div>
            </div>

            <form method="post" class="mt-6 flex flex-wrap items-center gap-2" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="record_checkin" />

                <select name="day_number" class="rounded-xl border border-orange-100 bg-white px-3 py-2 text-xs font-extrabold text-zinc-700 outline-none ring-molten/20 focus:border-molten focus:ring-4" <?= (!$availableDays) ? 'disabled' : '' ?>>
                    <?php foreach ($availableDays as $d): ?>
                        <option value="<?= (int) $d ?>" <?= (int) $d === (int) $defaultDay ? 'selected' : '' ?>>Day <?= (int) $d ?></option>
                    <?php endforeach; ?>
                </select>

                <input name="weight_lbs" type="number" min="50" max="500" step="0.01" value="" placeholder="Weight" class="w-28 rounded-xl border border-orange-100 bg-white px-3 py-2 text-xs font-extrabold text-zinc-700 outline-none ring-molten/20 focus:border-molten focus:ring-4" <?= (!$availableDays) ? 'disabled' : '' ?> />

                <button type="submit" class="rounded-xl bg-molten px-3 py-2 text-xs font-extrabold text-white hover:bg-pumpkin" <?= (!$availableDays) ? 'disabled' : '' ?>>Save</button>

                <?php if ($maxDayAllowed < 1): ?>
                    <span class="text-xs font-semibold text-zinc-500">Upcoming challenge</span>
                <?php elseif (!$availableDays): ?>
                    <span class="text-xs font-semibold text-zinc-500">All available days up to today are already recorded.</span>
                <?php endif; ?>
            </form>
        <?php else: ?>
            <form method="post" class="mt-6 space-y-4" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="join" />

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-semibold" for="height_ft">Height (ft)</label>
                        <input id="height_ft" name="height_ft" type="number" min="3" max="8" required class="w-full rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm outline-none ring-molten/20 focus:border-molten focus:ring-4" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold" for="height_in">Height (in)</label>
                        <input id="height_in" name="height_in" type="number" min="0" max="11" required class="w-full rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm outline-none ring-molten/20 focus:border-molten focus:ring-4" />
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold" for="start_weight_lbs">Starting Weight (lbs)</label>
                    <input id="start_weight_lbs" name="start_weight_lbs" type="number" min="50" max="500" step="0.01" required class="w-full rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm outline-none ring-molten/20 focus:border-molten focus:ring-4" />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold" for="bmi">BMI (Coach-entered)</label>
                    <input id="bmi" name="bmi" type="number" min="5" max="100" step="0.01" required class="w-full rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm outline-none ring-molten/20 focus:border-molten focus:ring-4" />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold" for="bmi_category">BMI Category</label>
                    <select id="bmi_category" name="bmi_category" required class="w-full rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm outline-none ring-molten/20 focus:border-molten focus:ring-4">
                        <option value="" disabled selected>Select category</option>
                        <?php foreach (bmi_category_options() as $opt): ?>
                            <option value="<?= h($opt) ?>"><?= h($opt) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="w-full rounded-xl bg-molten px-4 py-3 text-sm font-extrabold text-white hover:bg-pumpkin">
                    Join Coach Challenge
                </button>
            </form>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>
