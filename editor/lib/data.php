<?php
// Furutec Editor — data layer.
// Reads/writes /data/content.php and /data/field-map.php as PHP arrays.
// Using PHP arrays instead of JSON because json_encode is not compiled
// into this host's PHP 5.6 install (confirmed 2026-07-31 during contact
// form deployment).

require_once __DIR__ . '/util.php';

// Base paths, resolved relative to /public_html.
function fx_base_path() {
    // editor/lib -> up 2 = public_html
    return realpath(__DIR__ . '/../..');
}
function fx_data_path()      { return fx_base_path() . '/data/content.php'; }
function fx_fieldmap_path()  { return fx_base_path() . '/data/field-map.php'; }
function fx_template_dir()   { return fx_base_path() . '/editor/templates'; }
function fx_template_path()  { return fx_base_path() . '/editor/templates/home.template.html'; } // legacy single-page
function fx_live_index_path(){ return fx_base_path() . '/index.html'; }
function fx_live_path($file) { return fx_base_path() . '/' . ltrim($file, '/'); }
function fx_backups_dir()    { return fx_base_path() . '/backups'; }
function fx_uploads_dir()    { return fx_base_path() . '/assets/uploads'; }
function fx_config_path()    { return fx_base_path() . '/config/config.php'; }

/** Load current draft content as a PHP array. */
function fx_load_content() {
    $p = fx_data_path();
    if (!is_file($p)) return array();
    $data = include $p;
    return is_array($data) ? $data : array();
}

/** Save the draft content back as a PHP array file (atomic). */
function fx_save_content($data) {
    $p = fx_data_path();
    $header = "<?php\n// Furutec Editor content — auto-written by /editor/save.php.\n// Last update: " . date('Y-m-d H:i:s') . "\n\nreturn ";
    $body = fx_var_export($data, 0) . ";\n";
    $tmp  = $p . '.tmp';
    if (file_put_contents($tmp, $header . $body, LOCK_EX) === false) return false;
    // Atomic rename.
    return rename($tmp, $p);
}

/** Load the field map (readonly, defines the editor UI). */
function fx_load_fieldmap() {
    $p = fx_fieldmap_path();
    if (!is_file($p)) return array('sections' => array());
    $data = include $p;
    return is_array($data) ? $data : array('sections' => array());
}

/**
 * Pretty-print a PHP value as an array/scalar literal.
 * Cleaner output than var_export() for our nested associative arrays.
 * Assumes UTF-8 string values with no control characters.
 */
function fx_var_export($v, $depth) {
    $indent = str_repeat('    ', $depth);
    $childIndent = str_repeat('    ', $depth + 1);
    if ($v === null)  return 'null';
    if ($v === true)  return 'true';
    if ($v === false) return 'false';
    if (is_int($v) || is_float($v)) return (string)$v;
    if (is_string($v)) {
        // Single-quoted PHP string, escaping only \ and '
        $escaped = str_replace(array('\\', "'"), array('\\\\', "\\'"), $v);
        return "'" . $escaped . "'";
    }
    if (is_array($v)) {
        if (empty($v)) return 'array()';
        // Sequential vs associative.
        $isSeq = true; $i = 0;
        foreach ($v as $k => $_) {
            if ($k !== $i) { $isSeq = false; break; }
            $i++;
        }
        $lines = array('array(');
        foreach ($v as $k => $item) {
            if ($isSeq) {
                $lines[] = $childIndent . fx_var_export($item, $depth + 1) . ',';
            } else {
                $keyLit = fx_var_export($k, 0);
                $valLit = fx_var_export($item, $depth + 1);
                $lines[] = $childIndent . $keyLit . ' => ' . $valLit . ',';
            }
        }
        $lines[] = $indent . ')';
        return implode("\n", $lines);
    }
    return 'null';
}
