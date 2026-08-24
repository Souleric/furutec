<?php
// Furutec Editor — multi-page template renderer.
// - fx_render_page($page_id, $content) → returns rendered HTML for that page
//   (combining shared sections + the page's own sections).
// - Each page has its own template file (per data/field-map.php `pages`).
// - Shared sections (nav, footer) get merged into every page's substitution map.

require_once __DIR__ . '/util.php';
require_once __DIR__ . '/data.php';

/** Turn a media field value into a URL string safe to drop into src="..." attributes. */
function fx_asset_url($v) {
    $v = (string)$v;
    if ($v === '') return '';
    if (preg_match('#^(https?:)?//#i', $v) || $v[0] === '/') return fx_escape($v);
    return 'assets/' . fx_url_path($v);
}

/**
 * Build the substitution map from content array.
 * Expects nested content: content['shared']['nav'|'footer'], content[$page_id][sections...]
 */
function fx_build_substitutions($page_id, $content) {
    $s = array();

    $shared = isset($content['shared']) ? $content['shared'] : array();
    $page   = isset($content[$page_id]) ? $content[$page_id] : array();

    // -------- SHARED: Nav --------
    $n = isset($shared['nav']) ? $shared['nav'] : array();
    $s['NAV_LOGO_URL']            = fx_asset_url(isset($n['logo']) ? $n['logo'] : '');
    $s['NAV_PHONE']               = fx_escape(isset($n['phone']) ? $n['phone'] : '');
    $s['NAV_CONTACT_BUTTON_LABEL']= fx_escape(isset($n['contact_button_label']) ? $n['contact_button_label'] : '');

    // -------- SHARED: Footer --------
    $ft = isset($shared['footer']) ? $shared['footer'] : array();
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

    // Shared WhatsApp URL used wherever a phone number is displayed (nav bar
    // and footer contact block).  Sourced from the same field as the footer
    // social icon so editing that value updates every phone tap-target too.
    // Falls back to Furutec's business WhatsApp (12-887 4517) if the field
    // is blank — better than an inert '#' href on a phone-shaped link.
    $wa_raw = isset($ft['social_whatsapp']) ? trim((string)$ft['social_whatsapp']) : '';
    if ($wa_raw === '' || $wa_raw === '#') $wa_raw = 'https://wa.me/60128874517';
    $s['SHARED_WA_URL'] = fx_escape($wa_raw);

    // ============================================================
    // HOME page sections
    // ============================================================
    if ($page_id === 'home') {

        // Hero
        $hero = isset($page['hero']) ? $page['hero'] : array();
        $s['HERO_BG_VIDEO_URL'] = fx_url_path(isset($hero['bg_video']) ? $hero['bg_video'] : '');
        $s['HERO_HEADLINE_1']   = fx_escape(isset($hero['headline_1']) ? $hero['headline_1'] : '');
        $s['HERO_HEADLINE_2']   = fx_escape(isset($hero['headline_2']) ? $hero['headline_2'] : '');
        $s['HERO_LEDE']         = fx_escape(isset($hero['lede']) ? $hero['lede'] : '');

        // Origin
        $o = isset($page['origin']) ? $page['origin'] : array();
        $s['ORIGIN_EYEBROW']    = fx_escape(isset($o['eyebrow']) ? $o['eyebrow'] : '');
        $s['ORIGIN_HEADING']    = fx_escape(isset($o['heading']) ? $o['heading'] : '');
        $s['ORIGIN_BODY']       = fx_escape(isset($o['body'])    ? $o['body']    : '');
        $s['ORIGIN_VIDEO_URL']  = fx_url_path(isset($o['video']) ? $o['video']   : '');
        $s['ORIGIN_POSTER_URL'] = fx_url_path(isset($o['poster'])? $o['poster']  : '');
        $s['ORIGIN_CAPTION']    = fx_escape(isset($o['caption']) ? $o['caption'] : '');

        // Facilities
        $f = isset($page['facilities']) ? $page['facilities'] : array();
        $s['FAC_TAG']    = fx_escape(isset($f['tag'])    ? $f['tag']    : '');
        $s['FAC_NAME']   = fx_escape(isset($f['name'])   ? $f['name']   : '');
        $s['FAC_DESC_1'] = fx_markdown_to_html(isset($f['desc_1']) ? $f['desc_1'] : '');
        $s['FAC_DESC_2'] = fx_markdown_to_html(isset($f['desc_2']) ? $f['desc_2'] : '');
        for ($i = 1; $i <= 4; $i++) {
            $lbl = isset($f["badge_{$i}_label"]) ? $f["badge_{$i}_label"] : '';
            $lbl = str_replace(array(' / ', ' /', '/ '), "\n", $lbl);
            $escaped = fx_escape($lbl);
            $s["FAC_BADGE_{$i}_LABEL_HTML"] = str_replace("\n", '<br/>', $escaped);
            $s["FAC_BADGE_{$i}_SUB"]        = fx_escape(isset($f["badge_{$i}_sub"]) ? $f["badge_{$i}_sub"] : '');
        }
        $s['FAC_PHOTO_AERIAL_URL'] = fx_asset_url(isset($f['photo_aerial']) ? $f['photo_aerial'] : '');
        $s['FAC_PHOTO_FRONT_URL']  = fx_asset_url(isset($f['photo_front'])  ? $f['photo_front']  : '');
        for ($i = 1; $i <= 4; $i++) {
            $s["FAC_PROCESS_{$i}_URL"] = fx_asset_url(isset($f["process_{$i}"]) ? $f["process_{$i}"] : '');
        }

        // Company Profile
        $c = isset($page['company']) ? $page['company'] : array();
        $s['CMP_BG_VIDEO_URL'] = fx_asset_url(isset($c['bg_video']) ? $c['bg_video'] : '');
        $s['CMP_EYEBROW']      = fx_escape(isset($c['eyebrow'])   ? $c['eyebrow']   : '');
        $s['CMP_HEADING_1']    = fx_escape(isset($c['heading_1']) ? $c['heading_1'] : '');
        $s['CMP_HEADING_2']    = fx_escape(isset($c['heading_2']) ? $c['heading_2'] : '');
        $s['CMP_HEADING_3']    = fx_escape(isset($c['heading_3']) ? $c['heading_3'] : '');
        $s['CMP_BODY_1']       = fx_escape(isset($c['body_1'])    ? $c['body_1']    : '');
        $s['CMP_BODY_2']       = fx_escape(isset($c['body_2'])    ? $c['body_2']    : '');
        $s['CMP_BUTTON_LABEL'] = fx_escape(isset($c['button_label']) ? $c['button_label'] : '');
        $s['CMP_BUTTON_URL']   = fx_escape(isset($c['button_url'])   ? $c['button_url']   : '#');

        // Product Overview
        $po = isset($page['product_overview']) ? $page['product_overview'] : array();
        $s['POV_EYEBROW']         = fx_escape(isset($po['eyebrow'])         ? $po['eyebrow']         : '');
        $s['POV_HEADING_1']       = fx_escape(isset($po['heading_1'])       ? $po['heading_1']       : '');
        $s['POV_HEADING_ACCENT']  = fx_escape(isset($po['heading_accent'])  ? $po['heading_accent']  : '');
        $s['POV_HEADING_2']       = fx_escape(isset($po['heading_2'])       ? $po['heading_2']       : '');
        $s['POV_SUBTITLE']        = fx_escape(isset($po['subtitle'])        ? $po['subtitle']        : '');
        $s['POV_DIAGRAM_URL']     = fx_asset_url(isset($po['diagram'])      ? $po['diagram']         : '');
        for ($i = 1; $i <= 5; $i++) {
            $s["POV_CARD_{$i}_TITLE_JS"] = fx_js_string(isset($po["card_{$i}_title"]) ? $po["card_{$i}_title"] : '');
        }

        // Products
        $p = isset($page['products']) ? $page['products'] : array();
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

        // Certs & Portfolio CTA
        $cc = isset($page['certs_cta']) ? $page['certs_cta'] : array();
        $s['CERTS_BG_IMAGE_URL']   = fx_asset_url(isset($cc['bg_image'])  ? $cc['bg_image']  : '');
        $s['CERTS_HEADING_1']      = fx_escape(isset($cc['heading_1'])    ? $cc['heading_1']    : '');
        $s['CERTS_HEADING_2']      = fx_escape(isset($cc['heading_2'])    ? $cc['heading_2']    : '');
        $s['CERTS_SUBTITLE']       = fx_escape(isset($cc['subtitle'])     ? $cc['subtitle']     : '');
        $s['CERTS_BUTTON_1_LABEL'] = fx_escape(isset($cc['button_1_label'])? $cc['button_1_label']: '');
        $s['CERTS_BUTTON_2_LABEL'] = fx_escape(isset($cc['button_2_label'])? $cc['button_2_label']: '');

        // Get In Touch CTA
        $q = isset($page['quote_cta']) ? $page['quote_cta'] : array();
        $s['QUOTE_BG_IMAGE_URL'] = fx_asset_url(isset($q['bg_image']) ? $q['bg_image'] : '');
        $s['QUOTE_EYEBROW']      = fx_escape(isset($q['eyebrow'])     ? $q['eyebrow']     : '');
        $s['QUOTE_HEADING_1']    = fx_escape(isset($q['heading_1'])   ? $q['heading_1']   : '');
        $s['QUOTE_HEADING_2']    = fx_escape(isset($q['heading_2'])   ? $q['heading_2']   : '');
        $s['QUOTE_LEDE']         = fx_escape(isset($q['lede'])        ? $q['lede']        : '');
        $s['QUOTE_BUTTON_LABEL'] = fx_escape(isset($q['button_label']) ? $q['button_label'] : '');
    }

    // ============================================================
    // CERTIFICATES page sections (portfolio.html)
    // ============================================================
    if ($page_id === 'certificates') {
        $h = isset($page['cert_hero']) ? $page['cert_hero'] : array();
        $s['CH_EYEBROW']   = fx_escape(isset($h['eyebrow'])   ? $h['eyebrow']   : '');
        $s['CH_HEADING_1'] = fx_escape(isset($h['heading_1']) ? $h['heading_1'] : '');
        $s['CH_HEADING_2'] = fx_escape(isset($h['heading_2']) ? $h['heading_2'] : '');
        $s['CH_SUBTITLE']  = fx_escape(isset($h['subtitle'])  ? $h['subtitle']  : '');

        $g = isset($page['cert_grid']) ? $page['cert_grid'] : array();
        for ($i = 1; $i <= 9; $i++) {
            $s["CG_CARD_{$i}_IMAGE_URL"] = fx_asset_url(isset($g["card_{$i}_image"]) ? $g["card_{$i}_image"] : '');
            $s["CG_CARD_{$i}_ALT"]       = fx_escape(isset($g["card_{$i}_alt"])   ? $g["card_{$i}_alt"] : '');
            $s["CG_CARD_{$i}_SUB"]       = fx_escape(isset($g["card_{$i}_sub"])   ? $g["card_{$i}_sub"] : '');
        }

        $cs = isset($page['cert_stand']) ? $page['cert_stand'] : array();
        $s['CS_HEADING']   = fx_escape(isset($cs['heading']) ? $cs['heading'] : '');
        $s['CS_BODY']      = fx_escape(isset($cs['body'])    ? $cs['body']    : '');
        $s['CS_CTA_LABEL'] = fx_escape(isset($cs['cta_label']) ? $cs['cta_label'] : '');
    }

    // ============================================================
    // PORTFOLIO page sections (portfolio-track-record.html)
    // ============================================================
    if ($page_id === 'portfolio') {
        $h = isset($page['port_hero']) ? $page['port_hero'] : array();
        $s['PH_EYEBROW']   = fx_escape(isset($h['eyebrow'])   ? $h['eyebrow']   : '');
        $s['PH_HEADING_1'] = fx_escape(isset($h['heading_1']) ? $h['heading_1'] : '');
        $s['PH_HEADING_2'] = fx_escape(isset($h['heading_2']) ? $h['heading_2'] : '');
        $s['PH_SUBTITLE']  = fx_escape(isset($h['subtitle'])  ? $h['subtitle']  : '');

        $g = isset($page['port_grid']) ? $page['port_grid'] : array();
        for ($i = 1; $i <= 9; $i++) {
            $s["PG_CARD_{$i}_IMAGE_URL"] = fx_asset_url(isset($g["card_{$i}_image"]) ? $g["card_{$i}_image"] : '');
            $s["PG_CARD_{$i}_TAG"]       = fx_escape(isset($g["card_{$i}_tag"])   ? $g["card_{$i}_tag"]   : '');
            $s["PG_CARD_{$i}_TITLE"]     = fx_escape(isset($g["card_{$i}_title"]) ? $g["card_{$i}_title"] : '');
            $s["PG_CARD_{$i}_DESC"]      = fx_escape(isset($g["card_{$i}_desc"])  ? $g["card_{$i}_desc"]  : '');
        }
    }

    // ============================================================
    // PRODUCT page sections (product.html) — 5 product blocks +
    // intro banner + sticky sub-nav labels.
    // ============================================================
    if ($page_id === 'product') {
        // Intro banner
        $i = isset($page['product_intro']) ? $page['product_intro'] : array();
        $s['PP_INTRO_EYEBROW']        = fx_escape(isset($i['eyebrow'])        ? $i['eyebrow']        : '');
        $s['PP_INTRO_HEADING_1']      = fx_escape(isset($i['heading_1'])      ? $i['heading_1']      : '');
        $s['PP_INTRO_HEADING_ACCENT'] = fx_escape(isset($i['heading_accent']) ? $i['heading_accent'] : '');
        $s['PP_INTRO_SUBTITLE']       = fx_escape(isset($i['subtitle'])       ? $i['subtitle']       : '');

        // Sticky sub-nav labels
        $nv = isset($page['product_nav']) ? $page['product_nav'] : array();
        $s['PP_NAV_INDOOR_LABEL']     = fx_escape(isset($nv['indoor_label'])     ? $nv['indoor_label']     : '');
        $s['PP_NAV_OUTDOOR_LABEL']    = fx_escape(isset($nv['outdoor_label'])    ? $nv['outdoor_label']    : '');
        $s['PP_NAV_DATACENTER_LABEL'] = fx_escape(isset($nv['datacenter_label']) ? $nv['datacenter_label'] : '');
        $s['PP_NAV_POWER_LABEL']      = fx_escape(isset($nv['power_label'])      ? $nv['power_label']      : '');
        $s['PP_NAV_LIGHTING_LABEL']   = fx_escape(isset($nv['lighting_label'])   ? $nv['lighting_label']   : '');

        // Per-section builder: given the section-content array and the
        // placeholder-prefix (e.g. "PP_IN_"), plus counts, emit into $s.
        // Placed inline (not a helper) so PHP 5.6 has no closure issue.
        $product_blocks = array(
            array('key'=>'product_indoor',     'prefix'=>'PP_IN_',  'slides'=>5, 'features'=>8,  'specs'=>5, 'diagram'=>'single'),
            array('key'=>'product_outdoor',    'prefix'=>'PP_OUT_', 'slides'=>5, 'features'=>7,  'specs'=>5, 'diagram'=>'single'),
            array('key'=>'product_datacenter', 'prefix'=>'PP_DC_',  'slides'=>6, 'features'=>10, 'specs'=>5, 'diagram'=>'single'),
            array('key'=>'product_power',      'prefix'=>'PP_PW_',  'slides'=>3, 'features'=>6,  'specs'=>0, 'diagram'=>'none'),
            array('key'=>'product_lighting',   'prefix'=>'PP_LT_',  'slides'=>7, 'features'=>6,  'specs'=>7, 'diagram'=>'paired'),
        );
        foreach ($product_blocks as $b) {
            $p  = isset($page[$b['key']]) ? $page[$b['key']] : array();
            $px = $b['prefix'];

            $s[$px.'SECTION_TITLE'] = fx_escape(isset($p['section_title']) ? $p['section_title'] : '');
            $s[$px.'TAG_LABEL']     = fx_escape(isset($p['tag_label'])     ? $p['tag_label']     : '');
            $s[$px.'TITLE']         = fx_escape(isset($p['title'])         ? $p['title']         : '');
            $s[$px.'SUBTITLE']      = fx_escape(isset($p['subtitle'])      ? $p['subtitle']      : '');
            $s[$px.'DESC']          = fx_escape(isset($p['desc'])          ? $p['desc']          : '');

            for ($n = 1; $n <= $b['slides']; $n++) {
                $s[$px.'SLIDE_'.$n.'_URL'] = fx_asset_url(isset($p['slide_'.$n.'_image']) ? $p['slide_'.$n.'_image'] : '');
                $s[$px.'SLIDE_'.$n.'_ALT'] = fx_escape(isset($p['slide_'.$n.'_alt'])   ? $p['slide_'.$n.'_alt']   : '');
            }
            for ($n = 1; $n <= $b['features']; $n++) {
                $s[$px.'FEATURE_'.$n] = fx_escape(isset($p['feature_'.$n]) ? $p['feature_'.$n] : '');
            }
            for ($n = 1; $n <= $b['specs']; $n++) {
                $s[$px.'SPEC_'.$n.'_LABEL'] = fx_escape(isset($p['spec_'.$n.'_label']) ? $p['spec_'.$n.'_label'] : '');
                $s[$px.'SPEC_'.$n.'_VALUE'] = fx_escape(isset($p['spec_'.$n.'_value']) ? $p['spec_'.$n.'_value'] : '');
            }
            if ($b['diagram'] === 'single') {
                $s[$px.'DIAGRAM_TITLE']       = fx_escape(isset($p['diagram_title'])   ? $p['diagram_title']   : '');
                $s[$px.'DIAGRAM_VIDEO_URL']   = fx_asset_url(isset($p['diagram_video'])  ? $p['diagram_video']  : '');
                $s[$px.'DIAGRAM_POSTER_URL']  = fx_asset_url(isset($p['diagram_poster']) ? $p['diagram_poster'] : '');
            } elseif ($b['diagram'] === 'paired') {
                $s[$px.'DIAGRAM_TITLE']          = fx_escape(isset($p['diagram_title'])     ? $p['diagram_title']     : '');
                $s[$px.'DIAGRAM_1_LABEL']        = fx_escape(isset($p['diagram_1_label'])   ? $p['diagram_1_label']   : '');
                $s[$px.'DIAGRAM_1_VIDEO_URL']    = fx_asset_url(isset($p['diagram_1_video'])  ? $p['diagram_1_video']  : '');
                $s[$px.'DIAGRAM_1_POSTER_URL']   = fx_asset_url(isset($p['diagram_1_poster']) ? $p['diagram_1_poster'] : '');
                $s[$px.'DIAGRAM_2_LABEL']        = fx_escape(isset($p['diagram_2_label'])   ? $p['diagram_2_label']   : '');
                $s[$px.'DIAGRAM_2_VIDEO_URL']    = fx_asset_url(isset($p['diagram_2_video'])  ? $p['diagram_2_video']  : '');
                $s[$px.'DIAGRAM_2_POSTER_URL']   = fx_asset_url(isset($p['diagram_2_poster']) ? $p['diagram_2_poster'] : '');
            }
        }
    }

    // ============================================================
    // CONTACT page sections (quote.html)
    // ============================================================
    if ($page_id === 'contact') {
        // Hero
        $h = isset($page['contact_hero']) ? $page['contact_hero'] : array();
        $s['CT_HERO_EYEBROW']   = fx_escape(isset($h['eyebrow'])   ? $h['eyebrow']   : '');
        $s['CT_HERO_HEADING_1'] = fx_escape(isset($h['heading_1']) ? $h['heading_1'] : '');
        $s['CT_HERO_HEADING_2'] = fx_escape(isset($h['heading_2']) ? $h['heading_2'] : '');

        // Form labels
        $fm = isset($page['contact_form']) ? $page['contact_form'] : array();
        $s['CT_FORM_TITLE']    = fx_escape(isset($fm['title'])    ? $fm['title']    : '');
        $s['CT_FORM_REASSURE'] = fx_escape(isset($fm['reassure']) ? $fm['reassure'] : '');

        // Locations + map
        $l = isset($page['contact_locations']) ? $page['contact_locations'] : array();
        $s['CT_COMPANY_NAME'] = fx_escape(isset($l['company_name']) ? $l['company_name'] : '');
        // Addresses: preserve line breaks as <br/>.
        for ($n = 1; $n <= 2; $n++) {
            $s["CT_LOC{$n}_LABEL"] = fx_escape(isset($l["loc_{$n}_label"]) ? $l["loc_{$n}_label"] : '');
            $addr = isset($l["loc_{$n}_addr"]) ? $l["loc_{$n}_addr"] : '';
            // Normalise Windows/Mac newlines to \n, then escape, then swap.
            $addr = str_replace(array("\r\n", "\r"), "\n", $addr);
            $addr_escaped = fx_escape($addr);
            $s["CT_LOC{$n}_ADDR_HTML"] = str_replace("\n", "<br/>\n                ", $addr_escaped);
            $s["CT_LOC{$n}_MAPS_URL"]  = fx_escape(isset($l["loc_{$n}_maps_url"]) ? $l["loc_{$n}_maps_url"] : '#');
        }
        $s['CT_MAP_TAB_1_LABEL'] = fx_escape(isset($l['map_tab_1_label']) ? $l['map_tab_1_label'] : '');
        $s['CT_MAP_TAB_2_LABEL'] = fx_escape(isset($l['map_tab_2_label']) ? $l['map_tab_2_label'] : '');

        // Direct-contact tiles
        $d = isset($page['contact_direct']) ? $page['contact_direct'] : array();
        $s['CT_TILE_1_LABEL']        = fx_escape(isset($d['tile_1_label'])        ? $d['tile_1_label']        : '');
        $s['CT_TILE_1_VALUE']        = fx_escape(isset($d['tile_1_value'])        ? $d['tile_1_value']        : '');
        $s['CT_TILE_2_LABEL']        = fx_escape(isset($d['tile_2_label'])        ? $d['tile_2_label']        : '');
        $s['CT_TILE_2_VALUE']        = fx_escape(isset($d['tile_2_value'])        ? $d['tile_2_value']        : '');
        $s['CT_TILE_3_LABEL']        = fx_escape(isset($d['tile_3_label'])        ? $d['tile_3_label']        : '');
        $s['CT_TILE_3_VALUE_LINE_1'] = fx_escape(isset($d['tile_3_value_line_1']) ? $d['tile_3_value_line_1'] : '');
        $s['CT_TILE_3_VALUE_LINE_2'] = fx_escape(isset($d['tile_3_value_line_2']) ? $d['tile_3_value_line_2'] : '');
    }

    return $s;
}

