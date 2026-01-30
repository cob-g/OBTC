<?php
require __DIR__ . '/../app/bootstrap.php';
require_role('coach');

$step = (int) ($_GET['step'] ?? 1);
if ($step < 1 || $step > 4) {
    $step = 1;
}

if (!isset($_SESSION['pre_registration']) || !is_array($_SESSION['pre_registration'])) {
    $_SESSION['pre_registration'] = [];
}

$data = $_SESSION['pre_registration'];
$errors = [];

function pre_reg_progress_percent($step)
{
    $map = [1 => 25, 2 => 50, 3 => 75, 4 => 100];
    return $map[$step] ?? 25;
}

function pre_reg_require_fields($fields, $data, &$errors)
{
    foreach ($fields as $field => $label) {
        $value = isset($data[$field]) ? trim((string) $data[$field]) : '';
        if ($value === '') {
            $errors[] = $label . ' is required.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Invalid session. Please refresh and try again.';
    } else {
        $action = (string) ($_POST['action'] ?? 'next');

        if ($step === 1) {
            $incoming = [
                'full_name' => (string) ($_POST['full_name'] ?? ''),
                'gender' => (string) ($_POST['gender'] ?? ''),
                'age' => (string) ($_POST['age'] ?? ''),
                'height_ft' => (string) ($_POST['height_ft'] ?? ''),
                'height_in' => (string) ($_POST['height_in'] ?? ''),
                'start_weight_lbs' => (string) ($_POST['start_weight_lbs'] ?? ''),
                'waistline_in' => (string) ($_POST['waistline_in'] ?? ''),
            ];

            pre_reg_require_fields([
                'full_name' => 'Full name',
                'gender' => 'Gender',
                'age' => 'Age',
                'height_ft' => 'Height (ft)',
                'height_in' => 'Height (in)',
                'start_weight_lbs' => 'Weight (lbs)',
                'waistline_in' => 'Waistline (in)',
            ], $incoming, $errors);

            $fullName = trim((string) $incoming['full_name']);
            if ($fullName !== '' && mb_strlen($fullName) < 2) {
                $errors[] = 'Full name must be at least 2 characters.';
            }
            if ($fullName !== '' && mb_strlen($fullName) > 150) {
                $errors[] = 'Full name must be 150 characters or less.';
            }

            $gender = $incoming['gender'];
            if ($gender !== '' && $gender !== 'male' && $gender !== 'female') {
                $errors[] = 'Gender must be Male or Female.';
            }

            $age = (int) $incoming['age'];
            if ($incoming['age'] !== '' && ($age < 10 || $age > 100)) {
                $errors[] = 'Age must be between 10 and 100.';
            }

            $heightFt = (int) $incoming['height_ft'];
            $heightIn = (int) $incoming['height_in'];
            if ($incoming['height_ft'] !== '' && ($heightFt < 3 || $heightFt > 8)) {
                $errors[] = 'Height (ft) must be between 3 and 8.';
            }
            if ($incoming['height_in'] !== '' && ($heightIn < 0 || $heightIn > 11)) {
                $errors[] = 'Height (in) must be between 0 and 11.';
            }

            $weight = (float) $incoming['start_weight_lbs'];
            if ($incoming['start_weight_lbs'] !== '' && ($weight < 50 || $weight > 500)) {
                $errors[] = 'Weight must be between 50 and 500 lbs.';
            }

            $waist = (float) $incoming['waistline_in'];
            if ($incoming['waistline_in'] !== '' && ($waist < 20 || $waist > 80)) {
                $errors[] = 'Waistline must be between 20 and 80 inches.';
            }

            if (!$errors) {
                $_SESSION['pre_registration'] = array_merge($_SESSION['pre_registration'], [
                    'full_name' => $fullName,
                    'gender' => $gender,
                    'age' => $age,
                    'height_ft' => $heightFt,
                    'height_in' => $heightIn,
                    'start_weight_lbs' => number_format($weight, 2, '.', ''),
                    'waistline_in' => number_format($waist, 2, '.', ''),
                ]);

                if ($action === 'save_exit') {
                    redirect(url('/coach/dashboard.php'));
                }

                redirect(url('/coach/pre_registration.php?step=2'));
            }
        }

        if ($step === 2) {
            $token = $_SESSION['pre_registration']['upload_token'] ?? '';
            if ($token === '') {
                $token = bin2hex(random_bytes(12));
                $_SESSION['pre_registration']['upload_token'] = $token;
            }

            $baseDir = project_root_path('storage/uploads/pre_registration/' . $token);
            ensure_dir($baseDir);

            $frontOk = isset($_SESSION['pre_registration']['front_photo_path']) && is_string($_SESSION['pre_registration']['front_photo_path']);
            $sideOk = isset($_SESSION['pre_registration']['side_photo_path']) && is_string($_SESSION['pre_registration']['side_photo_path']);

            if (isset($_FILES['front_photo']) && $_FILES['front_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
                [$ok, $res] = store_uploaded_image($_FILES['front_photo'], $baseDir, 'front');
                if ($ok) {
                    $_SESSION['pre_registration']['front_photo_path'] = $res;
                    $frontOk = true;
                } else {
                    $errors[] = 'Front photo: ' . $res;
                }
            }

            if (isset($_FILES['side_photo']) && $_FILES['side_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
                [$ok, $res] = store_uploaded_image($_FILES['side_photo'], $baseDir, 'side');
                if ($ok) {
                    $_SESSION['pre_registration']['side_photo_path'] = $res;
                    $sideOk = true;
                } else {
                    $errors[] = 'Side photo: ' . $res;
                }
            }

            if (!$frontOk) {
                $errors[] = 'Front photo is required.';
            }
            if (!$sideOk) {
                $errors[] = 'Side photo is required.';
            }

            if (!$errors) {
                if ($action === 'save_exit') {
                    redirect(url('/coach/dashboard.php'));
                }

                redirect(url('/coach/pre_registration.php?step=3'));
            }
        }

        if ($step === 3) {
            $consent = (string) ($_POST['consent'] ?? '');
            $category = trim((string) ($_POST['bmi_category'] ?? ''));
            $coachBmiRaw = trim((string) ($_POST['bmi'] ?? ''));

            $systemBmi = bmi_from_imperial(
                $_SESSION['pre_registration']['start_weight_lbs'] ?? 0,
                $_SESSION['pre_registration']['height_ft'] ?? 0,
                $_SESSION['pre_registration']['height_in'] ?? 0
            );

            if ($systemBmi === null) {
                $errors[] = 'BMI could not be calculated. Please check height and weight.';
            }

            if ($coachBmiRaw === '') {
                $errors[] = 'BMI value is required.';
            }

            $coachBmi = null;
            if ($coachBmiRaw !== '') {
                // Require exactly two decimal places, no more, no less
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

            if ($consent !== 'yes') {
                $errors[] = 'Consent confirmation is required.';
            }

            if ($systemBmi !== null && $coachBmi !== null && !$errors) {
                $expectedCategory = bmi_category_suggest($systemBmi);

                // Compare BMI values exactly at two decimal places (no tolerance), truncate raw system BMI
                $systemBmiTruncated = floor((float) $systemBmi * 100) / 100; // truncate to 2 decimals
                $systemBmiFormatted = number_format($systemBmiTruncated, 2, '.', '');
                $coachBmiFormatted = $coachBmiRaw; // already validated to be two decimals
                $bmiMatches = ($coachBmiFormatted === $systemBmiFormatted);

                if (!$bmiMatches) {
                    $errors[] = "It looks like the BMI value doesn't match the client's height and weight. Please double-check your calculation and try again.";
                } elseif ($expectedCategory !== null && $category !== $expectedCategory) {
                    $errors[] = "The BMI value is correct, but the selected category doesn't match it. Please review the BMI category before continuing.";
                }
            }

            if (!$errors) {
                $_SESSION['pre_registration']['bmi'] = number_format((float) $coachBmi, 2, '.', '');
                $_SESSION['pre_registration']['bmi_category'] = $category;
                $_SESSION['pre_registration']['consent_confirmed'] = true;

                if ($action === 'save_exit') {
                    redirect(url('/coach/dashboard.php'));
                }

                redirect(url('/coach/pre_registration.php?step=4'));
            }
        }

        if ($step === 4) {
            if ($action === 'restart') {
                unset($_SESSION['pre_registration']);
                redirect(url('/coach/pre_registration.php?step=1'));
            }

            $required = [
                'full_name' => 'Full name',
                'gender' => 'Gender',
                'age' => 'Age',
                'height_ft' => 'Height (ft)',
                'height_in' => 'Height (in)',
                'start_weight_lbs' => 'Weight (lbs)',
                'waistline_in' => 'Waistline (in)',
                'front_photo_path' => 'Front photo',
                'side_photo_path' => 'Side photo',
                'bmi' => 'BMI',
                'bmi_category' => 'BMI category',
            ];
            pre_reg_require_fields($required, $_SESSION['pre_registration'], $errors);
            if (empty($_SESSION['pre_registration']['consent_confirmed'])) {
                $errors[] = 'Consent confirmation is required.';
            }

            if (!$errors) {
                $user = auth_user();
                $registeredAt = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');

                $challengeStartDate = null;
                if (db_has_column('clients', 'challenge_start_date')) {
                    $now = new DateTimeImmutable('now');
                    $dow = (int) $now->format('N');
                    if ($dow === 1) {
                        $challengeStartDate = $now->format('Y-m-d');
                    } elseif ($dow === 2) {
                        $challengeStartDate = $now->modify('monday this week')->format('Y-m-d');
                    } else {
                        $challengeStartDate = $now->modify('next monday')->format('Y-m-d');
                    }
                }

                if (db_has_column('clients', 'full_name')) {
                    if ($challengeStartDate !== null) {
                        $insert = db()->prepare('INSERT INTO clients (coach_user_id, full_name, gender, age, height_ft, height_in, start_weight_lbs, waistline_in, bmi, bmi_category, front_photo_path, side_photo_path, challenge_start_date, registered_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                        $insert->execute([
                            (int) $user['id'],
                            (string) $_SESSION['pre_registration']['full_name'],
                            (string) $_SESSION['pre_registration']['gender'],
                            (int) $_SESSION['pre_registration']['age'],
                            (int) $_SESSION['pre_registration']['height_ft'],
                            (int) $_SESSION['pre_registration']['height_in'],
                            (string) $_SESSION['pre_registration']['start_weight_lbs'],
                            (string) $_SESSION['pre_registration']['waistline_in'],
                            (string) $_SESSION['pre_registration']['bmi'],
                            (string) $_SESSION['pre_registration']['bmi_category'],
                            (string) $_SESSION['pre_registration']['front_photo_path'],
                            (string) $_SESSION['pre_registration']['side_photo_path'],
                            $challengeStartDate,
                            $registeredAt,
                        ]);
                    } else {
                        $insert = db()->prepare('INSERT INTO clients (coach_user_id, full_name, gender, age, height_ft, height_in, start_weight_lbs, waistline_in, bmi, bmi_category, front_photo_path, side_photo_path, registered_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                        $insert->execute([
                            (int) $user['id'],
                            (string) $_SESSION['pre_registration']['full_name'],
                            (string) $_SESSION['pre_registration']['gender'],
                            (int) $_SESSION['pre_registration']['age'],
                            (int) $_SESSION['pre_registration']['height_ft'],
                            (int) $_SESSION['pre_registration']['height_in'],
                            (string) $_SESSION['pre_registration']['start_weight_lbs'],
                            (string) $_SESSION['pre_registration']['waistline_in'],
                            (string) $_SESSION['pre_registration']['bmi'],
                            (string) $_SESSION['pre_registration']['bmi_category'],
                            (string) $_SESSION['pre_registration']['front_photo_path'],
                            (string) $_SESSION['pre_registration']['side_photo_path'],
                            $registeredAt,
                        ]);
                    }
                } else {
                    if ($challengeStartDate !== null) {
                        $insert = db()->prepare('INSERT INTO clients (coach_user_id, gender, age, height_ft, height_in, start_weight_lbs, waistline_in, bmi, bmi_category, front_photo_path, side_photo_path, challenge_start_date, registered_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                        $insert->execute([
                            (int) $user['id'],
                            (string) $_SESSION['pre_registration']['gender'],
                            (int) $_SESSION['pre_registration']['age'],
                            (int) $_SESSION['pre_registration']['height_ft'],
                            (int) $_SESSION['pre_registration']['height_in'],
                            (string) $_SESSION['pre_registration']['start_weight_lbs'],
                            (string) $_SESSION['pre_registration']['waistline_in'],
                            (string) $_SESSION['pre_registration']['bmi'],
                            (string) $_SESSION['pre_registration']['bmi_category'],
                            (string) $_SESSION['pre_registration']['front_photo_path'],
                            (string) $_SESSION['pre_registration']['side_photo_path'],
                            $challengeStartDate,
                            $registeredAt,
                        ]);
                    } else {
                        $insert = db()->prepare('INSERT INTO clients (coach_user_id, gender, age, height_ft, height_in, start_weight_lbs, waistline_in, bmi, bmi_category, front_photo_path, side_photo_path, registered_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                        $insert->execute([
                            (int) $user['id'],
                            (string) $_SESSION['pre_registration']['gender'],
                            (int) $_SESSION['pre_registration']['age'],
                            (int) $_SESSION['pre_registration']['height_ft'],
                            (int) $_SESSION['pre_registration']['height_in'],
                            (string) $_SESSION['pre_registration']['start_weight_lbs'],
                            (string) $_SESSION['pre_registration']['waistline_in'],
                            (string) $_SESSION['pre_registration']['bmi'],
                            (string) $_SESSION['pre_registration']['bmi_category'],
                            (string) $_SESSION['pre_registration']['front_photo_path'],
                            (string) $_SESSION['pre_registration']['side_photo_path'],
                            $registeredAt,
                        ]);
                    }
                }

                $clientId = (int) db()->lastInsertId();

                $consentText = 'I confirm that the client has provided consent for the collection and processing of their personal and health-related data in accordance with the Privacy Policy.';
                $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : null;
                $ua = isset($_SERVER['HTTP_USER_AGENT']) ? (string) $_SERVER['HTTP_USER_AGENT'] : null;

                $log = db()->prepare('INSERT INTO consent_logs (client_id, coach_user_id, consent_text, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)');
                $log->execute([$clientId, (int) $user['id'], $consentText, $ip, $ua]);

                unset($_SESSION['pre_registration']);
                redirect(url('/coach/clients.php'));
            }
        }
    }
}

