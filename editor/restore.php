<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/data.php';
require_once __DIR__ . '/lib/util.php';

if (!fx_is_logged_in()) fx_json_response(array('ok' => false, 'error' => 'Not signed in.'));
$csrfHeader = isset($_SERVER['HTTP_X_CSRF']) ? $_SERVER['HTTP_X_CSRF'] : '';
if (!fx_csrf_check($csrfHeader)) fx_json_response(array('ok' => false, 'error' => 'Session expired.'));
if ($_SERVER['REQUEST_METHOD'] !== 'POST') fx_json_response(array('ok' => false, 'error' => 'Method not allowed.'));

$backupsDir = fx_backups_dir();
$backups = glob($backupsDir . '/index-*.html');
if (!is_array($backups) || empty($backups)) {
    fx_json_response(array('ok' => false, 'error' => 'No backups available yet. First publish will create one.'));
}
sort($backups);
$latest = end($backups);
if (!is_file($latest)) {
    fx_json_response(array('ok' => false, 'error' => 'Latest backup file missing.'));
}

$live = fx_live_index_path();

// Also back up the CURRENT live file first (in case the user wants to redo).
if (is_file($live)) {
    @copy($live, $backupsDir . '/index-pre-restore-' . fx_ts() . '.html');
}

$tmp = $live . '.tmp';
if (!@copy($latest, $tmp)) {
    fx_json_response(array('ok' => false, 'error' => 'Could not copy backup to temp file.'));
}
if (!@rename($tmp, $live)) {
    @unlink($tmp);
    fx_json_response(array('ok' => false, 'error' => 'Could not atomically replace index.html with backup.'));
}
fx_json_response(array(
    'ok' => true,
    'restored_from' => basename($latest),
));
