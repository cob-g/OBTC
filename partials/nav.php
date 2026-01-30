<?php
$user = auth_user();
$role = $user ? $user['role'] : null;

$nav = [];

if ($role === 'coach') {
    $nav = [
        ['Dashboard', url('/coach/dashboard.php'), '<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>'],
        ['Pre-Registration', url('/coach/pre_registration.php'), '<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>'],
        ['Challenge', url('/coach/challenge.php'), '<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>'],
        ['Coach Challenge', url('/coach/coach_challenge.php'), '<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>'],
        ['Client', url('/coach/clients.php'), '<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>'],
        ['Profile', url('/coach/profile.php'), '<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>'],
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
<?php if ($role === 'admin' && $user): ?>
    <div class="min-h-screen bg-orange-50">
        <div class="flex min-h-screen">
            <aside class="sticky top-0 hidden h-screen w-72 shrink-0 overflow-y-auto border-r border-orange-100 bg-white lg:flex lg:flex-col">
                <div class="flex items-center gap-2 px-6 py-5">
                    <a href="<?= h(url('/index.php')) ?>" class="flex items-center gap-2 font-extrabold tracking-tight">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-molten text-white">10</span>
                        <span>Admin Panel</span>
                    </a>
                </div>

                <div class="px-6 pb-4">
                    <div class="text-sm font-semibold text-zinc-800"><?= h($user['name']) ?></div>
                    <div class="text-xs text-zinc-500"><?= h($user['email']) ?></div>
                </div>

                <nav class="flex-1 px-3 pb-6">
                    <?php foreach ($nav as $item): ?>
                        <a href="<?= h($item[1]) ?>" class="mb-1 block rounded-xl px-4 py-3 text-sm font-semibold text-zinc-700 hover:bg-orange-50 hover:text-molten">
                            <?= h($item[0]) ?>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <div class="border-t border-orange-100 p-4">
                    <a href="<?= h(url('/auth/logout.php')) ?>" class="block rounded-xl bg-molten px-4 py-3 text-center text-sm font-extrabold text-white hover:bg-pumpkin">Logout</a>
                </div>
            </aside>

            <div class="flex min-w-0 flex-1 flex-col">
                <header class="sticky top-0 z-50 border-b border-orange-100 bg-white/90 backdrop-blur">
                    <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3">
                        <div class="flex items-center gap-3">
                            <a href="<?= h(url('/index.php')) ?>" class="flex items-center gap-2 font-extrabold tracking-tight lg:hidden">
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-molten text-white">10</span>
                                <span class="hidden sm:inline">Admin Panel</span>
                            </a>
                            <div class="hidden sm:flex flex-col leading-tight">
                                <span class="text-sm font-semibold"><?= h($user['name']) ?></span>
                                <span class="text-xs text-zinc-500"><?= h($user['role']) ?></span>
                            </div>
                        </div>
                        <a href="<?= h(url('/auth/logout.php')) ?>" class="rounded-lg bg-molten px-3 py-2 text-sm font-semibold text-white hover:bg-pumpkin lg:hidden">Logout</a>
                    </div>

                    <nav class="lg:hidden border-t border-orange-100 bg-white">
                        <div class="mx-auto flex max-w-6xl gap-1 overflow-x-auto px-2 py-2">
                            <?php foreach ($nav as $item): ?>
                                <a href="<?= h($item[1]) ?>" class="whitespace-nowrap rounded-lg px-3 py-2 text-sm font-semibold text-zinc-700 hover:bg-orange-50 hover:text-molten">
                                    <?= h($item[0]) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </nav>
                </header>

                <div class="flex-1">
<?php else: ?>
    <header class="sticky top-0 z-50 border-b border-orange-100 bg-white/90 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3">
            <a href="<?= h(url('/index.php')) ?>" class="flex items-center gap-2 font-extrabold tracking-tight">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-molten to-amber-500 shadow-lg">
                    <svg class="h-6 w-6" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="trophyBodyNav" x1="0" y1="0" x2="48" y2="48" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#FFB300"/>
                                <stop offset="1" stop-color="#FF6F00"/>
                            </linearGradient>
                            <radialGradient id="trophyShineNav" cx="24" cy="12" r="20" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#FFFDE4" stop-opacity="0.8"/>
                                <stop offset="1" stop-color="#FFB300" stop-opacity="0"/>
                            </radialGradient>
                        </defs>
                        <path d="M12 10c0 8 4 16 12 16s12-8 12-16" fill="url(#trophyBodyNav)" stroke="#B45309" stroke-width="2"/>
                        <rect x="18" y="34" width="12" height="6" rx="2" fill="#B45309"/>
                        <rect x="16" y="40" width="16" height="4" rx="2" fill="#92400E"/>
                        <path d="M12 14c-4 0-6 4-6 8s2 8 6 8" stroke="#B45309" stroke-width="2" fill="none"/>
                        <path d="M36 14c4 0 6 4 6 8s-2 8-6 8" stroke="#B45309" stroke-width="2" fill="none"/>
                        <ellipse cx="24" cy="14" rx="8" ry="4" fill="url(#trophyShineNav)"/>
                        <polygon points="24,17 25.9,22.1 31.4,22.1 27,25.4 28.9,30.5 24,27.2 19.1,30.5 21,25.4 16.6,22.1 22.1,22.1" fill="#FFFDE4" stroke="#FFB300" stroke-width="1"/>
                    </svg>
                </div>
                <span class="hidden sm:inline">10 Days Weekly Challenge</span>
            </a>

            <nav class="hidden md:flex items-center gap-1">
                <?php foreach ($nav as $item): ?>
                    <a href="<?= h($item[1]) ?>" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-zinc-700 hover:bg-orange-50 hover:text-molten">
                        <?php if (isset($item[2])): ?><?= $item[2] ?><?php endif; ?>
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
                        <a href="<?= h($item[1]) ?>" class="flex items-center gap-2 whitespace-nowrap rounded-lg px-3 py-2 text-sm font-semibold text-zinc-700 hover:bg-orange-50 hover:text-molten">
                            <?php if (isset($item[2])): ?><?= $item[2] ?><?php endif; ?>
                            <?= h($item[0]) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </nav>
        <?php endif; ?>
    </header>
<?php endif; ?>
