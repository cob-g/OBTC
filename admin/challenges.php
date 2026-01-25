<?php
require __DIR__ . '/../app/bootstrap.php';
require_role('admin');

$page_title = 'Challenges';
require __DIR__ . '/../partials/layout_top.php';
require __DIR__ . '/../partials/nav.php';
?>

<main class="mx-auto max-w-6xl px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold tracking-tight">Challenges</h1>
        <p class="mt-1 text-sm text-zinc-600">Manage challenge cycles by resetting (archive) and reviewing reports.</p>
    </div>

    <div class="rounded-2xl border border-orange-100 bg-white p-6">
        <div class="text-sm font-semibold text-zinc-700">Status</div>
        <div class="mt-2 text-sm text-zinc-600">Use <span class="font-extrabold">Data Backup</span> to reset/archive the current challenge, then view past cycles in <span class="font-extrabold">Reports</span>.</div>
    </div>
</main>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>
