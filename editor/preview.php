<?php
// Renders the current draft (data/content.php) using the template and
// serves it as HTML for the iframe.  Auth-required.
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/data.php';
require_once __DIR__ . '/lib/render.php';
require_once __DIR__ . '/lib/util.php';

fx_require_login();

$content = fx_load_content();
$errors  = array();
$rendered = fx_render($content, $errors);

if ($rendered === null) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><meta charset="utf-8"><title>Preview error</title>'
        .'<style>body{font-family:-apple-system,sans-serif;max-width:640px;margin:40px auto;color:#333;padding:0 20px;}h1{color:#B71C1C;}code{background:#f0f0f0;padding:2px 6px;border-radius:3px;}</style>'
        .'<h1>Preview could not be rendered</h1>'
        .'<p>' . fx_escape(implode(' | ', $errors)) . '</p>'
        .'<p>The live homepage is unaffected. Fix the template / content and try again.</p>';
    exit;
}

// Neutralize any inline hero JS that might try to fetch or navigate.
// Also inject a "Draft preview" ribbon in the top corner so Amy always knows
// this is not the live site.
$ribbon = '<div style="position:fixed;top:0;right:0;z-index:99999;background:#B36B00;color:#fff;padding:5px 12px;font-family:-apple-system,sans-serif;font-size:11px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;border-bottom-left-radius:4px;box-shadow:0 2px 8px rgba(0,0,0,0.3);pointer-events:none;">Draft preview</div>';

// Also: because the iframe loads /editor/preview.php but asset URLs in the
// template are relative like "assets/..." (which would resolve to
// /editor/assets/...), we inject a <base href="../"> so relative asset URLs
// resolve to /assets/... as they do on the real page.
$baseTag = '<base href="../"/>';

// Try inserting after <head>; fall back to prepending.
$rendered = preg_replace('/(<head[^>]*>)/i', '$1' . $baseTag, $rendered, 1);
$rendered = preg_replace('/(<body[^>]*>)/i', '$1' . $ribbon, $rendered, 1);

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
echo $rendered;
