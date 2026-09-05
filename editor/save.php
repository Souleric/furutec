<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/data.php';
require_once __DIR__ . '/lib/merge.php';
require_once __DIR__ . '/lib/util.php';

if (!fx_is_logged_in()) fx_json_response(array('ok' => false, 'error' => 'Not signed in.'));
$csrfHeader = isset($_SERVER['HTTP_X_CSRF']) ? $_SERVER['HTTP_X_CSRF'] : '';
if (!fx_csrf_check($csrfHeader)) fx_json_response(array('ok' => false, 'error' => 'Session expired. Refresh the editor and sign in again.'));
if ($_SERVER['REQUEST_METHOD'] !== 'POST') fx_json_response(array('ok' => false, 'error' => 'Method not allowed.'));

$raw = file_get_contents('php://input');
$payload = fx_json_decode($raw);
if (!is_array($payload) || !isset($payload['content']) || !is_array($payload['content'])) {
    fx_json_response(array('ok' => false, 'error' => 'Bad request payload.'));
}

// Whitelist-validated merge, shared with livepreview.php so that what the
// preview shows is exactly what a save would store.
$updated = fx_merge_content(fx_load_content(), $payload['content'], fx_load_fieldmap());

if (!fx_save_content($updated)) {
    fx_json_response(array('ok' => false, 'error' => 'Could not write /data/content.php.'));
}
fx_json_response(array('ok' => true));
