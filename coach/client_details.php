<?php
require __DIR__ . '/../app/bootstrap.php';
require_role(['coach', 'admin']);

$user = auth_user();
$isAdmin = $user && (string) $user['role'] === 'admin';
$clientId = (int) ($_GET['id'] ?? 0);
if ($clientId <= 0) {
    http_response_code(404);
    exit;
}

$client = null;
$hasFullName = db_has_column('clients', 'full_name');
$hasDay10Front = db_has_column('clients', 'day10_front_photo_path');
$hasDay10Side = db_has_column('clients', 'day10_side_photo_path');
$hasChallengeStartDate = db_has_column('clients', 'challenge_start_date');
$hasDay10Waist = db_has_column('clients', 'day10_waistline_in');

if (!$hasDay10Waist) {
    try {
        db()->exec('ALTER TABLE clients ADD COLUMN day10_waistline_in DECIMAL(6,2) NULL AFTER waistline_in');
        $hasDay10Waist = db_has_column('clients', 'day10_waistline_in');
    } catch (Throwable $e) {
        $hasDay10Waist = false;
    }
}

$errors = [];
$success = null;

try {
    $select = $hasFullName
        ? 'SELECT id, coach_user_id, full_name, gender, age, height_ft, height_in, start_weight_lbs, waistline_in, bmi, bmi_category, front_photo_path, side_photo_path, registered_at' .
            ($hasChallengeStartDate ? ', challenge_start_date' : '') .
            ($hasDay10Front ? ', day10_front_photo_path' : '') .
            ($hasDay10Side ? ', day10_side_photo_path' : '') .
            ($hasDay10Waist ? ', day10_waistline_in' : '') .
            ' FROM clients WHERE id = ? LIMIT 1'
        : 'SELECT id, coach_user_id, gender, age, height_ft, height_in, start_weight_lbs, waistline_in, bmi, bmi_category, front_photo_path, side_photo_path, registered_at' .
            ($hasChallengeStartDate ? ', challenge_start_date' : '') .
            ($hasDay10Front ? ', day10_front_photo_path' : '') .
            ($hasDay10Side ? ', day10_side_photo_path' : '') .
            ($hasDay10Waist ? ', day10_waistline_in' : '') .
            ' FROM clients WHERE id = ? LIMIT 1';

    $stmt = db()->prepare($select);
    $stmt->execute([$clientId]);
    $client = $stmt->fetch();
} catch (Throwable $e) {
    $client = null;
}

