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
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-molten">
                    <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 0l-2 2a1 1 0 101.414 1.414L8 10.414l1.293 1.293a1 1 0 001.414 0l4-4z"/>
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