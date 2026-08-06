<?php
// Furutec Editor — template renderer.
// Combines editor/templates/index.template.html with data/content.php
// values, producing final HTML that gets written to public_html/index.html
// on publish (or to a live-preview response for the iframe).

require_once __DIR__ . '/util.php';
require_once __DIR__ . '/data.php';

/**
 * Turn a media field value into a URL string safe to drop into src="..." attributes.
 * - Full URLs (starting with http:// or https:// or //) pass through as-is (still HTML-escaped for attribute context).
 * - Absolute paths (starting with /) pass through.
 * - Everything else is treated as a filename relative to /assets/ and per-segment URL-encoded.
 */
function fx_asset_url($v) {
    $v = (string)$v;
    if ($v === '') return '';
    if (preg_match('#^(https?:)?//#i', $v) || $v[0] === '/') {
        // Already an absolute reference — don't touch, just HTML-escape.
        return fx_escape($v);
    }
    return 'assets/' . fx_url_path($v);
}

/**
 * Build the substitution map from content array.
 * Placeholder keys are ALL_CAPS_WITH_UNDERSCORES.
 * Text values are HTML-escaped.  Markdown values get ** -> <strong>.
 * Media values run through fx_asset_url (URL-aware).
 */
function fx_build_substitutions($content) {
    $s = array();

    // -------- Nav (site header) --------
    $n = isset($content['nav']) ? $content['nav'] : array();
    $s['NAV_LOGO_URL']            = fx_asset_url(isset($n['logo']) ? $n['logo'] : '');
    $s['NAV_PHONE']               = fx_escape(isset($n['phone']) ? $n['phone'] : '');
    $s['NAV_CONTACT_BUTTON_LABEL']= fx_escape(isset($n['contact_button_label']) ? $n['contact_button_label'] : '');

    // -------- Hero --------
    $hero = isset($content['hero']) ? $content['hero'] : array();
    $s['HERO_BG_VIDEO_URL'] = fx_url_path(isset($hero['bg_video']) ? $hero['bg_video'] : '');
    $s['HERO_HEADLINE_1']   = fx_escape(isset($hero['headline_1']) ? $hero['headline_1'] : '');
    $s['HERO_HEADLINE_2']   = fx_escape(isset($hero['headline_2']) ? $hero['headline_2'] : '');
    $s['HERO_LEDE']         = fx_escape(isset($hero['lede']) ? $hero['lede'] : '');

    // -------- Origin (Inside Furutec) --------
    $o = isset($content['origin']) ? $content['origin'] : array();
    $s['ORIGIN_EYEBROW']    = fx_escape(isset($o['eyebrow']) ? $o['eyebrow'] : '');
    $s['ORIGIN_HEADING']    = fx_escape(isset($o['heading']) ? $o['heading'] : '');
    $s['ORIGIN_BODY']       = fx_escape(isset($o['body'])    ? $o['body']    : '');
    $s['ORIGIN_VIDEO_URL']  = fx_url_path(isset($o['video']) ? $o['video']   : '');
    $s['ORIGIN_POSTER_URL'] = fx_url_path(isset($o['poster'])? $o['poster']  : '');
    $s['ORIGIN_CAPTION']    = fx_escape(isset($o['caption']) ? $o['caption'] : '');

    // -------- Facilities --------
    $f = isset($content['facilities']) ? $content['facilities'] : array();
    $s['FAC_TAG']    = fx_escape(isset($f['tag'])    ? $f['tag']    : '');
    $s['FAC_NAME']   = fx_escape(isset($f['name'])   ? $f['name']   : '');
    $s['FAC_DESC_1'] = fx_markdown_to_html(isset($f['desc_1']) ? $f['desc_1'] : '');
    $s['FAC_DESC_2'] = fx_markdown_to_html(isset($f['desc_2']) ? $f['desc_2'] : '');
    for ($i = 1; $i <= 4; $i++) {
        $lbl = isset($f["badge_{$i}_label"]) ? $f["badge_{$i}_label"] : '';
        // Users write "Line 1 / Line 2" for a hard line break inside the badge label.
        $lbl = str_replace(' / ', "\n", $lbl);
        $lbl = str_replace(' /',  "\n", $lbl);
        $lbl = str_replace('/ ',  "\n", $lbl);
        $escaped = fx_escape($lbl);
        $s["FAC_BADGE_{$i}_LABEL_HTML"] = str_replace("\n", '<br/>', $escaped);
        $s["FAC_BADGE_{$i}_SUB"]        = fx_escape(isset($f["badge_{$i}_sub"]) ? $f["badge_{$i}_sub"] : '');
    }
    $s['FAC_PHOTO_AERIAL_URL'] = fx_asset_url(isset($f['photo_aerial']) ? $f['photo_aerial'] : '');
    $s['FAC_PHOTO_FRONT_URL']  = fx_asset_url(isset($f['photo_front'])  ? $f['photo_front']  : '');
    for ($i = 1; $i <= 4; $i++) {
        $s["FAC_PROCESS_{$i}_URL"] = fx_asset_url(isset($f["process_{$i}"]) ? $f["process_{$i}"] : '');
    }

    // -------- Company Profile --------
    $c = isset($content['company']) ? $content['company'] : array();
    $s['CMP_BG_VIDEO_URL'] = fx_asset_url(isset($c['bg_video']) ? $c['bg_video'] : '');
    $s['CMP_EYEBROW']      = fx_escape(isset($c['eyebrow'])   ? $c['eyebrow']   : '');
    $s['CMP_HEADING_1']    = fx_escape(isset($c['heading_1']) ? $c['heading_1'] : '');
    $s['CMP_HEADING_2']    = fx_escape(isset($c['heading_2']) ? $c['heading_2'] : '');
    $s['CMP_HEADING_3']    = fx_escape(isset($c['heading_3']) ? $c['heading_3'] : '');
    $s['CMP_BODY_1']       = fx_escape(isset($c['body_1'])    ? $c['body_1']    : '');
    $s['CMP_BODY_2']       = fx_escape(isset($c['body_2'])    ? $c['body_2']    : '');
    $s['CMP_BUTTON_LABEL'] = fx_escape(isset($c['button_label']) ? $c['button_label'] : '');
    $s['CMP_BUTTON_URL']   = fx_escape(isset($c['button_url'])   ? $c['button_url']   : '#');

    // -------- Product Overview --------
    $po = isset($content['product_overview']) ? $content['product_overview'] : array();
    $s['POV_EYEBROW']         = fx_escape(isset($po['eyebrow'])         ? $po['eyebrow']         : '');
    $s['POV_HEADING_1']       = fx_escape(isset($po['heading_1'])       ? $po['heading_1']       : '');
    $s['POV_HEADING_ACCENT']  = fx_escape(isset($po['heading_accent'])  ? $po['heading_accent']  : '');
    $s['POV_HEADING_2']       = fx_escape(isset($po['heading_2'])       ? $po['heading_2']       : '');
    $s['POV_SUBTITLE']        = fx_escape(isset($po['subtitle'])        ? $po['subtitle']        : '');
    $s['POV_DIAGRAM_URL']     = fx_asset_url(isset($po['diagram'])       ? $po['diagram']         : '');
    for ($i = 1; $i <= 5; $i++) {
        $s["POV_CARD_{$i}_TITLE_JS"] = fx_js_string(isset($po["card_{$i}_title"]) ? $po["card_{$i}_title"] : '');
    }

    // -------- Products (5 cards) --------
    $p = isset($content['products']) ? $content['products'] : array();
    $s['PRODUCTS_EYEBROW']   = fx_escape(isset($p['section_eyebrow'])   ? $p['section_eyebrow']   : '');
    $s['PRODUCTS_HEADING_1'] = fx_escape(isset($p['section_heading_1']) ? $p['section_heading_1'] : '');
    $s['PRODUCTS_HEADING_2'] = fx_escape(isset($p['section_heading_2']) ? $p['section_heading_2'] : '');
    $s['PRODUCTS_SUBTITLE']  = fx_escape(isset($p['section_subtitle'])  ? $p['section_subtitle']  : '');
    for ($i = 1; $i <= 5; $i++) {
        $tKey = "card_{$i}_title";
        $dKey = "card_{$i}_description";
        $s["PRODUCT_{$i}_TITLE"]            = fx_escape(isset($p[$tKey]) ? $p[$tKey] : '');
        $s["PRODUCT_{$i}_DESCRIPTION_HTML"] = fx_markdown_to_html(isset($p[$dKey]) ? $p[$dKey] : '');
    }

    // -------- Certs & Portfolio CTA --------
    $cc = isset($content['certs_cta']) ? $content['certs_cta'] : array();
    $s['CERTS_BG_IMAGE_URL']   = fx_asset_url(isset($cc['bg_image'])  ? $cc['bg_image']  : '');
    $s['CERTS_HEADING_1']      = fx_escape(isset($cc['heading_1'])    ? $cc['heading_1']    : '');
    $s['CERTS_HEADING_2']      = fx_escape(isset($cc['heading_2'])    ? $cc['heading_2']    : '');
    $s['CERTS_SUBTITLE']       = fx_escape(isset($cc['subtitle'])     ? $cc['subtitle']     : '');
    $s['CERTS_BUTTON_1_LABEL'] = fx_escape(isset($cc['button_1_label'])? $cc['button_1_label']: '');
    $s['CERTS_BUTTON_2_LABEL'] = fx_escape(isset($cc['button_2_label'])? $cc['button_2_label']: '');

    // -------- Get In Touch CTA --------
    $q = isset($content['quote_cta']) ? $content['quote_cta'] : array();
    $s['QUOTE_BG_IMAGE_URL'] = fx_asset_url(isset($q['bg_image']) ? $q['bg_image'] : '');
    $s['QUOTE_EYEBROW']      = fx_escape(isset($q['eyebrow'])     ? $q['eyebrow']     : '');
    $s['QUOTE_HEADING_1']    = fx_escape(isset($q['heading_1'])   ? $q['heading_1']   : '');
    $s['QUOTE_HEADING_2']    = fx_escape(isset($q['heading_2'])   ? $q['heading_2']   : '');
    $s['QUOTE_LEDE']         = fx_escape(isset($q['lede'])        ? $q['lede']        : '');
    $s['QUOTE_BUTTON_LABEL'] = fx_escape(isset($q['button_label']) ? $q['button_label'] : '');

    // -------- Footer --------
    $ft = isset($content['footer']) ? $content['footer'] : array();
    $s['FT_LOGO_URL']     = fx_asset_url(isset($ft['logo']) ? $ft['logo'] : '');
    $s['FT_COMPANY_NAME'] = fx_escape(isset($ft['company_name']) ? $ft['company_name'] : '');
    $s['FT_COMPANY_REG']  = fx_escape(isset($ft['company_reg'])  ? $ft['company_reg']  : '');
    $s['FT_EMAIL']        = fx_escape(isset($ft['email'])        ? $ft['email']        : '');
    $s['FT_PHONE']        = fx_escape(isset($ft['phone'])        ? $ft['phone']        : '');
    $s['FT_LOCATION_1']   = fx_escape(isset($ft['location_1'])   ? $ft['location_1']   : '');
    $s['FT_LOCATION_2']   = fx_escape(isset($ft['location_2'])   ? $ft['location_2']   : '');
    $s['FT_COL_1_HEADING']= fx_escape(isset($ft['col_1_heading'])? $ft['col_1_heading']: '');
    $s['FT_COL_2_HEADING']= fx_escape(isset($ft['col_2_heading'])? $ft['col_2_heading']: '');
    $s['FT_COL_3_HEADING']= fx_escape(isset($ft['col_3_heading'])? $ft['col_3_heading']: '');
    $s['FT_COL_4_HEADING']= fx_escape(isset($ft['col_4_heading'])? $ft['col_4_heading']: '');
    $s['FT_COPYRIGHT']    = fx_escape(isset($ft['copyright'])    ? $ft['copyright']    : '');
    $s['FT_SOCIAL_FACEBOOK']= fx_escape(isset($ft['social_facebook']) ? $ft['social_facebook'] : '#');
    $s['FT_SOCIAL_YOUTUBE'] = fx_escape(isset($ft['social_youtube'])  ? $ft['social_youtube']  : '#');
    $s['FT_SOCIAL_LINKEDIN']= fx_escape(isset($ft['social_linkedin']) ? $ft['social_linkedin'] : '#');
    $s['FT_SOCIAL_WHATSAPP']= fx_escape(isset($ft['social_whatsapp']) ? $ft['social_whatsapp'] : '#');

    return $s;
}

