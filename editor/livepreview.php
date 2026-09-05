<?php
// Renders a page from the values currently sitting in the editor's form,
// WITHOUT saving them.  The editor calls this on a debounce as you type and
// patches the differences into the preview iframe, so the preview keeps up
// with your edits instead of showing the last saved version.
//
// Read-only by construction: it merges the payload into an in-memory copy of
// content.php and never calls fx_save_content().  A failed or malicious
// request can therefore change what you SEE, never what is stored.
//
// Returns HTML (not JSON) so the client can hand it straight to DOMParser.
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/data.php';
require_once __DIR__ . '/lib/merge.php';
require_once __DIR__ . '/lib/render.php';
require_once __DIR__ . '/lib/util.php';

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');

function fx_lp_fail($msg) {
    // 200 with a marker rather than an HTTP error: the client checks for the
    // marker and simply leaves the preview untouched.  Never let a failed
    // live render blank out a preview the user is working against.
    http_response_code(200);
    echo '<!-- fx-live-preview-error: ' . fx_escape($msg) . ' -->';
    exit;
}

if (!fx_is_logged_in()) fx_lp_fail('Not signed in.');
$csrfHeader = isset($_SERVER['HTTP_X_CSRF']) ? $_SERVER['HTTP_X_CSRF'] : '';
if (!fx_csrf_check($csrfHeader)) fx_lp_fail('Session expired.');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') fx_lp_fail('Method not allowed.');

$page_id = isset($_GET['page']) ? preg_replace('/[^a-z0-9_]/', '', $_GET['page']) : 'home';
if ($page_id === '') $page_id = 'home';

$raw = file_get_contents('php://input');
$payload = fx_json_decode($raw);
if (!is_array($payload) || !isset($payload['content']) || !is_array($payload['content'])) {
    fx_lp_fail('Bad request payload.');
}

$merged = fx_merge_content(fx_load_content(), $payload['content'], fx_load_fieldmap());

$errors   = array();
$rendered = fx_render_page($page_id, $merged, $errors);
if ($rendered === null) fx_lp_fail(implode(' | ', $errors));

echo fx_preview_decorate($rendered, $page_id);
