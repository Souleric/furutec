<?php
// Furutec Editor — Media Library backend.
//
// One endpoint, several actions (keeps the deploy surface small):
//   action=list    &path=...            → folders + files at that path
//   action=mkdir   &path=...&name=...   → create a sub-folder
//   action=delete  &path=...            → delete a file or an empty-of-in-use folder
//
// The library root is /assets/.  Every path that comes in from the browser is
// resolved with realpath() and checked to still sit inside that root, so a
// crafted "../../config" can't escape.  Deletes are refused when the file is
// still referenced by a field in content.php — the client is told exactly
// which fields use it so they can swap those first.
//
// PHP 5.6-safe.  No json_encode (not compiled on this host) — see lib/util.php.

require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/data.php';
require_once __DIR__ . '/lib/util.php';

if (!fx_is_logged_in()) fx_json_response(array('ok' => false, 'error' => 'Not signed in.'));
$csrfHeader = isset($_SERVER['HTTP_X_CSRF']) ? $_SERVER['HTTP_X_CSRF'] : '';
if (!fx_csrf_check($csrfHeader)) fx_json_response(array('ok' => false, 'error' => 'Session expired.'));

// ---------------------------------------------------------------- paths ----

function fx_lib_root() {
    return fx_base_path() . '/assets';
}

/**
 * Resolve a browser-supplied relative path against the library root.
 * Returns the absolute path, or false if it escapes the root / doesn't exist.
 */
function fx_lib_resolve($rel) {
    $rel = str_replace('\\', '/', (string)$rel);
    $rel = trim($rel, '/');
    // Reject traversal before touching the filesystem.
    if ($rel !== '' && (strpos($rel, '..') !== false || strpos($rel, "\0") !== false)) return false;

    $root = realpath(fx_lib_root());
    if ($root === false) return false;
    $full = ($rel === '') ? $root : $root . '/' . $rel;
    $real = realpath($full);
    if ($real === false) return false;
    // Must be the root itself or sit underneath it.
    if ($real !== $root && strpos($real, $root . DIRECTORY_SEPARATOR) !== 0) return false;
    return $real;
}

/** Absolute path -> path relative to the library root ('' for the root). */
function fx_lib_relative($abs) {
    $root = realpath(fx_lib_root());
    if ($abs === $root) return '';
    return ltrim(substr($abs, strlen($root)), '/\\');
}

/** Which extensions the library surfaces, and how we classify them. */
function fx_lib_kind($filename) {
    $dot = strrpos($filename, '.');
    if ($dot === false) return null;
    $ext = strtolower(substr($filename, $dot + 1));
    $images = array('jpg', 'jpeg', 'png', 'webp', 'svg', 'gif', 'avif');
    $videos = array('mp4', 'webm', 'mov');
    if (in_array($ext, $images, true)) return 'image';
    if (in_array($ext, $videos, true)) return 'video';
    return null;   // pdf, etc. — not offered as a media choice
}

// ---------------------------------------------------------------- usage ----

/**
 * Build an index of every asset currently referenced by a media field:
 *   'penang-plant-aerial.jpg' => array('Homepage → Bukit Minyak Facilities → Aerial plant photo')
 *
 * Also returns, per asset, the set of page-categories it belongs to — that's
 * what drives the virtual category chips in the picker.  Nothing moves on
 * disk; categories are derived fresh on every call so they can never drift
 * out of sync with content.php.
 */
