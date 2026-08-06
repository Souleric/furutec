<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/data.php';
require_once __DIR__ . '/lib/util.php';

if (!fx_is_logged_in()) fx_json_response(array('ok' => false, 'error' => 'Not signed in.'));
$csrfHeader = isset($_SERVER['HTTP_X_CSRF']) ? $_SERVER['HTTP_X_CSRF'] : '';
if (!fx_csrf_check($csrfHeader)) fx_json_response(array('ok' => false, 'error' => 'Session expired.'));
if ($_SERVER['REQUEST_METHOD'] !== 'POST') fx_json_response(array('ok' => false, 'error' => 'Method not allowed.'));

$fieldmap = fx_load_fieldmap();
$backupsDir = fx_backups_dir();

// Group backup files by timestamp.  Filename pattern: {basename}-{ts}.html
// where basename matches pathinfo of each page's live_file.
$all = glob($backupsDir . '/*.html');
if (!is_array($all) || empty($all)) {
    fx_json_response(array('ok' => false, 'error' => 'No backups available yet. First publish will create them.'));
}

// Map filename base -> live_file path.
$baseToLive = array();
foreach ($fieldmap['pages'] as $pd) {
    $b = pathinfo($pd['live_file'], PATHINFO_FILENAME);
    $baseToLive[$b] = $pd['live_file'];
}

// Group by timestamp suffix, then pick the newest complete set.
$byTs = array();
foreach ($all as $path) {
    $fn = basename($path, '.html');
    // Match {base}-{Y-m-d_H-i-s}
    if (!preg_match('/^(.+)-(\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2})$/', $fn, $m)) continue;
    $base = $m[1]; $ts = $m[2];
    if (!isset($baseToLive[$base])) continue;
    if (!isset($byTs[$ts])) $byTs[$ts] = array();
    $byTs[$ts][$base] = $path;
}
if (empty($byTs)) {
    fx_json_response(array('ok' => false, 'error' => 'No usable backups found.'));
}

// Newest timestamp (whether or not it has ALL pages — we restore what we have).
krsort($byTs);
$latestTs   = key($byTs);
$latestSet  = $byTs[$latestTs];

// Safety copy: back up current live files before restoring (so restore is undoable).
$preRestoreTs = fx_ts();
foreach ($fieldmap['pages'] as $pd) {
    $live = fx_live_path($pd['live_file']);
    if (is_file($live)) {
        $b = pathinfo($pd['live_file'], PATHINFO_FILENAME);
        @copy($live, $backupsDir . '/' . $b . '-pre-restore-' . $preRestoreTs . '.html');
    }
}

// Restore each file that has a backup at the chosen ts.
$restored = array();
foreach ($latestSet as $base => $backupPath) {
    if (!isset($baseToLive[$base])) continue;
    $live = fx_live_path($baseToLive[$base]);
    $tmp  = $live . '.tmp';
    if (!@copy($backupPath, $tmp)) continue;
    if (!@rename($tmp, $live)) { @unlink($tmp); continue; }
    $restored[] = $baseToLive[$base];
}

if (empty($restored)) {
    fx_json_response(array('ok' => false, 'error' => 'Could not restore any file.'));
}
fx_json_response(array('ok' => true, 'restored_from_ts' => $latestTs, 'files' => $restored));
