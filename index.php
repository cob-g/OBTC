<?php
require __DIR__ . '/app/bootstrap.php';

if (!auth_check()) {
    redirect(url('/auth/login.php'));
}

$user = auth_user();
if (!$user) {
    auth_logout();
    redirect(url('/auth/login.php'));
}

if ($user['role'] === 'coach') {
    redirect(url('/coach/dashboard.php'));
}

if ($user['role'] === 'admin') {
    redirect(url('/admin/overview.php'));
}

auth_logout();
redirect(url('/auth/login.php'));