function fx_lib_usage_index() {
    $map      = fx_load_fieldmap();
    $content  = fx_load_content();
    $sections = isset($map['sections'])        ? $map['sections']        : array();
    $pages    = isset($map['pages'])           ? $map['pages']           : array();
    $sharedIds= isset($map['shared_sections']) ? $map['shared_sections'] : array();

    // (content-prefix, section-id, section-def, category-key, category-label)
    $scopes = array();
    foreach ($sharedIds as $sid) {
        if (isset($sections[$sid])) {
            $scopes[] = array('shared', $sid, $sections[$sid], 'shared', 'Brand & Shared');
        }
    }
    foreach ($pages as $pid => $pdef) {
        $plabel = isset($pdef['label']) ? $pdef['label'] : $pid;
        $slist  = isset($pdef['sections']) ? $pdef['sections'] : array();
        foreach ($slist as $sid) {
            if (isset($sections[$sid])) {
                $scopes[] = array($pid, $sid, $sections[$sid], $pid, $plabel);
            }
        }
    }

    $usedBy     = array();   // asset => array of "Page → Section → Field"
    $categories = array();   // asset => array of category keys
    foreach ($scopes as $sc) {
        list($prefix, $sid, $sdef, $catKey, $catLabel) = $sc;
        $fields   = isset($sdef['fields']) ? $sdef['fields'] : array();
        $secLabel = isset($sdef['label'])  ? $sdef['label']  : $sid;
        foreach ($fields as $fname => $fdef) {
            $type = isset($fdef['type']) ? $fdef['type'] : 'text';
            if ($type !== 'image' && $type !== 'video') continue;
            $val = isset($content[$prefix][$sid][$fname]) ? $content[$prefix][$sid][$fname] : '';
            if (!is_string($val) || $val === '') continue;
            // Skip absolute/remote references — they aren't library files.
            if (preg_match('#^(https?:)?//#i', $val) || $val[0] === '/') continue;
            $key    = ltrim($val, '/');
            $flabel = isset($fdef['label']) ? $fdef['label'] : $fname;
            if (!isset($usedBy[$key])) $usedBy[$key] = array();
            $usedBy[$key][] = $catLabel . ' → ' . $secLabel . ' → ' . $flabel;
            if (!isset($categories[$key])) $categories[$key] = array();
            if (!in_array($catKey, $categories[$key], true)) $categories[$key][] = $catKey;
        }
    }
    return array('usedBy' => $usedBy, 'categories' => $categories);
}

/** The category chips the UI offers, in display order. */
function fx_lib_category_list() {
    $map   = fx_load_fieldmap();
    $pages = isset($map['pages']) ? $map['pages'] : array();
    $out   = array(array('key' => 'shared', 'label' => 'Brand & Shared'));
    foreach ($pages as $pid => $pdef) {
        $out[] = array('key' => $pid, 'label' => isset($pdef['label']) ? $pdef['label'] : $pid);
    }
    $out[] = array('key' => 'unused', 'label' => 'Not on the site');
    return $out;
}

// --------------------------------------------------------------- actions ---

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'list';

