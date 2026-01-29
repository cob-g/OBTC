<?php
require __DIR__ . '/../app/bootstrap.php';
require_role(['coach', 'admin']);

$user = auth_user();
$clientId = (int) ($_GET['id'] ?? 0);
$photo = (string) ($_GET['photo'] ?? '');

$allowed = ['day1_front', 'day1_side', 'day10_front', 'day10_side'];
if ($clientId <= 0 || !in_array($photo, $allowed, true)) {
    http_response_code(404);
    exit;
}

$hasFullName = db_has_column('clients', 'full_name');
$hasDay10Front = db_has_column('clients', 'day10_front_photo_path');
$hasDay10Side = db_has_column('clients', 'day10_side_photo_path');

try {
    $select = $hasFullName
        ? 'SELECT coach_user_id, front_photo_path, side_photo_path' .
            ($hasDay10Front ? ', day10_front_photo_path' : '') .
            ($hasDay10Side ? ', day10_side_photo_path' : '') .
            ' FROM clients WHERE id = ? LIMIT 1'
        : 'SELECT coach_user_id, front_photo_path, side_photo_path' .
            ($hasDay10Front ? ', day10_front_photo_path' : '') .
            ($hasDay10Side ? ', day10_side_photo_path' : '') .
            ' FROM clients WHERE id = ? LIMIT 1';

    $stmt = db()->prepare($select);
    $stmt->execute([$clientId]);
    $client = $stmt->fetch();
} catch (Throwable $e) {
    $client = null;
}

if (!$client || ($user['role'] === 'coach' && (int) $client['coach_user_id'] !== (int) $user['id'])) {
    http_response_code(404);
    exit;
}

$path = '';
if ($photo === 'day1_front') {
    $path = (string) ($client['front_photo_path'] ?? '');
} elseif ($photo === 'day1_side') {
    $path = (string) ($client['side_photo_path'] ?? '');
} elseif ($photo === 'day10_front') {
    $path = (string) ($client['day10_front_photo_path'] ?? '');
} elseif ($photo === 'day10_side') {
    $path = (string) ($client['day10_side_photo_path'] ?? '');
}

if ($path === '' || !is_file($path)) {
    http_response_code(404);
    exit;
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($path);
if ($mime !== 'image/jpeg' && $mime !== 'image/png') {
    http_response_code(415);
    exit;
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($path));
header('Cache-Control: no-store, max-age=0');

readfile($path);
