<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/data.php';
require_once __DIR__ . '/lib/util.php';

if (!fx_is_logged_in()) fx_json_response(array('ok' => false, 'error' => 'Not signed in.'));
$csrfHeader = isset($_SERVER['HTTP_X_CSRF']) ? $_SERVER['HTTP_X_CSRF'] : '';
if (!fx_csrf_check($csrfHeader)) fx_json_response(array('ok' => false, 'error' => 'Session expired.'));
if ($_SERVER['REQUEST_METHOD'] !== 'POST') fx_json_response(array('ok' => false, 'error' => 'Method not allowed.'));

if (empty($_FILES['file'])) fx_json_response(array('ok' => false, 'error' => 'No file received.'));
$kind = isset($_POST['kind']) ? $_POST['kind'] : '';
if ($kind !== 'image' && $kind !== 'video') fx_json_response(array('ok' => false, 'error' => 'Bad file kind.'));

$file = $_FILES['file'];
if ((isset($file['error']) ? $file['error'] : UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
    fx_json_response(array('ok' => false, 'error' => 'Upload error code ' . (int)$file['error']));
}

// Size cap.
$maxMb = ($kind === 'video') ? 30 : 5;
if ((int)$file['size'] > $maxMb * 1024 * 1024) {
    fx_json_response(array('ok' => false, 'error' => 'File too large. Max ' . $maxMb . ' MB.'));
}

// Extension + MIME whitelist.
$allowed = ($kind === 'video')
    ? array('mp4' => 'video/mp4')
    : array('jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp');

$origName = isset($file['name']) ? $file['name'] : 'upload';
$dot = strrpos($origName, '.');
if ($dot === false) fx_json_response(array('ok' => false, 'error' => 'File has no extension.'));
$ext = strtolower(substr($origName, $dot + 1));
if (!isset($allowed[$ext])) fx_json_response(array('ok' => false, 'error' => 'Unsupported file type. Allowed: ' . implode(', ', array_keys($allowed))));

// Verify actual MIME (finfo is more reliable than the browser-provided type).
$actualMime = '';
if (function_exists('finfo_open')) {
    $fi = finfo_open(FILEINFO_MIME_TYPE);
    if ($fi) { $actualMime = finfo_file($fi, $file['tmp_name']); finfo_close($fi); }
}
if ($actualMime !== '' && $actualMime !== $allowed[$ext]) {
    // Allow jpeg <-> jpg mismatch harmlessly.
    if (!($ext === 'jpg' && $actualMime === 'image/jpeg') && !($ext === 'jpeg' && $actualMime === 'image/jpeg')) {
        fx_json_response(array('ok' => false, 'error' => 'File contents do not match extension (' . $actualMime . ').'));
    }
}

// Destination filename: {timestamp}-{sanitized-basename}.{ext}
$sanitized = fx_sanitize_filename($origName);
if (!$sanitized) fx_json_response(array('ok' => false, 'error' => 'Invalid filename.'));
$finalName = fx_ts() . '-' . $sanitized;

// Destination folder.  Default is assets/uploads/ (the original behaviour,
// still used by the plain "Replace image/video" button).  The Media Library
// passes `dest` so an upload lands in whichever folder the user is browsing.
// `dest` is resolved against /assets/ and re-checked with realpath so a
// crafted "../../config" cannot escape.
$assetsRoot = realpath(fx_base_path() . '/assets');
$destRel    = isset($_POST['dest']) ? trim(str_replace('\\', '/', $_POST['dest']), '/') : '';

if ($destRel === '') {
    $targetDir = fx_uploads_dir();
    $relPrefix = 'uploads/';
} else {
    if (strpos($destRel, '..') !== false || strpos($destRel, "\0") !== false) {
        fx_json_response(array('ok' => false, 'error' => 'Bad destination folder.'));
    }
    $candidate = realpath($assetsRoot . '/' . $destRel);
    if ($candidate === false || !is_dir($candidate)
        || strpos($candidate, $assetsRoot . DIRECTORY_SEPARATOR) !== 0) {
        fx_json_response(array('ok' => false, 'error' => 'Destination folder not found.'));
    }
    $targetDir = $candidate;
    $relPrefix = $destRel . '/';
}

if (!is_dir($targetDir)) @mkdir($targetDir, 0755, true);
$targetPath = $targetDir . '/' . $finalName;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    fx_json_response(array('ok' => false, 'error' => 'Could not save uploaded file. Check folder permissions.'));
}
@chmod($targetPath, 0644);

$relativePath = $relPrefix . $finalName;
fx_json_response(array(
    'ok' => true,
    'filename' => $relativePath,
    'bytes' => (int)$file['size']
));
