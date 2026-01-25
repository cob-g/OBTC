<?php
require __DIR__ . '/../app/bootstrap.php';
require_role('admin');

function ensure_users_is_active_column()
{
    if (db_has_column('users', 'is_active')) {
        return;
    }

    try {
        db()->exec('ALTER TABLE users ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1');
    } catch (Throwable $e) {
        // ignore
    }
}

ensure_users_is_active_column();

$error = '';
$success = '';

$flash = (string) ($_GET['success'] ?? '');
if ($flash === 'create') {
    $success = 'Coach account created.';
}
if ($flash === 'reset') {
    $success = 'Coach password updated.';
}
if ($flash === 'deactivate') {
    $success = 'Coach account deactivated.';
}
if ($flash === 'activate') {
    $success = 'Coach account reactivated.';
}

$coachName = '';
$coachEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Invalid session. Please refresh and try again.';
    } else {
        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'create_coach') {
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
                    $insert = db()->prepare('INSERT INTO users (name, email, role, password_hash, is_active) VALUES (?, ?, ?, ?, 1)');
                    $insert->execute([$coachName, $coachEmail, 'coach', $hash]);
                    redirect(url('/admin/settings.php?success=create'));
                } catch (Throwable $e) {
                    $error = 'Unable to create coach account (email may already exist).';
                }
            }
        }

        if ($action === 'reset_password') {
            $coachId = (int) ($_POST['coach_id'] ?? 0);
            $password = (string) ($_POST['new_password'] ?? '');

            if ($coachId < 1) {
                $error = 'Invalid coach selected.';
            } elseif (strlen($password) < 8) {
                $error = 'Password must be at least 8 characters.';
            } else {
                try {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $upd = db()->prepare("UPDATE users SET password_hash = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND role = 'coach' LIMIT 1");
                    $upd->execute([$hash, $coachId]);
                    redirect(url('/admin/settings.php?success=reset'));
                } catch (Throwable $e) {
                    $error = 'Unable to reset coach password.';
                }
            }
        }

        if ($action === 'deactivate' || $action === 'activate') {
            $coachId = (int) ($_POST['coach_id'] ?? 0);
            $newValue = $action === 'activate' ? 1 : 0;
            if ($coachId < 1) {
                $error = 'Invalid coach selected.';
            } else {
                try {
                    $upd = db()->prepare("UPDATE users SET is_active = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND role = 'coach' LIMIT 1");
                    $upd->execute([(int) $newValue, $coachId]);
                    redirect(url('/admin/settings.php?success=' . ($newValue === 1 ? 'activate' : 'deactivate')));
                } catch (Throwable $e) {
                    $error = 'Unable to update coach status.';
                }
            }
        }
    }
}

$coaches = [];
try {
    $select = 'SELECT id, name, email' . (db_has_column('users', 'is_active') ? ', is_active' : '') . ', created_at FROM users WHERE role = ? ORDER BY created_at DESC, id DESC';
    $stmt = db()->prepare($select);
    $stmt->execute(['coach']);
    $coaches = $stmt->fetchAll();
} catch (Throwable $e) {
    $coaches = [];
}

$page_title = 'Settings';
require __DIR__ . '/../partials/layout_top.php';
require __DIR__ . '/../partials/nav.php';
?>

<main class="mx-auto max-w-6xl px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold tracking-tight">Settings</h1>
        <p class="mt-1 text-sm text-zinc-600">Manage coach accounts.</p>
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
            <input type="hidden" name="action" value="create_coach" />
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

    <div class="mt-6 rounded-2xl border border-orange-100 bg-white p-6">
        <div class="text-lg font-extrabold tracking-tight">Coach Accounts</div>
        <p class="mt-1 text-sm text-zinc-600">Reset passwords and deactivate/reactivate coach access.</p>

        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-orange-100 text-xs font-extrabold uppercase tracking-wide text-zinc-600">
                        <th class="py-3 pr-4">Name</th>
                        <th class="py-3 pr-4">Email</th>
                        <th class="py-3 pr-4">Status</th>
                        <th class="py-3 pr-0">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$coaches): ?>
                        <tr>
                            <td colspan="4" class="py-6 text-zinc-600">No coach accounts found.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($coaches as $c): ?>
                        <?php $active = !db_has_column('users', 'is_active') ? true : ((int) ($c['is_active'] ?? 1) === 1); ?>
                        <tr class="border-b border-orange-50">
                            <td class="py-4 pr-4 font-extrabold text-zinc-900"><?= h((string) $c['name']) ?></td>
                            <td class="py-4 pr-4 text-zinc-700"><?= h((string) $c['email']) ?></td>
                            <td class="py-4 pr-4">
                                <?php if ($active): ?>
                                    <span class="inline-flex rounded-lg bg-green-100 px-2 py-1 text-xs font-extrabold text-green-800">Active</span>
                                <?php else: ?>
                                    <span class="inline-flex rounded-lg bg-red-100 px-2 py-1 text-xs font-extrabold text-red-800">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 pr-0">
                                <form method="post" class="mb-2 flex flex-wrap items-center gap-2" novalidate>
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="reset_password" />
                                    <input type="hidden" name="coach_id" value="<?= (int) $c['id'] ?>" />
                                    <input name="new_password" type="password" placeholder="New password" class="w-44 rounded-xl border border-orange-100 bg-white px-3 py-2 text-xs font-extrabold text-zinc-700 outline-none ring-molten/20 focus:border-molten focus:ring-4" required />
                                    <button type="submit" class="rounded-xl border border-orange-100 bg-white px-3 py-2 text-xs font-extrabold text-zinc-700 hover:bg-orange-50">Reset</button>
                                </form>

                                <?php if (db_has_column('users', 'is_active')): ?>
                                    <?php if ($active): ?>
                                        <form method="post" class="inline" onsubmit="return confirm('Deactivate this coach account? They will not be able to log in.')">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="deactivate" />
                                            <input type="hidden" name="coach_id" value="<?= (int) $c['id'] ?>" />
                                            <button type="submit" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-extrabold text-red-800 hover:bg-red-100">Deactivate</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="post" class="inline" onsubmit="return confirm('Reactivate this coach account?')">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="activate" />
                                            <input type="hidden" name="coach_id" value="<?= (int) $c['id'] ?>" />
                                            <button type="submit" class="rounded-xl border border-green-200 bg-green-50 px-3 py-2 text-xs font-extrabold text-green-800 hover:bg-green-100">Reactivate</button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>
