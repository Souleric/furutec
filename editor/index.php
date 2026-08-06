<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/data.php';
fx_require_login();

$content = fx_load_content();
$map     = fx_load_fieldmap();
$csrf    = fx_csrf_token();

// Group fields by their `group` key (fields without a group land in "__top__").
function fx_section_field_groups($fields) {
    $groups = array();
    foreach ($fields as $name => $def) {
        $g = isset($def['group']) ? $def['group'] : '__top__';
        if (!isset($groups[$g])) $groups[$g] = array();
        $groups[$g][$name] = $def;
    }
    return $groups;
}
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1"/>
    <title>Furutec Editor</title>
    <link rel="stylesheet" href="assets/editor.css?v=2"/>
    <meta name="robots" content="noindex, nofollow"/>
</head>
<body class="fx-body">

<header class="fx-topbar">
    <div class="fx-topbar-left">
        <span class="fx-brand">Furutec Editor</span>
        <span class="fx-brand-sub">Homepage · <?= count($map['sections']) ?> sections</span>
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

    <!-- LEFT: accordion sidebar (all sections stacked, click header to expand/collapse) -->
    <aside class="fx-sidebar" id="fx-sidebar">
        <div class="fx-sidebar-controls">
            <button type="button" class="fx-linkbtn" id="fx-expand-all">Expand all</button>
            <span class="fx-sep">·</span>
            <button type="button" class="fx-linkbtn" id="fx-collapse-all">Collapse all</button>
        </div>

        <?php $panelIdx = 0; foreach ($map['sections'] as $sid => $s):
            $groups = fx_section_field_groups($s['fields']);
            $sectionContent = isset($content[$sid]) ? $content[$sid] : array();
            $isOpen = ($panelIdx === 0);   // first section open by default
        ?>
        <section class="fx-acc<?= $isOpen ? ' is-open' : '' ?>" data-panel="<?= fx_escape($sid) ?>">
            <button type="button" class="fx-acc-head" aria-expanded="<?= $isOpen ? 'true' : 'false' ?>">
                <span class="fx-acc-title"><?= fx_escape($s['label']) ?></span>
                <?php if (!empty($s['summary'])): ?>
                    <span class="fx-acc-summary"><?= fx_escape($s['summary']) ?></span>
                <?php endif; ?>
                <svg class="fx-acc-chev" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
            </button>
            <div class="fx-acc-body">

                <?php foreach ($groups as $groupName => $fields):
                    if ($groupName !== '__top__'): ?>
                        <h3 class="fx-group-head"><?= fx_escape($groupName) ?></h3>
                    <?php endif; ?>

                    <?php foreach ($fields as $fname => $fdef):
                        $val = isset($sectionContent[$fname]) ? $sectionContent[$fname] : '';
                        $fieldKey = $sid . '.' . $fname;
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
        <?php $panelIdx++; endforeach; ?>
    </aside>

    <!-- RIGHT: live iframe preview -->
    <section class="fx-preview-wrap">
        <div class="fx-preview-bar">
            <span class="fx-preview-label">Preview (unpublished draft)</span>
            <span class="fx-preview-hint">Updates when you Save.  This is what the live homepage would look like after publishing.</span>
            <button type="button" class="fx-btn fx-btn-ghost fx-btn-small" id="fx-refresh-preview">Refresh preview</button>
        </div>
        <div class="fx-preview-frame-wrap">
            <iframe id="fx-preview" src="preview.php?ts=<?= time() ?>" title="Homepage draft preview"></iframe>
        </div>
    </section>
</main>

<script>
window.FX = { csrf: <?= fx_json_encode($csrf) ?> };
</script>
<script src="assets/editor.js?v=2"></script>
</body>
</html>
