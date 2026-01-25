<?php
require __DIR__ . '/../app/bootstrap.php';
require_role('admin');

$page_title = 'Admin Profile';
require __DIR__ . '/../partials/layout_top.php';
require __DIR__ . '/../partials/nav.php';
$user = auth_user();
?>

<main class="mx-auto max-w-3xl px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold tracking-tight">Profile</h1>
        <p class="mt-1 text-sm text-zinc-600">Admin account details (to be implemented).</p>
    </div>

    <div class="rounded-2xl border border-orange-100 bg-white p-6">
        <div class="text-sm font-semibold text-zinc-700">Signed in as</div>
        <div class="mt-2 text-sm text-zinc-800"><?= h($user['name'] ?? '-') ?></div>
        <div class="mt-1 text-sm text-zinc-600"><?= h($user['email'] ?? '-') ?></div>
    </div>
</main>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>
