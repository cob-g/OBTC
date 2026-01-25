<?php

function ensure_dir($dir)
{
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
}

function project_root_path($path = '')
{
    $root = dirname(__DIR__);
    $path = ltrim((string) $path, '/\\');

    return $root . DIRECTORY_SEPARATOR . $path;
}

function store_uploaded_image($file, $destinationDir, $destinationFilename)
{
    if (!isset($file) || !is_array($file)) {
        return [false, 'Missing file.'];
    }

    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return [false, 'Upload failed.'];
    }

    $maxBytes = 10 * 1024 * 1024;
    if (!isset($file['size']) || (int) $file['size'] > $maxBytes) {
        return [false, 'File must be 10MB or less.'];
    }

    $tmp = (string) ($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        return [false, 'Invalid upload.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];

    if (!isset($allowed[$mime])) {
        return [false, 'Only JPG and PNG images are allowed.'];
    }

    ensure_dir($destinationDir);

    $filename = $destinationFilename . '.' . $allowed[$mime];
    $fullPath = rtrim($destinationDir, '/\\') . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($tmp, $fullPath)) {
        return [false, 'Failed to save uploaded file.'];
    }

    return [true, $fullPath];
}
