<?php
// Renders a specific page's draft (data/content.php) using its template.
// Query: ?page=home (default) | ?page=certificates
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/data.php';
require_once __DIR__ . '/lib/render.php';
require_once __DIR__ . '/lib/util.php';

fx_require_login();

$page_id = isset($_GET['page']) ? preg_replace('/[^a-z0-9_]/', '', $_GET['page']) : 'home';
if ($page_id === '') $page_id = 'home';

$content  = fx_load_content();
$errors   = array();
$rendered = fx_render_page($page_id, $content, $errors);

if ($rendered === null) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><meta charset="utf-8"><title>Preview error</title>'
        .'<style>body{font-family:-apple-system,sans-serif;max-width:640px;margin:40px auto;color:#333;padding:0 20px;}h1{color:#B71C1C;}</style>'
        .'<h1>Preview error</h1><p>' . fx_escape(implode(' | ', $errors)) . '</p>';
    exit;
}

$ribbon = '<div style="position:fixed;top:0;right:0;z-index:99999;background:#B36B00;color:#fff;padding:5px 12px;font-family:-apple-system,sans-serif;font-size:11px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;border-bottom-left-radius:4px;box-shadow:0 2px 8px rgba(0,0,0,0.3);pointer-events:none;">Draft preview · ' . fx_escape($page_id) . '</div>';
// target="_blank" ensures that if Amy clicks any link inside the draft
// preview, it opens in a new tab instead of hijacking the iframe (which
// would replace the draft with the actual live page and confuse her).
$baseTag = '<base href="../" target="_blank"/>';
$rendered = preg_replace('/(<head[^>]*>)/i', '$1' . $baseTag, $rendered, 1);
$rendered = preg_replace('/(<body[^>]*>)/i', '$1' . $ribbon, $rendered, 1);

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
echo $rendered;
