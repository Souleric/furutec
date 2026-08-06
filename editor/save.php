<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/data.php';
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

$fieldmap = fx_load_fieldmap();
$current  = fx_load_content();
$incoming = $payload['content'];

// Merge incoming into current, whitelist-validated per section+field.
// Structure: incoming['shared'][section][field], incoming[$page_id][section][field].
$updated = $current;

// Helper: whitelisted section field validation.
function fx_apply_section($sect_id, $incoming_section, &$updated_section, $fieldmap) {
    if (!isset($fieldmap['sections'][$sect_id])) return;
    $section_def = $fieldmap['sections'][$sect_id];
    foreach ($section_def['fields'] as $fname => $fdef) {
        if (!isset($incoming_section[$fname])) continue;
        $val = $incoming_section[$fname];
        if (!is_string($val)) $val = (string)$val;
        $type = isset($fdef['type']) ? $fdef['type'] : 'text';
        $max  = isset($fdef['max'])  ? (int)$fdef['max'] : 5000;
        if ($type === 'text' || $type === 'url') {
            $val = trim(preg_replace('/\s+/', ' ', $val));
        } elseif ($type === 'textarea' || $type === 'markdown') {
            $val = trim($val);
        } elseif ($type === 'image' || $type === 'video') {
            $val = trim($val);
        }
        if ($max > 0 && strlen($val) > $max) $val = substr($val, 0, $max);
        $updated_section[$fname] = $val;
    }
}

// Shared sections (nav, footer).
if (isset($incoming['shared']) && is_array($incoming['shared'])) {
    if (!isset($updated['shared']) || !is_array($updated['shared'])) $updated['shared'] = array();
    foreach ($fieldmap['shared_sections'] as $sect_id) {
        if (!isset($incoming['shared'][$sect_id]) || !is_array($incoming['shared'][$sect_id])) continue;
        if (!isset($updated['shared'][$sect_id])) $updated['shared'][$sect_id] = array();
        fx_apply_section($sect_id, $incoming['shared'][$sect_id], $updated['shared'][$sect_id], $fieldmap);
    }
}

// Per-page sections.
foreach ($fieldmap['pages'] as $page_id => $pageDef) {
    if (!isset($incoming[$page_id]) || !is_array($incoming[$page_id])) continue;
    if (!isset($updated[$page_id]) || !is_array($updated[$page_id])) $updated[$page_id] = array();
    foreach ($pageDef['sections'] as $sect_id) {
        if (!isset($incoming[$page_id][$sect_id]) || !is_array($incoming[$page_id][$sect_id])) continue;
        if (!isset($updated[$page_id][$sect_id])) $updated[$page_id][$sect_id] = array();
        fx_apply_section($sect_id, $incoming[$page_id][$sect_id], $updated[$page_id][$sect_id], $fieldmap);
    }
}

if (!fx_save_content($updated)) {
    fx_json_response(array('ok' => false, 'error' => 'Could not write /data/content.php.'));
}
fx_json_response(array('ok' => true));
