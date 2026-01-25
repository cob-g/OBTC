<?php
require __DIR__ . '/../app/bootstrap.php';

$stmt = db()->query('SELECT COUNT(*) AS c FROM users');
$row = $stmt->fetch();
$count = $row ? (int) $row['c'] : 0;

if ($count > 0) {
    http_response_code(404);
    echo 'Not Found';
    exit;
}

$error = '';
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Invalid session. Please refresh and try again.';
    } else {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        if ($name === '' || $email === '' || $password === '') {
            $error = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $insert = db()->prepare('INSERT INTO users (name, email, role, password_hash) VALUES (?, ?, ?, ?)');
            $insert->execute([$name, $email, 'admin', $hash]);

            redirect(url('/auth/login.php'));
        }
    }
}

$page_title = 'Setup';
require __DIR__ . '/../partials/layout_top.php';
?>

<div class="min-h-screen bg-orange-50">
    <div class="mx-auto flex min-h-screen max-w-6xl items-center justify-center px-4 py-10">
        <div class="w-full max-w-md rounded-2xl border border-orange-100 bg-white p-6 shadow-sm">
            <div class="mb-6">
                <div class="mb-3 inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo_bloom text-white font-black">A</div>
                <h1 class="text-2xl font-extrabold tracking-tight text-zinc-900">Initial Admin Setup</h1>
                <p class="mt-1 text-sm text-zinc-600">Create the first admin account (only available when no users exist).</p>
            </div>

            <?php if ($error): ?>
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                    <?= h($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" class="space-y-4" novalidate>
                <?= csrf_field() ?>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-zinc-800" for="name">Name</label>
                    <input id="name" name="name" type="text" value="<?= h($name) ?>" required
                           class="w-full rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm outline-none ring-indigo_bloom/20 focus:border-indigo_bloom focus:ring-4" />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-zinc-800" for="email">Email</label>
                    <input id="email" name="email" type="email" value="<?= h($email) ?>" required
                           class="w-full rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm outline-none ring-indigo_bloom/20 focus:border-indigo_bloom focus:ring-4" />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-zinc-800" for="password">Password</label>
                    <input id="password" name="password" type="password" required
                           class="w-full rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm outline-none ring-indigo_bloom/20 focus:border-indigo_bloom focus:ring-4" />
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-zinc-800" for="confirm_password">Confirm Password</label>
                    <input id="confirm_password" name="confirm_password" type="password" required
                           class="w-full rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm outline-none ring-indigo_bloom/20 focus:border-indigo_bloom focus:ring-4" />
                </div>

                <button type="submit" class="w-full rounded-xl bg-indigo_bloom px-4 py-3 text-sm font-extrabold text-white shadow-sm hover:brightness-110">
                    Create Admin
                </button>

                <div class="pt-2 text-center text-sm text-zinc-600">
                    <a class="font-semibold text-molten hover:underline" href="<?= h(url('/auth/login.php')) ?>">Back to login</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>
