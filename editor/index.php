<?php
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/data.php';
fx_require_login();

$content = fx_load_content();
$map     = fx_load_fieldmap();
$csrf    = fx_csrf_token();

// Precompute per-field group headers within the products section.
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
    <link rel="stylesheet" href="assets/editor.css?v=1"/>
    <meta name="robots" content="noindex, nofollow"/>
</head>
<body class="fx-body">

<header class="fx-topbar">
    <div class="fx-topbar-left">
        <span class="fx-brand">Furutec Editor</span>
        <nav class="fx-tabs" id="fx-tabs">
            <?php $first = true; foreach ($map['sections'] as $sid => $s): ?>
                <button type="button" class="fx-tab<?= $first ? ' is-active' : '' ?>" data-tab="<?= fx_escape($sid) ?>"><?= fx_escape($s['label']) ?></button>
            <?php $first = false; endforeach; ?>
        </nav>
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

    <!-- LEFT: form fields per section -->
    <aside class="fx-sidebar" id="fx-sidebar">
        <?php $panelIdx = 0; foreach ($map['sections'] as $sid => $s):
            $groups = fx_section_field_groups($s['fields']);
            $sectionContent = isset($content[$sid]) ? $content[$sid] : array();
        ?>
        <section class="fx-panel<?= $panelIdx === 0 ? ' is-active' : '' ?>" data-panel="<?= fx_escape($sid) ?>">
            <div class="fx-panel-head">
                <div class="fx-panel-title"><?= fx_escape($s['label']) ?></div>
                <?php if (!empty($s['summary'])): ?>
                    <div class="fx-panel-summary"><?= fx_escape($s['summary']) ?></div>
                <?php endif; ?>
            </div>

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
                    <?php if ($type === 'text'): ?>
                        <input class="fx-input fx-track" type="text"
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
                                    $url = '../assets/' . fx_url_path($val);
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

        </section>
        <?php $panelIdx++; endforeach; ?>
    </aside>

    <!-- RIGHT: live iframe preview -->
    <section class="fx-preview-wrap">
        <div class="fx-preview-bar">
            <span class="fx-preview-label">Preview (unpublished draft)</span>
            <span class="fx-preview-hint">This is how the homepage would look after publishing. It updates when you Save.</span>
            <button type="button" class="fx-btn fx-btn-ghost fx-btn-small" id="fx-refresh-preview">Refresh preview</button>
        </div>
        <div class="fx-preview-frame-wrap">
            <iframe id="fx-preview" src="preview.php?ts=<?= time() ?>" title="Homepage draft preview"></iframe>
        </div>
    </section>
</main>

<script>
window.FX = {
    csrf: <?= fx_json_encode($csrf) ?>
};
</script>
<script src="assets/editor.js?v=1"></script>
</body>
</html>
