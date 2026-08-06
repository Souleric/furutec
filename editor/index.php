<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/data.php';
fx_require_login();

$content  = fx_load_content();
$map      = fx_load_fieldmap();
$csrf     = fx_csrf_token();

$pages          = isset($map['pages'])          ? $map['pages']          : array();
$shared_ids     = isset($map['shared_sections'])? $map['shared_sections']: array();
$section_defs   = isset($map['sections'])       ? $map['sections']       : array();

function fx_section_field_groups($fields) {
    $groups = array();
    foreach ($fields as $name => $def) {
        $g = isset($def['group']) ? $def['group'] : '__top__';
        if (!isset($groups[$g])) $groups[$g] = array();
        $groups[$g][$name] = $def;
    }
    return $groups;
}

/**
 * Render a single accordion for a section.
 *   $section_id   — key into $section_defs
 *   $data_prefix  — 'shared' or page_id (e.g. 'home')
 *   $section_content — assoc array of current values for this section
 *   $default_open — bool
 */
function fx_render_accordion($section_id, $data_prefix, $section_content, $section_def, $default_open = false) {
    $isOpen = $default_open;
    $groups = fx_section_field_groups($section_def['fields']);
    ?>
    <section class="fx-acc<?= $isOpen ? ' is-open' : '' ?>">
        <button type="button" class="fx-acc-head" aria-expanded="<?= $isOpen ? 'true' : 'false' ?>">
            <span class="fx-acc-title"><?= fx_escape($section_def['label']) ?></span>
            <?php if (!empty($section_def['summary'])): ?>
                <span class="fx-acc-summary"><?= fx_escape($section_def['summary']) ?></span>
            <?php endif; ?>
            <svg class="fx-acc-chev" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
        </button>
        <div class="fx-acc-body">
            <?php foreach ($groups as $groupName => $fields):
                if ($groupName !== '__top__'): ?>
                    <h3 class="fx-group-head"><?= fx_escape($groupName) ?></h3>
                <?php endif; ?>
                <?php foreach ($fields as $fname => $fdef):
                    $val = isset($section_content[$fname]) ? $section_content[$fname] : '';
                    $fieldKey = $data_prefix . '.' . $section_id . '.' . $fname;
                    $type = isset($fdef['type']) ? $fdef['type'] : 'text';
                    $label = isset($fdef['label']) ? $fdef['label'] : $fname;
                    $help  = isset($fdef['help']) ? $fdef['help']  : '';
                    $max   = isset($fdef['max'])  ? (int)$fdef['max'] : 500;
                    $rows  = isset($fdef['rows']) ? (int)$fdef['rows']: 3;
                ?>
                <label class="fx-field" data-field-key="<?= fx_escape($fieldKey) ?>">
                    <span class="fx-field-label"><?= fx_escape($label) ?></span>
                    <?php if ($type === 'text' || $type === 'url'): ?>
                        <input class="fx-input fx-track" type="<?= $type === 'url' ? 'url' : 'text' ?>"
                               data-field="<?= fx_escape($fieldKey) ?>"
                               value="<?= fx_escape($val) ?>"
                               maxlength="<?= $max ?>"/>
                    <?php elseif ($type === 'textarea' || $type === 'markdown'): ?>
                        <textarea class="fx-textarea fx-track"
                                  data-field="<?= fx_escape($fieldKey) ?>"
                                  rows="<?= $rows ?>"
                                  maxlength="<?= $max ?>"><?= fx_escape($val) ?></textarea>
                        <?php if ($type === 'markdown'): ?>
                            <span class="fx-hint">Tip: wrap text in <code>**double asterisks**</code> to make it <strong>bold</strong>.</span>
                        <?php endif; ?>
                    <?php elseif ($type === 'image' || $type === 'video'): ?>
                        <div class="fx-media">
                            <div class="fx-media-preview">
                                <?php if ($val !== ''):
                                    $isUrl = preg_match('#^(https?:)?//#i', $val) || (strlen($val) && $val[0] === '/');
                                    $url = $isUrl ? $val : '../assets/' . fx_url_path($val);
                                    if ($type === 'image'): ?>
                                        <img src="<?= fx_escape($url) ?>" alt=""/>
                                    <?php else: ?>
                                        <video src="<?= fx_escape($url) ?>" controls muted playsinline preload="metadata"></video>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="fx-media-empty">(no file selected)</span>
                                <?php endif; ?>
                            </div>
                            <div class="fx-media-controls">
                                <input type="hidden" class="fx-track" data-field="<?= fx_escape($fieldKey) ?>" value="<?= fx_escape($val) ?>"/>
                                <div class="fx-media-filename"><?= $val !== '' ? fx_escape($val) : '' ?></div>
                                <label class="fx-btn fx-btn-ghost fx-upload-btn">
                                    Replace <?= $type === 'video' ? 'video' : 'image' ?>
                                    <input type="file" class="fx-file"
                                           data-field-key="<?= fx_escape($fieldKey) ?>"
                                           data-media-type="<?= fx_escape($type) ?>"
                                           accept="<?= $type === 'video' ? 'video/mp4' : 'image/jpeg,image/png,image/webp' ?>"
                                           hidden/>
                                </label>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($help !== ''): ?>
                        <span class="fx-hint"><?= fx_escape($help) ?></span>
                    <?php endif; ?>
                </label>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
}

