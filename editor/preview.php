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

$rendered = fx_preview_decorate($rendered, $page_id);

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
echo $rendered;
