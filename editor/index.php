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
    $previewTarget = isset($section_def['preview_target']) ? $section_def['preview_target'] : '';
    ?>
    <section class="fx-acc<?= $isOpen ? ' is-open' : '' ?>"<?php if ($previewTarget !== ''): ?> data-preview-target="<?= fx_escape($previewTarget) ?>"<?php endif; ?>>
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
                <?php
                // Conditional visibility.  `show_when` names another field in
                // the SAME section plus the value it must hold; the editor JS
                // hides this field (and any group heading left with nothing
                // under it) whenever that doesn't match.  The input stays in
                // the DOM either way, so a hidden field keeps its value and
                // still saves — switching back and forth is non-destructive.
                $showWhen = isset($fdef['show_when']) && is_array($fdef['show_when']) ? $fdef['show_when'] : null;
                $showWhenKey = $showWhen ? $data_prefix . '.' . $section_id . '.' . $showWhen['field'] : '';
                ?>
                <?php if ($type === 'hidden'):
                    // Machine-set values (e.g. a video focal point) — saved like any
                    // other field, but never shown, so there's no meaningless textbox
                    // for the client to wonder about.
                    ?>
                    <input type="hidden" class="fx-track" data-field="<?= fx_escape($fieldKey) ?>" value="<?= fx_escape($val) ?>"/>
                <?php continue; endif; ?>
                <label class="fx-field" data-field-key="<?= fx_escape($fieldKey) ?>"<?php
                    if ($showWhen): ?> data-show-when="<?= fx_escape($showWhenKey) ?>" data-show-when-is="<?= fx_escape($showWhen['is']) ?>"<?php endif; ?>>
                    <span class="fx-field-label"><?= fx_escape($label) ?></span>
                    <?php if ($type === 'text' || $type === 'url'): ?>
                        <input class="fx-input fx-track" type="<?= $type === 'url' ? 'url' : 'text' ?>"
                               data-field="<?= fx_escape($fieldKey) ?>"
                               value="<?= fx_escape($val) ?>"
                               maxlength="<?= $max ?>"/>
                    <?php elseif ($type === 'select'): ?>
                        <select class="fx-input fx-select fx-track" data-field="<?= fx_escape($fieldKey) ?>">
                            <?php
                            $opts = isset($fdef['options']) && is_array($fdef['options']) ? $fdef['options'] : array();
                            $optKeys = array_keys($opts);
                            $cur = ($val !== '' && isset($opts[$val])) ? $val : (isset($optKeys[0]) ? $optKeys[0] : '');
                            foreach ($opts as $oKey => $oLabel): ?>
                                <option value="<?= fx_escape($oKey) ?>"<?= $oKey === $cur ? ' selected' : '' ?>><?= fx_escape($oLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php elseif ($type === 'number'):
                        // Fall back to the declared default so the box is never
                        // blank on a field the client hasn't touched yet.
                        $numVal = ($val !== '') ? $val : (isset($fdef['default']) ? (string)$fdef['default'] : '');
                    ?>
                        <input class="fx-input fx-track" type="number"
                               data-field="<?= fx_escape($fieldKey) ?>"
                               value="<?= fx_escape($numVal) ?>"
                               min="<?= isset($fdef['min']) ? (int)$fdef['min'] : 0 ?>"
                               max="<?= isset($fdef['max_value']) ? (int)$fdef['max_value'] : 9999 ?>"
                               step="<?= isset($fdef['step']) ? (int)$fdef['step'] : 1 ?>"/>
                    <?php elseif ($type === 'textarea' || $type === 'markdown'): ?>
                        <textarea class="fx-textarea fx-track"
                                  data-field="<?= fx_escape($fieldKey) ?>"
                                  rows="<?= $rows ?>"
                                  maxlength="<?= $max ?>"><?= fx_escape($val) ?></textarea>
                        <?php if ($type === 'markdown'): ?>
                            <span class="fx-hint">Tip: wrap text in <code>**double asterisks**</code> to make it <strong>bold</strong>.</span>
                        <?php endif; ?>
                    <?php elseif ($type === 'image' || $type === 'video'):
                        // Slots whose CSS crops the image (background-size/object-fit
                        // cover) declare the shape they display at, so the cropper can
                        // lock to that ratio and output a correctly-sized file.
                        // Slots without one still get a cropper, just free-form.
                        $crop  = isset($fdef['crop']) && is_array($fdef['crop']) ? $fdef['crop'] : null;
                        $cropW = $crop ? (int)$crop['w'] : 0;
                        $cropH = $crop ? (int)$crop['h'] : 0;
                    ?>
                        <div class="fx-media"<?php if ($crop): ?> data-crop-w="<?= $cropW ?>" data-crop-h="<?= $cropH ?>"<?php endif; ?>>
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
                                <div class="fx-media-btns">
                                    <button type="button" class="fx-btn fx-btn-ghost fx-lib-btn"
                                            data-field-key="<?= fx_escape($fieldKey) ?>"
                                            data-media-type="<?= fx_escape($type) ?>">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/></svg>
                                        Library
                                    </button>
                                    <label class="fx-btn fx-btn-ghost fx-upload-btn">
                                        Upload <?= $type === 'video' ? 'video' : 'image' ?>
                                        <input type="file" class="fx-file"
                                               data-field-key="<?= fx_escape($fieldKey) ?>"
                                               data-media-type="<?= fx_escape($type) ?>"
                                               accept="<?= $type === 'video' ? 'video/mp4' : 'image/jpeg,image/png,image/webp' ?>"
                                               hidden/>
                                    </label>
                                    <?php if ($type === 'image'): ?>
                                    <button type="button" class="fx-btn fx-btn-ghost fx-crop-btn"
                                            data-field-key="<?= fx_escape($fieldKey) ?>"
                                            <?php if ($crop): ?>data-crop-w="<?= $cropW ?>" data-crop-h="<?= $cropH ?>"<?php endif; ?>
                                            title="Crop this image to fit the slot">Crop</button>
                                    <?php endif; ?>
                                    <?php if ($type === 'video' && !empty($fdef['focus'])):
                                        // Only offered on videos the layout actually crops.
                                        // A focal point on a letterboxed video would do nothing.
                                        ?>
                                    <button type="button" class="fx-btn fx-btn-ghost fx-focus-btn"
                                            data-field-key="<?= fx_escape($fieldKey) ?>"
                                            data-focus-key="<?= fx_escape($fieldKey . '_focus') ?>"
                                            title="Choose which part of the video stays visible when it's cropped to fill">Focal point</button>
                                    <?php endif; ?>
                                    <?php // Clearing a slot was impossible before this — the only way to
                                          // "remove" a slideshow image was to overwrite it.  Hidden by CSS
                                          // while the field is empty so it doesn't add noise to blank slots. ?>
                                    <button type="button" class="fx-btn fx-btn-ghost fx-clear-btn"
                                            data-field-key="<?= fx_escape($fieldKey) ?>"
                                            title="Remove this file from the field">Remove</button>
                                </div>
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
    <link rel="stylesheet" href="assets/editor.css?v=21"/>
    <link rel="icon" href="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='18' fill='%232E3192'/><text x='50' y='72' text-anchor='middle' font-family='-apple-system,Arial,sans-serif' font-weight='800' font-size='62' fill='white'>F</text></svg>"/>
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
        <button type="button" class="fx-btn" id="fx-save"
                title="Save your edits and refresh the preview. Visitors do NOT see them yet — click Publish for that.">Save draft</button>
        <button type="button" class="fx-btn fx-btn-ghost" id="fx-restore"
                title="Put the live site back to the previously published version. Your draft is kept.">Restore previous</button>
        <?php $publishingEnabled = fx_publishing_enabled(); ?>
        <button type="button"
                class="fx-btn fx-btn-primary<?= $publishingEnabled ? '' : ' is-disabled' ?>"
                id="fx-publish"
                <?= $publishingEnabled ? '' : 'disabled aria-disabled="true"' ?>
                title="<?= $publishingEnabled ? 'Push your saved draft to the live website so visitors can see it. Every publish keeps a backup you can undo with Restore previous.' : 'Publishing is temporarily disabled by the site administrator. Save draft still works — contact Eric to re-enable Publish.' ?>">Publish<?= $publishingEnabled ? '' : ' (disabled)' ?></button>
        <a class="fx-btn fx-btn-link" href="logout.php">Sign out</a>
    </div>
</header>

<main class="fx-main">
    <aside class="fx-sidebar" id="fx-sidebar">

        <?php // Draft-vs-live is the one concept the whole tool rests on, and
              // nothing used to state it.  Shown until dismissed, then remembered
              // per browser so it never nags. ?>
        <div class="fx-intro" id="fx-intro" hidden>
            <button type="button" class="fx-intro-x" id="fx-intro-dismiss" aria-label="Dismiss">&times;</button>
            <strong class="fx-intro-title">How editing works</strong>
            <ol class="fx-intro-steps">
                <li>Change something below.</li>
                <li><strong>Save draft</strong> — updates the preview on the right. Visitors still see the old site.</li>
                <li><strong>Publish</strong> — puts it on the live website for everyone.</li>
            </ol>
            <span class="fx-intro-foot">Changed your mind after publishing? <strong>Restore previous</strong> puts the live site back.</span>
        </div>

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
            <?php // Sidebar is a fixed 440px; on a 1024px laptop that leaves the
                  // preview only 584px, so you end up judging a squeezed version of
                  // your own page.  This hands the width back when you want to look
                  // rather than edit.  State is remembered per browser. ?>
            <button type="button" class="fx-sidebar-toggle" id="fx-sidebar-toggle"
                    aria-label="Hide the editing panel" aria-expanded="true"
                    title="Hide the editing panel to see the preview full width">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="16" rx="2"/><line x1="9" y1="4" x2="9" y2="20"/>
                </svg>
            </button>
            <span class="fx-preview-label" id="fx-preview-label">Preview · <?= fx_escape($pages[$firstPageId]['label']) ?> (draft)</span>
            <div class="fx-device-toggle" role="group" aria-label="Preview device size">
                <button type="button" class="fx-device-btn is-active" data-device="desktop" title="Desktop preview">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    <span>Desktop</span>
                </button>
                <button type="button" class="fx-device-btn" data-device="tablet" title="Tablet preview (820 px wide)">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                    <span>Tablet</span>
                </button>
                <button type="button" class="fx-device-btn" data-device="mobile" title="Mobile preview (390 px wide)">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="7" y="2" width="10" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                    <span>Mobile</span>
                </button>
            </div>
            <!-- Zoom (desktop only) -->
            <label class="fx-zoom-wrap" title="Preview zoom (desktop only)">
                <select id="fx-zoom-select" class="fx-zoom-select" aria-label="Preview zoom">
                    <option value="0.5">50%</option>
                    <option value="0.6">60%</option>
                    <option value="0.75">75%</option>
                    <option value="0.9">90%</option>
                    <option value="1" selected>100%</option>
                    <option value="1.15">115%</option>
                    <option value="1.35">135%</option>
                    <option value="1.5">150%</option>
                </select>
            </label>
            <button type="button" class="fx-btn fx-btn-ghost fx-btn-small" id="fx-refresh-preview">Refresh preview</button>
        </div>
        <?php // The preview is server-rendered, so it only changes on save.  Without
              // this the pane silently contradicts whatever you just typed — the single
              // biggest source of confusion in the tool.  Appears the moment anything
              // is edited and carries the fix with it. ?>
        <div class="fx-stale-bar" id="fx-stale-bar" hidden>
            <span class="fx-stale-text">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                You have unsaved edits — this preview still shows the last saved version.
            </span>
            <button type="button" class="fx-btn fx-btn-small fx-stale-save" id="fx-stale-save">Save &amp; refresh</button>
        </div>
        <div class="fx-preview-frame-wrap" id="fx-preview-frame-wrap" data-device="desktop">
            <iframe id="fx-preview" src="preview.php?page=<?= fx_escape($firstPageId) ?>&ts=<?= time() ?>" title="Draft preview"></iframe>
        </div>
    </section>
</main>

<!-- ============================================================
     MEDIA LIBRARY  —  modal picker
     Opened by any "Library" button on a media field.  Browses
     /assets/ (real folders) with a virtual category filter on top
     derived live from which fields reference each file.
     ============================================================ -->
<div class="fx-lib-overlay" id="fx-lib-overlay" hidden>
    <div class="fx-lib" role="dialog" aria-modal="true" aria-labelledby="fx-lib-title">

        <header class="fx-lib-head">
            <div>
                <h2 class="fx-lib-title" id="fx-lib-title">Media Library</h2>
                <p class="fx-lib-sub" id="fx-lib-sub">Choose a file, or upload a new one.</p>
            </div>
            <button type="button" class="fx-lib-close" id="fx-lib-close" aria-label="Close library">&times;</button>
        </header>

        <div class="fx-lib-cats" id="fx-lib-cats"><!-- category chips injected --></div>

        <div class="fx-lib-bar">
            <nav class="fx-lib-crumbs" id="fx-lib-crumbs" aria-label="Folder path"></nav>
            <div class="fx-lib-bar-actions">
                <input type="search" class="fx-lib-search" id="fx-lib-search" placeholder="Search files…" autocomplete="off"/>
                <button type="button" class="fx-btn fx-btn-ghost fx-btn-small" id="fx-lib-mkdir">New folder</button>
                <label class="fx-btn fx-btn-ghost fx-btn-small fx-lib-upload-btn">
                    Upload here
                    <input type="file" id="fx-lib-upload" hidden/>
                </label>
            </div>
        </div>

        <div class="fx-lib-body" id="fx-lib-body">
            <p class="fx-lib-empty">Loading…</p>
        </div>

        <footer class="fx-lib-foot">
            <span class="fx-lib-status" id="fx-lib-status"></span>
            <div class="fx-lib-foot-actions">
                <button type="button" class="fx-btn fx-btn-ghost" id="fx-lib-cancel">Cancel</button>
                <button type="button" class="fx-btn fx-btn-primary" id="fx-lib-use" disabled>Use selected file</button>
            </div>
        </footer>

    </div>
</div>

<!-- ============================================================
     VIDEO FOCAL POINT
     A video can't be re-cut in the browser, so instead of cropping we
     choose the anchor point that stays visible when CSS crops it to
     fill.  Stored as an object-position pair.
     ============================================================ -->
<div class="fx-crop-overlay" id="fx-focus-overlay" hidden>
    <div class="fx-crop" role="dialog" aria-modal="true" aria-labelledby="fx-focus-title">
        <header class="fx-crop-head">
            <div>
                <h2 class="fx-crop-title" id="fx-focus-title">Video focal point</h2>
                <p class="fx-crop-sub">
                    This video is cropped to fill its section, so the edges get cut off.
                    Click the part that must stay visible — the crop is anchored there.
                </p>
            </div>
            <button type="button" class="fx-lib-close" id="fx-focus-close" aria-label="Close">&times;</button>
        </header>

        <div class="fx-crop-stage">
            <div class="fx-focus-wrap" id="fx-focus-wrap">
                <video id="fx-focus-video" muted playsinline preload="metadata"></video>
                <span class="fx-focus-marker" id="fx-focus-marker"></span>
            </div>
        </div>

        <footer class="fx-crop-foot">
            <span class="fx-crop-status" id="fx-focus-status"></span>
            <div class="fx-crop-foot-actions">
                <button type="button" class="fx-btn fx-btn-ghost" id="fx-focus-center">Re-centre</button>
                <button type="button" class="fx-btn fx-btn-ghost" id="fx-focus-cancel">Cancel</button>
                <button type="button" class="fx-btn fx-btn-primary" id="fx-focus-apply">Use this point</button>
            </div>
        </footer>
    </div>
</div>

<!-- ============================================================
     IMAGE CROPPER
     Opens after a new image is chosen for a slot that declares the
     shape it displays at, and from the Crop button on any image field.
     Crops + resizes on a canvas in the browser and uploads the result
     as a new file — the original is never touched.
     ============================================================ -->
<div class="fx-crop-overlay" id="fx-crop-overlay" hidden>
    <div class="fx-crop" role="dialog" aria-modal="true" aria-labelledby="fx-crop-title">
        <header class="fx-crop-head">
            <div>
                <h2 class="fx-crop-title" id="fx-crop-title">Crop image</h2>
                <p class="fx-crop-sub" id="fx-crop-sub"></p>
            </div>
            <button type="button" class="fx-lib-close" id="fx-crop-close" aria-label="Close cropper">&times;</button>
        </header>

        <div class="fx-crop-stage" id="fx-crop-stage">
            <div class="fx-crop-imgwrap" id="fx-crop-imgwrap">
                <img id="fx-crop-img" alt="" draggable="false"/>
                <div class="fx-crop-shade" id="fx-crop-shade"></div>
                <div class="fx-crop-box" id="fx-crop-box">
                    <span class="fx-crop-handle" data-h="nw"></span>
                    <span class="fx-crop-handle" data-h="ne"></span>
                    <span class="fx-crop-handle" data-h="sw"></span>
                    <span class="fx-crop-handle" data-h="se"></span>
                </div>
            </div>
        </div>

        <footer class="fx-crop-foot">
            <span class="fx-crop-status" id="fx-crop-status"></span>
            <div class="fx-crop-foot-actions">
                <?php // Only shown when the cropper auto-opened after a pick, where
                      // there is a previous value to genuinely go back to. ?>
                <button type="button" class="fx-btn fx-btn-ghost" id="fx-crop-discard" hidden>Discard this image</button>
                <button type="button" class="fx-btn fx-btn-ghost" id="fx-crop-reset">Reset</button>
                <button type="button" class="fx-btn fx-btn-ghost" id="fx-crop-skip">Use uncropped</button>
                <button type="button" class="fx-btn fx-btn-primary" id="fx-crop-apply">Apply crop</button>
            </div>
        </footer>
    </div>
</div>

<?php
$labels = array();
foreach ($pages as $pid => $pdef) $labels[$pid] = $pdef['label'];
?>
<script>
window.FX = {
    csrf: <?= fx_json_encode($csrf) ?>,
    pageLabels: <?= fx_json_encode($labels) ?>,
    publishingEnabled: <?= $publishingEnabled ? 'true' : 'false' ?>
};
</script>
<script src="assets/editor.js?v=21"></script>
</body>
</html>