if (!$client || (!$isAdmin && (int) $client['coach_user_id'] !== (int) $user['id'])) {
    http_response_code(404);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($isAdmin) {
        $errors[] = 'Admins can view client details here, but cannot modify client data on this page.';
    } elseif (!csrf_verify()) {
        $errors[] = 'Invalid session. Please refresh and try again.';
    } else {
        $action = (string) ($_POST['action'] ?? '');
        if ($action === 'set_day1_baseline') {
            $weight = (float) ($_POST['start_weight_lbs'] ?? 0);
            $waist = (float) ($_POST['waistline_in'] ?? 0);
            $category = trim((string) ($_POST['bmi_category'] ?? ''));

            if ($weight < 50 || $weight > 500) {
                $errors[] = 'Weight must be between 50 and 500 lbs.';
            }
            if ($waist < 20 || $waist > 80) {
                $errors[] = 'Waistline must be between 20 and 80 inches.';
            }
            if ($category === '') {
                $errors[] = 'BMI category is required.';
            } elseif (!in_array($category, bmi_category_options(), true)) {
                $errors[] = 'Invalid BMI category.';
            }

            $bmi = bmi_from_imperial($weight, (int) $client['height_ft'], (int) $client['height_in']);
            if ($bmi === null) {
                $errors[] = 'BMI could not be calculated. Please check height and weight.';
            }

            $baseDir = project_root_path('storage/uploads/clients/' . (int) $client['id'] . '/day1');
            $frontPath = null;
            $sidePath = null;

            if (isset($_FILES['day1_front']) && $_FILES['day1_front']['error'] !== UPLOAD_ERR_NO_FILE) {
                [$ok, $res] = store_uploaded_image($_FILES['day1_front'], $baseDir, 'front');
                if ($ok) {
                    $frontPath = $res;
                } else {
                    $errors[] = 'Day 1 front photo: ' . $res;
                }
            } else {
                $errors[] = 'Day 1 front photo is required.';
            }

            if (isset($_FILES['day1_side']) && $_FILES['day1_side']['error'] !== UPLOAD_ERR_NO_FILE) {
                [$ok, $res] = store_uploaded_image($_FILES['day1_side'], $baseDir, 'side');
                if ($ok) {
                    $sidePath = $res;
                } else {
                    $errors[] = 'Day 1 side photo: ' . $res;
                }
            } else {
                $errors[] = 'Day 1 side photo is required.';
            }

            if (!$errors) {
                try {
                    $stmt = db()->prepare('UPDATE clients SET start_weight_lbs = ?, waistline_in = ?, bmi = ?, bmi_category = ?, front_photo_path = ?, side_photo_path = ? WHERE id = ? AND coach_user_id = ?');
                    $stmt->execute([
                        number_format((float) $weight, 2, '.', ''),
                        number_format((float) $waist, 2, '.', ''),
                        number_format((float) $bmi, 2, '.', ''),
                        (string) $category,
                        (string) $frontPath,
                        (string) $sidePath,
                        (int) $client['id'],
                        (int) $user['id'],
                    ]);

                    $success = 'Day 1 baseline saved.';
                    redirect(url('/coach/client_details.php?id=' . (int) $client['id']));
                } catch (Throwable $e) {
                    $errors[] = 'Failed to save Day 1 baseline.';
                }
            }
        }
        if ($action === 'upload_day10_photos') {
            if (!$hasDay10Front || !$hasDay10Side) {
                $errors[] = 'Day 10 photo uploads are not enabled in this database schema.';
            }

            $day10WaistRaw = trim((string) ($_POST['day10_waistline_in'] ?? ''));
            $day10Waist = null;
            $updateWaist = false;
            if ($hasDay10Waist) {
                if ($day10WaistRaw === '') {
                    $errors[] = 'Day 10 waistline is required.';
                } elseif (!is_numeric($day10WaistRaw)) {
                    $errors[] = 'Day 10 waistline must be a valid number.';
                } else {
                    $day10Waist = (float) $day10WaistRaw;
                    if ($day10Waist < 20 || $day10Waist > 80) {
                        $errors[] = 'Day 10 waistline must be between 20 and 80 inches.';
                    } else {
                        $updateWaist = true;
                    }
                }
            }

            if (!$errors) {
                try {
                    $stmt = db()->prepare('SELECT 1 FROM client_checkins WHERE client_id = ? AND coach_user_id = ? AND day_number = 10 LIMIT 1');
                    $stmt->execute([(int) $client['id'], (int) $user['id']]);
                    if (!$stmt->fetchColumn()) {
                        $errors[] = 'Day 10 photos can only be uploaded after the Day 10 weigh-in is recorded.';
                    }
                } catch (Throwable $e) {
                    $errors[] = 'Failed to validate Day 10 check-in.';
                }
            }

            if (!$errors) {
                $baseDir = project_root_path('storage/uploads/clients/' . (int) $client['id'] . '/day10');

                $updatedFront = false;
                $updatedSide = false;
                $frontPath = null;
                $sidePath = null;

                if (isset($_FILES['day10_front']) && $_FILES['day10_front']['error'] !== UPLOAD_ERR_NO_FILE) {
                    [$ok, $res] = store_uploaded_image($_FILES['day10_front'], $baseDir, 'front');
                    if ($ok) {
                        $frontPath = $res;
                        $updatedFront = true;
                    } else {
                        $errors[] = 'Day 10 front photo: ' . $res;
                    }
                }

                if (isset($_FILES['day10_side']) && $_FILES['day10_side']['error'] !== UPLOAD_ERR_NO_FILE) {
                    [$ok, $res] = store_uploaded_image($_FILES['day10_side'], $baseDir, 'side');
                    if ($ok) {
                        $sidePath = $res;
                        $updatedSide = true;
                    } else {
                        $errors[] = 'Day 10 side photo: ' . $res;
                    }
                }

                if (!$errors && !$updatedFront && !$updatedSide && !$updateWaist) {
                    $errors[] = 'Please select at least one Day 10 photo to upload.';
                }

                if (!$errors) {
                    try {
                        $setParts = [];
                        $params = [];

                        if ($updatedFront) {
                            $setParts[] = 'day10_front_photo_path = ?';
                            $params[] = (string) $frontPath;
                        }
                        if ($updatedSide) {
                            $setParts[] = 'day10_side_photo_path = ?';
                            $params[] = (string) $sidePath;
                        }
                        if ($updateWaist && $hasDay10Waist) {
                            $setParts[] = 'day10_waistline_in = ?';
                            $params[] = number_format((float) $day10Waist, 2, '.', '');
                        }

                        $params[] = (int) $client['id'];
                        $params[] = (int) $user['id'];

                        $stmt = db()->prepare('UPDATE clients SET ' . implode(', ', $setParts) . ' WHERE id = ? AND coach_user_id = ?');
                        $stmt->execute($params);

                        $success = 'Day 10 progress saved.';
                        redirect(url('/coach/client_details.php?id=' . (int) $client['id']));
                    } catch (Throwable $e) {
                        $errors[] = 'Failed to save Day 10 progress.';
                    }
                }
            }
        }
    }
}

