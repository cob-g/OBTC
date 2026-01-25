<?php
require __DIR__ . '/../app/bootstrap.php';
require_role('admin');

$errors = [];
$success = null;

if (isset($_SESSION['flash_success'])) {
    $success = (string) $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}

function ensure_challenge_history_tables()
{
    db()->exec(
        'CREATE TABLE IF NOT EXISTS challenge_cycles ('
        . 'id INT UNSIGNED NOT NULL AUTO_INCREMENT,'
        . 'label VARCHAR(64) NULL,'
        . 'started_at DATETIME NOT NULL,'
        . 'ended_at DATETIME NOT NULL,'
        . 'created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,'
        . 'PRIMARY KEY (id)'
        . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    try {
        if (!db_has_column('challenge_cycles', 'label')) {
            db()->exec('ALTER TABLE challenge_cycles ADD COLUMN label VARCHAR(64) NULL AFTER id');
        }
    } catch (Throwable $e) {
        // ignore
    }

    db()->exec(
        'CREATE TABLE IF NOT EXISTS challenge_clients ('
        . 'id INT UNSIGNED NOT NULL AUTO_INCREMENT,'
        . 'cycle_id INT UNSIGNED NOT NULL,'
        . 'client_id INT UNSIGNED NOT NULL,'
        . 'coach_user_id INT UNSIGNED NOT NULL,'
        . 'full_name VARCHAR(150) NULL,'
        . 'gender VARCHAR(16) NULL,'
        . 'age TINYINT UNSIGNED NULL,'
        . 'height_ft TINYINT UNSIGNED NULL,'
        . 'height_in TINYINT UNSIGNED NULL,'
        . 'start_weight_lbs DECIMAL(6,2) NULL,'
        . 'waistline_in DECIMAL(6,2) NULL,'
        . 'bmi DECIMAL(6,2) NULL,'
        . 'bmi_category VARCHAR(32) NULL,'
        . 'front_photo_path VARCHAR(255) NULL,'
        . 'side_photo_path VARCHAR(255) NULL,'
        . 'day10_front_photo_path VARCHAR(255) NULL,'
        . 'day10_side_photo_path VARCHAR(255) NULL,'
        . 'challenge_start_date DATE NULL,'
        . 'registered_at DATETIME NULL,'
        . 'created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,'
        . 'PRIMARY KEY (id),'
        . 'KEY idx_challenge_clients_cycle (cycle_id),'
        . 'KEY idx_challenge_clients_client (client_id)'
        . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    db()->exec(
        'CREATE TABLE IF NOT EXISTS challenge_checkins ('
        . 'id INT UNSIGNED NOT NULL AUTO_INCREMENT,'
        . 'cycle_id INT UNSIGNED NOT NULL,'
        . 'client_id INT UNSIGNED NOT NULL,'
        . 'coach_user_id INT UNSIGNED NOT NULL,'
        . 'day_number TINYINT UNSIGNED NOT NULL,'
        . 'weight_lbs DECIMAL(6,2) NOT NULL,'
        . 'recorded_at DATETIME NOT NULL,'
        . 'created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,'
        . 'PRIMARY KEY (id),'
        . 'KEY idx_challenge_checkins_cycle (cycle_id),'
        . 'KEY idx_challenge_checkins_client (client_id)'
        . ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );
}

function next_monday_date()
{
    $now = new DateTimeImmutable('now');
    $dow = (int) $now->format('N');
    $daysAhead = 8 - $dow;
    if ($daysAhead <= 0) {
        $daysAhead = 7;
    }
    return $now->modify('+' . $daysAhead . ' days')->format('Y-m-d');
}

function default_cycle_label($endedAt)
{
    try {
        $dt = new DateTimeImmutable((string) $endedAt);
    } catch (Throwable $e) {
        $dt = new DateTimeImmutable('now');
    }
    return $dt->format('M Y') . ' Challenge';
}

function csv_string_from_rows($rows)
{
    $out = fopen('php://temp', 'w+');
    $first = true;
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        if ($first) {
            fputcsv($out, array_keys($row));
            $first = false;
        }
        fputcsv($out, array_values($row));
    }
    rewind($out);
    $csv = stream_get_contents($out);
    fclose($out);
    return $csv === false ? '' : $csv;
}

$stats = [
    'clients' => 0,
    'checkins' => 0,
    'consent_logs' => 0,
];

try {
    $stats['clients'] = (int) (db()->query('SELECT COUNT(*) AS c FROM clients')->fetch()['c'] ?? 0);
    $stats['checkins'] = (int) (db()->query('SELECT COUNT(*) AS c FROM client_checkins')->fetch()['c'] ?? 0);
    $stats['consent_logs'] = (int) (db()->query('SELECT COUNT(*) AS c FROM consent_logs')->fetch()['c'] ?? 0);
} catch (Throwable $e) {
}

if (isset($_GET['export'])) {
    $export = (string) $_GET['export'];
    $allowed = ['clients_csv', 'checkins_csv', 'consent_logs_csv'];
    if (in_array($export, $allowed, true)) {
        try {
            $timestamp = date('Ymd_His');
            if ($export === 'clients_csv') {
                $hasFullName = db_has_column('clients', 'full_name');
                $hasChallengeStart = db_has_column('clients', 'challenge_start_date');
                $hasDay10Front = db_has_column('clients', 'day10_front_photo_path');
                $hasDay10Side = db_has_column('clients', 'day10_side_photo_path');

                $clientCols = ['c.id', 'c.coach_user_id'];
                if ($hasFullName) {
                    $clientCols[] = 'c.full_name';
                }
                $clientCols[] = 'c.gender';
                $clientCols[] = 'c.age';
                $clientCols[] = 'c.height_ft';
                $clientCols[] = 'c.height_in';
                $clientCols[] = 'c.start_weight_lbs';
                $clientCols[] = 'c.waistline_in';
                $clientCols[] = 'c.bmi';
                $clientCols[] = 'c.bmi_category';
                $clientCols[] = 'c.front_photo_path';
                $clientCols[] = 'c.side_photo_path';
                if ($hasDay10Front) {
                    $clientCols[] = 'c.day10_front_photo_path';
                }
                if ($hasDay10Side) {
                    $clientCols[] = 'c.day10_side_photo_path';
                }
                if ($hasChallengeStart) {
                    $clientCols[] = 'c.challenge_start_date';
                }
                $clientCols[] = 'c.registered_at';
                $clientCols[] = 'c.created_at';
                $clientCols[] = 'c.updated_at';

                $rows = db()->query('SELECT ' . implode(', ', $clientCols) . ' FROM clients c ORDER BY c.id ASC')->fetchAll();
                $csv = csv_string_from_rows($rows);
                header('Content-Type: text/csv; charset=UTF-8');
                header('Content-Disposition: attachment; filename="clients_' . $timestamp . '.csv"');
                header('Cache-Control: no-store, max-age=0');
                echo $csv;
                exit;
            }

            if ($export === 'checkins_csv') {
                $rows = db()->query('SELECT id, client_id, coach_user_id, day_number, weight_lbs, recorded_at, created_at FROM client_checkins ORDER BY client_id ASC, day_number ASC')->fetchAll();
                $csv = csv_string_from_rows($rows);
                header('Content-Type: text/csv; charset=UTF-8');
                header('Content-Disposition: attachment; filename="client_checkins_' . $timestamp . '.csv"');
                header('Cache-Control: no-store, max-age=0');
                echo $csv;
                exit;
            }

            if ($export === 'consent_logs_csv') {
                $rows = db()->query('SELECT id, client_id, coach_user_id, consent_text, ip_address, user_agent, created_at FROM consent_logs ORDER BY id ASC')->fetchAll();
                $csv = csv_string_from_rows($rows);
                header('Content-Type: text/csv; charset=UTF-8');
                header('Content-Disposition: attachment; filename="consent_logs_' . $timestamp . '.csv"');
                header('Cache-Control: no-store, max-age=0');
                echo $csv;
                exit;
            }
        } catch (Throwable $e) {
            $errors[] = 'Failed to export CSV.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Invalid session. Please refresh and try again.';
    } else {
        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'download_backup_zip') {
            if (!class_exists('ZipArchive')) {
                $errors[] = 'ZIP export is not available on this PHP installation (ZipArchive missing). Use the CSV export buttons below or enable the PHP zip extension.';
            }

            if (!$errors) {
                try {
                    $hasFullName = db_has_column('clients', 'full_name');
                    $hasChallengeStart = db_has_column('clients', 'challenge_start_date');
                    $hasDay10Front = db_has_column('clients', 'day10_front_photo_path');
                    $hasDay10Side = db_has_column('clients', 'day10_side_photo_path');

                    $clientCols = ['c.id', 'c.coach_user_id'];
                    if ($hasFullName) {
                        $clientCols[] = 'c.full_name';
                    }
                    $clientCols[] = 'c.gender';
                    $clientCols[] = 'c.age';
                    $clientCols[] = 'c.height_ft';
                    $clientCols[] = 'c.height_in';
                    $clientCols[] = 'c.start_weight_lbs';
                    $clientCols[] = 'c.waistline_in';
                    $clientCols[] = 'c.bmi';
                    $clientCols[] = 'c.bmi_category';
                    $clientCols[] = 'c.front_photo_path';
                    $clientCols[] = 'c.side_photo_path';
                    if ($hasDay10Front) {
                        $clientCols[] = 'c.day10_front_photo_path';
                    }
                    if ($hasDay10Side) {
                        $clientCols[] = 'c.day10_side_photo_path';
                    }
                    if ($hasChallengeStart) {
                        $clientCols[] = 'c.challenge_start_date';
                    }
                    $clientCols[] = 'c.registered_at';
                    $clientCols[] = 'c.created_at';
                    $clientCols[] = 'c.updated_at';

                    $clients = db()->query('SELECT ' . implode(', ', $clientCols) . ' FROM clients c ORDER BY c.id ASC')->fetchAll();
                    $checkins = db()->query('SELECT id, client_id, coach_user_id, day_number, weight_lbs, recorded_at, created_at FROM client_checkins ORDER BY client_id ASC, day_number ASC')->fetchAll();
                    $consents = db()->query('SELECT id, client_id, coach_user_id, consent_text, ip_address, user_agent, created_at FROM consent_logs ORDER BY id ASC')->fetchAll();

                    $zipPath = tempnam(sys_get_temp_dir(), 'challenge_backup_');
                    if (!$zipPath) {
                        throw new RuntimeException('Failed to create temporary file.');
                    }
                    @unlink($zipPath);
                    $zipPath .= '.zip';

                    $zip = new ZipArchive();
                    if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
                        throw new RuntimeException('Failed to create ZIP archive.');
                    }

                    $timestamp = date('Ymd_His');
                    $zip->addFromString('clients_' . $timestamp . '.csv', csv_string_from_rows($clients));
                    $zip->addFromString('client_checkins_' . $timestamp . '.csv', csv_string_from_rows($checkins));
                    $zip->addFromString('consent_logs_' . $timestamp . '.csv', csv_string_from_rows($consents));
                    $zip->addFromString('meta_' . $timestamp . '.txt', "Generated: " . date('c') . "\nNext Monday: " . next_monday_date() . "\n");
                    $zip->close();

                    header('Content-Type: application/zip');
                    header('Content-Disposition: attachment; filename="challenge_backup_' . $timestamp . '.zip"');
                    header('Content-Length: ' . (string) filesize($zipPath));
                    header('Cache-Control: no-store, max-age=0');
                    readfile($zipPath);
                    @unlink($zipPath);
                    exit;
                } catch (Throwable $e) {
                    $errors[] = 'Failed to generate backup.';
                }
            }
        }

        if ($action === 'reset_challenge') {
            $confirm = trim((string) ($_POST['confirm_text'] ?? ''));
            if ($confirm !== 'RESET') {
                $errors[] = 'Type RESET to confirm.';
            }

            if (!$errors) {
                $hasChallengeStart = db_has_column('clients', 'challenge_start_date');
                $hasDay10Front = db_has_column('clients', 'day10_front_photo_path');
                $hasDay10Side = db_has_column('clients', 'day10_side_photo_path');
                $hasFullName = db_has_column('clients', 'full_name');

                try {
                    db()->beginTransaction();

                    ensure_challenge_history_tables();

                    $cycleStartedAt = (new DateTimeImmutable('now'))->modify('-10 days')->format('Y-m-d H:i:s');
                    $cycleEndedAt = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');
                    $cycleLabel = default_cycle_label($cycleEndedAt);

                    if (db_has_column('challenge_cycles', 'label')) {
                        $insCycle = db()->prepare('INSERT INTO challenge_cycles (label, started_at, ended_at) VALUES (?, ?, ?)');
                        $insCycle->execute([(string) $cycleLabel, (string) $cycleStartedAt, (string) $cycleEndedAt]);
                    } else {
                        $insCycle = db()->prepare('INSERT INTO challenge_cycles (started_at, ended_at) VALUES (?, ?)');
                        $insCycle->execute([(string) $cycleStartedAt, (string) $cycleEndedAt]);
                    }
                    $cycleId = (int) db()->lastInsertId();

                    $clientSelectCols = ['id AS client_id', 'coach_user_id'];
                    if ($hasFullName) {
                        $clientSelectCols[] = 'full_name';
                    } else {
                        $clientSelectCols[] = "'' AS full_name";
                    }
                    $clientSelectCols[] = 'gender';
                    $clientSelectCols[] = 'age';
                    $clientSelectCols[] = 'height_ft';
                    $clientSelectCols[] = 'height_in';
                    $clientSelectCols[] = 'start_weight_lbs';
                    $clientSelectCols[] = 'waistline_in';
                    $clientSelectCols[] = 'bmi';
                    $clientSelectCols[] = 'bmi_category';
                    $clientSelectCols[] = 'front_photo_path';
                    $clientSelectCols[] = 'side_photo_path';
                    if ($hasDay10Front) {
                        $clientSelectCols[] = 'day10_front_photo_path';
                    } else {
                        $clientSelectCols[] = 'NULL AS day10_front_photo_path';
                    }
                    if ($hasDay10Side) {
                        $clientSelectCols[] = 'day10_side_photo_path';
                    } else {
                        $clientSelectCols[] = 'NULL AS day10_side_photo_path';
                    }
                    if ($hasChallengeStart) {
                        $clientSelectCols[] = 'challenge_start_date';
                    } else {
                        $clientSelectCols[] = 'NULL AS challenge_start_date';
                    }
                    $clientSelectCols[] = 'registered_at';

                    $clientsToArchive = db()->query('SELECT ' . implode(', ', $clientSelectCols) . ' FROM clients ORDER BY id ASC')->fetchAll();
                    $insClient = db()->prepare('INSERT INTO challenge_clients (cycle_id, client_id, coach_user_id, full_name, gender, age, height_ft, height_in, start_weight_lbs, waistline_in, bmi, bmi_category, front_photo_path, side_photo_path, day10_front_photo_path, day10_side_photo_path, challenge_start_date, registered_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                    foreach ($clientsToArchive as $c) {
                        $insClient->execute([
                            $cycleId,
                            (int) $c['client_id'],
                            (int) $c['coach_user_id'],
                            (string) ($c['full_name'] ?? ''),
                            (string) ($c['gender'] ?? ''),
                            isset($c['age']) ? (int) $c['age'] : null,
                            isset($c['height_ft']) ? (int) $c['height_ft'] : null,
                            isset($c['height_in']) ? (int) $c['height_in'] : null,
                            isset($c['start_weight_lbs']) ? (float) $c['start_weight_lbs'] : null,
                            isset($c['waistline_in']) ? (float) $c['waistline_in'] : null,
                            isset($c['bmi']) ? (float) $c['bmi'] : null,
                            (string) ($c['bmi_category'] ?? ''),
                            (string) ($c['front_photo_path'] ?? ''),
                            (string) ($c['side_photo_path'] ?? ''),
                            $c['day10_front_photo_path'] ?? null,
                            $c['day10_side_photo_path'] ?? null,
                            $c['challenge_start_date'] ?? null,
                            $c['registered_at'] ?? null,
                        ]);
                    }

                    $checkinsToArchive = db()->query('SELECT client_id, coach_user_id, day_number, weight_lbs, recorded_at FROM client_checkins ORDER BY client_id ASC, day_number ASC')->fetchAll();
                    $insCheckin = db()->prepare('INSERT INTO challenge_checkins (cycle_id, client_id, coach_user_id, day_number, weight_lbs, recorded_at) VALUES (?, ?, ?, ?, ?, ?)');
                    foreach ($checkinsToArchive as $r) {
                        $insCheckin->execute([
                            $cycleId,
                            (int) $r['client_id'],
                            (int) $r['coach_user_id'],
                            (int) $r['day_number'],
                            number_format((float) $r['weight_lbs'], 2, '.', ''),
                            (string) $r['recorded_at'],
                        ]);
                    }

                    db()->exec('DELETE FROM client_checkins');

                    $setParts = [];
                    $params = [];

                    $setParts[] = 'start_weight_lbs = 0.00';
                    $setParts[] = 'waistline_in = 0.00';
                    $setParts[] = 'bmi = 0.00';
                    $setParts[] = "bmi_category = ''";
                    $setParts[] = "front_photo_path = ''";
                    $setParts[] = "side_photo_path = ''";

                    if ($hasDay10Front) {
                        $setParts[] = 'day10_front_photo_path = NULL';
                    }
                    if ($hasDay10Side) {
                        $setParts[] = 'day10_side_photo_path = NULL';
                    }
                    if ($hasChallengeStart) {
                        $setParts[] = 'challenge_start_date = ?';
                        $params[] = (string) next_monday_date();
                    }

                    if ($setParts) {
                        $stmt = db()->prepare('UPDATE clients SET ' . implode(', ', $setParts));
                        $stmt->execute($params);
                    }

                    if (db()->inTransaction()) {
                        db()->commit();
                    }
                    $_SESSION['flash_success'] = 'Archived current challenge to history and reset client challenge data. Check-ins cleared' . ($hasChallengeStart ? (' and challenge start set to next Monday (' . next_monday_date() . ').') : '.');
                    redirect(url('/admin/backup.php'));
                } catch (Throwable $e) {
                    if (db()->inTransaction()) {
                        db()->rollBack();
                    }
                    $errors[] = 'Failed to reset challenge data: ' . $e->getMessage();
                }
            }
        }
    }
}

