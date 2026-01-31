<?php
require __DIR__ . '/../app/bootstrap.php';
require_role('coach');

$user = auth_user();
$errors = [];
$success = null;

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Invalid session. Please refresh and try again.';
    } else {
        $action = (string) ($_POST['action'] ?? '');
        
        if ($action === 'update_profile') {
            $name = trim((string) ($_POST['name'] ?? ''));
            $email = trim((string) ($_POST['email'] ?? ''));
            
            if ($name === '') {
                $errors[] = 'Name is required.';
            } elseif (strlen($name) < 2) {
                $errors[] = 'Name must be at least 2 characters.';
            } elseif (strlen($name) > 100) {
                $errors[] = 'Name must not exceed 100 characters.';
            }
            
            if ($email === '') {
                $errors[] = 'Email is required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Please provide a valid email address.';
            } elseif (strlen($email) > 255) {
                $errors[] = 'Email must not exceed 255 characters.';
            }
            
            // Check if email is already taken by another user
            if (!$errors && $email !== $user['email']) {
                try {
                    $stmt = db()->prepare('SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1');
                    $stmt->execute([$email, (int) $user['id']]);
                    if ($stmt->fetchColumn()) {
                        $errors[] = 'This email is already in use by another account.';
                    }
                } catch (Throwable $e) {
                    $errors[] = 'Failed to validate email availability.';
                }
            }
            
            if (!$errors) {
                try {
                    $stmt = db()->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
                    $stmt->execute([$name, $email, (int) $user['id']]);
                    
                    // Refresh user data in session
                    $_SESSION['user']['name'] = $name;
                    $_SESSION['user']['email'] = $email;
                    $user = auth_user();
                    
                    $success = 'Profile updated successfully.';
                } catch (Throwable $e) {
                    $errors[] = 'Failed to update profile. Please try again.';
                }
            }
        }
        
        if ($action === 'change_password') {
            $currentPassword = (string) ($_POST['current_password'] ?? '');
            $newPassword = (string) ($_POST['new_password'] ?? '');
            $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
            
            if ($currentPassword === '') {
                $errors[] = 'Current password is required.';
            }
            
            if ($newPassword === '') {
                $errors[] = 'New password is required.';
            } elseif (strlen($newPassword) < 8) {
                $errors[] = 'New password must be at least 8 characters.';
            } elseif (strlen($newPassword) > 255) {
                $errors[] = 'New password must not exceed 255 characters.';
            }
            
            if ($confirmPassword === '') {
                $errors[] = 'Please confirm your new password.';
            } elseif ($newPassword !== $confirmPassword) {
                $errors[] = 'New password and confirmation do not match.';
            }
            
            // Verify current password
            if (!$errors) {
                try {
                    $stmt = db()->prepare('SELECT password FROM users WHERE id = ? LIMIT 1');
                    $stmt->execute([(int) $user['id']]);
                    $row = $stmt->fetch();
                    
                    if (!$row || !password_verify($currentPassword, (string) $row['password'])) {
                        $errors[] = 'Current password is incorrect.';
                    }
                } catch (Throwable $e) {
                    $errors[] = 'Failed to verify current password.';
                }
            }
            
            if (!$errors) {
                try {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = db()->prepare('UPDATE users SET password = ? WHERE id = ?');
                    $stmt->execute([$hashedPassword, (int) $user['id']]);
                    
                    $success = 'Password changed successfully.';
                } catch (Throwable $e) {
                    $errors[] = 'Failed to update password. Please try again.';
                }
            }
        }
    }
}

$page_title = 'Profile';
require __DIR__ . '/../partials/layout_top.php';
require __DIR__ . '/../partials/nav.php';
?>

