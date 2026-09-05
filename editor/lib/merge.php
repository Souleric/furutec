<?php
// Merging incoming editor values over the stored content, whitelist-validated
// against the field map.
//
// Lives here rather than inside save.php because two callers need the exact
// same result: save.php (which then writes it) and livepreview.php (which
// only renders it).  If they validated differently, the live preview would
// show something the save wouldn't actually produce — the one thing a
// preview must never do.

// Apply one section's incoming values onto $updated_section, coercing and
// bounding each value by its declared type.
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
        } elseif ($type === 'select') {
            // Only accept one of the declared option keys.  Anything else
            // falls back to the first option, so a tampered payload can't
            // put an arbitrary string into a value the template branches on.
            $opts = isset($fdef['options']) && is_array($fdef['options']) ? $fdef['options'] : array();
            $keys = array_keys($opts);
            $val  = trim($val);
            if (!in_array($val, $keys, true)) $val = isset($keys[0]) ? $keys[0] : '';
        } elseif ($type === 'hidden') {
            // Machine-written values.  The only one so far is a video focal
            // point, which must look exactly like an object-position pair —
            // anything else is discarded rather than passed through into a
            // style attribute.
            $val = trim($val);
            if ($val !== '' && !preg_match('/^\d{1,3}% \d{1,3}%$/', $val)) $val = '';
        } elseif ($type === 'number') {
            // Clamp into [min, max] and store as a plain integer string.
            $n   = (int)trim($val);
            $lo  = isset($fdef['min']) ? (int)$fdef['min'] : 0;
            $hi  = isset($fdef['max_value']) ? (int)$fdef['max_value'] : 9999;
            if ($n < $lo) $n = $lo;
            if ($n > $hi) $n = $hi;
            $val = (string)$n;
        }
        if ($max > 0 && strlen($val) > $max) $val = substr($val, 0, $max);
        $updated_section[$fname] = $val;
    }
}

// Merge $incoming (nested: [page|'shared'][section][field]) over $current.
// Only sections and fields declared in the field map are considered, so an
// unknown key in the payload can never reach content.php or a template.
function fx_merge_content($current, $incoming, $fieldmap) {
    $updated = $current;
    if (!is_array($incoming)) return $updated;

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

    return $updated;
}
