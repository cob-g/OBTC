<?php

$root = dirname(__DIR__);

$GLOBALS['app_config'] = require $root . '/config/config.php';

require_once $root . '/app/helpers.php';

date_default_timezone_set((string) app_config('app.timezone', 'UTC'));

$secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

session_name((string) app_config('security.session_name', session_name()));
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_start();

require_once $root . '/app/db.php';
require_once $root . '/app/auth.php';
require_once $root . '/app/uploads.php';
require_once $root . '/app/bmi.php';
