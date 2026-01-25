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

<div class="min-h-screen bg-orange-50">
    <div class="mx-auto flex min-h-screen max-w-6xl items-center justify-center px-4 py-10">
        <div class="w-full max-w-md rounded-2xl border border-orange-100 bg-white p-6 shadow-sm">
            <div class="mb-6">
                <div class="mb-3 inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-molten text-white font-black">10</div>
                <h1 class="text-2xl font-extrabold tracking-tight text-zinc-900">Coach/Admin Login</h1>
                <p class="mt-1 text-sm text-zinc-600">Sign in to manage your 10-day weekly challenge.</p>
            </div>

            <?php if ($error): ?>
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                    <?= h($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" class="space-y-4" novalidate>
                <?= csrf_field() ?>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-zinc-800" for="email">Email</label>
                    <input id="email" name="email" type="email" value="<?= h($email) ?>" required
                           class="w-full rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm outline-none ring-molten/20 focus:border-molten focus:ring-4" />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-zinc-800" for="password">Password</label>
                    <input id="password" name="password" type="password" required
                           class="w-full rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm outline-none ring-molten/20 focus:border-molten focus:ring-4" />
                </div>

                <button type="submit" class="w-full rounded-xl bg-molten px-4 py-3 text-sm font-extrabold text-white shadow-sm hover:bg-pumpkin">
                    Login
                </button>

                <div class="pt-2 text-center text-sm text-zinc-600">
                    <a class="font-semibold text-indigo_bloom hover:underline" href="<?= h(url('/auth/setup.php')) ?>">First time setup</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>
