<?php
/**
 * Main Entry Point
 * 
 * - Unauthenticated users: See the public landing page
 * - Authenticated users: Redirect to their respective dashboards
 */
require __DIR__ . '/app/bootstrap.php';

// If not authenticated, show the public landing page
if (!auth_check()) {
    require __DIR__ . '/landing.php';
    exit;
}

// Authenticated users - redirect based on role
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

// Unknown role - logout and redirect to login
auth_logout();
redirect(url('/auth/login.php'));
