<?php
require __DIR__ . '/../app/bootstrap.php';
require_role('coach');

$key = (string) ($_GET['key'] ?? '');
if ($key !== 'front' && $key !== 'side') {
    http_response_code(404);
    exit;
}

$base = project_root_path('storage/uploads/references');
$filename = $key === 'front' ? 'front_reference.png' : 'side_reference.png';
$path = $base . DIRECTORY_SEPARATOR . $filename;

if (!is_file($path)) {
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