$name = $hasFullName ? trim((string) ($client['full_name'] ?? '')) : '';
if ($name === '') {
    $name = 'Client #' . (string) $client['id'];
}

$checkins = [];
$daysCompleted = 0;
$latestWeight = null;

try {
    if ($isAdmin) {
        $stmt = db()->prepare('SELECT day_number, weight_lbs, recorded_at FROM client_checkins WHERE client_id = ? ORDER BY day_number ASC');
        $stmt->execute([(int) $client['id']]);
    } else {
        $stmt = db()->prepare('SELECT day_number, weight_lbs, recorded_at FROM client_checkins WHERE client_id = ? AND coach_user_id = ? ORDER BY day_number ASC');
        $stmt->execute([(int) $client['id'], (int) $user['id']]);
    }
    $checkins = $stmt->fetchAll();
    $daysCompleted = count($checkins);
    if ($checkins) {
        $latest = $checkins[count($checkins) - 1];
        $latestWeight = isset($latest['weight_lbs']) ? (float) $latest['weight_lbs'] : null;
    }
} catch (Throwable $e) {
    $checkins = [];
    $daysCompleted = 0;
    $latestWeight = null;
}

$hasDay10Checkin = false;
foreach ($checkins as $row) {
    if ((int) ($row['day_number'] ?? 0) === 10) {
        $hasDay10Checkin = true;
        break;
    }
}

$checkinsByDay = [];
foreach ($checkins as $row) {
    $dn = (int) ($row['day_number'] ?? 0);
    if ($dn >= 1 && $dn <= 10) {
        $checkinsByDay[$dn] = $row;
    }
}

function challenge_start_date_for_client_details(array $client)
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

function challenge_day_from_start_details($startDate)
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

$challengeStartDate = challenge_start_date_for_client_details($client);
$currentDay = challenge_day_from_start_details($challengeStartDate);
$maxDayAllowed = $currentDay === null ? 0 : ($currentDay === 0 ? 0 : ($currentDay === 11 ? 10 : (int) $currentDay));

$hasDay10Photos = ($hasDay10Front && !empty($client['day10_front_photo_path'])) && ($hasDay10Side && !empty($client['day10_side_photo_path']));
$isCompleted = $hasDay10Checkin && $hasDay10Photos;

$startWeight = (float) $client['start_weight_lbs'];
$weightLoss = $latestWeight === null ? null : max(0, $startWeight - (float) $latestWeight);

$hasDay1Photos = !empty($client['front_photo_path']) && !empty($client['side_photo_path']);
$hasBaselineMetrics = $startWeight > 0 && ((float) $client['bmi'] ?? 0) > 0;

$page_title = 'Client Details';
require __DIR__ . '/../partials/layout_top.php';
require __DIR__ . '/../partials/nav.php';
?>

