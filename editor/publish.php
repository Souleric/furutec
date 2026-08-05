<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/data.php';
require_once __DIR__ . '/lib/render.php';
require_once __DIR__ . '/lib/util.php';

if (!fx_is_logged_in()) fx_json_response(array('ok' => false, 'error' => 'Not signed in.'));
$csrfHeader = isset($_SERVER['HTTP_X_CSRF']) ? $_SERVER['HTTP_X_CSRF'] : '';
if (!fx_csrf_check($csrfHeader)) fx_json_response(array('ok' => false, 'error' => 'Session expired. Refresh the editor and sign in again.'));
if ($_SERVER['REQUEST_METHOD'] !== 'POST') fx_json_response(array('ok' => false, 'error' => 'Method not allowed.'));

// 1) Load draft content and render.
$content = fx_load_content();
$errors  = array();
$rendered = fx_render($content, $errors);
if ($rendered === null) {
    fx_json_response(array('ok' => false, 'error' => 'Render refused. ' . implode(' | ', $errors)));
}
if (strlen($rendered) < 10000) {
    // Sanity: our real homepage is 300 KB+. Anything much smaller means the
    // template or content is broken.  Refuse to overwrite the live file.
    fx_json_response(array('ok' => false, 'error' => 'Render is suspiciously small (' . strlen($rendered) . ' bytes). Aborted.'));
}

// 2) Back up the current live index.html.
$live = fx_live_index_path();
$backupsDir = fx_backups_dir();
if (!is_dir($backupsDir)) @mkdir($backupsDir, 0755, true);
$backupOk = true;
if (is_file($live)) {
    $backupPath = $backupsDir . '/index-' . fx_ts() . '.html';
    if (!@copy($live, $backupPath)) $backupOk = false;
}
if (!$backupOk) fx_json_response(array('ok' => false, 'error' => 'Backup failed. Aborted — live file untouched.'));

// 3) Write rendered HTML atomically.
$tmp = $live . '.tmp';
if (file_put_contents($tmp, $rendered, LOCK_EX) === false) {
    fx_json_response(array('ok' => false, 'error' => 'Could not write temp file. Aborted — live file untouched.'));
}
if (!@rename($tmp, $live)) {
    @unlink($tmp);
    fx_json_response(array('ok' => false, 'error' => 'Could not atomically replace live index.html.'));
}

// 4) Trim old backups: keep the newest 20.
$backups = glob($backupsDir . '/index-*.html');
if (is_array($backups) && count($backups) > 20) {
    sort($backups);
    $toDelete = array_slice($backups, 0, count($backups) - 20);
    foreach ($toDelete as $b) @unlink($b);
}

fx_json_response(array(
    'ok' => true,
    'published_at' => date('c'),
    'bytes' => strlen($rendered)
));
