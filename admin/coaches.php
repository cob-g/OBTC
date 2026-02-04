<?php
require __DIR__ . '/../app/bootstrap.php';
require_role('admin');

$errors = [];
$success = null;

if (isset($_GET['msg'])) {
    $msg = (string) $_GET['msg'];
    if ($msg === 'activated') {
        $success = 'Coach account activated.';
    } elseif ($msg === 'deactivated') {
        $success = 'Coach account deactivated.';
    } elseif ($msg === 'deleted') {
        $success = 'Coach deleted successfully.';
    } elseif ($msg === 'delete_failed') {
        $errors[] = 'Failed to delete coach. They may have associated clients.';
    } elseif ($msg === 'not_found') {
        $errors[] = 'Coach not found.';
    } elseif ($msg === 'added') {
        $success = 'New coach added successfully.';
    } elseif ($msg === 'add_failed') {
        $errors[] = 'Failed to add coach. Email may already exist.';
    } elseif ($msg === 'updated') {
        $success = 'Coach updated successfully.';
    }
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $errors[] = 'Invalid session. Please refresh and try again.';
    } else {
        $action = (string) ($_POST['action'] ?? '');
        
        // Toggle active status
        if ($action === 'toggle_active') {
            $coachId = (int) ($_POST['coach_id'] ?? 0);
            $newStatus = (int) ($_POST['new_status'] ?? 0);
            
            if ($coachId <= 0) {
                redirect(url('/admin/coaches.php?msg=not_found'));
            }
            
            try {
                $stmt = db()->prepare('UPDATE users SET is_active = ? WHERE id = ? AND role = ?');
                $stmt->execute([$newStatus, $coachId, 'coach']);
                redirect(url('/admin/coaches.php?msg=' . ($newStatus ? 'activated' : 'deactivated')));
            } catch (Throwable $e) {
                $errors[] = 'Failed to update coach status.';
            }
        }
        
        // Delete coach
        if ($action === 'delete_coach') {
            $coachId = (int) ($_POST['coach_id'] ?? 0);
            
            if ($coachId <= 0) {
                redirect(url('/admin/coaches.php?msg=not_found'));
            }
            
            try {
                // Check if coach has clients
                $stmt = db()->prepare('SELECT COUNT(*) FROM clients WHERE coach_user_id = ?');
                $stmt->execute([$coachId]);
                $clientCount = (int) $stmt->fetchColumn();
                
                if ($clientCount > 0) {
                    redirect(url('/admin/coaches.php?msg=delete_failed'));
                }
                
                $del = db()->prepare('DELETE FROM users WHERE id = ? AND role = ?');
                $del->execute([$coachId, 'coach']);
                redirect(url('/admin/coaches.php?msg=deleted'));
            } catch (Throwable $e) {
                redirect(url('/admin/coaches.php?msg=delete_failed'));
            }
        }
        
        // Add new coach
        if ($action === 'add_coach') {
            $name = trim((string) ($_POST['name'] ?? ''));
            $email = trim((string) ($_POST['email'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            
            if ($name === '' || $email === '' || $password === '') {
                $errors[] = 'All fields are required.';
            } elseif (strlen($password) < 6) {
                $errors[] = 'Password must be at least 6 characters.';
            } else {
                try {
                    // Check if email exists
                    $stmt = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
                    $stmt->execute([$email]);
                    if ($stmt->fetch()) {
                        redirect(url('/admin/coaches.php?msg=add_failed'));
                    }
                    
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = db()->prepare('INSERT INTO users (name, email, password_hash, role, is_active) VALUES (?, ?, ?, ?, 1)');
                    $stmt->execute([$name, $email, $hash, 'coach']);
                    redirect(url('/admin/coaches.php?msg=added'));
                } catch (Throwable $e) {
                    redirect(url('/admin/coaches.php?msg=add_failed'));
                }
            }
        }
        
        // Update coach
        if ($action === 'update_coach') {
            $coachId = (int) ($_POST['coach_id'] ?? 0);
            $name = trim((string) ($_POST['name'] ?? ''));
            $email = trim((string) ($_POST['email'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            
            if ($coachId <= 0) {
                redirect(url('/admin/coaches.php?msg=not_found'));
            }
            
            if ($name === '' || $email === '') {
                $errors[] = 'Name and email are required.';
            } else {
                try {
                    // Check if email exists for another user
                    $stmt = db()->prepare('SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1');
                    $stmt->execute([$email, $coachId]);
                    if ($stmt->fetch()) {
                        $errors[] = 'Email already exists for another user.';
                    } else {
                        if ($password !== '' && strlen($password) >= 6) {
                            $hash = password_hash($password, PASSWORD_DEFAULT);
                            $stmt = db()->prepare('UPDATE users SET name = ?, email = ?, password_hash = ? WHERE id = ? AND role = ?');
                            $stmt->execute([$name, $email, $hash, $coachId, 'coach']);
                        } else {
                            $stmt = db()->prepare('UPDATE users SET name = ?, email = ? WHERE id = ? AND role = ?');
                            $stmt->execute([$name, $email, $coachId, 'coach']);
                        }
                        redirect(url('/admin/coaches.php?msg=updated'));
                    }
                } catch (Throwable $e) {
                    $errors[] = 'Failed to update coach.';
                }
            }
        }
    }
}

// Fetch all coaches with their stats
$coaches = [];
try {
    $sql = 'SELECT u.id, u.name, u.email, u.is_active, u.created_at,
            (SELECT COUNT(*) FROM clients WHERE coach_user_id = u.id) AS client_count
            FROM users u
            WHERE u.role = ?
            ORDER BY u.created_at DESC, u.id DESC';
    $stmt = db()->prepare($sql);
    $stmt->execute(['coach']);
    $coaches = $stmt->fetchAll();
} catch (Throwable $e) {
    $coaches = [];
}

$page_title = 'Coaches Management';
require __DIR__ . '/../partials/layout_top.php';
require __DIR__ . '/../partials/nav.php';
?>

<main class="mx-auto max-w-6xl px-4 py-8">
    <!-- Page Header with Gradient Banner -->
    <div class="relative mb-8 overflow-hidden rounded-2xl bg-gradient-to-r from-indigo_bloom via-purple-600 to-indigo-800 p-6 shadow-lg shadow-indigo-300/30">
        <div class="absolute -right-8 -top-8 h-40 w-40 rounded-full bg-white/5"></div>
        <div class="absolute -bottom-6 -right-16 h-32 w-32 rounded-full bg-white/5"></div>
        <div class="relative flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/10 backdrop-blur-sm">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h1 class="text-2xl font-extrabold tracking-tight text-white sm:text-3xl">Coaches Management</h1>
                <p class="mt-1 text-sm text-white/70">Manage coach accounts and view their performance</p>
            </div>
            <button onclick="document.getElementById('addCoachModal').classList.remove('hidden')" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-indigo_bloom hover:bg-indigo-50 transition-colors shadow-lg">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add Coach
            </button>
        </div>
    </div>

    <!-- Success Alert -->
    <?php if ($success): ?>
        <div class="mb-6 flex items-center gap-3 rounded-2xl border border-green-200 bg-gradient-to-r from-green-50 to-emerald-50 p-4 shadow-sm">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-500/10">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <div class="text-sm font-bold text-green-800"><?= h($success) ?></div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Error Alert -->
    <?php if (!empty($errors)): ?>
        <div class="mb-6 flex items-start gap-3 rounded-2xl border border-red-200 bg-gradient-to-r from-red-50 to-rose-50 p-4 shadow-sm">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-500/10 flex-shrink-0">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <?php foreach ($errors as $e): ?>
                    <div class="text-sm font-bold text-red-800"><?= h($e) ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 text-indigo_bloom">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500">Total Coaches</p>
                    <p class="text-2xl font-black text-slate-900"><?= count($coaches) ?></p>
                </div>
            </div>
        </div>
        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-100 text-green-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500">Active</p>
                    <p class="text-2xl font-black text-slate-900"><?= count(array_filter($coaches, fn($c) => (int) $c['is_active'] === 1)) ?></p>
                </div>
            </div>
        </div>
        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-100 text-red-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500">Inactive</p>
                    <p class="text-2xl font-black text-slate-900"><?= count(array_filter($coaches, fn($c) => (int) $c['is_active'] === 0)) ?></p>
                </div>
            </div>
        </div>
        <div class="rounded-2xl bg-white border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500">Total Clients</p>
                    <p class="text-2xl font-black text-slate-900"><?= array_sum(array_column($coaches, 'client_count')) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Coaches Table -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
            <h2 class="text-lg font-bold text-slate-900">All Coaches</h2>
        </div>
        
        <?php if (empty($coaches)): ?>
            <div class="p-12 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100">
                    <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <p class="mt-4 text-lg font-semibold text-slate-700">No coaches found</p>
                <p class="mt-1 text-sm text-slate-500">Get started by adding your first coach.</p>
                <button onclick="document.getElementById('addCoachModal').classList.remove('hidden')" class="mt-4 inline-flex items-center justify-center gap-2 rounded-lg bg-indigo_bloom px-4 py-2 text-sm font-bold text-white hover:bg-indigo-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Add First Coach
                </button>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                            <th class="px-6 py-4">Coach</th>
                            <th class="px-6 py-4">Email</th>
                            <th class="px-6 py-4 text-center">Clients</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4">Joined</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($coaches as $coach): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-indigo_bloom to-purple-600 text-white font-bold text-sm">
                                            <?= h(strtoupper(substr($coach['name'], 0, 1))) ?>
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900"><?= h($coach['name']) ?></div>
                                            <div class="text-xs text-slate-500">ID: <?= (int) $coach['id'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-slate-600"><?= h($coach['email']) ?></span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center rounded-full bg-indigo-100 px-3 py-1 text-sm font-bold text-indigo_bloom">
                                        <?= (int) $coach['client_count'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <?php if ((int) $coach['is_active'] === 1): ?>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                            Active
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                            Inactive
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-slate-600"><?= h(date('M d, Y', strtotime($coach['created_at']))) ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Edit Button -->
                                        <button onclick="openEditModal(<?= (int) $coach['id'] ?>, '<?= h(addslashes($coach['name'])) ?>', '<?= h(addslashes($coach['email'])) ?>')" class="rounded-lg p-2 text-slate-400 hover:bg-indigo-50 hover:text-indigo_bloom transition-colors" title="Edit">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        
                                        <!-- Toggle Status -->
                                        <form method="POST" class="inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="toggle_active">
                                            <input type="hidden" name="coach_id" value="<?= (int) $coach['id'] ?>">
                                            <input type="hidden" name="new_status" value="<?= (int) $coach['is_active'] === 1 ? 0 : 1 ?>">
                                            <button type="submit" class="rounded-lg p-2 <?= (int) $coach['is_active'] === 1 ? 'text-slate-400 hover:bg-red-50 hover:text-red-600' : 'text-slate-400 hover:bg-green-50 hover:text-green-600' ?> transition-colors" title="<?= (int) $coach['is_active'] === 1 ? 'Deactivate' : 'Activate' ?>">
                                                <?php if ((int) $coach['is_active'] === 1): ?>
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                    </svg>
                                                <?php else: ?>
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                <?php endif; ?>
                                            </button>
                                        </form>
                                        
                                        <!-- Delete Button -->
                                        <?php if ((int) $coach['client_count'] === 0): ?>
                                            <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this coach?')">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="delete_coach">
                                                <input type="hidden" name="coach_id" value="<?= (int) $coach['id'] ?>">
                                                <button type="submit" class="rounded-lg p-2 text-slate-400 hover:bg-red-50 hover:text-red-600 transition-colors" title="Delete">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="rounded-lg p-2 text-slate-300 cursor-not-allowed" title="Cannot delete: has clients">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Add Coach Modal -->
<div id="addCoachModal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="document.getElementById('addCoachModal').classList.add('hidden')"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
            <button onclick="document.getElementById('addCoachModal').classList.add('hidden')" class="absolute right-4 top-4 rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <h3 class="text-xl font-bold text-slate-900">Add New Coach</h3>
            <p class="mt-1 text-sm text-slate-500">Create a new coach account</p>
            
            <form method="POST" class="mt-6 space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add_coach">
                
                <div>
                    <label class="block text-sm font-bold text-slate-700">Name</label>
                    <input type="text" name="name" required class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo_bloom focus:ring-2 focus:ring-indigo_bloom/20">
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-slate-700">Email</label>
                    <input type="email" name="email" required class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo_bloom focus:ring-2 focus:ring-indigo_bloom/20">
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-slate-700">Password</label>
                    <input type="password" name="password" required minlength="6" class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo_bloom focus:ring-2 focus:ring-indigo_bloom/20">
                    <p class="mt-1 text-xs text-slate-500">Minimum 6 characters</p>
                </div>
                
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="document.getElementById('addCoachModal').classList.add('hidden')" class="flex-1 rounded-xl border border-slate-300 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="flex-1 rounded-xl bg-indigo_bloom px-4 py-3 text-sm font-bold text-white hover:bg-indigo-700">Add Coach</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Coach Modal -->
<div id="editCoachModal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="document.getElementById('editCoachModal').classList.add('hidden')"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
            <button onclick="document.getElementById('editCoachModal').classList.add('hidden')" class="absolute right-4 top-4 rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <h3 class="text-xl font-bold text-slate-900">Edit Coach</h3>
            <p class="mt-1 text-sm text-slate-500">Update coach information</p>
            
            <form method="POST" class="mt-6 space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_coach">
                <input type="hidden" name="coach_id" id="edit_coach_id">
                
                <div>
                    <label class="block text-sm font-bold text-slate-700">Name</label>
                    <input type="text" name="name" id="edit_name" required class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo_bloom focus:ring-2 focus:ring-indigo_bloom/20">
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-slate-700">Email</label>
                    <input type="email" name="email" id="edit_email" required class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo_bloom focus:ring-2 focus:ring-indigo_bloom/20">
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-slate-700">New Password <span class="font-normal text-slate-400">(optional)</span></label>
                    <input type="password" name="password" minlength="6" class="mt-1 block w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo_bloom focus:ring-2 focus:ring-indigo_bloom/20" placeholder="Leave blank to keep current">
                    <p class="mt-1 text-xs text-slate-500">Minimum 6 characters if changing</p>
                </div>
                
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="document.getElementById('editCoachModal').classList.add('hidden')" class="flex-1 rounded-xl border border-slate-300 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="flex-1 rounded-xl bg-indigo_bloom px-4 py-3 text-sm font-bold text-white hover:bg-indigo-700">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditModal(id, name, email) {
    document.getElementById('edit_coach_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_email').value = email;
    document.getElementById('editCoachModal').classList.remove('hidden');
}
</script>

<?php require __DIR__ . '/../partials/layout_bottom.php'; ?>
