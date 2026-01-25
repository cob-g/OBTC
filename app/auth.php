<?php

function auth_user()
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $stmt = db()->prepare('SELECT id, name, email, role FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function auth_check()
{
    return !empty($_SESSION['user_id']);
}

function require_login()
{
    if (!auth_check()) {
        redirect(url('/auth/login.php'));
    }
}

function require_role($roles)
{
    require_login();

    $user = auth_user();
    if (!$user) {
        auth_logout();
        redirect(url('/auth/login.php'));
    }

    $allowed = is_array($roles) ? $roles : [$roles];
    if (!in_array($user['role'], $allowed, true)) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
}

function auth_login($email, $password)
{
    $stmt = db()->prepare('SELECT id, name, email, role, password_hash FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([(string) $email]);
    $user = $stmt->fetch();

    if (!$user) {
        return false;
    }

    if (!password_verify((string) $password, (string) $user['password_hash'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];

    return true;
}

function auth_logout()
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}