/**
 * Escape a UTF-8 string as a JavaScript string literal (with surrounding quotes).
 * Used for values injected into <script> DATA arrays (Product Overview card titles).
 */
function fx_js_string($s) {
    $s = (string)$s;
    $out = '\'';
    $len = strlen($s);
    for ($i = 0; $i < $len; $i++) {
        $c = $s[$i];
        if ($c === '\\') { $out .= '\\\\'; continue; }
        if ($c === '\'') { $out .= '\\\''; continue; }
        if ($c === '<')  { $out .= '\\u003C'; continue; }   // guard against </script>
        if ($c === '>')  { $out .= '\\u003E'; continue; }
        if ($c === '&')  { $out .= '\\u0026'; continue; }
        if ($c === "\n") { $out .= '\\n'; continue; }
        if ($c === "\r") { $out .= '\\r'; continue; }
        $o = ord($c);
        if ($o < 0x20)   { $out .= sprintf('\\u%04x', $o); continue; }
        $out .= $c;
    }
    $out .= '\'';
    return $out;
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

    $map = array();
    foreach ($subs as $k => $v) $map['{{' . $k . '}}'] = $v;
    $rendered = strtr($template, $map);

    if (preg_match_all('/\{\{[A-Z0-9_]+\}\}/', $rendered, $m)) {
        if (is_array($errors_out)) {
            $errors_out[] = 'Unfilled placeholders in template: ' . implode(', ', array_unique($m[0]));
        }
        return null;
    }
    return $rendered;
}
