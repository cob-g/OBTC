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

            $bmi = bmi_from_imperial(
                $_SESSION['pre_registration']['start_weight_lbs'] ?? 0,
                $_SESSION['pre_registration']['height_ft'] ?? 0,
                $_SESSION['pre_registration']['height_in'] ?? 0
            );

            if ($bmi === null) {
                $errors[] = 'BMI could not be calculated. Please check height and weight.';
            }

            if ($category === '') {
                $errors[] = 'BMI category is required.';
            } elseif (!in_array($category, bmi_category_options(), true)) {
                $errors[] = 'Invalid BMI category.';
            }

            if ($consent !== 'yes') {
                $errors[] = 'Consent confirmation is required.';
            }

            if (!$errors) {
                $_SESSION['pre_registration']['bmi'] = number_format((float) $bmi, 2, '.', '');
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
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold tracking-tight">Pre-Registration</h1>
        <p class="mt-1 text-sm text-zinc-600">Step <?= h((string) $step) ?> of 4</p>
    </div>

    <div class="mb-6">
        <div class="flex items-center justify-between text-sm">
            <span class="font-semibold text-molten"><?= h('Step ' . $step . ' of 4') ?></span>
            <span class="text-zinc-600"><?= h((string) pre_reg_progress_percent($step)) ?>% Completed</span>
        </div>
        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-orange-100">
            <div class="h-full bg-molten" style="width: <?= h((string) pre_reg_progress_percent($step)) ?>%"></div>
        </div>
    </div>

    <?php if ($errors): ?>
        <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
            <div class="font-extrabold">Please fix the following:</div>
            <ul class="mt-2 list-disc pl-5">
                <?php foreach ($errors as $e): ?>
                    <li><?= h($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($step === 1): ?>
        <div class="rounded-2xl border border-orange-100 bg-white p-6">
            <h2 class="text-xl font-extrabold tracking-tight">Basic Information</h2>
            <p class="mt-1 text-sm text-zinc-600">Coach-only data entry. Height in feet/inches, weight in pounds.</p>

            <form method="post" class="mt-6 space-y-4" novalidate>
                <?= csrf_field() ?>

                <div>
                    <label class="mb-1 block text-sm font-semibold" for="full_name">Full Name</label>
                    <input id="full_name" name="full_name" type="text" required value="<?= h((string) ($data['full_name'] ?? '')) ?>" placeholder="e.g., Juan Dela Cruz" class="w-full rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm outline-none ring-molten/20 focus:border-molten focus:ring-4" />
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-semibold" for="gender">Gender</label>
                        <select id="gender" name="gender" required class="w-full rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm outline-none ring-molten/20 focus:border-molten focus:ring-4">
                            <option value="" <?= (($data['gender'] ?? '') === '') ? 'selected' : '' ?> disabled>Select gender</option>
                            <option value="male" <?= (($data['gender'] ?? '') === 'male') ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= (($data['gender'] ?? '') === 'female') ? 'selected' : '' ?>>Female</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold" for="age">Age</label>
                        <input id="age" name="age" type="number" min="10" max="100" required value="<?= h((string) ($data['age'] ?? '')) ?>" class="w-full rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm outline-none ring-molten/20 focus:border-molten focus:ring-4" />
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold">Height</label>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="relative">
                                <input name="height_ft" type="number" min="3" max="8" required value="<?= h((string) ($data['height_ft'] ?? '')) ?>" placeholder="5" class="w-full rounded-xl border border-orange-100 bg-white px-4 py-3 pr-10 text-sm outline-none ring-molten/20 focus:border-molten focus:ring-4" />
                                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-zinc-500">ft</span>
                            </div>
                        </div>
                        <div>
                            <div class="relative">
                                <input name="height_in" type="number" min="0" max="11" required value="<?= h((string) ($data['height_in'] ?? '')) ?>" placeholder="8" class="w-full rounded-xl border border-orange-100 bg-white px-4 py-3 pr-10 text-sm outline-none ring-molten/20 focus:border-molten focus:ring-4" />
                                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-zinc-500">in</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-semibold" for="start_weight_lbs">Starting Weight</label>
                        <div class="relative">
                            <input id="start_weight_lbs" name="start_weight_lbs" type="number" min="50" max="500" step="0.1" required value="<?= h((string) ($data['start_weight_lbs'] ?? '')) ?>" class="w-full rounded-xl border border-orange-100 bg-white px-4 py-3 pr-14 text-sm outline-none ring-molten/20 focus:border-molten focus:ring-4" />
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-zinc-500">lbs</span>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold" for="waistline_in">Waistline</label>
                        <div class="relative">
                            <input id="waistline_in" name="waistline_in" type="number" min="20" max="80" step="0.1" required value="<?= h((string) ($data['waistline_in'] ?? '')) ?>" class="w-full rounded-xl border border-orange-100 bg-white px-4 py-3 pr-14 text-sm outline-none ring-molten/20 focus:border-molten focus:ring-4" />
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-zinc-500">in</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-between">
                    <button type="submit" name="action" value="save_exit" class="rounded-xl px-4 py-3 text-sm font-extrabold text-zinc-700 hover:bg-orange-50">
                        Save & Finish Later
                    </button>
                    <button type="submit" class="rounded-xl bg-molten px-4 py-3 text-sm font-extrabold text-white hover:bg-pumpkin">
                        Next Step
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($step === 2): ?>
        <div class="rounded-2xl border border-orange-100 bg-white p-6">
            <h2 class="text-xl font-extrabold tracking-tight">Photo Upload</h2>
            <p class="mt-1 text-sm text-zinc-600">Front and side photos are required. Follow the example positioning and use good lighting.</p>

            <form method="post" enctype="multipart/form-data" class="mt-6 space-y-6" novalidate>
                <?= csrf_field() ?>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="rounded-2xl border border-orange-100 bg-orange-50 p-4">
                        <div class="flex items-center justify-between">
                            <div class="text-sm font-extrabold">Front Photo</div>
                            <span class="rounded-lg bg-white px-2 py-1 text-xs font-extrabold text-zinc-700">Required</span>
                        </div>
                        <div class="mt-3 overflow-hidden rounded-xl bg-white">
                            <div class="aspect-[3/4] w-full bg-gradient-to-b from-orange-50 to-white flex items-center justify-center">
                                <?php if (!empty($data['front_photo_path']) && is_file((string) $data['front_photo_path'])): ?>
                                    <img src="<?= h(url('/media/pre_registration_photo.php?key=front')) ?>" alt="Front upload" class="h-full w-full object-cover" />
                                <?php else: ?>
                                    <img src="<?= h(url('/media/pre_registration_reference.php?key=front')) ?>" alt="Front example" class="h-full w-full object-contain bg-white" />
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="mt-3">
                            <input name="front_photo" type="file" accept="image/png,image/jpeg" class="block w-full text-sm" />
                            <div class="mt-1 text-xs text-zinc-600">JPG/PNG up to 10MB</div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-orange-100 bg-orange-50 p-4">
                        <div class="flex items-center justify-between">
                            <div class="text-sm font-extrabold">Side Photo</div>
                            <span class="rounded-lg bg-white px-2 py-1 text-xs font-extrabold text-zinc-700">Required</span>
                        </div>
                        <div class="mt-3 overflow-hidden rounded-xl bg-white">
                            <div class="aspect-[3/4] w-full bg-gradient-to-b from-orange-50 to-white flex items-center justify-center">
                                <?php if (!empty($data['side_photo_path']) && is_file((string) $data['side_photo_path'])): ?>
                                    <img src="<?= h(url('/media/pre_registration_photo.php?key=side')) ?>" alt="Side upload" class="h-full w-full object-cover" />
                                <?php else: ?>
                                    <img src="<?= h(url('/media/pre_registration_reference.php?key=side')) ?>" alt="Side example" class="h-full w-full object-contain bg-white" />
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="mt-3">
                            <input name="side_photo" type="file" accept="image/png,image/jpeg" class="block w-full text-sm" />
                            <div class="mt-1 text-xs text-zinc-600">JPG/PNG up to 10MB</div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-between">
                    <a href="<?= h(url('/coach/pre_registration.php?step=1')) ?>" class="inline-flex items-center justify-center rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm font-extrabold text-zinc-700 hover:bg-orange-50">
                        Back
                    </a>
                    <div class="flex flex-col-reverse gap-3 sm:flex-row">
                        <button type="submit" name="action" value="save_exit" class="rounded-xl px-4 py-3 text-sm font-extrabold text-zinc-700 hover:bg-orange-50">
                            Save & Finish Later
                        </button>
                        <button type="submit" class="rounded-xl bg-molten px-4 py-3 text-sm font-extrabold text-white hover:bg-pumpkin">
                            Continue
                        </button>
                    </div>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($step === 3): ?>
        <?php
            $bmi = bmi_from_imperial(
                $data['start_weight_lbs'] ?? 0,
                $data['height_ft'] ?? 0,
                $data['height_in'] ?? 0
            );
            $suggested = bmi_category_suggest($bmi);
            $selected = (string) ($data['bmi_category'] ?? ($suggested ?? ''));
        ?>
        <div class="rounded-2xl border border-orange-100 bg-white p-6">
            <h2 class="text-xl font-extrabold tracking-tight">BMI & Consent</h2>
            <p class="mt-1 text-sm text-zinc-600">BMI is calculated by the system. Coach confirms the category and records consent.</p>

            <div class="mt-5 rounded-2xl border border-orange-100 bg-orange-50 p-4">
                <div class="text-sm font-semibold text-zinc-700">Calculated BMI</div>
                <div class="mt-1 text-3xl font-extrabold text-molten">
                    <?= $bmi === null ? '-' : h(number_format((float) $bmi, 2, '.', '')) ?>
                </div>
                <?php if ($suggested): ?>
                    <div class="mt-1 text-sm text-zinc-600">Suggested category: <span class="font-extrabold text-zinc-800"><?= h($suggested) ?></span></div>
                <?php endif; ?>
            </div>

            <form method="post" class="mt-6 space-y-5" novalidate>
                <?= csrf_field() ?>

                <div>
                    <label class="mb-1 block text-sm font-semibold" for="bmi_category">BMI Category (Coach)</label>
                    <select id="bmi_category" name="bmi_category" required class="w-full rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm outline-none ring-molten/20 focus:border-molten focus:ring-4">
                        <option value="" disabled <?= $selected === '' ? 'selected' : '' ?>>Select category</option>
                        <?php foreach (bmi_category_options() as $opt): ?>
                            <option value="<?= h($opt) ?>" <?= $selected === $opt ? 'selected' : '' ?>><?= h($opt) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="rounded-2xl border border-orange-100 bg-white p-4">
                    <label class="flex cursor-pointer gap-3">
                        <input type="checkbox" name="consent" value="yes" class="mt-1 h-5 w-5 accent-molten" <?= !empty($data['consent_confirmed']) ? 'checked' : '' ?> required>
                        <span class="text-sm text-zinc-700">
                            I confirm that the client has provided consent for the collection and processing of their personal and health-related data in accordance with the Privacy Policy.
                        </span>
                    </label>
                </div>

                <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-between">
                    <a href="<?= h(url('/coach/pre_registration.php?step=2')) ?>" class="inline-flex items-center justify-center rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm font-extrabold text-zinc-700 hover:bg-orange-50">
                        Back
                    </a>
                    <div class="flex flex-col-reverse gap-3 sm:flex-row">
                        <button type="submit" name="action" value="save_exit" class="rounded-xl px-4 py-3 text-sm font-extrabold text-zinc-700 hover:bg-orange-50">
                            Save & Finish Later
                        </button>
                        <button type="submit" class="rounded-xl bg-molten px-4 py-3 text-sm font-extrabold text-white hover:bg-pumpkin">
                            Continue
                        </button>
                    </div>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($step === 4): ?>
        <div class="rounded-2xl border border-orange-100 bg-white p-6">
            <h2 class="text-xl font-extrabold tracking-tight">Review & Complete</h2>
            <p class="mt-1 text-sm text-zinc-600">Confirm details before saving this client into the system.</p>

            <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="rounded-2xl border border-orange-100 bg-orange-50 p-4">
                    <div class="text-sm font-extrabold">Basic Info</div>
                    <div class="mt-2 space-y-1 text-sm text-zinc-700">
                        <div><span class="font-semibold">Full name:</span> <?= h((string) ($data['full_name'] ?? '-')) ?></div>
                        <div><span class="font-semibold">Gender:</span> <?= h((string) ($data['gender'] ?? '-')) ?></div>
                        <div><span class="font-semibold">Age:</span> <?= h((string) ($data['age'] ?? '-')) ?></div>
                        <div><span class="font-semibold">Height:</span> <?= h((string) ($data['height_ft'] ?? '-')) ?>ft <?= h((string) ($data['height_in'] ?? '-')) ?>in</div>
                        <div><span class="font-semibold">Weight:</span> <?= h((string) ($data['start_weight_lbs'] ?? '-')) ?> lbs</div>
                        <div><span class="font-semibold">Waistline:</span> <?= h((string) ($data['waistline_in'] ?? '-')) ?> in</div>
                    </div>
                </div>

                <div class="rounded-2xl border border-orange-100 bg-orange-50 p-4">
                    <div class="text-sm font-extrabold">BMI</div>
                    <div class="mt-2 space-y-1 text-sm text-zinc-700">
                        <div><span class="font-semibold">BMI:</span> <?= h((string) ($data['bmi'] ?? '-')) ?></div>
                        <div><span class="font-semibold">Category:</span> <?= h((string) ($data['bmi_category'] ?? '-')) ?></div>
                        <div><span class="font-semibold">Consent:</span> <?= !empty($data['consent_confirmed']) ? 'Confirmed' : 'Missing' ?></div>
                    </div>
                </div>

                <div class="rounded-2xl border border-orange-100 bg-orange-50 p-4">
                    <div class="text-sm font-extrabold">Front Photo</div>
                    <div class="mt-3 overflow-hidden rounded-xl bg-white">
                        <div class="aspect-[3/4] w-full">
                            <?php if (!empty($data['front_photo_path']) && is_file((string) $data['front_photo_path'])): ?>
                                <img src="<?= h(url('/media/pre_registration_photo.php?key=front')) ?>" alt="Front" class="h-full w-full object-cover" />
                            <?php else: ?>
                                <div class="flex h-full w-full items-center justify-center text-sm text-zinc-600">Missing</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-orange-100 bg-orange-50 p-4">
                    <div class="text-sm font-extrabold">Side Photo</div>
                    <div class="mt-3 overflow-hidden rounded-xl bg-white">
                        <div class="aspect-[3/4] w-full">
                            <?php if (!empty($data['side_photo_path']) && is_file((string) $data['side_photo_path'])): ?>
                                <img src="<?= h(url('/media/pre_registration_photo.php?key=side')) ?>" alt="Side" class="h-full w-full object-cover" />
                            <?php else: ?>
                                <div class="flex h-full w-full items-center justify-center text-sm text-zinc-600">Missing</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <form method="post" class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between">
                <?= csrf_field() ?>
                <button type="submit" name="action" value="restart" class="rounded-xl px-4 py-3 text-sm font-extrabold text-zinc-700 hover:bg-orange-50">
                    Restart
                </button>
                <div class="flex flex-col-reverse gap-3 sm:flex-row">
                    <a href="<?= h(url('/coach/pre_registration.php?step=3')) ?>" class="inline-flex items-center justify-center rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm font-extrabold text-zinc-700 hover:bg-orange-50">
                        Back
                    </a>
                    <button type="submit" class="rounded-xl bg-molten px-4 py-3 text-sm font-extrabold text-white hover:bg-pumpkin">
                        Complete Registration
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</main>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>