<main class="mx-auto max-w-6xl px-4 py-8">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight"><?= h($name) ?></h1>
            <p class="mt-1 text-sm text-zinc-600">Client details, progress summary, check-in history, and progress photos.</p>
        </div>
        <a href="<?= h($isAdmin ? url('/admin/clients.php') : url('/coach/clients.php')) ?>" class="inline-flex items-center justify-center rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm font-extrabold text-zinc-700 hover:bg-orange-50">Back to Clients</a>
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

    <div class="mb-6 rounded-2xl border border-orange-100 bg-white p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="text-lg font-extrabold tracking-tight">Challenge Completion</div>
                <div class="mt-1 text-sm text-zinc-600">Completion requires a Day 10 weigh-in and Day 10 front/side photos.</div>
            </div>
            <div class="rounded-xl border border-orange-100 bg-orange-50 px-4 py-3 text-sm font-extrabold <?= $isCompleted ? 'text-green-700' : 'text-zinc-700' ?>">
                <?= $isCompleted ? 'Completed' : 'In Progress' ?>
            </div>
        </div>

        <?php if (!$hasDay1Photos || !$hasBaselineMetrics): ?>
            <div class="mt-5 rounded-2xl border border-orange-100 bg-orange-50 p-5">
                <div class="text-sm font-extrabold text-zinc-800">Set New Baseline (Day 1)</div>
                <div class="mt-1 text-sm text-zinc-600">Required after an admin reset or when re-enrolling a returning client.</div>

                <form method="post" enctype="multipart/form-data" class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="set_day1_baseline" />

                    <div>
                        <label class="block text-xs font-extrabold uppercase tracking-wide text-zinc-600">Start Weight (lbs)</label>
                        <input type="number" step="0.01" min="50" max="500" name="start_weight_lbs" class="mt-2 block w-full rounded-xl border border-orange-100 bg-white px-3 py-2 text-sm text-zinc-700" required />
                    </div>

                    <div>
                        <label class="block text-xs font-extrabold uppercase tracking-wide text-zinc-600">Waistline (in)</label>
                        <input type="number" step="0.01" min="20" max="80" name="waistline_in" class="mt-2 block w-full rounded-xl border border-orange-100 bg-white px-3 py-2 text-sm text-zinc-700" required />
                    </div>

                    <div>
                        <label class="block text-xs font-extrabold uppercase tracking-wide text-zinc-600">BMI Category</label>
                        <select name="bmi_category" class="mt-2 block w-full rounded-xl border border-orange-100 bg-white px-3 py-2 text-sm text-zinc-700" required>
                            <option value="">Select category</option>
                            <?php foreach (bmi_category_options() as $opt): ?>
                                <option value="<?= h($opt) ?>"><?= h($opt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hidden sm:block"></div>

                    <div>
                        <label class="block text-xs font-extrabold uppercase tracking-wide text-zinc-600">Day 1 Front Photo</label>
                        <input type="file" name="day1_front" accept="image/jpeg,image/png" class="mt-2 block w-full rounded-xl border border-orange-100 bg-white px-3 py-2 text-sm text-zinc-700" required />
                    </div>

                    <div>
                        <label class="block text-xs font-extrabold uppercase tracking-wide text-zinc-600">Day 1 Side Photo</label>
                        <input type="file" name="day1_side" accept="image/jpeg,image/png" class="mt-2 block w-full rounded-xl border border-orange-100 bg-white px-3 py-2 text-sm text-zinc-700" required />
                    </div>

                    <div class="sm:col-span-2 sm:text-right">
                        <button type="submit" class="w-full rounded-xl bg-molten px-4 py-3 text-sm font-extrabold text-white hover:bg-pumpkin sm:w-auto">Save Day 1 Baseline</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($hasDay10Front && $hasDay10Side): ?>
            <form method="post" enctype="multipart/form-data" class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3 sm:items-end" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="upload_day10_photos" />

                <div>
                    <label class="block text-xs font-extrabold uppercase tracking-wide text-zinc-600">Day 10 Front</label>
                    <input type="file" name="day10_front" accept="image/jpeg,image/png" class="mt-2 block w-full rounded-xl border border-orange-100 bg-white px-3 py-2 text-sm text-zinc-700" <?= !$hasDay10Checkin ? 'disabled' : '' ?> />
                </div>

                <?php if ($hasDay10Waist): ?>
                    <?php $day10WaistVal = isset($client['day10_waistline_in']) ? (string) $client['day10_waistline_in'] : ''; ?>
                    <div>
                        <label class="block text-xs font-extrabold uppercase tracking-wide text-zinc-600">Day 10 Waistline (in)</label>
                        <input type="number" step="0.01" min="20" max="80" name="day10_waistline_in" value="<?= h($day10WaistVal) ?>" class="mt-2 block w-full rounded-xl border border-orange-100 bg-white px-3 py-2 text-sm text-zinc-700" <?= !$hasDay10Checkin ? 'disabled' : '' ?> />
                    </div>
                <?php endif; ?>

                <div>
                    <label class="block text-xs font-extrabold uppercase tracking-wide text-zinc-600">Day 10 Side</label>
                    <input type="file" name="day10_side" accept="image/jpeg,image/png" class="mt-2 block w-full rounded-xl border border-orange-100 bg-white px-3 py-2 text-sm text-zinc-700" <?= !$hasDay10Checkin ? 'disabled' : '' ?> />
                </div>

                <div class="sm:text-right">
                    <button type="submit" class="w-full rounded-xl bg-molten px-4 py-3 text-sm font-extrabold text-white hover:bg-pumpkin sm:w-auto" <?= !$hasDay10Checkin ? 'disabled' : '' ?>>Upload Day 10 Photos</button>
                </div>
            </form>
            <?php if (!$hasDay10Checkin): ?>
                <div class="mt-3 text-sm text-zinc-600">Day 10 photo upload is locked until the Day 10 weigh-in is recorded.</div>
            <?php endif; ?>
        <?php else: ?>
            <div class="mt-4 text-sm text-zinc-600">Day 10 photo uploads are not available on this database schema.</div>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border border-orange-100 bg-white p-6 lg:col-span-1">
            <div class="text-lg font-extrabold tracking-tight">Profile Information</div>

            <div class="mt-4 space-y-2 text-sm text-zinc-700">
                <div><span class="font-semibold">Gender:</span> <?= h((string) $client['gender']) ?></div>
                <div><span class="font-semibold">Age:</span> <?= h((string) $client['age']) ?></div>
                <div><span class="font-semibold">Height:</span> <?= h((string) $client['height_ft']) ?>ft <?= h((string) $client['height_in']) ?>in</div>
                <div><span class="font-semibold">Initial Weight:</span> <?= $startWeight > 0 ? h((string) $client['start_weight_lbs']) : '-' ?><?= $startWeight > 0 ? ' lbs' : '' ?></div>
                <?php $waistline = isset($client['waistline_in']) ? (float) $client['waistline_in'] : 0.0; ?>
                <div><span class="font-semibold">Waistline:</span> <?= $waistline > 0 ? h((string) $client['waistline_in']) . ' in' : '-' ?></div>
                <div><span class="font-semibold">Initial BMI:</span> <?= $hasBaselineMetrics ? h((string) $client['bmi']) : '-' ?><?= $hasBaselineMetrics ? (' (' . h((string) $client['bmi_category']) . ')') : '' ?></div>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-4">
                <div class="rounded-2xl border border-orange-100 bg-orange-50 p-4">
                    <div class="text-xs font-extrabold uppercase tracking-wide text-zinc-600">Days Completed</div>
                    <div class="mt-2 text-3xl font-extrabold text-molten"><?= h((string) $daysCompleted) ?></div>
                </div>
                <div class="rounded-2xl border border-orange-100 bg-orange-50 p-4">
                    <div class="text-xs font-extrabold uppercase tracking-wide text-zinc-600">Weight Loss</div>
                    <div class="mt-2 text-3xl font-extrabold text-molten">
                        <?= $weightLoss === null ? '-' : h(number_format((float) $weightLoss, 2, '.', '')) ?>
                    </div>
                    <div class="mt-1 text-xs text-zinc-600"><?= $latestWeight === null ? 'No check-ins yet' : ('Latest: ' . h(number_format((float) $latestWeight, 2, '.', '')) . ' lbs') ?></div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-orange-100 bg-white p-6 lg:col-span-2">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <div class="text-lg font-extrabold tracking-tight">Daily Check-ins History</div>
                    <div class="mt-1 text-sm text-zinc-600">Shows recorded daily weights. Logging will be added in the Challenge module.</div>
                </div>
            </div>

            <div class="mt-5 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-orange-100 text-xs font-extrabold uppercase tracking-wide text-zinc-600">
                            <th class="py-3 pr-4">Day</th>
                            <th class="py-3 pr-4">Weight (lbs)</th>
                            <th class="py-3 pr-0">Recorded At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($d = 1; $d <= 10; $d++): ?>
                            <?php $row = $checkinsByDay[$d] ?? null; ?>
                            <tr class="border-b border-orange-50">
                                <td class="py-4 pr-4 font-extrabold text-zinc-900">Day <?= h((string) $d) ?></td>
                                <?php if ($row): ?>
                                    <td class="py-4 pr-4 text-zinc-700"><?= h((string) $row['weight_lbs']) ?></td>
                                    <td class="py-4 pr-0 text-zinc-700"><?= h((string) $row['recorded_at']) ?></td>
                                <?php else: ?>
                                    <?php if ($maxDayAllowed > 0 && $d <= $maxDayAllowed): ?>
                                        <td class="py-4 pr-4 font-extrabold text-red-700">Missed</td>
                                        <td class="py-4 pr-0 text-zinc-500">-</td>
                                    <?php else: ?>
                                        <td class="py-4 pr-4 text-zinc-500">Upcoming</td>
                                        <td class="py-4 pr-0 text-zinc-500">-</td>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-orange-100 bg-white p-6 lg:col-span-3">
            <div class="text-lg font-extrabold tracking-tight">Progress Photos</div>
            <div class="mt-1 text-sm text-zinc-600">Day 1 photos come from pre-registration. Day 10 will show once uploaded.</div>

            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-orange-100 bg-orange-50 p-4">
                    <div class="text-sm font-extrabold">Day 1 (Front)</div>
                    <div class="mt-3 overflow-hidden rounded-xl bg-white">
                        <div class="aspect-[3/4] w-full">
                            <?php if (!empty($client['front_photo_path'])): ?>
                                <img src="<?= h(url('/media/client_photo.php?id=' . (int) $client['id'] . '&photo=day1_front')) ?>" alt="Day 1 front" class="h-full w-full object-cover" />
                            <?php else: ?>
                                <div class="flex h-full w-full items-center justify-center text-sm text-zinc-600">Not available yet</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-orange-100 bg-orange-50 p-4">
                    <div class="text-sm font-extrabold">Day 1 (Side)</div>
                    <div class="mt-3 overflow-hidden rounded-xl bg-white">
                        <div class="aspect-[3/4] w-full">
                            <?php if (!empty($client['side_photo_path'])): ?>
                                <img src="<?= h(url('/media/client_photo.php?id=' . (int) $client['id'] . '&photo=day1_side')) ?>" alt="Day 1 side" class="h-full w-full object-cover" />
                            <?php else: ?>
                                <div class="flex h-full w-full items-center justify-center text-sm text-zinc-600">Not available yet</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-orange-100 bg-orange-50 p-4">
                    <div class="text-sm font-extrabold">Day 10 (Front)</div>
                    <div class="mt-3 overflow-hidden rounded-xl bg-white">
                        <div class="aspect-[3/4] w-full">
                            <?php if ($hasDay10Front && !empty($client['day10_front_photo_path'])): ?>
                                <img src="<?= h(url('/media/client_photo.php?id=' . (int) $client['id'] . '&photo=day10_front')) ?>" alt="Day 10 front" class="h-full w-full object-cover" />
                            <?php else: ?>
                                <div class="flex h-full w-full items-center justify-center text-sm text-zinc-600">Not available yet</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-orange-100 bg-orange-50 p-4">
                    <div class="text-sm font-extrabold">Day 10 (Side)</div>
                    <div class="mt-3 overflow-hidden rounded-xl bg-white">
                        <div class="aspect-[3/4] w-full">
                            <?php if ($hasDay10Side && !empty($client['day10_side_photo_path'])): ?>
                                <img src="<?= h(url('/media/client_photo.php?id=' . (int) $client['id'] . '&photo=day10_side')) ?>" alt="Day 10 side" class="h-full w-full object-cover" />
                            <?php else: ?>
                                <div class="flex h-full w-full items-center justify-center text-sm text-zinc-600">Not available yet</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>
