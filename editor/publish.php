<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/data.php';
require_once __DIR__ . '/lib/render.php';
require_once __DIR__ . '/lib/util.php';

if (!fx_is_logged_in()) fx_json_response(array('ok' => false, 'error' => 'Not signed in.'));
$csrfHeader = isset($_SERVER['HTTP_X_CSRF']) ? $_SERVER['HTTP_X_CSRF'] : '';
if (!fx_csrf_check($csrfHeader)) fx_json_response(array('ok' => false, 'error' => 'Session expired.'));
if ($_SERVER['REQUEST_METHOD'] !== 'POST') fx_json_response(array('ok' => false, 'error' => 'Method not allowed.'));

$fieldmap = fx_load_fieldmap();
$content  = fx_load_content();
$backupsDir = fx_backups_dir();
if (!is_dir($backupsDir)) @mkdir($backupsDir, 0755, true);
$ts = fx_ts();

// Render each page first — if ANY page fails to render, refuse the whole publish
// so we never end up with a mismatched set of pages live.
$rendered = array();
foreach ($fieldmap['pages'] as $page_id => $pageDef) {
    $errors = array();
    $html = fx_render_page($page_id, $content, $errors);
    if ($html === null) {
        fx_json_response(array('ok' => false, 'error' => 'Render failed for ' . $page_id . ': ' . implode(' | ', $errors)));
    }
    if (strlen($html) < 5000) {
        fx_json_response(array('ok' => false, 'error' => 'Render for ' . $page_id . ' is suspiciously small (' . strlen($html) . ' bytes). Aborted.'));
    }
    $rendered[$page_id] = $html;
}

// Backup + write each page atomically.
$publishedFiles = array();
foreach ($fieldmap['pages'] as $page_id => $pageDef) {
    $live = fx_live_path($pageDef['live_file']);
    // Back up current live file (if it exists).
    if (is_file($live)) {
        $backupName = pathinfo($pageDef['live_file'], PATHINFO_FILENAME) . '-' . $ts . '.html';
        @copy($live, $backupsDir . '/' . $backupName);
    }
    $tmp = $live . '.tmp';
    if (file_put_contents($tmp, $rendered[$page_id], LOCK_EX) === false) {
        fx_json_response(array('ok' => false, 'error' => 'Could not write temp file for ' . $page_id . '.'));
    }
    if (!@rename($tmp, $live)) {
        @unlink($tmp);
        fx_json_response(array('ok' => false, 'error' => 'Could not atomically replace ' . $pageDef['live_file'] . '.'));
    }
    $publishedFiles[] = $pageDef['live_file'];
}

// Trim old backups: keep newest 40 (roughly 20 publishes across 2 pages).
$backups = glob($backupsDir . '/*.html');
if (is_array($backups) && count($backups) > 40) {
    sort($backups);
    $toDelete = array_slice($backups, 0, count($backups) - 40);
    foreach ($toDelete as $b) @unlink($b);
}

fx_json_response(array(
    'ok' => true,
    'published_at' => date('c'),
    'files' => $publishedFiles,
    'timestamp' => $ts,
));
