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

        // ---- Hero background: single looping video, or an image slideshow --
        // Built here rather than branched in the template because the
        // template engine is a flat strtr() with no conditionals.  Emits
        // two placeholders: the background layer itself, and the dots +
        // timer script (empty string when there's nothing to cycle).
        $heroMode = isset($hero['bg_mode']) ? $hero['bg_mode'] : 'video';
        if ($heroMode !== 'slideshow') $heroMode = 'video';

        // Collect the non-empty slide slots, preserving slot order.
        $heroSlides = array();
        for ($i = 1; $i <= 6; $i++) {
            $v = isset($hero["slide_{$i}_image"]) ? trim((string)$hero["slide_{$i}_image"]) : '';
            if ($v !== '') $heroSlides[] = $v;
        }
        // A slideshow with nothing in it would render a black hero — fall
        // back to the video so a half-configured switch can't blank the page.
        if ($heroMode === 'slideshow' && empty($heroSlides)) $heroMode = 'video';

        $interval = isset($hero['slide_interval']) ? (int)$hero['slide_interval'] : 5;
        if ($interval < 2)  $interval = 2;
        if ($interval > 30) $interval = 30;

        if ($heroMode === 'video') {
            // Focal point: which part survives the cover-crop.  Set in the
            // editor; defaults to centre, and anything that doesn't look like
            // an object-position pair is ignored rather than injected.
            $heroFocus = isset($hero['bg_video_focus']) ? trim((string)$hero['bg_video_focus']) : '';
            if (!preg_match('/^\d{1,3}% \d{1,3}%$/', $heroFocus)) $heroFocus = '50% 50%';
            $s['HERO_BG_HTML'] =
                '<video id="hero-bg-video" autoplay muted loop playsinline preload="auto"'
              . ' style="position:absolute;top:50%;left:50%;min-width:100%;min-height:100%;width:auto;height:auto;transform:translate(-50%,-50%);object-fit:cover;object-position:' . $heroFocus . ';">'
              . '<source src="assets/' . $s['HERO_BG_VIDEO_URL'] . '" type="video/mp4"/>'
              . '</video>';
            $s['HERO_SLIDESHOW_HTML'] = '';
        } else {
            $slidesHtml = '<div class="fxhero-slides" data-interval="' . ($interval * 1000) . '">';
            foreach ($heroSlides as $idx => $img) {
                $slidesHtml .= '<div class="fxhero-slide' . ($idx === 0 ? ' is-on' : '')
                             . '" style="background-image:url(\'assets/' . fx_url_path($img) . '\');"></div>';
            }
            $slidesHtml .= '</div>';
            $s['HERO_BG_HTML'] = $slidesHtml;

            // Dots + auto-advance.  Only when there's more than one slide —
            // a single image is just a still background.
            if (count($heroSlides) > 1) {
                $dots = '<div class="fxhero-dots" role="tablist" aria-label="Hero slides">';
                foreach ($heroSlides as $idx => $img) {
                    $dots .= '<button type="button" class="fxhero-dot' . ($idx === 0 ? ' is-on' : '')
                           . '" data-i="' . $idx . '" role="tab"'
                           . ' aria-label="Show slide ' . ($idx + 1) . '"'
                           . ' aria-selected="' . ($idx === 0 ? 'true' : 'false') . '"></button>';
                }
                $dots .= '</div>';
                $s['HERO_SLIDESHOW_HTML'] = $dots;
            } else {
                $s['HERO_SLIDESHOW_HTML'] = '';
            }
        }

        // Hero certification bar (7 logo + alt-text pairs, editor-editable).
        // Fallbacks preserve the original hardcoded values for a safe rollout —
        // if content.php doesn't yet have `home.hero_certs`, the site renders
        // exactly the same 7 marks it always has.
        $hc = isset($page['hero_certs']) ? $page['hero_certs'] : array();
        $hero_cert_defaults = array(
            1 => array('img'=>'CERTS LOGOS/UL_Mark (1).svg',              'alt'=>'UL'),
            2 => array('img'=>'CERTS LOGOS/Dekra.png',                    'alt'=>'DEKRA'),
            3 => array('img'=>'certs/intertek.svg',                       'alt'=>'Intertek'),
            4 => array('img'=>'CERTS LOGOS/SGBP.jpg',                     'alt'=>'SGBC'),
            5 => array('img'=>'CERTS LOGOS/TUV.png',                      'alt'=>'TÜV SÜD'),
            6 => array('img'=>'CERTS LOGOS/ISO 9001 Certified_col.png',   'alt'=>'ISO 9001 Certified'),
            7 => array('img'=>'CERTS LOGOS/Malaysian Brand.png',          'alt'=>'Malaysian Brand'),
        );
        for ($i = 1; $i <= 7; $i++) {
            $img = isset($hc["cert_{$i}_image"]) && $hc["cert_{$i}_image"] !== ''
                ? $hc["cert_{$i}_image"] : $hero_cert_defaults[$i]['img'];
            $alt = isset($hc["cert_{$i}_alt"]) && $hc["cert_{$i}_alt"] !== ''
                ? $hc["cert_{$i}_alt"] : $hero_cert_defaults[$i]['alt'];
            $s["HERO_CERT_{$i}_URL"] = fx_asset_url($img);
            $s["HERO_CERT_{$i}_ALT"] = fx_escape($alt);
        }

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
        // Same cover-crop anchor as the hero, for the company background video.
        $cmpFocus = isset($c['bg_video_focus']) ? trim((string)$c['bg_video_focus']) : '';
        if (!preg_match('/^\d{1,3}% \d{1,3}%$/', $cmpFocus)) $cmpFocus = '50% 50%';
        $s['CMP_BG_FOCUS'] = $cmpFocus;
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

        // Action buttons shared by all five product blocks.  Fallbacks keep
        // the buttons labelled even on a content.php saved before these
        // fields existed (the server copy is older than the field map).
        $bt = isset($page['product_buttons']) ? $page['product_buttons'] : array();
        $btContact = isset($bt['contact_label'])   ? trim((string)$bt['contact_label'])   : '';
        $btCatLbl  = isset($bt['catalogue_label']) ? trim((string)$bt['catalogue_label']) : '';
        $btCatUrl  = isset($bt['catalogue_url'])   ? trim((string)$bt['catalogue_url'])   : '';
        if ($btContact === '') $btContact = 'Contact Us';
        if ($btCatLbl  === '') $btCatLbl  = 'Request a Catalogue';
        if ($btCatUrl  === '') $btCatUrl  = 'quote.html';
        $s['PP_BTN_CONTACT_LABEL']   = fx_escape($btContact);
        $s['PP_BTN_CATALOGUE_LABEL'] = fx_escape($btCatLbl);
        $s['PP_BTN_CATALOGUE_URL']   = fx_escape($btCatUrl);

        // Per-section builder: given the section-content array and the
        // placeholder-prefix (e.g. "PP_IN_"), plus counts, emit into $s.
        // Placed inline (not a helper) so PHP 5.6 has no closure issue.
        // Every product gallery now offers 10 slots.  Blank ones are skipped
        // when the gallery HTML is built below, so a section can use 3 images
        // or 10 without any template change.
        $product_blocks = array(
            array('key'=>'product_indoor',     'prefix'=>'PP_IN_',  'slides'=>10, 'features'=>8,  'specs'=>8, 'diagram'=>'single'),
            array('key'=>'product_outdoor',    'prefix'=>'PP_OUT_', 'slides'=>10, 'features'=>7,  'specs'=>8, 'diagram'=>'single'),
            array('key'=>'product_datacenter', 'prefix'=>'PP_DC_',  'slides'=>10, 'features'=>10, 'specs'=>8, 'diagram'=>'single'),
            array('key'=>'product_power',      'prefix'=>'PP_PW_',  'slides'=>10, 'features'=>6,  'specs'=>8, 'diagram'=>'none'),
            array('key'=>'product_lighting',   'prefix'=>'PP_LT_',  'slides'=>10, 'features'=>6,  'specs'=>8, 'diagram'=>'paired'),
        );
        foreach ($product_blocks as $b) {
            $p  = isset($page[$b['key']]) ? $page[$b['key']] : array();
            $px = $b['prefix'];

            $s[$px.'SECTION_TITLE'] = fx_escape(isset($p['section_title']) ? $p['section_title'] : '');
            $s[$px.'TAG_LABEL']     = fx_escape(isset($p['tag_label'])     ? $p['tag_label']     : '');
            $s[$px.'TITLE']         = fx_escape(isset($p['title'])         ? $p['title']         : '');
            $s[$px.'SUBTITLE']      = fx_escape(isset($p['subtitle'])      ? $p['subtitle']      : '');
            $s[$px.'DESC']          = fx_escape(isset($p['desc'])          ? $p['desc']          : '');

            // Per-slot placeholders are still emitted (harmless, and any
            // template that references one directly keeps working).
            for ($n = 1; $n <= $b['slides']; $n++) {
                $s[$px.'SLIDE_'.$n.'_URL'] = fx_asset_url(isset($p['slide_'.$n.'_image']) ? $p['slide_'.$n.'_image'] : '');
                $s[$px.'SLIDE_'.$n.'_ALT'] = fx_escape(isset($p['slide_'.$n.'_alt'])   ? $p['slide_'.$n.'_alt']   : '');
            }

            // ---- Gallery, built server-side ---------------------------
            // The markup used to be hardcoded per section, which pinned each
            // gallery to a fixed slide count and a hardcoded "/ N" counter.
            // Generating it here means empty slots simply don't appear, the
            // counter total is always right, and the thumbnail strip always
            // matches the stage.  The existing [data-gallery] script reads
            // slides.length at runtime, so it adapts with no changes.
            $filled = array();
            for ($n = 1; $n <= $b['slides']; $n++) {
                $img = isset($p['slide_'.$n.'_image']) ? trim((string)$p['slide_'.$n.'_image']) : '';
                if ($img === '') continue;
                $filled[] = array(
                    'url' => fx_asset_url($img),
                    'alt' => fx_escape(isset($p['slide_'.$n.'_alt']) ? $p['slide_'.$n.'_alt'] : ''),
                );
            }
            $total = count($filled);

            if ($total === 0) {
                // Nothing to show — emit an empty gallery rather than a
                // stage full of broken <img> tags.
                $s[$px.'GALLERY_HTML'] = '';
            } else {
                $g  = '<div class="ps-gallery" data-gallery>';
                $g .= '<div class="ps-gallery-stage">';
                foreach ($filled as $idx => $sl) {
                    $g .= '<div class="ps-gallery-slide' . ($idx === 0 ? ' is-active' : '') . '">'
                        . '<img src="' . $sl['url'] . '" alt="' . $sl['alt'] . '"'
                        . ($idx === 0 ? '' : ' loading="lazy"') . '/></div>';
                }
                // Arrows and counter only earn their place with 2+ images.
                if ($total > 1) {
                    $g .= '<button type="button" class="ps-gallery-nav ps-gallery-prev" aria-label="Previous image">'
                        . '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>'
                        . '</button>'
                        . '<button type="button" class="ps-gallery-nav ps-gallery-next" aria-label="Next image">'
                        . '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg>'
                        . '</button>'
                        . '<div class="ps-gallery-counter"><span data-counter-current>1</span> / ' . $total . '</div>';
                }
                $g .= '</div>';

                if ($total > 1) {
                    $g .= '<div class="ps-gallery-thumbs">';
                    foreach ($filled as $idx => $sl) {
                        $g .= '<button type="button" class="ps-gallery-thumb' . ($idx === 0 ? ' is-active' : '') . '"'
                            . ' aria-label="View image ' . ($idx + 1) . '">'
                            . '<img src="' . $sl['url'] . '" alt="" loading="lazy"/></button>';
                    }
                    $g .= '</div>';
                }
                $g .= '</div>';
                $s[$px.'GALLERY_HTML'] = $g;
            }
            for ($n = 1; $n <= $b['features']; $n++) {
                $s[$px.'FEATURE_'.$n] = fx_escape(isset($p['feature_'.$n]) ? $p['feature_'.$n] : '');
            }
            // ---- Technical Specification, built server-side -------------
            // Every product offers the same 8 rows (the client asked for one
            // standard table).  Every labelled row is shown, and a value that
            // hasn't been supplied yet prints as "-" — the client wants the
            // full 8-row shape visible even while they fill the gaps.  The
            // table (and its heading) only appears once at least one row has
            // a real value, so Power Monitoring, which has no tech specs,
            // shows nothing until they choose to add some.
            $specRows = '';
            $specFilled = 0;
            for ($n = 1; $n <= $b['specs']; $n++) {
                $lbl = isset($p['spec_'.$n.'_label']) ? trim((string)$p['spec_'.$n.'_label']) : '';
                $val = isset($p['spec_'.$n.'_value']) ? trim((string)$p['spec_'.$n.'_value']) : '';
                $s[$px.'SPEC_'.$n.'_LABEL'] = fx_escape($lbl);
                $s[$px.'SPEC_'.$n.'_VALUE'] = fx_escape($val);
                if ($lbl === '') continue;
                if ($val !== '') $specFilled++;
                // A " | " in the value is the client's natural separator between
                // variants ("IP65 / IP66 (…) | IP40 / IP54 (…)"): render it as
                // a line break so each variant sits on its own line.
                $valHtml = ($val === '') ? '-'
                    : implode('<br/>', array_map('fx_escape', preg_split('/\s*\|\s*/', $val)));
                $specRows .= '<div><span class="label">' . fx_escape($lbl) . '</span>'
                           . '<span class="value">' . $valHtml . '</span></div>';
            }
            $s[$px.'SPEC_BLOCK_HTML'] = ($specFilled === 0) ? ''
                : '<div class="ps-content-section-label">Technical Specification</div>'
                . '<div class="ps-spec-rows">' . $specRows . '</div>'
                . '<div class="ps-content-divider"></div>';
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

/**
 * Decorate a rendered page for display inside the editor's preview iframe.
 *
 * Shared by preview.php (the initial load) and livepreview.php (every
 * re-render as you type).  It has to be identical in both: the editor diffs
 * one against the other, so a ribbon or <base> tag present in only one of
 * them would register as a content change and get patched in or out on the
 * first keystroke.
 */
function fx_preview_decorate($rendered, $page_id) {
    // Bottom-left, not top-right.  Top-right sat directly on the sticky
    // header — over the nav, and over the burger button at narrower widths —
    // so the one part of the page you could never inspect was the part every
    // visitor sees first.  Both bottom corners are free of fixed elements on
    // every template, and pointer-events:none keeps it from eating clicks
    // wherever it happens to land.
    //
    // Kept small and translucent rather than dropped altogether: opened
    // outside the editor this is the only thing distinguishing a draft from
    // the real site.  Inside the editor the toolbar already says
    // "Preview · <page> (draft)", so it's just a quiet second confirmation.
    $ribbon = '<div style="position:fixed;bottom:0;left:0;z-index:99999;'
            . 'background:rgba(120,72,0,0.80);color:#fff;padding:3px 9px;'
            . 'font-family:-apple-system,sans-serif;font-size:10px;font-weight:600;'
            . 'letter-spacing:0.05em;text-transform:uppercase;line-height:1.5;'
            . 'border-top-right-radius:4px;pointer-events:none;'
            . '-webkit-user-select:none;user-select:none;">'
            . 'Draft · ' . fx_escape($page_id) . '</div>';
    // target="_blank" ensures that if Amy clicks any link inside the draft
    // preview, it opens in a new tab instead of hijacking the iframe (which
    // would replace the draft with the actual live page and confuse her).
    $baseTag = '<base href="../" target="_blank"/>';
    $rendered = preg_replace('/(<head[^>]*>)/i', '$1' . $baseTag, $rendered, 1);
    $rendered = preg_replace('/(<body[^>]*>)/i', '$1' . $ribbon, $rendered, 1);
    return $rendered;
}
