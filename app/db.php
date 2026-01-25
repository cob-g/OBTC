<?php

function db()
{
    static $pdo;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = (string) app_config('db.host');
    $port = (int) app_config('db.port');
    $database = (string) app_config('db.database');
    $username = (string) app_config('db.username');
    $password = (string) app_config('db.password');
    $charset = (string) app_config('db.charset', 'utf8mb4');

    $dsn = 'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $database . ';charset=' . $charset;

    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return $pdo;
}

function db_has_column($table, $column)
{
    try {
        $database = (string) app_config('db.database');
        $stmt = db()->prepare('SELECT COUNT(*) AS c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?');
        $stmt->execute([$database, (string) $table, (string) $column]);
        $row = $stmt->fetch();
        return $row ? ((int) $row['c'] > 0) : false;
    } catch (Throwable $e) {
        return false;
    }
}
