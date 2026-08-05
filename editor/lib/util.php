<?php
// Furutec Editor — generic helpers.
// PHP 5.6-safe.  No dependencies.  No json_encode/decode (not compiled on this host).

/** Escape a string for safe insertion into HTML text/attribute context. */
function fx_escape($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/** URL-encode each path segment (preserving '/'). */
function fx_url_path($path) {
    $parts = explode('/', (string)$path);
    return implode('/', array_map('rawurlencode', $parts));
}

/**
 * Convert plain text with **bold** markdown into safe HTML.
 * Escapes HTML first, then converts **...** to <strong>...</strong>.
 */
function fx_markdown_to_html($text) {
    $escaped = fx_escape($text);
    return preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $escaped);
}

/**
 * Hand-rolled JSON encoder for the small responses this app returns.
 * Handles: null, bool, int, float, strings, sequential arrays, associative arrays.
 * Everything else becomes null.  UTF-8-safe.
 */
function fx_json_encode($v) {
    if ($v === null)               return 'null';
    if ($v === true)               return 'true';
    if ($v === false)              return 'false';
    if (is_int($v) || is_float($v))return (string)$v;
    if (is_string($v))             return fx_json_string($v);
    if (is_array($v)) {
        // sequential vs associative
        $isSeq = true;
        $i = 0;
        foreach ($v as $k => $_) {
            if ($k !== $i) { $isSeq = false; break; }
            $i++;
        }
        if ($isSeq) {
            $parts = array();
            foreach ($v as $item) $parts[] = fx_json_encode($item);
            return '[' . implode(',', $parts) . ']';
        }
        $parts = array();
        foreach ($v as $k => $item) {
            $parts[] = fx_json_string((string)$k) . ':' . fx_json_encode($item);
        }
        return '{' . implode(',', $parts) . '}';
    }
    return 'null';
}

/** Escape a UTF-8 string as a JSON string literal (with surrounding quotes). */
function fx_json_string($s) {
    $out = '"';
    $len = strlen($s);
    for ($i = 0; $i < $len; $i++) {
        $c = $s[$i];
        $o = ord($c);
        if      ($c === '"')  $out .= '\\"';
        elseif  ($c === '\\') $out .= '\\\\';
        elseif  ($c === "\n") $out .= '\\n';
        elseif  ($c === "\r") $out .= '\\r';
        elseif  ($c === "\t") $out .= '\\t';
        elseif  ($c === "\b") $out .= '\\b';
        elseif  ($c === "\f") $out .= '\\f';
        elseif  ($o < 0x20)   $out .= sprintf('\\u%04x', $o);
        else                  $out .= $c;
    }
    $out .= '"';
    return $out;
}

/**
 * Basic hand-rolled JSON decoder for the very small POST bodies this
 * app parses (only used in save.php for the "changes" payload from the
 * editor UI).  Handles: null, true, false, numbers, strings, arrays,
 * objects (into assoc arrays).  Returns null on parse failure.
 */