// ---- list -----------------------------------------------------------------
if ($action === 'list') {
    $rel  = isset($_REQUEST['path']) ? $_REQUEST['path'] : '';
    $abs  = fx_lib_resolve($rel);
    if ($abs === false || !is_dir($abs)) {
        fx_json_response(array('ok' => false, 'error' => 'Folder not found.'));
    }
    $relNorm = fx_lib_relative($abs);
    $usage   = fx_lib_usage_index();

    $folders = array();
    $files   = array();
    $dh = @opendir($abs);
    if ($dh) {
        while (($entry = readdir($dh)) !== false) {
            if ($entry === '.' || $entry === '..') continue;
            if ($entry[0] === '.') continue;                 // hide dotfiles
            $entryAbs = $abs . '/' . $entry;
            $entryRel = ($relNorm === '') ? $entry : $relNorm . '/' . $entry;
            if (is_dir($entryAbs)) {
                // Count only media the picker would actually show.
                $n = 0;
                $sub = @opendir($entryAbs);
                if ($sub) {
                    while (($se = readdir($sub)) !== false) {
                        if ($se === '.' || $se === '..' || $se[0] === '.') continue;
                        if (is_dir($entryAbs . '/' . $se) || fx_lib_kind($se) !== null) $n++;
                    }
                    closedir($sub);
                }
                $folders[] = array('name' => $entry, 'path' => $entryRel, 'count' => $n);
            } else {
                $kind = fx_lib_kind($entry);
                if ($kind === null) continue;                // skip pdf/other
                $cats = isset($usage['categories'][$entryRel]) ? $usage['categories'][$entryRel] : array();
                if (empty($cats)) $cats = array('unused');
                $files[] = array(
                    'name'  => $entry,
                    'path'  => $entryRel,
                    'kind'  => $kind,
                    'bytes' => (int)@filesize($entryAbs),
                    'url'   => '../assets/' . fx_url_path($entryRel),
                    'cats'  => $cats,
                    'uses'  => isset($usage['usedBy'][$entryRel]) ? count($usage['usedBy'][$entryRel]) : 0,
                );
            }
        }
        closedir($dh);
    }

    // Case-insensitive name sort, folders first (handled client-side by section).
    usort($folders, 'fx_lib_cmp_name');
    usort($files,   'fx_lib_cmp_name');

    // Breadcrumb trail back to the root.
    $crumbs = array(array('label' => 'Library', 'path' => ''));
    if ($relNorm !== '') {
        $acc = '';
        foreach (explode('/', $relNorm) as $seg) {
            $acc = ($acc === '') ? $seg : $acc . '/' . $seg;
            $crumbs[] = array('label' => $seg, 'path' => $acc);
        }
    }

    fx_json_response(array(
        'ok'         => true,
        'path'       => $relNorm,
        'breadcrumb' => $crumbs,
        'folders'    => $folders,
        'files'      => $files,
        'categories' => fx_lib_category_list(),
    ));
}

// ---- browse-all (flat, for category filtering) ----------------------------
// Walks the whole library so a category chip can show every matching file
// regardless of which folder it physically lives in.
if ($action === 'all') {
    $usage = fx_lib_usage_index();
    $root  = realpath(fx_lib_root());
    $files = array();

    $stack = array($root);
    $guard = 0;
    while (!empty($stack) && $guard < 5000) {
        $dir = array_pop($stack);
        $dh = @opendir($dir);
        if (!$dh) continue;
        while (($entry = readdir($dh)) !== false) {
            $guard++;
            if ($entry === '.' || $entry === '..' || $entry[0] === '.') continue;
            $entryAbs = $dir . '/' . $entry;
            if (is_dir($entryAbs)) { $stack[] = $entryAbs; continue; }
            $kind = fx_lib_kind($entry);
            if ($kind === null) continue;
            $entryRel = fx_lib_relative($entryAbs);
            $cats = isset($usage['categories'][$entryRel]) ? $usage['categories'][$entryRel] : array();
            if (empty($cats)) $cats = array('unused');
            $files[] = array(
                'name'  => $entry,
                'path'  => $entryRel,
                'kind'  => $kind,
                'bytes' => (int)@filesize($entryAbs),
                'url'   => '../assets/' . fx_url_path($entryRel),
                'cats'  => $cats,
                'uses'  => isset($usage['usedBy'][$entryRel]) ? count($usage['usedBy'][$entryRel]) : 0,
            );
        }
        closedir($dh);
    }
    usort($files, 'fx_lib_cmp_name');
    fx_json_response(array(
        'ok'         => true,
        'files'      => $files,
        'categories' => fx_lib_category_list(),
    ));
}

// ---- mkdir ----------------------------------------------------------------
if ($action === 'mkdir') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') fx_json_response(array('ok' => false, 'error' => 'Method not allowed.'));
    $rel    = isset($_POST['path']) ? $_POST['path'] : '';
    $name   = isset($_POST['name']) ? trim($_POST['name']) : '';
    $parent = fx_lib_resolve($rel);
    if ($parent === false || !is_dir($parent)) fx_json_response(array('ok' => false, 'error' => 'Parent folder not found.'));

    // Folder names: letters, numbers, space, dash, underscore, ampersand.
    if ($name === '' || !preg_match('/^[A-Za-z0-9 _\-&]{1,60}$/', $name)) {
        fx_json_response(array('ok' => false, 'error' => 'Folder name can use letters, numbers, spaces, - _ & only (max 60).'));
    }
    $target = $parent . '/' . $name;
    if (file_exists($target)) fx_json_response(array('ok' => false, 'error' => 'A folder with that name already exists.'));
    if (!@mkdir($target, 0755)) fx_json_response(array('ok' => false, 'error' => 'Could not create the folder. Check /assets/ permissions.'));

    fx_json_response(array('ok' => true, 'path' => fx_lib_relative(realpath($target))));
}