$page_title = 'Data Backup';
require __DIR__ . '/../partials/layout_top.php';
require __DIR__ . '/../partials/nav.php';
?>

<main class="mx-auto max-w-6xl px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold tracking-tight">Data Backup & Reset</h1>
        <p class="mt-1 text-sm text-zinc-600">Backup completed challenge data and reset active challenge data to prepare for the next Monday cycle.</p>
    </div>

    <div class="rounded-2xl border border-orange-100 bg-white p-6">
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

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-orange-100 bg-white p-5">
                <div class="text-sm font-semibold text-zinc-600">Total Clients</div>
                <div class="mt-2 text-3xl font-extrabold text-molten"><?= h((string) $stats['clients']) ?></div>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white p-5">
                <div class="text-sm font-semibold text-zinc-600">Total Check-ins</div>
                <div class="mt-2 text-3xl font-extrabold text-molten"><?= h((string) $stats['checkins']) ?></div>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white p-5">
                <div class="text-sm font-semibold text-zinc-600">Consent Logs</div>
                <div class="mt-2 text-3xl font-extrabold text-molten"><?= h((string) $stats['consent_logs']) ?></div>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
            <div class="rounded-2xl border border-orange-100 bg-orange-50 p-6">
                <div class="text-lg font-extrabold tracking-tight">Backup (ZIP)</div>
                <div class="mt-1 text-sm text-zinc-700">Downloads a ZIP containing CSV exports for clients, check-ins, and consent logs.</div>
                <form method="post" class="mt-4" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="download_backup_zip" />
                    <button type="submit" class="w-full rounded-xl bg-molten px-4 py-3 text-sm font-extrabold text-white hover:bg-pumpkin sm:w-auto">Download Backup ZIP</button>
                </form>

                <div class="mt-4 text-sm font-extrabold text-zinc-800">CSV exports (works even if ZIP is unavailable)</div>
                <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-3">
                    <a href="<?= h(url('/admin/backup.php?export=clients_csv')) ?>" class="inline-flex items-center justify-center rounded-xl border border-orange-100 bg-white px-3 py-2 text-sm font-extrabold text-zinc-700 hover:bg-orange-50">Clients CSV</a>
                    <a href="<?= h(url('/admin/backup.php?export=checkins_csv')) ?>" class="inline-flex items-center justify-center rounded-xl border border-orange-100 bg-white px-3 py-2 text-sm font-extrabold text-zinc-700 hover:bg-orange-50">Check-ins CSV</a>
                    <a href="<?= h(url('/admin/backup.php?export=consent_logs_csv')) ?>" class="inline-flex items-center justify-center rounded-xl border border-orange-100 bg-white px-3 py-2 text-sm font-extrabold text-zinc-700 hover:bg-orange-50">Consent Logs CSV</a>
                </div>
            </div>

            <div class="rounded-2xl border border-red-200 bg-red-50 p-6">
                <div class="text-lg font-extrabold tracking-tight text-red-900">Reset Challenge (Danger)</div>
                <div class="mt-1 text-sm text-red-800">This clears all client check-ins. It also clears Day 10 photo references and sets the next challenge start date to next Monday when available.</div>
                <div class="mt-4 text-sm font-extrabold text-red-900">Type RESET to confirm.</div>

                <form method="post" class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 sm:items-end" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="reset_challenge" />
                    <div>
                        <label class="block text-xs font-extrabold uppercase tracking-wide text-red-800">Confirmation</label>
                        <input type="text" name="confirm_text" placeholder="RESET" class="mt-2 block w-full rounded-xl border border-red-200 bg-white px-3 py-2 text-sm text-zinc-700" />
                    </div>
                    <div class="sm:text-right">
                        <button type="submit" class="w-full rounded-xl bg-red-700 px-4 py-3 text-sm font-extrabold text-white hover:bg-red-800 sm:w-auto">Reset Challenge Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>