/** Escape a UTF-8 string as a JavaScript string literal (with surrounding quotes). */
function fx_js_string($s) {
    $s = (string)$s;
    $out = '\'';
    $len = strlen($s);
    for ($i = 0; $i < $len; $i++) {
        $c = $s[$i];
        if ($c === '\\') { $out .= '\\\\'; continue; }
        if ($c === '\'') { $out .= '\\\''; continue; }
        if ($c === '<')  { $out .= '\\u003C'; continue; }
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
 * Render a specific page.
 * Returns HTML string on success, null on failure (with error messages
 * pushed into $errors_out).
 */
function fx_render_page($page_id, $content, &$errors_out = null) {
    $fieldmap = fx_load_fieldmap();
    if (!isset($fieldmap['pages'][$page_id])) {
        if (is_array($errors_out)) $errors_out[] = "Unknown page id: $page_id";
        return null;
    }
    $pageDef = $fieldmap['pages'][$page_id];
    $templatePath = fx_template_dir() . '/' . $pageDef['template'];
    $template = @file_get_contents($templatePath);
    if ($template === false) {
        if (is_array($errors_out)) $errors_out[] = "Template file missing: {$pageDef['template']}";
        return null;
    }

    $subs = fx_build_substitutions($page_id, $content);
    $map = array();
    foreach ($subs as $k => $v) $map['{{' . $k . '}}'] = $v;
    $rendered = strtr($template, $map);

    // Strip empty feature/list items so the CSS tick (::before) doesn't render
    // for placeholders the editor left blank.  The pattern `<li><span></span></li>`
    // only appears in .ps-feature-grid UL blocks (product page), so a global
    // sweep is safe.  Also strips a leading newline+indent so the resulting
    // HTML stays tidy.
    $rendered = preg_replace(
        '#\n\s*<li><span>\s*</span></li>#',
        '',
        $rendered
    );

    if (preg_match_all('/\{\{[A-Z0-9_]+\}\}/', $rendered, $m)) {
        if (is_array($errors_out)) {
            $errors_out[] = "[$page_id] Unfilled placeholders: " . implode(', ', array_unique($m[0]));
        }
        return null;
    }
    return $rendered;
}

/** Legacy single-page render for backward-compat callers.  Uses 'home' by default. */
function fx_render($content, &$errors_out = null) {
    return fx_render_page('home', $content, $errors_out);
}