// ---- delete ---------------------------------------------------------------
if ($action === 'delete') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') fx_json_response(array('ok' => false, 'error' => 'Method not allowed.'));
    $rel = isset($_POST['path']) ? $_POST['path'] : '';
    if ($rel === '') fx_json_response(array('ok' => false, 'error' => 'Cannot delete the library root.'));
    $abs = fx_lib_resolve($rel);
    if ($abs === false) fx_json_response(array('ok' => false, 'error' => 'Not found.'));

    $usage  = fx_lib_usage_index();
    $usedBy = $usage['usedBy'];

    if (is_dir($abs)) {
        // Refuse if ANY file inside is still referenced, and name the offenders.
        $blockers = array();
        $stack = array($abs);
        $guard = 0;
        while (!empty($stack) && $guard < 5000) {
            $dir = array_pop($stack);
            $dh = @opendir($dir);
            if (!$dh) continue;
            while (($e = readdir($dh)) !== false) {
                $guard++;
                if ($e === '.' || $e === '..') continue;
                $p = $dir . '/' . $e;
                if (is_dir($p)) { $stack[] = $p; continue; }
                $r = fx_lib_relative($p);
                if (isset($usedBy[$r])) {
                    foreach ($usedBy[$r] as $u) $blockers[] = $e . '  —  ' . $u;
                }
            }
            closedir($dh);
        }
        if (!empty($blockers)) {
            fx_json_response(array(
                'ok' => false,
                'error' => 'That folder holds files still used on the site.',
                'in_use' => array_slice($blockers, 0, 12),
                'in_use_total' => count($blockers),
            ));
        }
        // Recursive delete (only reached when nothing inside is referenced).
        $ok = fx_lib_rmdir_recursive($abs);
        if (!$ok) fx_json_response(array('ok' => false, 'error' => 'Could not remove the folder.'));
        fx_json_response(array('ok' => true, 'deleted' => $rel, 'type' => 'folder'));
    }

    // Single file.
    $r = fx_lib_relative($abs);
    if (isset($usedBy[$r])) {
        fx_json_response(array(
            'ok' => false,
            'error' => 'This file is still used on the site.',
            'in_use' => $usedBy[$r],
            'in_use_total' => count($usedBy[$r]),
        ));
    }
    if (!@unlink($abs)) fx_json_response(array('ok' => false, 'error' => 'Could not delete the file.'));
    fx_json_response(array('ok' => true, 'deleted' => $rel, 'type' => 'file'));
}

fx_json_response(array('ok' => false, 'error' => 'Unknown action.'));

// --------------------------------------------------------------- helpers ---

/**
 * usort comparator — case-insensitive by 'name'.
 * A named function rather than create_function(): the latter is deprecated
 * on PHP 7.3 (this host) and removed in PHP 8, and its deprecation notice
 * would print into the response body and corrupt the JSON.
 */
function fx_lib_cmp_name($a, $b) {
    return strcasecmp($a['name'], $b['name']);
}

function fx_lib_rmdir_recursive($dir) {
    $items = @scandir($dir);
    if ($items === false) return false;
    foreach ($items as $i) {
        if ($i === '.' || $i === '..') continue;
        $p = $dir . '/' . $i;
        if (is_dir($p)) {
            if (!fx_lib_rmdir_recursive($p)) return false;
        } else {
            if (!@unlink($p)) return false;
        }
    }
    return @rmdir($dir);
}