function fx_json_decode($s) {
    $s = (string)$s;
    $pos = 0;
    $result = fx_json_parse_value($s, $pos);
    if ($result === '__FX_JSON_ERR__') return null;
    fx_json_skip_ws($s, $pos);
    if ($pos !== strlen($s)) return null;
    return $result;
}
function fx_json_skip_ws($s, &$pos) {
    $len = strlen($s);
    while ($pos < $len && ($s[$pos] === ' ' || $s[$pos] === "\t" || $s[$pos] === "\n" || $s[$pos] === "\r")) $pos++;
}
function fx_json_parse_value($s, &$pos) {
    fx_json_skip_ws($s, $pos);
    if ($pos >= strlen($s)) return '__FX_JSON_ERR__';
    $c = $s[$pos];
    if ($c === '"') return fx_json_parse_string($s, $pos);
    if ($c === '{') return fx_json_parse_object($s, $pos);
    if ($c === '[') return fx_json_parse_array($s, $pos);
    if ($c === 't' || $c === 'f' || $c === 'n') return fx_json_parse_literal($s, $pos);
    if ($c === '-' || ($c >= '0' && $c <= '9')) return fx_json_parse_number($s, $pos);
    return '__FX_JSON_ERR__';
}
function fx_json_parse_string($s, &$pos) {
    if ($s[$pos] !== '"') return '__FX_JSON_ERR__';
    $pos++;
    $out = '';
    $len = strlen($s);
    while ($pos < $len) {
        $c = $s[$pos];
        if ($c === '"') { $pos++; return $out; }
        if ($c === '\\') {
            $pos++;
            if ($pos >= $len) return '__FX_JSON_ERR__';
            $e = $s[$pos]; $pos++;
            if      ($e === '"')  $out .= '"';
            elseif  ($e === '\\') $out .= '\\';
            elseif  ($e === '/')  $out .= '/';
            elseif  ($e === 'n')  $out .= "\n";
            elseif  ($e === 'r')  $out .= "\r";
            elseif  ($e === 't')  $out .= "\t";
            elseif  ($e === 'b')  $out .= "\b";
            elseif  ($e === 'f')  $out .= "\f";
            elseif  ($e === 'u') {
                if ($pos + 4 > $len) return '__FX_JSON_ERR__';
                $hex = substr($s, $pos, 4); $pos += 4;
                $cp = hexdec($hex);
                // BMP only — no surrogate-pair reassembly (fine for our text).
                if ($cp < 0x80) $out .= chr($cp);
                elseif ($cp < 0x800) $out .= chr(0xC0 | ($cp >> 6)) . chr(0x80 | ($cp & 0x3F));
                else $out .= chr(0xE0 | ($cp >> 12)) . chr(0x80 | (($cp >> 6) & 0x3F)) . chr(0x80 | ($cp & 0x3F));
            } else return '__FX_JSON_ERR__';
        } else {
            $out .= $c; $pos++;
        }
    }
    return '__FX_JSON_ERR__';
}
function fx_json_parse_number($s, &$pos) {
    $start = $pos;
    if ($s[$pos] === '-') $pos++;
    while ($pos < strlen($s) && (($s[$pos] >= '0' && $s[$pos] <= '9') || $s[$pos] === '.' || $s[$pos] === 'e' || $s[$pos] === 'E' || $s[$pos] === '+' || $s[$pos] === '-')) $pos++;
    $numStr = substr($s, $start, $pos - $start);
    if ($numStr === '' || $numStr === '-') return '__FX_JSON_ERR__';
    if (strpos($numStr, '.') !== false || strpos($numStr, 'e') !== false || strpos($numStr, 'E') !== false) return (float)$numStr;
    return (int)$numStr;
}
function fx_json_parse_literal($s, &$pos) {
    if (substr($s, $pos, 4) === 'true')  { $pos += 4; return true; }
    if (substr($s, $pos, 5) === 'false') { $pos += 5; return false; }
    if (substr($s, $pos, 4) === 'null')  { $pos += 4; return null; }
    return '__FX_JSON_ERR__';
}
function fx_json_parse_array($s, &$pos) {
    if ($s[$pos] !== '[') return '__FX_JSON_ERR__';
    $pos++;
    $out = array();
    fx_json_skip_ws($s, $pos);
    if ($pos < strlen($s) && $s[$pos] === ']') { $pos++; return $out; }
    while (true) {
        $v = fx_json_parse_value($s, $pos);
        if ($v === '__FX_JSON_ERR__') return '__FX_JSON_ERR__';
        $out[] = $v;
        fx_json_skip_ws($s, $pos);
        if ($pos >= strlen($s)) return '__FX_JSON_ERR__';
        if ($s[$pos] === ',') { $pos++; continue; }
        if ($s[$pos] === ']') { $pos++; return $out; }
        return '__FX_JSON_ERR__';
    }
    return $out;
}
function fx_json_parse_object($s, &$pos) {
    if ($s[$pos] !== '{') return '__FX_JSON_ERR__';
    $pos++;
    $out = array();
    fx_json_skip_ws($s, $pos);
    if ($pos < strlen($s) && $s[$pos] === '}') { $pos++; return $out; }
    while (true) {
        fx_json_skip_ws($s, $pos);
        $k = fx_json_parse_string($s, $pos);
        if ($k === '__FX_JSON_ERR__') return '__FX_JSON_ERR__';
        fx_json_skip_ws($s, $pos);
        if ($pos >= strlen($s) || $s[$pos] !== ':') return '__FX_JSON_ERR__';
        $pos++;
        $v = fx_json_parse_value($s, $pos);
        if ($v === '__FX_JSON_ERR__') return '__FX_JSON_ERR__';
        $out[$k] = $v;
        fx_json_skip_ws($s, $pos);
        if ($pos >= strlen($s)) return '__FX_JSON_ERR__';
        if ($s[$pos] === ',') { $pos++; continue; }
        if ($s[$pos] === '}') { $pos++; return $out; }
        return '__FX_JSON_ERR__';
    }
    return $out;
}

/** Send a JSON response and exit. */
function fx_json_response($data, $status = 200) {
    while (ob_get_level() > 0) ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    if ($status !== 200) {
        // We deliberately do NOT set non-200 status codes on this host —
        // LiteSpeed drops the body of non-200 responses.  Instead, the JSON
        // body carries { ok: false, error: ... } and the client checks .ok.
    }
    echo fx_json_encode($data);
    exit;
}

/** Sanitize an uploaded filename: strip path, keep dashes/dots, force lowercase. */
function fx_sanitize_filename($name) {
    $name = basename((string)$name);
    // Split into base + ext.
    $dot = strrpos($name, '.');
    if ($dot === false || $dot === 0) return null;
    $base = substr($name, 0, $dot);
    $ext  = strtolower(substr($name, $dot + 1));
    // Normalize base.
    $base = preg_replace('/[^A-Za-z0-9]+/', '-', $base);
    $base = trim($base, '-');
    if ($base === '') $base = 'file';
    if (strlen($base) > 60) $base = substr($base, 0, 60);
    return strtolower($base) . '.' . $ext;
}

/**
 * Timestamp string suitable for filenames and log lines (server local time).
 * Format: 2026-08-05_23-45-12
 */
function fx_ts() { return date('Y-m-d_H-i-s'); }
