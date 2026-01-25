<?php

function app_config($key, $default = null)
{
    $config = isset($GLOBALS['app_config']) ? $GLOBALS['app_config'] : [];

    $segments = explode('.', (string) $key);
    $value = $config;

    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }

    return $value;
}

function url($path = '')
{
    $base = rtrim((string) app_config('app.base_url', ''), '/');
    $path = '/' . ltrim((string) $path, '/');

    return $base . $path;
}

function url_with_query($path, $query)
{
    $u = url($path);
    if (!$query) {
        return $u;
    }
    return $u . (strpos($u, '?') !== false ? '&' : '?') . http_build_query($query);
}

function redirect($path)
{
    header('Location: ' . $path);
    exit;
}

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}

function csrf_verify()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return true;
    }

    $sent = isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : '';
    $expected = isset($_SESSION['csrf_token']) ? (string) $_SESSION['csrf_token'] : '';

    return $sent !== '' && $expected !== '' && hash_equals($expected, $sent);
}
