<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/data.php';
require_once __DIR__ . '/lib/util.php';

// Session + CSRF gates.
if (!fx_is_logged_in()) fx_json_response(array('ok' => false, 'error' => 'Not signed in.'));
$csrfHeader = isset($_SERVER['HTTP_X_CSRF']) ? $_SERVER['HTTP_X_CSRF'] : '';
if (!fx_csrf_check($csrfHeader)) fx_json_response(array('ok' => false, 'error' => 'Session expired. Refresh the editor and sign in again.'));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fx_json_response(array('ok' => false, 'error' => 'Method not allowed.'));

$raw = file_get_contents('php://input');
$payload = fx_json_decode($raw);
if (!is_array($payload) || !isset($payload['content']) || !is_array($payload['content'])) {
    fx_json_response(array('ok' => false, 'error' => 'Bad request payload.'));
}

$fieldmap = fx_load_fieldmap();
$current  = fx_load_content();

// Whitelist merge: only accept fields that exist in the field map.
$incoming = $payload['content'];
$updated  = $current;

foreach ($fieldmap['sections'] as $sid => $sect) {
    foreach ($sect['fields'] as $fname => $fdef) {
        if (!isset($incoming[$sid][$fname])) continue;
        $val = $incoming[$sid][$fname];
        if (!is_string($val)) $val = (string)$val;

        $type = isset($fdef['type']) ? $fdef['type'] : 'text';
        $max  = isset($fdef['max'])  ? (int)$fdef['max'] : 5000;

        // Normalize whitespace for text.
        if ($type === 'text') {
            $val = trim(preg_replace('/\s+/', ' ', $val));
        } elseif ($type === 'textarea' || $type === 'markdown') {
            // Preserve line breaks but strip leading/trailing whitespace.
            $val = trim($val);
        } elseif ($type === 'image' || $type === 'video') {
            // Media field: filename relative to /assets/. Keep the value the
            // upload handler stored (already sanitized to a safe path).
            $val = trim($val);
        }
        if ($max > 0 && strlen($val) > $max) $val = substr($val, 0, $max);

        if (!isset($updated[$sid])) $updated[$sid] = array();
        $updated[$sid][$fname] = $val;
    }
}

if (!fx_save_content($updated)) {
    fx_json_response(array('ok' => false, 'error' => 'Could not write /data/content.php — check permissions.'));
}
fx_json_response(array('ok' => true));
