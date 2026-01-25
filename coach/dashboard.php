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

<main class="mx-auto max-w-6xl px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold tracking-tight">Dashboard</h1>
        <p class="mt-1 text-sm text-zinc-600">Overview of the active 10-day challenge.</p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-2xl border border-orange-100 bg-white p-5">
            <div class="text-sm font-semibold text-zinc-600">Total Clients</div>
            <div class="mt-2 text-3xl font-extrabold text-molten"><?= h((string) $totalClients) ?></div>
        </div>
        <div class="rounded-2xl border border-orange-100 bg-white p-5">
            <div class="text-sm font-semibold text-zinc-600">Challenge Day</div>
            <div class="mt-2 text-3xl font-extrabold text-molten"><?= h((string) $challengeDayLabel) ?></div>
        </div>
        <div class="rounded-2xl border border-orange-100 bg-white p-5">
            <div class="text-sm font-semibold text-zinc-600">Avg Weight Loss</div>
            <div class="mt-2 text-3xl font-extrabold text-molten"><?= h((string) $avgWeightLossLabel) ?></div>
        </div>
    </div>

    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
        <a class="inline-flex items-center justify-center rounded-xl bg-indigo_bloom px-4 py-3 text-sm font-extrabold text-white hover:brightness-110" href="<?= h(url('/coach/pre_registration.php')) ?>">
            Pre-Register Challenger
        </a>
        <a class="inline-flex items-center justify-center rounded-xl bg-molten px-4 py-3 text-sm font-extrabold text-white hover:bg-pumpkin" href="<?= h(url('/coach/challenge.php')) ?>">
            View Active Challenge
        </a>
    </div>
</main>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>