<main class="mx-auto max-w-4xl px-4 py-8">
    <!-- Hero Header -->
    <div class="mb-6 rounded-2xl border border-orange-100 bg-gradient-to-r from-orange-50 to-amber-50 p-6 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-molten to-pumpkin text-white font-bold text-2xl shadow-lg">
                <?= strtoupper(substr($user['name'] ?? 'C', 0, 1)) ?>
            </div>
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-zinc-900">Coach Profile</h1>
                <p class="mt-1 flex items-center gap-2 text-sm text-zinc-600">
                    <svg class="h-4 w-4 text-molten" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Manage your account settings
                </p>
            </div>
        </div>
    </div>

    <!-- Success Alert -->
    <?php if ($success): ?>
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-gradient-to-r from-emerald-50 to-green-50 p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100">
                    <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div class="text-sm font-bold text-emerald-800"><?= h($success) ?></div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Error Alert -->
    <?php if ($errors): ?>
        <div class="mb-6 rounded-2xl border border-red-200 bg-gradient-to-r from-red-50 to-rose-50 p-5 shadow-sm">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-red-100">
                    <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="text-sm font-extrabold text-red-800">Please fix the following:</div>
                    <ul class="mt-2 space-y-1 text-sm text-red-700">
                        <?php foreach ($errors as $e): ?>
                            <li class="flex items-start gap-2">
                                <span class="mt-1.5 h-1 w-1 flex-shrink-0 rounded-full bg-red-400"></span>
                                <span><?= h((string) $e) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Profile Information Card -->
        <div class="rounded-2xl border border-orange-100 bg-white p-6 shadow-sm">
            <div class="flex items-center gap-3 pb-4 border-b border-orange-100">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-amber-100 to-orange-100">
                    <svg class="h-5 w-5 text-molten" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-lg font-extrabold tracking-tight text-zinc-900">Profile Information</div>
                    <div class="mt-0.5 text-sm text-zinc-500">Update your personal details</div>
                </div>
            </div>

            <form method="post" class="mt-6 space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_profile" />

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-zinc-600">Full Name</label>
                    <input type="text" name="name" value="<?= h($user['name'] ?? '') ?>" class="mt-2 block w-full rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm text-zinc-700 transition focus:border-molten focus:outline-none focus:ring-2 focus:ring-orange-200" required />
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-zinc-600">Email Address</label>
                    <input type="email" name="email" value="<?= h($user['email'] ?? '') ?>" class="mt-2 block w-full rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm text-zinc-700 transition focus:border-molten focus:outline-none focus:ring-2 focus:ring-orange-200" required />
                </div>

                <div class="flex items-center gap-2 rounded-xl bg-blue-50 border border-blue-100 p-3">
                    <svg class="h-5 w-5 flex-shrink-0 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-xs text-blue-700">Your role is <strong><?= h($user['role'] ?? 'coach') ?></strong> and cannot be changed here.</p>
                </div>

                <div class="pt-2">
                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-molten to-pumpkin px-6 py-3 text-sm font-bold text-white shadow-md shadow-orange-200/50 transition hover:shadow-lg hover:scale-[1.02] active:scale-[0.98]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Update Profile
                    </button>
                </div>
            </form>
        </div>

        <!-- Change Password Card -->
        <div class="rounded-2xl border border-orange-100 bg-white p-6 shadow-sm">
            <div class="flex items-center gap-3 pb-4 border-b border-orange-100">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-amber-100 to-orange-100">
                    <svg class="h-5 w-5 text-molten" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-lg font-extrabold tracking-tight text-zinc-900">Change Password</div>
                    <div class="mt-0.5 text-sm text-zinc-500">Update your account password</div>
                </div>
            </div>

            <form method="post" class="mt-6 space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="change_password" />

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-zinc-600">Current Password</label>
                    <input type="password" name="current_password" class="mt-2 block w-full rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm text-zinc-700 transition focus:border-molten focus:outline-none focus:ring-2 focus:ring-orange-200" required />
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-zinc-600">New Password</label>
                    <input type="password" name="new_password" class="mt-2 block w-full rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm text-zinc-700 transition focus:border-molten focus:outline-none focus:ring-2 focus:ring-orange-200" required />
                    <p class="mt-1 text-xs text-zinc-500">Minimum 8 characters</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-zinc-600">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="mt-2 block w-full rounded-xl border border-orange-100 bg-white px-4 py-3 text-sm text-zinc-700 transition focus:border-molten focus:outline-none focus:ring-2 focus:ring-orange-200" required />
                </div>

                <div class="flex items-start gap-2 rounded-xl bg-amber-50 border border-amber-200 p-3">
                    <svg class="h-5 w-5 flex-shrink-0 text-amber-600 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <p class="text-xs text-amber-700">Make sure your new password is strong and unique.</p>
                </div>

                <div class="pt-2">
                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-molten to-pumpkin px-6 py-3 text-sm font-bold text-white shadow-md shadow-orange-200/50 transition hover:shadow-lg hover:scale-[1.02] active:scale-[0.98]">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Change Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Account Info Card -->
    <div class="mt-6 rounded-2xl border border-orange-100 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-3 pb-4 border-b border-orange-100">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-amber-100 to-orange-100">
                <svg class="h-5 w-5 text-molten" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <div class="text-lg font-extrabold tracking-tight text-zinc-900">Account Information</div>
                <div class="mt-0.5 text-sm text-zinc-500">Your current account details</div>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-blue-100 bg-gradient-to-br from-blue-50 to-indigo-50 p-4">
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-blue-600">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Name
                </div>
                <div class="mt-2 text-sm font-bold text-blue-900"><?= h($user['name'] ?? 'N/A') ?></div>
            </div>

            <div class="rounded-xl border border-purple-100 bg-gradient-to-br from-purple-50 to-pink-50 p-4">
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-purple-600">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Email
                </div>
                <div class="mt-2 text-sm font-bold text-purple-900 truncate"><?= h($user['email'] ?? 'N/A') ?></div>
            </div>

            <div class="rounded-xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-green-50 p-4">
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide text-emerald-600">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                    Role
                </div>
                <div class="mt-2 text-sm font-bold text-emerald-900 capitalize"><?= h($user['role'] ?? 'coach') ?></div>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>
