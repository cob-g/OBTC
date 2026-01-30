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
    <!-- Hero Header -->
    <div class="mb-6 rounded-2xl border border-orange-100 bg-gradient-to-r from-orange-50 to-amber-50 p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-molten to-pumpkin text-white font-bold text-2xl shadow-lg">
                    <?= strtoupper(substr($name, 0, 1)) ?>
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-zinc-900"><?= h($name) ?></h1>
                    <p class="mt-1 flex items-center gap-2 text-sm text-zinc-600">
                        <svg class="h-4 w-4 text-molten" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Progress tracking & health metrics
                    </p>
                </div>
            </div>
            <a href="<?= h($isAdmin ? url('/admin/clients.php') : url('/coach/clients.php')) ?>" class="inline-flex items-center gap-2 rounded-xl border border-orange-200 bg-white px-4 py-3 text-sm font-bold text-zinc-700 shadow-sm transition hover:bg-orange-50 hover:border-orange-300">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Clients
            </a>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-gradient-to-r from-emerald-50 to-green-50 p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100">
                    <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div class="text-sm font-bold text-emerald-800"><?= h($success) ?></div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="mb-6 rounded-2xl border border-red-200 bg-gradient-to-r from-red-50 to-rose-50 p-5 shadow-sm">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-red-100">
                    <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="text-sm font-extrabold text-red-800">Please fix the following:</div>
                    <ul class="mt-2 space-y-1 text-sm text-red-700">
                        <?php foreach ($errors as $e): ?>
                            <li class="flex items-start gap-2">
                                <span class="mt-1.5 h-1 w-1 flex-shrink-0 rounded-full bg-red-400"></span>
                                <span><?= h((string) $e) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="mb-6 rounded-2xl border border-orange-100 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-amber-100 to-orange-100">
                    <svg class="h-6 w-6 text-molten" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-lg font-extrabold tracking-tight text-zinc-900">Challenge Status</div>
                    <div class="mt-0.5 text-sm text-zinc-500">Requires Day 10 weigh-in + progress photos</div>
                </div>
            </div>
            <?php if ($isCompleted): ?>
                <div class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-gradient-to-r from-emerald-50 to-green-50 px-4 py-3 text-sm font-bold text-emerald-700 shadow-sm">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Completed
                </div>
            <?php else: ?>
                <div class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-gradient-to-r from-amber-50 to-orange-50 px-4 py-3 text-sm font-bold text-amber-700 shadow-sm">
                    <svg class="h-5 w-5 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    In Progress
                </div>
            <?php endif; ?>
        </div>

        <?php if (!$hasDay1Photos || !$hasBaselineMetrics): ?>
            <div class="mt-5 rounded-2xl border-2 border-dashed border-orange-200 bg-gradient-to-br from-orange-50 to-amber-50 p-6">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-100">
                        <svg class="h-5 w-5 text-molten" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-extrabold text-zinc-800">Set New Baseline (Day 1)</div>
                        <div class="text-xs text-zinc-600">Required after admin reset or re-enrollment</div>
                    </div>
                </div>

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
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-molten to-pumpkin px-6 py-3 text-sm font-bold text-white shadow-md shadow-orange-200/50 transition hover:shadow-lg hover:scale-[1.02] active:scale-[0.98] sm:w-auto">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            Save Day 1 Baseline
                        </button>
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
                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-molten to-pumpkin px-6 py-3 text-sm font-bold text-white shadow-md shadow-orange-200/50 transition hover:shadow-lg hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed sm:w-auto" <?= !$hasDay10Checkin ? 'disabled' : '' ?>>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Upload Day 10 Photos
                    </button>
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
        <div class="rounded-2xl border border-orange-100 bg-white p-6 shadow-sm lg:col-span-1">
            <div class="flex items-center gap-3 pb-4 border-b border-orange-100">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-amber-100 to-orange-100">
                    <svg class="h-5 w-5 text-molten" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div class="text-lg font-extrabold tracking-tight text-zinc-900">Profile Information</div>
            </div>

            <div class="mt-4 space-y-3 text-sm text-zinc-700">
                <div><span class="font-semibold">Gender:</span> <?= h((string) $client['gender']) ?></div>
                <div><span class="font-semibold">Age:</span> <?= h((string) $client['age']) ?></div>
                <div><span class="font-semibold">Height:</span> <?= h((string) $client['height_ft']) ?>ft <?= h((string) $client['height_in']) ?>in</div>
                <div><span class="font-semibold">Initial Weight:</span> <?= $startWeight > 0 ? h((string) $client['start_weight_lbs']) : '-' ?><?= $startWeight > 0 ? ' lbs' : '' ?></div>
                <?php $waistline = isset($client['waistline_in']) ? (float) $client['waistline_in'] : 0.0; ?>
                <div><span class="font-semibold">Waistline:</span> <?= $waistline > 0 ? h((string) $client['waistline_in']) . ' in' : '-' ?></div>
                <div><span class="font-semibold">Initial BMI:</span> <?= $hasBaselineMetrics ? h((string) $client['bmi']) : '-' ?><?= $hasBaselineMetrics ? (' (' . h((string) $client['bmi_category']) . ')') : '' ?></div>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-4">
                <div class="rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 to-indigo-50 p-4 shadow-sm">
                    <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-blue-600">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Days Done
                    </div>
                    <div class="mt-2 text-3xl font-extrabold text-blue-700"><?= h((string) $daysCompleted) ?></div>
                    <div class="mt-1 text-xs text-blue-600">of 10 days</div>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-green-50 p-4 shadow-sm">
                    <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-emerald-600">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        Lost
                    </div>
                    <div class="mt-2 text-3xl font-extrabold text-emerald-700">
                        <?= $weightLoss === null ? '-' : h(number_format((float) $weightLoss, 1)) ?><?= $weightLoss !== null ? ' lbs' : '' ?>
                    </div>
                    <div class="mt-1 text-xs text-emerald-600"><?= $latestWeight === null ? 'No check-ins yet' : ('Latest: ' . h(number_format((float) $latestWeight, 1)) . ' lbs') ?></div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-orange-100 bg-white p-6 shadow-sm lg:col-span-2">
            <div class="flex items-center gap-3 pb-4 border-b border-orange-100">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-amber-100 to-orange-100">
                    <svg class="h-5 w-5 text-molten" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-lg font-extrabold tracking-tight text-zinc-900">Daily Check-ins</div>
                    <div class="mt-0.5 text-sm text-zinc-500">10-day weight tracking timeline</div>
                </div>
            </div>

            <!-- Timeline Style Check-ins -->
            <div class="mt-6 space-y-3">
                <?php for ($d = 1; $d <= 10; $d++): ?>
                    <?php $row = $checkinsByDay[$d] ?? null; ?>
                    <?php 
                        $isCompleted = $row !== null;
                        $isMissed = !$isCompleted && $maxDayAllowed > 0 && $d <= $maxDayAllowed;
                        $isUpcoming = !$isCompleted && !$isMissed;
                    ?>
                    <div class="flex items-center gap-4 rounded-xl border <?= $isCompleted ? 'border-emerald-100 bg-gradient-to-r from-emerald-50/50 to-green-50/50' : ($isMissed ? 'border-red-100 bg-gradient-to-r from-red-50/50 to-rose-50/50' : 'border-zinc-100 bg-zinc-50/50') ?> p-4 transition hover:shadow-sm">
                        <!-- Day Indicator -->
                        <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl <?= $isCompleted ? 'bg-emerald-100 text-emerald-700' : ($isMissed ? 'bg-red-100 text-red-700' : 'bg-zinc-100 text-zinc-500') ?> font-bold shadow-sm">
                            <?php if ($isCompleted): ?>
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            <?php elseif ($isMissed): ?>
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            <?php else: ?>
                                <?= h((string) $d) ?>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Day Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <div class="font-extrabold <?= $isCompleted ? 'text-emerald-900' : ($isMissed ? 'text-red-900' : 'text-zinc-700') ?>">Day <?= h((string) $d) ?></div>
                                <?php if ($isCompleted): ?>
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-700">Completed</span>
                                <?php elseif ($isMissed): ?>
                                    <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-bold text-red-700">Missed</span>
                                <?php else: ?>
                                    <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-bold text-zinc-600">Upcoming</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($row): ?>
                                <div class="mt-1 text-sm text-zinc-600">
                                    Recorded <?= h((string) $row['recorded_at']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Weight Display -->
                        <?php if ($row): ?>
                            <div class="text-right">
                                <div class="text-2xl font-extrabold text-emerald-700"><?= h((string) $row['weight_lbs']) ?></div>
                                <div class="text-xs text-emerald-600">lbs</div>
                            </div>
                        <?php else: ?>
                            <div class="text-right">
                                <div class="text-2xl font-extrabold text-zinc-300">-</div>
                                <div class="text-xs text-zinc-400">lbs</div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <div class="rounded-2xl border border-orange-100 bg-white p-6 shadow-sm lg:col-span-3">
            <div class="flex items-center gap-3 pb-4 border-b border-orange-100">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-amber-100 to-orange-100">
                    <svg class="h-5 w-5 text-molten" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-lg font-extrabold tracking-tight text-zinc-900">Progress Photos</div>
                    <div class="mt-0.5 text-sm text-zinc-500">Visual transformation journey</div>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="group rounded-2xl border border-orange-100 bg-gradient-to-br from-orange-50 to-amber-50 p-4 shadow-sm transition hover:shadow-md">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-100">
                            <span class="text-xs font-bold text-molten">1</span>
                        </div>
                        <div class="text-sm font-extrabold text-zinc-900">Day 1 • Front</div>
                    </div>
                    <div class="mt-3 overflow-hidden rounded-xl border-2 border-orange-100 bg-white shadow-sm transition group-hover:border-orange-200">
                        <div class="aspect-[3/4] w-full">
                            <?php if (!empty($client['front_photo_path'])): ?>
                                <img src="<?= h(url('/media/client_photo.php?id=' . (int) $client['id'] . '&photo=day1_front')) ?>" alt="Day 1 front" class="h-full w-full object-cover transition group-hover:scale-105" />
                            <?php else: ?>
                                <div class="flex h-full w-full flex-col items-center justify-center text-sm text-zinc-400">
                                    <svg class="h-12 w-12 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span>Not available</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="group rounded-2xl border border-orange-100 bg-gradient-to-br from-orange-50 to-amber-50 p-4 shadow-sm transition hover:shadow-md">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-100">
                            <span class="text-xs font-bold text-molten">1</span>
                        </div>
                        <div class="text-sm font-extrabold text-zinc-900">Day 1 • Side</div>
                    </div>
                    <div class="mt-3 overflow-hidden rounded-xl border-2 border-orange-100 bg-white shadow-sm transition group-hover:border-orange-200">
                        <div class="aspect-[3/4] w-full">
                            <?php if (!empty($client['side_photo_path'])): ?>
                                <img src="<?= h(url('/media/client_photo.php?id=' . (int) $client['id'] . '&photo=day1_side')) ?>" alt="Day 1 side" class="h-full w-full object-cover transition group-hover:scale-105" />
                            <?php else: ?>
                                <div class="flex h-full w-full flex-col items-center justify-center text-sm text-zinc-400">
                                    <svg class="h-12 w-12 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span>Not available</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="group rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-green-50 p-4 shadow-sm transition hover:shadow-md">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100">
                            <span class="text-xs font-bold text-emerald-700">10</span>
                        </div>
                        <div class="text-sm font-extrabold text-zinc-900">Day 10 • Front</div>
                    </div>
                    <div class="mt-3 overflow-hidden rounded-xl border-2 border-emerald-100 bg-white shadow-sm transition group-hover:border-emerald-200">
                        <div class="aspect-[3/4] w-full">
                            <?php if ($hasDay10Front && !empty($client['day10_front_photo_path'])): ?>
                                <img src="<?= h(url('/media/client_photo.php?id=' . (int) $client['id'] . '&photo=day10_front')) ?>" alt="Day 10 front" class="h-full w-full object-cover transition group-hover:scale-105" />
                            <?php else: ?>
                                <div class="flex h-full w-full flex-col items-center justify-center text-sm text-zinc-400">
                                    <svg class="h-12 w-12 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span>Pending</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="group rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-green-50 p-4 shadow-sm transition hover:shadow-md">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100">
                            <span class="text-xs font-bold text-emerald-700">10</span>
                        </div>
                        <div class="text-sm font-extrabold text-zinc-900">Day 10 • Side</div>
                    </div>
                    <div class="mt-3 overflow-hidden rounded-xl border-2 border-emerald-100 bg-white shadow-sm transition group-hover:border-emerald-200">
                        <div class="aspect-[3/4] w-full">
                            <?php if ($hasDay10Side && !empty($client['day10_side_photo_path'])): ?>
                                <img src="<?= h(url('/media/client_photo.php?id=' . (int) $client['id'] . '&photo=day10_side')) ?>" alt="Day 10 side" class="h-full w-full object-cover transition group-hover:scale-105" />
                            <?php else: ?>
                                <div class="flex h-full w-full flex-col items-center justify-center text-sm text-zinc-400">
                                    <svg class="h-12 w-12 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span>Pending</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>
