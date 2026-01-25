<?php
require __DIR__ . '/../app/bootstrap.php';
require_role('admin');

$page_title = 'Settings';
require __DIR__ . '/../partials/layout_top.php';
require __DIR__ . '/../partials/nav.php';

$error = '';
$success = '';
$coachName = '';
$coachEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Invalid session. Please refresh and try again.';
    } else {
        $coachName = trim((string) ($_POST['coach_name'] ?? ''));
        $coachEmail = trim((string) ($_POST['coach_email'] ?? ''));
        $password = (string) ($_POST['coach_password'] ?? '');

        if ($coachName === '' || $coachEmail === '' || $password === '') {
            $error = 'All coach fields are required.';
        } elseif (!filter_var($coachEmail, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid coach email.';
        } elseif (strlen($password) < 8) {
            $error = 'Coach password must be at least 8 characters.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            try {
                $insert = db()->prepare('INSERT INTO users (name, email, role, password_hash) VALUES (?, ?, ?, ?)');
                $insert->execute([$coachName, $coachEmail, 'coach', $hash]);
                $success = 'Coach account created.';
                $coachName = '';
                $coachEmail = '';
            } catch (Throwable $e) {
                $error = 'Unable to create coach account (email may already exist).';
            }
        }
    }
}
?>

<main class="mx-auto max-w-6xl px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold tracking-tight">Settings</h1>
        <p class="mt-1 text-sm text-zinc-600">BMI category ranges, challenge duration, late registration rules (to be implemented).</p>
    </div>

    <div class="rounded-2xl border border-orange-100 bg-white p-6">
        <?php if ($error): ?>
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800"><?= h($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800"><?= h($success) ?></div>
        <?php endif; ?>

        <div class="text-lg font-extrabold tracking-tight">Create Coach Account</div>
        <p class="mt-1 text-sm text-zinc-600">Coaches are the primary users who pre-register clients and input daily weigh-ins.</p>

        <form method="post" class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-3" novalidate>
            <?= csrf_field() ?>
            <div>
                <label class="mb-1 block text-sm font-semibold" for="coach_name">Name</label>
                <input id="coach_name" name="coach_name" type="text" value="<?= h($coachName) ?>" class="w-full rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm outline-none ring-molten/20 focus:border-molten focus:ring-4" required>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold" for="coach_email">Email</label>
                <input id="coach_email" name="coach_email" type="email" value="<?= h($coachEmail) ?>" class="w-full rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm outline-none ring-molten/20 focus:border-molten focus:ring-4" required>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold" for="coach_password">Password</label>
                <input id="coach_password" name="coach_password" type="password" class="w-full rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm outline-none ring-molten/20 focus:border-molten focus:ring-4" required>
            </div>
            <div class="md:col-span-3">
                <button type="submit" class="rounded-xl bg-molten px-4 py-3 text-sm font-extrabold text-white hover:bg-pumpkin">Create Coach</button>
            </div>
        </form>
    </div>
</main>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>
