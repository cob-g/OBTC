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
        ['Overview', url('/admin/overview.php'), '<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>'],
        ['Challenges', url('/admin/challenges.php'), '<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>'],
        ['Coach Challenges', url('/admin/coach_challenges.php'), '<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>'],
        ['Coach Leaderboard', url('/admin/coach_leaderboard.php'), '<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>'],
        ['Leaderboard', url('/admin/leaderboard.php'), '<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>'],
        ['Clients', url('/admin/clients.php'), '<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>'],
        ['Data Backup', url('/admin/backup.php'), '<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>'],
        ['Reports', url('/admin/reports.php'), '<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'],
        ['Privacy Logs', url('/admin/privacy_logs.php'), '<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>'],
        ['Profile', url('/admin/profile.php'), '<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>'],
        ['Settings', url('/admin/settings.php'), '<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'],
    ];
}
?>
<?php if ($role === 'admin' && $user): ?>
    <div class="min-h-screen bg-slate-50">
        <div class="flex min-h-screen">
            <!-- Admin Sidebar -->
            <aside class="sticky top-0 hidden h-screen w-72 shrink-0 overflow-y-auto border-r border-slate-200 bg-slate-900 lg:flex lg:flex-col">
                <!-- Admin Brand -->
                <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-700">
                    <a href="<?= h(url('/index.php')) ?>" class="flex items-center gap-3 font-extrabold tracking-tight">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo_bloom to-purple-600 text-white shadow-lg">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-white text-sm font-bold">Admin Panel</span>
                            <span class="text-slate-400 text-xs">10 Days Challenge</span>
                        </div>
                    </a>
                </div>

                <!-- Admin User Info -->
                <div class="px-4 py-4 border-b border-slate-700">
                    <div class="flex items-center gap-3 px-2 py-2 rounded-lg bg-slate-800">
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-indigo_bloom to-purple-600 text-white">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-semibold text-white truncate"><?= h($user['name']) ?></div>
                            <div class="text-xs text-slate-400 truncate"><?= h($user['email']) ?></div>
                        </div>
                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide bg-indigo_bloom/20 text-indigo-300 rounded-full">Admin</span>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-3 py-4 overflow-y-auto">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 px-4 mb-2">Main Menu</div>
                    <?php foreach ($nav as $item): ?>
                        <a href="<?= h($item[1]) ?>" class="mb-1 flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                            <?php if (isset($item[2])): ?>
                                <span class="text-indigo-400"><?= $item[2] ?></span>
                            <?php endif; ?>
                            <?= h($item[0]) ?>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <!-- Logout -->
                <div class="border-t border-slate-700 p-4">
                    <a href="<?= h(url('/auth/logout.php')) ?>" class="flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo_bloom to-purple-600 px-4 py-3 text-sm font-bold text-white hover:from-indigo-600 hover:to-purple-700 transition-all shadow-lg">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Sign Out
                    </a>
                </div>
            </aside>

            <div class="flex min-w-0 flex-1 flex-col">
                <!-- Admin Mobile Header -->
                <header class="sticky top-0 z-50 border-b border-slate-200 bg-slate-900 lg:hidden">
                    <div class="flex items-center justify-between gap-4 px-4 py-3">
                        <div class="flex items-center gap-3">
                            <button onclick="toggleAdminMobileMenu()" class="flex items-center justify-center h-10 w-10 rounded-lg text-slate-300 hover:bg-slate-800 focus:outline-none">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                            </button>
                            <a href="<?= h(url('/index.php')) ?>" class="flex items-center gap-2 font-extrabold tracking-tight">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-indigo_bloom to-purple-600 text-white">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </div>
                                <span class="text-white text-sm font-bold">Admin</span>
                            </a>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="hidden sm:inline-block px-2 py-1 text-[10px] font-bold uppercase tracking-wide bg-indigo_bloom/20 text-indigo-300 rounded-full"><?= h($user['name']) ?></span>
                        </div>
                    </div>
                </header>

                <!-- Admin Mobile Menu Drawer -->
                <div id="adminMobileMenu" class="hidden fixed inset-0 z-[9999] lg:hidden">
                    <div class="absolute inset-0 bg-slate-900/80" onclick="toggleAdminMobileMenu()"></div>
                    <div class="absolute left-0 top-0 h-full w-72 max-w-[85vw] bg-slate-900 shadow-2xl">
                        <div class="flex flex-col h-full">
                            <!-- Header -->
                            <div class="flex items-center justify-between border-b border-slate-700 px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo_bloom to-purple-600 text-white">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                        </svg>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-white text-sm font-bold">Admin Panel</span>
                                        <span class="text-slate-400 text-xs">10 Days Challenge</span>
                                    </div>
                                </div>
                                <button onclick="toggleAdminMobileMenu()" class="flex items-center justify-center h-10 w-10 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            
                            <!-- User Info -->
                            <div class="px-4 py-3 border-b border-slate-700">
                                <div class="flex items-center gap-3 px-2 py-2 rounded-lg bg-slate-800">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-indigo_bloom to-purple-600 text-white">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-semibold text-white truncate"><?= h($user['name']) ?></div>
                                        <div class="text-xs text-slate-400">Administrator</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Navigation -->
                            <nav class="flex-1 overflow-y-auto px-3 py-4">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 px-4 mb-2">Menu</div>
                                <?php foreach ($nav as $item): ?>
                                    <a href="<?= h($item[1]) ?>" class="mb-1 flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                                        <?php if (isset($item[2])): ?>
                                            <span class="text-indigo-400"><?= $item[2] ?></span>
                                        <?php endif; ?>
                                        <?= h($item[0]) ?>
                                    </a>
                                <?php endforeach; ?>
                            </nav>
                            
                            <!-- Logout -->
                            <div class="border-t border-slate-700 p-4">
                                <a href="<?= h(url('/auth/logout.php')) ?>" class="flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo_bloom to-purple-600 px-4 py-3 text-sm font-bold text-white hover:from-indigo-600 hover:to-purple-700 transition-all">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    Sign Out
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex-1">
<?php else: ?>
    <header class="sticky top-0 z-50 border-b border-orange-200 bg-gradient-to-r from-orange-50 via-white to-amber-50 shadow-sm">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3">
            <a href="<?= h(url('/index.php')) ?>" class="flex items-center gap-3 font-extrabold tracking-tight group">
                <div class="relative flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-molten via-orange-500 to-amber-500 shadow-lg shadow-orange-200/50 group-hover:shadow-orange-300/60 transition-shadow">
                    <svg class="h-7 w-7" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
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
                    <!-- Shine effect -->
                    <div class="absolute inset-0 rounded-xl bg-gradient-to-tr from-white/0 via-white/20 to-white/0 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                </div>
                <div class="flex flex-col">
                    <span class="hidden sm:block text-sm font-extrabold text-zinc-900">10 Days Weekly</span>
                    <span class="hidden sm:block text-xs font-semibold text-molten">Challenge Portal</span>
                </div>
            </a>

            <nav class="hidden lg:flex items-center gap-1 bg-white/60 backdrop-blur-sm rounded-xl px-2 py-1.5 border border-orange-100">
                <?php foreach ($nav as $item): ?>
                    <a href="<?= h($item[1]) ?>" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-zinc-600 hover:bg-gradient-to-r hover:from-orange-100 hover:to-amber-50 hover:text-molten transition-all">
                        <?php if (isset($item[2])): ?><span class="text-orange-400"><?= $item[2] ?></span><?php endif; ?>
                        <?= h($item[0]) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="flex items-center gap-3">
                <?php if ($user): ?>
                    <!-- Mobile Menu Button -->
                    <button onclick="toggleMobileMenu()" class="lg:hidden flex items-center justify-center h-10 w-10 rounded-xl bg-gradient-to-br from-orange-100 to-amber-100 text-molten hover:from-orange-200 hover:to-amber-200 focus:outline-none focus:ring-2 focus:ring-molten focus:ring-offset-2 transition-all shadow-sm">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    
                    <!-- User Avatar Dropdown - Hidden on mobile, visible on lg+ -->
                    <div class="relative hidden lg:block">
                        <button onclick="toggleUserDropdown()" class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-molten to-amber-500 text-white hover:shadow-lg hover:shadow-orange-200/50 transition-all focus:outline-none focus:ring-2 focus:ring-molten focus:ring-offset-2">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </button>
                        <div id="userDropdown" class="absolute right-0 mt-2 w-56 rounded-2xl border border-orange-100 bg-white shadow-xl shadow-orange-100/50 hidden z-50">
                            <div class="px-4 py-3 border-b border-orange-100 bg-gradient-to-r from-orange-50 to-amber-50 rounded-t-2xl">
                                <div class="text-sm font-bold text-zinc-800"><?= h($user['name']) ?></div>
                                <div class="text-xs text-orange-600 mt-0.5 capitalize flex items-center gap-1">
                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    <?= h($user['role']) ?>
                                </div>
                            </div>
                            <div class="p-2">
                                <a href="<?= h(url('/auth/logout.php')) ?>" class="flex items-center gap-2 rounded-xl bg-gradient-to-r from-molten to-orange-500 px-4 py-2.5 text-sm font-bold text-white hover:from-pumpkin hover:to-amber-500 transition shadow-sm">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    Sign Out
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Mobile Menu Drawer - Opens from LEFT side -->
    <?php if ($user): ?>
        <div id="mobileMenu" class="hidden fixed inset-0 z-[9999]">
            <!-- Overlay -->
            <div class="absolute inset-0 bg-zinc-900/60 backdrop-blur-sm" onclick="toggleMobileMenu()"></div>
            
            <!-- Menu Drawer - Now on LEFT side -->
            <div class="absolute left-0 top-0 h-full w-80 max-w-[85vw] bg-gradient-to-b from-white to-orange-50/50 shadow-2xl transform transition-transform duration-300">
                <div class="flex flex-col h-full">
                    <!-- Header with Brand -->
                    <div class="flex items-center justify-between border-b border-orange-100 px-5 py-4 bg-gradient-to-r from-orange-50 via-white to-amber-50">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-molten to-amber-500 text-white shadow-lg shadow-orange-200/50">
                                <svg class="h-6 w-6" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 10c0 8 4 16 12 16s12-8 12-16" fill="#FFB300" stroke="#B45309" stroke-width="2"/>
                                    <rect x="18" y="34" width="12" height="6" rx="2" fill="#B45309"/>
                                    <rect x="16" y="40" width="16" height="4" rx="2" fill="#92400E"/>
                                    <polygon points="24,17 25.9,22.1 31.4,22.1 27,25.4 28.9,30.5 24,27.2 19.1,30.5 21,25.4 16.6,22.1 22.1,22.1" fill="#FFFDE4" stroke="#FFB300" stroke-width="1"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-zinc-800">Coach Portal</div>
                                <div class="text-xs text-orange-600">10 Days Challenge</div>
                            </div>
                        </div>
                        <button onclick="toggleMobileMenu()" class="flex items-center justify-center h-10 w-10 rounded-xl bg-orange-100 text-zinc-500 hover:bg-orange-200 hover:text-zinc-700 transition-colors">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- User Info Card -->
                    <div class="mx-4 mt-4 rounded-2xl bg-gradient-to-br from-molten to-amber-500 p-4 text-white shadow-lg shadow-orange-200/50">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-bold"><?= h($user['name']) ?></div>
                                <div class="text-xs text-orange-100 flex items-center gap-1">
                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    Coach Account
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Navigation Links -->
                    <nav class="flex-1 overflow-y-auto px-4 py-4">
                        <div class="text-xs font-bold text-zinc-400 uppercase tracking-wider px-4 mb-3">Menu</div>
                        <?php foreach ($nav as $item): ?>
                            <a href="<?= h($item[1]) ?>" class="flex items-center gap-3 rounded-xl px-4 py-3.5 text-sm font-semibold text-zinc-700 hover:bg-gradient-to-r hover:from-orange-100 hover:to-amber-50 hover:text-molten mb-1 transition-all group">
                                <?php if (isset($item[2])): ?>
                                    <span class="text-orange-400 group-hover:text-molten transition-colors"><?= $item[2] ?></span>
                                <?php endif; ?>
                                <?= h($item[0]) ?>
                            </a>
                        <?php endforeach; ?>
                    </nav>
                    
                    <!-- Logout Button -->
                    <div class="border-t border-orange-100 p-4 bg-white/50">
                        <a href="<?= h(url('/auth/logout.php')) ?>" class="flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-molten to-orange-500 px-4 py-3.5 text-sm font-bold text-white hover:from-pumpkin hover:to-amber-500 transition shadow-lg shadow-orange-200/50 w-full">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Sign Out
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>
