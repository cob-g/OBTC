<?php
require __DIR__ . '/../app/bootstrap.php';

if (auth_check()) {
    redirect(url('/index.php'));
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Invalid session. Please refresh and try again.';
    } else {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $error = 'Email and password are required.';
        } else {
            $ok = auth_login($email, $password);
            if ($ok) {
                redirect(url('/index.php'));
            }
            $error = auth_last_error() === 'inactive'
                ? 'Your account has been deactivated. Please contact the admin.'
                : 'Invalid credentials.';
        }
    }
}

$page_title = 'Login';
require __DIR__ . '/../partials/layout_top.php';
?>

<div class="flex min-h-screen bg-neutral-50">
    <!-- Left Side - Login Form -->
    <div class="flex w-full items-center justify-center px-6 py-12 lg:w-1/2 lg:px-12">
        <div class="w-full max-w-md">
            <!-- Logo and Brand -->
            <div class="mb-12 flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gradient-to-br from-molten to-amber-500 shadow-lg">
                    <svg class="h-8 w-8" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="trophyBody" x1="0" y1="0" x2="48" y2="48" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#FFB300"/>
                                <stop offset="1" stop-color="#FF6F00"/>
                            </linearGradient>
                            <radialGradient id="trophyShine" cx="24" cy="12" r="20" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#FFFDE4" stop-opacity="0.8"/>
                                <stop offset="1" stop-color="#FFB300" stop-opacity="0"/>
                            </radialGradient>
                        </defs>
                        <!-- Trophy cup -->
                        <path d="M12 10c0 8 4 16 12 16s12-8 12-16" fill="url(#trophyBody)" stroke="#B45309" stroke-width="2"/>
                        <!-- Trophy base -->
                        <rect x="18" y="34" width="12" height="6" rx="2" fill="#B45309"/>
                        <rect x="16" y="40" width="16" height="4" rx="2" fill="#92400E"/>
                        <!-- Handles -->
                        <path d="M12 14c-4 0-6 4-6 8s2 8 6 8" stroke="#B45309" stroke-width="2" fill="none"/>
                        <path d="M36 14c4 0 6 4 6 8s-2 8-6 8" stroke="#B45309" stroke-width="2" fill="none"/>
                        <!-- Shine -->
                        <ellipse cx="24" cy="14" rx="8" ry="4" fill="url(#trophyShine)"/>
                        <!-- Star in the center -->
                        <polygon points="24,17 25.9,22.1 31.4,22.1 27,25.4 28.9,30.5 24,27.2 19.1,30.5 21,25.4 16.6,22.1 22.1,22.1" fill="#FFFDE4" stroke="#FFB300" stroke-width="1"/>
                    </svg>
                </div>
                <h1 class="text-lg font-bold tracking-tight text-zinc-900">10 Days Weekly Challenge</h1>
            </div>

            <!-- Header -->
            <div class="mb-10">
                <h2 class="mb-3 text-3xl font-extrabold tracking-tight text-zinc-900">Welcome Back, Coach</h2>
                <p class="text-[15px] leading-relaxed text-molten">Enter your credentials to manage the challenge dashboard.</p>
            </div>

            <?php if ($error): ?>
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3.5 text-sm font-medium text-red-700">
                    <?= h($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" class="space-y-6" novalidate>
                <?= csrf_field() ?>

                <div>
                    <label class="mb-2.5 block text-sm font-semibold text-zinc-900" for="email">Email Address</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <svg class="h-5 w-5 text-molten" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <input 
                            id="email" 
                            name="email" 
                            type="email" 
                            value="<?= h($email) ?>" 
                            required 
                            placeholder="coach@10dayschallenge.com"
                            class="w-full rounded-xl border border-zinc-200 bg-white px-4 py-3.5 pl-12 text-[15px] text-zinc-900 placeholder:text-zinc-400 outline-none transition focus:border-molten focus:ring-4 focus:ring-molten/10"
                        />
                    </div>
                </div>

                <div>
                    <div class="mb-2.5 flex items-center justify-between">
                        <label class="block text-sm font-semibold text-zinc-900" for="password">Password</label>
                        <a href="#" class="text-sm font-semibold text-molten transition hover:text-pumpkin hover:underline">Forgot password?</a>
                    </div>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <svg class="h-5 w-5 text-amber-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input 
                            id="password" 
                            name="password" 
                            type="password" 
                            required 
                            placeholder="••••••••••"
                            class="w-full rounded-xl border border-zinc-200 bg-white px-4 py-3.5 pl-12 text-[15px] text-zinc-900 placeholder:text-zinc-400 outline-none transition focus:border-molten focus:ring-4 focus:ring-molten/10"
                        />
                    </div>
                </div>

                <button type="submit" class="mt-8 w-full rounded-xl bg-molten px-6 py-4 text-base font-bold text-white shadow-sm transition hover:bg-pumpkin hover:shadow-md active:scale-[0.98]">
                    Sign In
                </button>

                <div class="relative pt-8">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-zinc-200"></div>
                    </div>
                </div>

                <div class="pt-4 text-center">
                    <p class="mb-3 text-sm text-amber-700">Need help accessing your account?</p>
                    <a href="#" class="inline-flex items-center gap-2 text-sm font-semibold text-zinc-900 transition hover:text-molten">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        Contact Admin Support
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Side - Hero Image -->
    <div class="relative hidden lg:block lg:w-1/2">
        <div class="absolute inset-0 bg-gradient-to-br from-zinc-900/70 to-zinc-900/50"></div>
        <img 
            src="https://lh3.googleusercontent.com/aida-public/AB6AXuAxgkBqvszKJcXznwCrKItFjUfklCPa0O2USigPkHGxOoN4B2WZiHJTgdS8Dew-fklOWHJLMV5G_X4-S7X61BXMgj2MBSD1vPX_6Xx96sAMcQPDA9D46_kIoln5cDnY92V7qA-_7TbOUG_vfXU0xvLSh45CLxp7ReKKjkdkvbPUQwTyRko0nus-Sk-c8xHS8TcUylJ2Tom1Dc4LuWfRHy0kIVceQB9FIhzn6Q6R-qoRffJhZ4rcIIon9U1qnWB0aQbwl5_1EtzvKSza" 
            alt="Fitness Training" 
            class="h-full w-full object-cover"
        />
        <div class="absolute inset-0 flex items-end justify-start px-20 mb-28">
            <div class="max-w-2xl text-start">
                <h2 class="text-4xl font-extrabold leading-tight text-white drop-shadow-2xl">
                    Build Champions Every Week.
                </h2>
                <p class="mt-6 text-lg leading-relaxed text-white/95 drop-shadow-lg w-3/4">
                    Join the elite coaching team driving the 10 Days Weekly Challenge. Track progress, manage athletes, and inspire greatness.
                </p>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>