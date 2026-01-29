<?php
require __DIR__ . '/../app/bootstrap.php';
require_role('admin');

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

$rows = [];
try {
    $sql = "
        SELECT
            u.id AS coach_id,
            u.name AS coach_name,
            p.start_weight_lbs,
            latest.latest_weight,
            latest.days_completed,
            latest.has_day10,
            ((p.start_weight_lbs - latest.latest_weight) / p.start_weight_lbs) * 100 AS loss_pct
        FROM coach_challenge_participants p
        JOIN users u ON u.id = p.coach_user_id
        JOIN (
            SELECT
                coach_user_id,
                COUNT(*) AS days_completed,
                MAX(CASE WHEN day_number = 10 THEN 1 ELSE 0 END) AS has_day10,
                SUBSTRING_INDEX(GROUP_CONCAT(weight_lbs ORDER BY day_number DESC), ',', 1) AS latest_weight
            FROM coach_checkins
            WHERE coach_challenge_id = ?
            GROUP BY coach_user_id
        ) latest ON latest.coach_user_id = p.coach_user_id
        WHERE p.coach_challenge_id = ?
        ORDER BY latest.has_day10 DESC, latest.days_completed DESC, loss_pct DESC
        LIMIT 10
    ";

    $stmt = db()->prepare($sql);
    $stmt->execute([(int) $challenge['id'], (int) $challenge['id']]);
    $rows = $stmt->fetchAll();
} catch (Throwable $e) {
    $rows = [];
}

$page_title = 'Coach Leaderboard';
require __DIR__ . '/../partials/layout_top.php';
require __DIR__ . '/../partials/nav.php';
?>

<main class="mx-auto max-w-4xl px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold tracking-tight">Coach Leaderboard</h1>
        <p class="mt-1 text-sm text-zinc-600">Live Top 10 (latest weigh-in) • <?= h((string) $challenge['name']) ?></p>
    </div>

    <div class="rounded-2xl border border-orange-100 bg-white p-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <div class="text-lg font-extrabold tracking-tight">Top 10 Coaches</div>
                <div class="mt-1 text-sm text-zinc-600">Ranking updates based on the latest recorded weigh-in.</div>
            </div>
            <a href="<?= h(url('/coach/coach_challenge.php')) ?>" class="rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm font-extrabold text-zinc-700 hover:bg-orange-50">My Coach Challenge</a>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-orange-100 text-xs font-extrabold uppercase tracking-wide text-zinc-600">
                        <th class="py-3 pr-4">Rank</th>
                        <th class="py-3 pr-4">Coach</th>
                        <th class="py-3 pr-4">Days</th>
                        <th class="py-3 pr-4">Start</th>
                        <th class="py-3 pr-4">Latest</th>
                        <th class="py-3 pr-0">Loss %</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$rows): ?>
                        <tr>
                            <td colspan="6" class="py-6 text-zinc-600">No leaderboard entries yet. Coaches need to join and record at least one weigh-in.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($rows as $i => $r): ?>
                        <tr class="border-b border-orange-50">
                            <td class="py-4 pr-4 font-extrabold text-molten">#<?= h((string) ($i + 1)) ?></td>
                            <td class="py-4 pr-4 font-extrabold text-zinc-900"><?= h((string) $r['coach_name']) ?></td>
                            <td class="py-4 pr-4 text-zinc-700"><?= h((string) ((int) $r['days_completed'])) ?>/10</td>
                            <td class="py-4 pr-4 text-zinc-700"><?= h(number_format((float) $r['start_weight_lbs'], 2, '.', '')) ?> lbs</td>
                            <td class="py-4 pr-4 text-zinc-700"><?= h(number_format((float) $r['latest_weight'], 2, '.', '')) ?> lbs</td>
                            <td class="py-4 pr-0 font-extrabold text-zinc-900"><?= h(number_format((float) $r['loss_pct'], 2, '.', '')) ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>