$data = $_SESSION['pre_registration'];

$page_title = 'Pre-Registration';
require __DIR__ . '/../partials/layout_top.php';
require __DIR__ . '/../partials/nav.php';
?>

<main class="mx-auto max-w-3xl px-4 py-8">
    <!-- Page Header with Icon -->
    <div class="mb-8">
        <div class="flex items-center gap-4">
            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-molten to-amber-500 shadow-lg shadow-orange-200/50">
                <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold tracking-tight text-zinc-900">Pre-Registration</h1>
                <p class="mt-0.5 text-sm text-zinc-500">Register a new challenger for the 10 Days Challenge</p>
            </div>
        </div>
    </div>

    <!-- Step Progress Indicator -->
    <div class="mb-8 rounded-2xl border border-orange-100 bg-gradient-to-r from-orange-50 via-white to-amber-50 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-molten/10 px-3 py-1.5 text-sm font-bold text-molten">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Step <?= h((string) $step) ?> of 4
                </span>
            </div>
            <span class="text-sm font-semibold text-zinc-600"><?= h((string) pre_reg_progress_percent($step)) ?>% Complete</span>
        </div>
        
        <!-- Visual Step Indicators -->
        <div class="flex items-center justify-between mb-3">
            <?php 
            $stepLabels = ['Info', 'Photos', 'BMI', 'Review'];
            $stepIcons = [
                '<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
                '<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
                '<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>',
                '<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
            ];
            foreach ($stepLabels as $i => $label): 
                $stepNum = $i + 1;
                $isComplete = $stepNum < $step;
                $isCurrent = $stepNum === $step;
            ?>
                <div class="flex flex-col items-center gap-1.5 flex-1">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl <?= $isComplete ? 'bg-green-500 text-white' : ($isCurrent ? 'bg-gradient-to-br from-molten to-amber-500 text-white shadow-lg shadow-orange-200/50' : 'bg-zinc-100 text-zinc-400') ?> transition-all">
                        <?php if ($isComplete): ?>
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <?php else: ?>
                            <?= $stepIcons[$i] ?>
                        <?php endif; ?>
                    </div>
                    <span class="text-xs font-semibold <?= $isCurrent ? 'text-molten' : ($isComplete ? 'text-green-600' : 'text-zinc-400') ?>"><?= $label ?></span>
                </div>
                <?php if ($i < 3): ?>
                    <div class="flex-1 h-0.5 mx-1 <?= $stepNum < $step ? 'bg-green-500' : 'bg-zinc-200' ?> rounded-full -mt-6"></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        
        <!-- Progress Bar -->
        <div class="h-2 w-full overflow-hidden rounded-full bg-orange-100">
            <div class="h-full bg-gradient-to-r from-molten to-amber-500 transition-all duration-500" style="width: <?= h((string) pre_reg_progress_percent($step)) ?>%"></div>
        </div>
    </div>

    <?php if ($errors): ?>
        <div class="mb-6 rounded-2xl border-2 border-red-200 bg-gradient-to-r from-red-50 to-rose-50 px-5 py-4 shadow-sm">
            <div class="flex items-center gap-2 font-bold text-red-700">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Please fix the following:
            </div>
            <ul class="mt-3 space-y-1.5 text-sm text-red-700">
                <?php foreach ($errors as $e): ?>
                    <li class="flex items-start gap-2">
                        <svg class="h-4 w-4 text-red-400 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        <?= h($e) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($step === 1): ?>
        <div class="rounded-2xl border border-orange-100 bg-white p-6 shadow-sm">
            <div class="flex items-center gap-3 mb-1">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-extrabold tracking-tight text-zinc-900">Basic Information</h2>
                    <p class="text-sm text-zinc-500">Coach-only data entry. Height in feet/inches, weight in pounds.</p>
                </div>
            </div>

            <form method="post" class="mt-6 space-y-5" novalidate>
                <?= csrf_field() ?>

                <div>
                    <label class="mb-1.5 flex items-center gap-2 text-sm font-bold text-zinc-700" for="full_name">
                        <svg class="h-4 w-4 text-orange-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Full Name
                    </label>
                    <input id="full_name" name="full_name" type="text" required value="<?= h((string) ($data['full_name'] ?? '')) ?>" placeholder="e.g., Juan Dela Cruz" class="w-full rounded-xl border border-orange-200 bg-orange-50/30 px-4 py-3 text-sm font-medium text-zinc-800 placeholder:text-zinc-400 outline-none ring-molten/30 transition-all focus:border-molten focus:bg-white focus:ring-4" />
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 flex items-center gap-2 text-sm font-bold text-zinc-700" for="gender">
                            <svg class="h-4 w-4 text-orange-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            Gender
                        </label>
                        <select id="gender" name="gender" required class="w-full rounded-xl border border-orange-200 bg-orange-50/30 px-4 py-3 text-sm font-medium text-zinc-800 outline-none ring-molten/30 transition-all focus:border-molten focus:bg-white focus:ring-4">
                            <option value="" <?= (($data['gender'] ?? '') === '') ? 'selected' : '' ?> disabled>Select gender</option>
                            <option value="male" <?= (($data['gender'] ?? '') === 'male') ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= (($data['gender'] ?? '') === 'female') ? 'selected' : '' ?>>Female</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 flex items-center gap-2 text-sm font-bold text-zinc-700" for="age">
                            <svg class="h-4 w-4 text-orange-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Age
                        </label>
                        <input id="age" name="age" type="number" min="10" max="100" required value="<?= h((string) ($data['age'] ?? '')) ?>" placeholder="25" class="w-full rounded-xl border border-orange-200 bg-orange-50/30 px-4 py-3 text-sm font-medium text-zinc-800 placeholder:text-zinc-400 outline-none ring-molten/30 transition-all focus:border-molten focus:bg-white focus:ring-4" />
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 flex items-center gap-2 text-sm font-bold text-zinc-700">
                        <svg class="h-4 w-4 text-orange-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                        Height
                    </label>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="relative">
                                <input name="height_ft" type="number" min="3" max="8" required value="<?= h((string) ($data['height_ft'] ?? '')) ?>" placeholder="5" class="w-full rounded-xl border border-orange-200 bg-orange-50/30 px-4 py-3 pr-12 text-sm font-medium text-zinc-800 placeholder:text-zinc-400 outline-none ring-molten/30 transition-all focus:border-molten focus:bg-white focus:ring-4" />
                                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 rounded-md bg-orange-100 px-2 py-0.5 text-xs font-bold text-orange-700">ft</span>
                            </div>
                        </div>
                        <div>
                            <div class="relative">
                                <input name="height_in" type="number" min="0" max="11" required value="<?= h((string) ($data['height_in'] ?? '')) ?>" placeholder="8" class="w-full rounded-xl border border-orange-200 bg-orange-50/30 px-4 py-3 pr-12 text-sm font-medium text-zinc-800 placeholder:text-zinc-400 outline-none ring-molten/30 transition-all focus:border-molten focus:bg-white focus:ring-4" />
                                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 rounded-md bg-orange-100 px-2 py-0.5 text-xs font-bold text-orange-700">in</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 flex items-center gap-2 text-sm font-bold text-zinc-700" for="start_weight_lbs">
                            <svg class="h-4 w-4 text-orange-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                            Starting Weight
                        </label>
                        <div class="relative">
                            <input id="start_weight_lbs" name="start_weight_lbs" type="number" min="50" max="500" step="0.1" required value="<?= h((string) ($data['start_weight_lbs'] ?? '')) ?>" placeholder="150" class="w-full rounded-xl border border-orange-200 bg-orange-50/30 px-4 py-3 pr-14 text-sm font-medium text-zinc-800 placeholder:text-zinc-400 outline-none ring-molten/30 transition-all focus:border-molten focus:bg-white focus:ring-4" />
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 rounded-md bg-orange-100 px-2 py-0.5 text-xs font-bold text-orange-700">lbs</span>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 flex items-center gap-2 text-sm font-bold text-zinc-700" for="waistline_in">
                            <svg class="h-4 w-4 text-orange-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Waistline
                        </label>
                        <div class="relative">
                            <input id="waistline_in" name="waistline_in" type="number" min="20" max="80" step="0.1" required value="<?= h((string) ($data['waistline_in'] ?? '')) ?>" placeholder="32" class="w-full rounded-xl border border-orange-200 bg-orange-50/30 px-4 py-3 pr-14 text-sm font-medium text-zinc-800 placeholder:text-zinc-400 outline-none ring-molten/30 transition-all focus:border-molten focus:bg-white focus:ring-4" />
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 rounded-md bg-orange-100 px-2 py-0.5 text-xs font-bold text-orange-700">in</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 pt-4 sm:flex-row sm:justify-between border-t border-orange-100">
                    <button type="submit" name="action" value="save_exit" class="inline-flex items-center justify-center gap-2 rounded-xl border border-orange-200 bg-white px-5 py-3 text-sm font-bold text-zinc-600 hover:bg-orange-50 hover:text-zinc-800 transition-all">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        Save & Finish Later
                    </button>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-molten to-orange-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-orange-200/50 hover:from-pumpkin hover:to-amber-500 transition-all">
                        Next Step
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($step === 2): ?>
        <div class="rounded-2xl border border-orange-100 bg-white p-6 shadow-sm">
            <div class="flex items-center gap-3 mb-1">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 text-white">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-extrabold tracking-tight text-zinc-900">Photo Upload</h2>
                    <p class="text-sm text-zinc-500">Front and side photos are required. Follow the example positioning and use good lighting.</p>
                </div>
            </div>

            <form method="post" enctype="multipart/form-data" class="mt-6 space-y-6" novalidate>
                <?= csrf_field() ?>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <!-- Front Photo Card -->
                    <div class="rounded-2xl border-2 border-dashed border-orange-200 bg-gradient-to-b from-orange-50/50 to-white p-5 hover:border-molten/50 transition-colors">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                                <span class="text-sm font-bold text-zinc-800">Front Photo</span>
                            </div>
                            <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-1 text-xs font-bold text-red-700">
                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/></svg>
                                Required
                            </span>
                        </div>
                        <div class="overflow-hidden rounded-xl border border-orange-100 bg-white shadow-inner">
                            <div class="aspect-[3/4] w-full bg-gradient-to-b from-orange-50/50 to-white flex items-center justify-center">
                                <?php if (!empty($data['front_photo_path']) && is_file((string) $data['front_photo_path'])): ?>
                                    <img id="front_preview" src="<?= h(url('/media/pre_registration_photo.php?key=front')) ?>" alt="Front upload" class="h-full w-full object-cover" />
                                <?php else: ?>
                                    <img id="front_preview" src="<?= h(url('/media/pre_registration_reference.php?key=front')) ?>" alt="Front example" class="h-full w-full object-contain bg-white" />
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="flex items-center justify-center gap-2 rounded-xl border border-orange-200 bg-gradient-to-r from-orange-50 to-amber-50 px-4 py-3 text-sm font-bold text-zinc-700 cursor-pointer hover:from-orange-100 hover:to-amber-100 transition-all">
                                <svg class="h-5 w-5 text-molten" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Choose Photo
                                <input name="front_photo" type="file" accept="image/png,image/jpeg" class="sr-only" />
                            </label>
                            <p class="mt-2 text-center text-xs text-zinc-500">JPG/PNG up to 10MB</p>
                        </div>
                    </div>

                    <!-- Side Photo Card -->
                    <div class="rounded-2xl border-2 border-dashed border-orange-200 bg-gradient-to-b from-orange-50/50 to-white p-5 hover:border-molten/50 transition-colors">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-100 text-purple-600">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <span class="text-sm font-bold text-zinc-800">Side Photo</span>
                            </div>
                            <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-1 text-xs font-bold text-red-700">
                                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/></svg>
                                Required
                            </span>
                        </div>
                        <div class="overflow-hidden rounded-xl border border-orange-100 bg-white shadow-inner">
                            <div class="aspect-[3/4] w-full bg-gradient-to-b from-orange-50/50 to-white flex items-center justify-center">
                                <?php if (!empty($data['side_photo_path']) && is_file((string) $data['side_photo_path'])): ?>
                                    <img id="side_preview" src="<?= h(url('/media/pre_registration_photo.php?key=side')) ?>" alt="Side upload" class="h-full w-full object-cover" />
                                <?php else: ?>
                                    <img id="side_preview" src="<?= h(url('/media/pre_registration_reference.php?key=side')) ?>" alt="Side example" class="h-full w-full object-contain bg-white" />
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="flex items-center justify-center gap-2 rounded-xl border border-orange-200 bg-gradient-to-r from-orange-50 to-amber-50 px-4 py-3 text-sm font-bold text-zinc-700 cursor-pointer hover:from-orange-100 hover:to-amber-100 transition-all">
                                <svg class="h-5 w-5 text-molten" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Choose Photo
                                <input name="side_photo" type="file" accept="image/png,image/jpeg" class="sr-only" />
                            </label>
                            <p class="mt-2 text-center text-xs text-zinc-500">JPG/PNG up to 10MB</p>
                        </div>
                    </div>
                </div>

                <script>
                    (function () {
                        var frontInput = document.querySelector('input[name="front_photo"]');
                        var sideInput = document.querySelector('input[name="side_photo"]');
                        var frontImg = document.getElementById('front_preview');
                        var sideImg = document.getElementById('side_preview');

                        function bindPreview(input, img) {
                            if (!input || !img) return;
                            input.addEventListener('change', function () {
                                if (!input.files || !input.files[0]) return;
                                var file = input.files[0];
                                var url = URL.createObjectURL(file);
                                img.src = url;
                                img.classList.remove('object-cover');
                                img.classList.add('object-contain');
                                img.classList.add('bg-white');
                            });
                        }

                        bindPreview(frontInput, frontImg);
                        bindPreview(sideInput, sideImg);
                    })();
                </script>

                <div class="flex flex-col-reverse gap-3 pt-4 sm:flex-row sm:justify-between border-t border-orange-100">
                    <a href="<?= h(url('/coach/pre_registration.php?step=1')) ?>" class="inline-flex items-center justify-center gap-2 rounded-xl border border-orange-200 bg-white px-5 py-3 text-sm font-bold text-zinc-600 hover:bg-orange-50 hover:text-zinc-800 transition-all">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
                        Back
                    </a>
                    <div class="flex flex-col-reverse gap-3 sm:flex-row">
                        <button type="submit" name="action" value="save_exit" class="inline-flex items-center justify-center gap-2 rounded-xl border border-orange-200 bg-white px-5 py-3 text-sm font-bold text-zinc-600 hover:bg-orange-50 hover:text-zinc-800 transition-all">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                            Save & Finish Later
                        </button>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-molten to-orange-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-orange-200/50 hover:from-pumpkin hover:to-amber-500 transition-all">
                            Continue
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($step === 3): ?>
        <?php
            $selected = (string) ($data['bmi_category'] ?? '');
            $bmiValue = (string) ($data['bmi'] ?? '');
        ?>
        <div class="rounded-2xl border border-orange-100 bg-white p-6 shadow-sm">
            <div class="flex items-center gap-3 mb-1">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-extrabold tracking-tight text-zinc-900">BMI & Consent</h2>
                    <p class="text-sm text-zinc-500">Coach manually calculates BMI, enters the value, selects the appropriate category, and records consent.</p>
                </div>
            </div>

            <form method="post" class="mt-6 space-y-5" novalidate>
                <?= csrf_field() ?>

                <div>
                    <label class="mb-1.5 flex items-center gap-2 text-sm font-bold text-zinc-700" for="bmi">
                        <svg class="h-4 w-4 text-orange-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        BMI (Coach-entered)
                    </label>
                    <input id="bmi" name="bmi" type="number" min="5" max="100" step="0.01" required value="<?= h($bmiValue) ?>" placeholder="e.g., 24.50" class="w-full rounded-xl border border-orange-200 bg-orange-50/30 px-4 py-3 text-sm font-medium text-zinc-800 placeholder:text-zinc-400 outline-none ring-molten/30 transition-all focus:border-molten focus:bg-white focus:ring-4" />
                    <p class="mt-1 text-xs text-zinc-600">Calculate BMI manually based on the clients height and weight, then enter the value here.</p>
                </div>

                <div>
                    <label class="mb-1.5 flex items-center gap-2 text-sm font-bold text-zinc-700" for="bmi_category">
                        <svg class="h-4 w-4 text-orange-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        BMI Category
                    </label>
                    <select id="bmi_category" name="bmi_category" required class="w-full rounded-xl border border-orange-200 bg-orange-50/30 px-4 py-3 text-sm font-medium text-zinc-800 outline-none ring-molten/30 transition-all focus:border-molten focus:bg-white focus:ring-4">
                        <option value="" disabled <?= $selected === '' ? 'selected' : '' ?>>Select category</option>
                        <?php foreach (bmi_category_options() as $opt): ?>
                            <option value="<?= h($opt) ?>" <?= $selected === $opt ? 'selected' : '' ?>><?= h($opt) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Consent Card -->
                <div class="rounded-2xl border-2 border-orange-200 bg-gradient-to-r from-orange-50 via-white to-amber-50 p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-100 text-green-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <span class="text-sm font-bold text-zinc-800">Privacy Consent</span>
                    </div>
                    <label class="flex cursor-pointer gap-3 group">
                        <input type="checkbox" name="consent" value="yes" class="mt-1 h-5 w-5 rounded-md border-2 border-orange-300 text-molten accent-molten focus:ring-molten focus:ring-offset-2" <?= !empty($data['consent_confirmed']) ? 'checked' : '' ?> required>
                        <span class="text-sm text-zinc-600 leading-relaxed group-hover:text-zinc-800 transition-colors">
                            I confirm that the client has provided consent for the collection and processing of their personal and health-related data in accordance with the Privacy Policy.
                        </span>
                    </label>
                </div>

                <div class="flex flex-col-reverse gap-3 pt-4 sm:flex-row sm:justify-between border-t border-orange-100">
                    <a href="<?= h(url('/coach/pre_registration.php?step=2')) ?>" class="inline-flex items-center justify-center gap-2 rounded-xl border border-orange-200 bg-white px-5 py-3 text-sm font-bold text-zinc-600 hover:bg-orange-50 hover:text-zinc-800 transition-all">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
                        Back
                    </a>
                    <div class="flex flex-col-reverse gap-3 sm:flex-row">
                        <button type="submit" name="action" value="save_exit" class="inline-flex items-center justify-center gap-2 rounded-xl border border-orange-200 bg-white px-5 py-3 text-sm font-bold text-zinc-600 hover:bg-orange-50 hover:text-zinc-800 transition-all">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                            Save & Finish Later
                        </button>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-molten to-orange-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-orange-200/50 hover:from-pumpkin hover:to-amber-500 transition-all">
                            Continue
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($step === 4): ?>
        <div class="rounded-2xl border border-orange-100 bg-white p-6 shadow-sm">
            <div class="flex items-center gap-3 mb-1">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 text-white">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-extrabold tracking-tight text-zinc-900">Review & Complete</h2>
                    <p class="text-sm text-zinc-500">Confirm details before saving this client into the system.</p>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                <!-- Basic Info Card -->
                <div class="rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-50 to-indigo-50 p-4">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-500 text-white">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <span class="text-sm font-bold text-zinc-800">Basic Info</span>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between items-center py-1 border-b border-blue-100/50"><span class="text-zinc-500">Full name</span><span class="font-semibold text-zinc-800"><?= h((string) ($data['full_name'] ?? '-')) ?></span></div>
                        <div class="flex justify-between items-center py-1 border-b border-blue-100/50"><span class="text-zinc-500">Gender</span><span class="font-semibold text-zinc-800 capitalize"><?= h((string) ($data['gender'] ?? '-')) ?></span></div>
                        <div class="flex justify-between items-center py-1 border-b border-blue-100/50"><span class="text-zinc-500">Age</span><span class="font-semibold text-zinc-800"><?= h((string) ($data['age'] ?? '-')) ?> yrs</span></div>
                        <div class="flex justify-between items-center py-1 border-b border-blue-100/50"><span class="text-zinc-500">Height</span><span class="font-semibold text-zinc-800"><?= h((string) ($data['height_ft'] ?? '-')) ?>'<?= h((string) ($data['height_in'] ?? '-')) ?>"</span></div>
                        <div class="flex justify-between items-center py-1 border-b border-blue-100/50"><span class="text-zinc-500">Weight</span><span class="font-semibold text-zinc-800"><?= h((string) ($data['start_weight_lbs'] ?? '-')) ?> lbs</span></div>
                        <div class="flex justify-between items-center py-1"><span class="text-zinc-500">Waistline</span><span class="font-semibold text-zinc-800"><?= h((string) ($data['waistline_in'] ?? '-')) ?> in</span></div>
                    </div>
                </div>

                <!-- BMI Card -->
                <div class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-teal-50 p-4">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500 text-white">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                        <span class="text-sm font-bold text-zinc-800">BMI & Consent</span>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between items-center py-1 border-b border-emerald-100/50"><span class="text-zinc-500">BMI</span><span class="font-bold text-lg text-emerald-600"><?= h((string) ($data['bmi'] ?? '-')) ?></span></div>
                        <div class="flex justify-between items-center py-1 border-b border-emerald-100/50"><span class="text-zinc-500">Category</span><span class="font-semibold text-zinc-800"><?= h((string) ($data['bmi_category'] ?? '-')) ?></span></div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-zinc-500">Consent</span>
                            <?php if (!empty($data['consent_confirmed'])): ?>
                                <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-1 text-xs font-bold text-green-700">
                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    Confirmed
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-1 text-xs font-bold text-red-700">Missing</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Front Photo Card -->
                <div class="rounded-2xl border border-purple-100 bg-gradient-to-br from-purple-50 to-pink-50 p-4">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-purple-500 text-white">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <span class="text-sm font-bold text-zinc-800">Front Photo</span>
                    </div>
                    <div class="overflow-hidden rounded-xl border border-purple-100 bg-white shadow-inner">
                        <div class="aspect-[3/4] w-full">
                            <?php if (!empty($data['front_photo_path']) && is_file((string) $data['front_photo_path'])): ?>
                                <img src="<?= h(url('/media/pre_registration_photo.php?key=front')) ?>" alt="Front" class="h-full w-full object-cover" />
                            <?php else: ?>
                                <div class="flex h-full w-full flex-col items-center justify-center text-zinc-400">
                                    <svg class="h-12 w-12 mb-2" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span class="text-sm">Missing</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Side Photo Card -->
                <div class="rounded-2xl border border-amber-100 bg-gradient-to-br from-amber-50 to-orange-50 p-4">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-500 text-white">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span class="text-sm font-bold text-zinc-800">Side Photo</span>
                    </div>
                    <div class="overflow-hidden rounded-xl border border-amber-100 bg-white shadow-inner">
                        <div class="aspect-[3/4] w-full">
                            <?php if (!empty($data['side_photo_path']) && is_file((string) $data['side_photo_path'])): ?>
                                <img src="<?= h(url('/media/pre_registration_photo.php?key=side')) ?>" alt="Side" class="h-full w-full object-cover" />
                            <?php else: ?>
                                <div class="flex h-full w-full flex-col items-center justify-center text-zinc-400">
                                    <svg class="h-12 w-12 mb-2" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span class="text-sm">Missing</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <form method="post" class="mt-6 flex flex-col-reverse gap-3 pt-4 border-t border-orange-100 sm:flex-row sm:justify-between">
                <?= csrf_field() ?>
                <button type="submit" name="action" value="restart" class="inline-flex items-center justify-center gap-2 rounded-xl border border-red-200 bg-white px-5 py-3 text-sm font-bold text-red-600 hover:bg-red-50 transition-all">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Restart
                </button>
                <div class="flex flex-col-reverse gap-3 sm:flex-row">
                    <a href="<?= h(url('/coach/pre_registration.php?step=3')) ?>" class="inline-flex items-center justify-center gap-2 rounded-xl border border-orange-200 bg-white px-5 py-3 text-sm font-bold text-zinc-600 hover:bg-orange-50 hover:text-zinc-800 transition-all">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
                        Back
                    </a>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-green-200/50 hover:from-green-600 hover:to-emerald-700 transition-all">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Complete Registration
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</main>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>
