<?php
$user = auth_user();
$role = $user ? $user['role'] : null;

$nav = [];

if ($role === 'coach') {
    $nav = [
        ['Dashboard', url('/coach/dashboard.php')],
        ['Pre-Registration', url('/coach/pre_registration.php')],
        ['Challenge', url('/coach/challenge.php')],
        ['Coach Challenge', url('/coach/coach_challenge.php')],
        ['Client', url('/coach/clients.php')],
        ['Profile', url('/coach/profile.php')],
    ];
}

if ($role === 'admin') {
    $nav = [
        ['Overview', url('/admin/overview.php')],
        ['Challenges', url('/admin/challenges.php')],
        ['Coach Challenges', url('/admin/coach_challenges.php')],
        ['Coach Leaderboard', url('/admin/coach_leaderboard.php')],
        ['Leaderboard', url('/admin/leaderboard.php')],
        ['Clients', url('/admin/clients.php')],
        ['Data Backup', url('/admin/backup.php')],
        ['Reports', url('/admin/reports.php')],
        ['Privacy Logs', url('/admin/privacy_logs.php')],
        ['Profile', url('/admin/profile.php')],
        ['Settings', url('/admin/settings.php')],
    ];
}
?>
<header class="sticky top-0 z-50 border-b border-orange-100 bg-white/90 backdrop-blur">
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3">
        <a href="<?= h(url('/index.php')) ?>" class="flex items-center gap-2 font-extrabold tracking-tight">
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-molten text-white">10</span>
            <span class="hidden sm:inline">10 Days Weekly Challenge</span>
        </a>

        <nav class="hidden md:flex items-center gap-1">
            <?php foreach ($nav as $item): ?>
                <a href="<?= h($item[1]) ?>" class="rounded-lg px-3 py-2 text-sm font-semibold text-zinc-700 hover:bg-orange-50 hover:text-molten">
                    <?= h($item[0]) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="flex items-center gap-3">
            <?php if ($user): ?>
                <div class="hidden sm:flex flex-col leading-tight">
                    <span class="text-sm font-semibold"><?= h($user['name']) ?></span>
                    <span class="text-xs text-zinc-500"><?= h($user['role']) ?></span>
                </div>
                <a href="<?= h(url('/auth/logout.php')) ?>" class="rounded-lg bg-molten px-3 py-2 text-sm font-semibold text-white hover:bg-pumpkin">Logout</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($user): ?>
        <nav class="md:hidden border-t border-orange-100 bg-white">
            <div class="mx-auto flex max-w-6xl gap-1 overflow-x-auto px-2 py-2">
                <?php foreach ($nav as $item): ?>
                    <a href="<?= h($item[1]) ?>" class="whitespace-nowrap rounded-lg px-3 py-2 text-sm font-semibold text-zinc-700 hover:bg-orange-50 hover:text-molten">
                        <?= h($item[0]) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </nav>
    <?php endif; ?>
</header>
