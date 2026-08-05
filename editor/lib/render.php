<?php
// Furutec Editor — template renderer.
// Combines editor/templates/index.template.html with data/content.php
// values, producing final HTML that gets written to public_html/index.html
// on publish (or to a live-preview response for the iframe).

require_once __DIR__ . '/util.php';
require_once __DIR__ . '/data.php';

/**
 * Build the substitution map from content array.
 * Placeholder keys are ALL_CAPS_WITH_UNDERSCORES.
 * Text values are HTML-escaped.  Markdown values get ** -> <strong>.
 * URL values (media paths) are rawurlencoded per segment.
 */
function fx_build_substitutions($content) {
    $s = array();

    // ---- Hero ----
    $hero = isset($content['hero']) ? $content['hero'] : array();
    $s['HERO_BG_VIDEO_URL'] = fx_url_path(isset($hero['bg_video']) ? $hero['bg_video'] : '');
    $s['HERO_HEADLINE_1']   = fx_escape(isset($hero['headline_1']) ? $hero['headline_1'] : '');
    $s['HERO_HEADLINE_2']   = fx_escape(isset($hero['headline_2']) ? $hero['headline_2'] : '');
    $s['HERO_LEDE']         = fx_escape(isset($hero['lede']) ? $hero['lede'] : '');

    // ---- Origin (Inside Furutec) ----
    $o = isset($content['origin']) ? $content['origin'] : array();
    $s['ORIGIN_EYEBROW']    = fx_escape(isset($o['eyebrow']) ? $o['eyebrow'] : '');
    $s['ORIGIN_HEADING']    = fx_escape(isset($o['heading']) ? $o['heading'] : '');
    $s['ORIGIN_BODY']       = fx_escape(isset($o['body'])    ? $o['body']    : '');
    $s['ORIGIN_VIDEO_URL']  = fx_url_path(isset($o['video']) ? $o['video']   : '');
    $s['ORIGIN_POSTER_URL'] = fx_url_path(isset($o['poster'])? $o['poster']  : '');
    $s['ORIGIN_CAPTION']    = fx_escape(isset($o['caption']) ? $o['caption'] : '');

    // ---- Products ----
    $p = isset($content['products']) ? $content['products'] : array();
    $s['PRODUCTS_EYEBROW']  = fx_escape(isset($p['section_eyebrow'])   ? $p['section_eyebrow']   : '');
    $s['PRODUCTS_HEADING_1']= fx_escape(isset($p['section_heading_1']) ? $p['section_heading_1'] : '');
    $s['PRODUCTS_HEADING_2']= fx_escape(isset($p['section_heading_2']) ? $p['section_heading_2'] : '');
    $s['PRODUCTS_SUBTITLE'] = fx_escape(isset($p['section_subtitle'])  ? $p['section_subtitle']  : '');

    for ($i = 1; $i <= 5; $i++) {
        $tKey = "card_{$i}_title";
        $dKey = "card_{$i}_description";
        $s["PRODUCT_{$i}_TITLE"]            = fx_escape(isset($p[$tKey]) ? $p[$tKey] : '');
        $s["PRODUCT_{$i}_DESCRIPTION_HTML"] = fx_markdown_to_html(isset($p[$dKey]) ? $p[$dKey] : '');
    }

    return $s;
}

/**
 * Render the template with a content array.  Returns the final HTML string.
 * On template-file failure returns null; on missing-placeholder detection
 * returns null and populates $errors_out with a list of unfilled tokens
 * (so publish.php can refuse to overwrite a live file with a broken render).
 */
function fx_render($content, &$errors_out = null) {
    $template = @file_get_contents(fx_template_path());
    if ($template === false) {
        if (is_array($errors_out)) $errors_out[] = 'template file missing';
        return null;
    }
    $subs = fx_build_substitutions($content);

    // Do the substitution.  Using strtr for speed and safety (no regex parsing of $ etc).
    $map = array();
    foreach ($subs as $k => $v) $map['{{' . $k . '}}'] = $v;
    $rendered = strtr($template, $map);

    // Detect any remaining {{XXX}} tokens.  If found, refuse the render.
    if (preg_match_all('/\{\{[A-Z0-9_]+\}\}/', $rendered, $m)) {
        if (is_array($errors_out)) {
            $errors_out[] = 'Unfilled placeholders in template: ' . implode(', ', array_unique($m[0]));
        }
        return null;
    }
    return $rendered;
}
