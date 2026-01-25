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
                        if ($dayNumber > 10) {
                            $errors[] = 'You cannot record a future day weigh-in.';
                        }
                    } elseif ($currentDay !== null && $dayNumber > (int) $currentDay) {
                        $errors[] = 'You cannot record a future day weigh-in.';
                    }
                }
            }

            if (!$errors) {
                try {
                    $now = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');
                    $ins = db()->prepare('INSERT INTO client_checkins (client_id, coach_user_id, day_number, weight_lbs, recorded_at) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE weight_lbs = VALUES(weight_lbs), recorded_at = VALUES(recorded_at)');
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
        $sql = 'SELECT client_id, COUNT(*) AS days_completed, MAX(day_number) AS max_day, SUBSTRING_INDEX(GROUP_CONCAT(weight_lbs ORDER BY day_number DESC), ",", 1) AS latest_weight FROM client_checkins WHERE coach_user_id = ? AND client_id IN (' . $placeholders . ') GROUP BY client_id';
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
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold tracking-tight">Challenge</h1>
        <p class="mt-1 text-sm text-zinc-600">Record daily weigh-ins for your clients (coach-only).</p>
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
        <div class="flex items-center justify-between gap-3">
            <div>
                <div class="text-lg font-extrabold tracking-tight">Active Challenge Clients</div>
                <div class="mt-1 text-sm text-zinc-600">Challenge starts on Monday. Tuesday registrations can still complete Day 1/2 without being marked missed.</div>
            </div>
            <a href="<?= h(url('/coach/clients.php')) ?>" class="rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm font-extrabold text-zinc-700 hover:bg-orange-50">View Clients</a>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-orange-100 text-xs font-extrabold uppercase tracking-wide text-zinc-600">
                        <th class="py-3 pr-4">Client</th>
                        <th class="py-3 pr-4">Start</th>
                        <th class="py-3 pr-4">Day</th>
                        <th class="py-3 pr-4">Days Done</th>
                        <th class="py-3 pr-4">Latest</th>
                        <th class="py-3 pr-4">Loss</th>
                        <th class="py-3 pr-0">Record</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$clients): ?>
                        <tr>
                            <td colspan="7" class="py-6 text-zinc-600">No clients yet. Pre-register a challenger first.</td>
                        </tr>
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
                            $defaultDay = $maxDayAllowed >= 1 ? $maxDayAllowed : 1;
                        ?>
                        <tr class="border-b border-orange-50">
                            <td class="py-4 pr-4">
                                <div class="font-extrabold text-zinc-900"><?= h($name) ?></div>
                                <div class="mt-1 text-xs text-zinc-600"><?= h((string) $c['gender']) ?>, <?= h((string) $c['age']) ?>y • <?= h((string) $c['height_ft']) ?>ft <?= h((string) $c['height_in']) ?>in</div>
                            </td>
                            <td class="py-4 pr-4 text-zinc-700"><?= h((string) ($startDate ?? '-')) ?></td>
                            <td class="py-4 pr-4 font-extrabold text-molten"><?= h($dayLabel) ?></td>
                            <td class="py-4 pr-4 text-zinc-700"><?= h((string) $daysDone) ?>/10</td>
                            <td class="py-4 pr-4 text-zinc-700"><?= $latestWeight === null ? '-' : h(number_format((float) $latestWeight, 2, '.', '')) ?></td>
                            <td class="py-4 pr-4 font-extrabold text-zinc-900"><?= $loss === null ? '-' : h(number_format((float) $loss, 2, '.', '')) ?></td>
                            <td class="py-4 pr-0">
                                <form method="post" class="flex flex-wrap items-center justify-end gap-2" novalidate>
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="record_checkin" />
                                    <input type="hidden" name="client_id" value="<?= h((string) $c['id']) ?>" />

                                    <select name="day_number" class="rounded-xl border border-orange-100 bg-white px-3 py-2 text-xs font-extrabold text-zinc-700 outline-none ring-molten/20 focus:border-molten focus:ring-4" <?= $maxDayAllowed < 1 ? 'disabled' : '' ?>>
                                        <?php for ($i = 1; $i <= 10; $i++): ?>
                                            <?php if ($maxDayAllowed >= 1 && $i <= $maxDayAllowed): ?>
                                                <option value="<?= $i ?>" <?= $i === $defaultDay ? 'selected' : '' ?>>Day <?= $i ?></option>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </select>

                                    <input name="weight_lbs" type="number" min="50" max="500" step="0.01" value="" placeholder="Weight" class="w-28 rounded-xl border border-orange-100 bg-white px-3 py-2 text-xs font-extrabold text-zinc-700 outline-none ring-molten/20 focus:border-molten focus:ring-4" <?= $maxDayAllowed < 1 ? 'disabled' : '' ?> />

                                    <button type="submit" class="rounded-xl bg-molten px-3 py-2 text-xs font-extrabold text-white hover:bg-pumpkin" <?= $maxDayAllowed < 1 ? 'disabled' : '' ?>>Save</button>
                                </form>
                                <?php if ($maxDayAllowed < 1): ?>
                                    <div class="mt-2 text-right text-xs text-zinc-500">Upcoming challenge</div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="mt-5 flex items-center justify-between gap-3 text-sm">
                <div class="font-semibold text-zinc-700">Page <?= h((string) $page) ?> of <?= h((string) $totalPages) ?></div>
                <div class="flex items-center gap-2">
                    <?php if ($page > 1): ?>
                        <a class="rounded-xl border border-orange-100 bg-white px-3 py-2 text-xs font-extrabold text-zinc-700 hover:bg-orange-50" href="<?= h(url('/coach/challenge.php?page=' . (int) ($page - 1))) ?>">Prev</a>
                    <?php endif; ?>
                    <?php if ($page < $totalPages): ?>
                        <a class="rounded-xl border border-orange-100 bg-white px-3 py-2 text-xs font-extrabold text-zinc-700 hover:bg-orange-50" href="<?= h(url('/coach/challenge.php?page=' . (int) ($page + 1))) ?>">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>