$firstPageId = array_keys($pages)[0];
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1"/>
    <title>Furutec Editor</title>
    <link rel="stylesheet" href="assets/editor.css?v=3"/>
    <meta name="robots" content="noindex, nofollow"/>
</head>
<body class="fx-body">

<header class="fx-topbar">
    <div class="fx-topbar-left">
        <span class="fx-brand">Furutec Editor</span>
        <span class="fx-brand-sub"><?= count($pages) ?> pages · <?= count($section_defs) ?> sections</span>
    </div>
    <div class="fx-topbar-right">
        <span id="fx-status" class="fx-status"></span>
        <button type="button" class="fx-btn" id="fx-save">Save draft</button>
        <button type="button" class="fx-btn fx-btn-ghost" id="fx-restore" title="Restore the previous version of the live site">Restore previous</button>
        <button type="button" class="fx-btn fx-btn-primary" id="fx-publish">Publish</button>
        <a class="fx-btn fx-btn-link" href="logout.php">Sign out</a>
    </div>
</header>

<main class="fx-main">
    <aside class="fx-sidebar" id="fx-sidebar">

        <!-- Page picker -->
        <div class="fx-page-picker">
            <span class="fx-picker-label">Page:</span>
            <div class="fx-picker-buttons">
                <?php $first = true; foreach ($pages as $pid => $pdef): ?>
                    <button type="button" class="fx-picker-btn<?= $first ? ' is-active' : '' ?>" data-page="<?= fx_escape($pid) ?>"><?= fx_escape($pdef['label']) ?></button>
                <?php $first = false; endforeach; ?>
            </div>
        </div>

        <!-- Expand/collapse controls -->
        <div class="fx-sidebar-controls">
            <button type="button" class="fx-linkbtn" id="fx-expand-all">Expand all</button>
            <span class="fx-sep">·</span>
            <button type="button" class="fx-linkbtn" id="fx-collapse-all">Collapse all</button>
        </div>

        <!-- Shared sections (always visible, apply to all pages on publish) -->
        <div class="fx-shared-block">
            <div class="fx-shared-title">Shared across all pages</div>
            <?php foreach ($shared_ids as $sect_id):
                if (!isset($section_defs[$sect_id])) continue;
                $sc = isset($content['shared'][$sect_id]) ? $content['shared'][$sect_id] : array();
                fx_render_accordion($sect_id, 'shared', $sc, $section_defs[$sect_id], false);
            endforeach; ?>
        </div>

        <!-- Per-page sections (only active page is visible) -->
        <?php foreach ($pages as $pid => $pdef): ?>
            <div class="fx-page-sections<?= $pid === $firstPageId ? ' is-active' : '' ?>" data-page="<?= fx_escape($pid) ?>">
                <div class="fx-page-title"><?= fx_escape($pdef['label']) ?> sections</div>
                <?php $idx = 0; foreach ($pdef['sections'] as $sect_id):
                    if (!isset($section_defs[$sect_id])) continue;
                    $sc = isset($content[$pid][$sect_id]) ? $content[$pid][$sect_id] : array();
                    fx_render_accordion($sect_id, $pid, $sc, $section_defs[$sect_id], ($idx === 0));
                    $idx++;
                endforeach; ?>
            </div>
        <?php endforeach; ?>

    </aside>

    <section class="fx-preview-wrap">
        <div class="fx-preview-bar">
            <span class="fx-preview-label" id="fx-preview-label">Preview · <?= fx_escape($pages[$firstPageId]['label']) ?> (draft)</span>
            <span class="fx-preview-hint">Updates when you Save.</span>
            <button type="button" class="fx-btn fx-btn-ghost fx-btn-small" id="fx-refresh-preview">Refresh preview</button>
        </div>
        <div class="fx-preview-frame-wrap">
            <iframe id="fx-preview" src="preview.php?page=<?= fx_escape($firstPageId) ?>&ts=<?= time() ?>" title="Draft preview"></iframe>
        </div>
    </section>
</main>

<?php
$labels = array();
foreach ($pages as $pid => $pdef) $labels[$pid] = $pdef['label'];
?>
<script>
window.FX = {
    csrf: <?= fx_json_encode($csrf) ?>,
    pageLabels: <?= fx_json_encode($labels) ?>
};
</script>
<script src="assets/editor.js?v=3"></script>
</body>
</html>
