// Furutec Editor — multi-page client glue.
(function () {
    'use strict';

    var status     = document.getElementById('fx-status');
    var preview    = document.getElementById('fx-preview');
    var previewLbl = document.getElementById('fx-preview-label');
    var saveBtn    = document.getElementById('fx-save');
    var publishBtn = document.getElementById('fx-publish');
    var restoreBtn = document.getElementById('fx-restore');
    var refreshBtn = document.getElementById('fx-refresh-preview');
    var expandAll  = document.getElementById('fx-expand-all');
    var collapseAll= document.getElementById('fx-collapse-all');
    var pickerBtns = Array.prototype.slice.call(document.querySelectorAll('.fx-picker-btn'));
    var pageBlocks = Array.prototype.slice.call(document.querySelectorAll('.fx-page-sections'));
    var deviceBtns = Array.prototype.slice.call(document.querySelectorAll('.fx-device-btn'));
    var frameWrap  = document.getElementById('fx-preview-frame-wrap');
    var previewWrap= frameWrap ? frameWrap.parentNode : null;   // .fx-preview-wrap — carries data-device too so preview-bar controls can react
    var zoomSelect = document.getElementById('fx-zoom-select');
    var dirty      = false;
    var currentPage = pickerBtns.length ? pickerBtns[0].getAttribute('data-page') : 'home';

    // Initial: mark outer wrap desktop so zoom control shows on load
    if (previewWrap) previewWrap.setAttribute('data-device', 'desktop');

    // ---- Accordion open/close (delegated) ----
    // Also anchors the preview iframe to the corresponding section on the
    // rendered page — same-origin, so we directly reach into the iframe's
    // contentDocument and call scrollIntoView() on the target element.
    document.addEventListener('click', function (e) {
        var head = e.target.closest && e.target.closest('.fx-acc-head');
        if (!head) return;
        var acc = head.closest('.fx-acc');
        if (!acc) return;
        var open = !acc.classList.contains('is-open');
        acc.classList.toggle('is-open', open);
        head.setAttribute('aria-expanded', open ? 'true' : 'false');

        // Any manual sidebar click clears the preview-click focus highlight
        // from every OTHER section — the user is telling us where they're
        // working, so the persistent orange accent should follow them.
        var focused = document.querySelectorAll('.fx-acc.is-focused');
        for (var i = 0; i < focused.length; i++) {
            if (focused[i] !== acc) focused[i].classList.remove('is-focused');
        }

        // Scroll the preview to this section — fires on both open and re-close-click,
        // which is fine (clicking the header re-anchors the preview even if the
        // section is already open).
        var target = acc.getAttribute('data-preview-target');
        if (target) scrollPreviewTo(target);
    });

    // ---- Scroll iframe preview to a selector (same-origin, no postMessage) ----
    // Silently no-ops if the iframe hasn't loaded yet, the target isn't found,
    // or a cross-origin exception is thrown.  Adds a subtle flash on the target
    // to help Amy locate what she's editing.
    function scrollPreviewTo(selector) {
        if (!preview) return;
        try {
            var doc = preview.contentDocument;
            if (!doc) return;
            var el = doc.querySelector(selector);
            if (!el) return;
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            flashPreviewTarget(el);
        } catch (err) { /* cross-origin, doc gone, etc. — silent */ }
    }
    function flashPreviewTarget(el) {
        try {
            var prev = el.style.boxShadow;
            var prevT = el.style.transition;
            el.style.transition = 'box-shadow .35s ease';
            el.style.boxShadow  = 'inset 0 0 0 3px rgba(46, 49, 146, 0.55)';
            setTimeout(function () {
                el.style.boxShadow  = prev;
                setTimeout(function () { el.style.transition = prevT; }, 400);
            }, 900);
        } catch (err) { /* silent */ }
    }

    // ---- Reverse anchoring: preview click → sidebar section highlight ----
    // Same-origin trick again: on every iframe load we attach a single
    // click listener to the iframe's document, walk up from the click
    // target to the first ancestor that matches any known section's
    // preview_target selector, then focus the matching sidebar accordion
    // (scrolls the sidebar to it + adds the persistent .is-focused class).
    // Silently no-ops on cross-origin, no matching selector, or no
    // sidebar section for this page.
    function focusSidebarSection(acc) {
        if (!acc) return;
        // Clear focus from any previously-focused accordion (site-wide, not
        // just current page — page switches also drop the class naturally).
        var all = document.querySelectorAll('.fx-acc.is-focused');
        for (var i = 0; i < all.length; i++) {
            if (all[i] !== acc) all[i].classList.remove('is-focused');
        }
        // Open + focus.  Re-adding the class after a microtask restarts
        // the CSS pulse animation even when the same section is re-clicked.
        acc.classList.remove('is-focused');
        acc.classList.add('is-open');
        var head = acc.querySelector('.fx-acc-head');
        if (head) head.setAttribute('aria-expanded', 'true');
        // Scroll the sidebar so the accordion header is comfortably in view.
        // block:'center' keeps the whole heading visible without slamming to top.
        acc.scrollIntoView({ behavior: 'smooth', block: 'center' });
        // Force reflow before re-adding the class so the animation replays.
        void acc.offsetWidth;
        acc.classList.add('is-focused');
    }
    function installPreviewClickTracker() {
        try {
            var doc = preview && preview.contentDocument;
            if (!doc || !doc.body) return;
            // Guard: install at most once per iframe document.
            if (doc.body.dataset.fxTrackerInstalled === '1') return;
            doc.body.dataset.fxTrackerInstalled = '1';

            doc.addEventListener('click', function (e) {
                // Build the list of selectors *currently* active (this page).
                // Doing it lazily on click keeps things correct even if
                // Amy switches pages/expands accordions mid-session.
                var active = document.querySelector('.fx-page-sections.is-active');
                if (!active) return;
                var accs = active.querySelectorAll('.fx-acc[data-preview-target]');
                var pairs = [];
                for (var i = 0; i < accs.length; i++) {
                    var sel = accs[i].getAttribute('data-preview-target');
                    if (sel) pairs.push({ selector: sel, acc: accs[i] });
                }
                if (!pairs.length) return;

                // Walk up from the click target; first ancestor that matches
                // ANY of our selectors wins (deepest = most specific).
                var node = e.target;
                var match = null;
                while (node && node !== doc.documentElement) {
                    for (var j = 0; j < pairs.length; j++) {
                        try {
                            if (node.matches && node.matches(pairs[j].selector)) {
                                match = pairs[j].acc;
                                break;
                            }
                        } catch (err) { /* bad selector, skip */ }
                    }
                    if (match) break;
                    node = node.parentNode;
                }
                if (match) focusSidebarSection(match);
                // No match → silent no-op (click in nav/footer/etc.)
            }, true);
        } catch (err) { /* cross-origin, doc gone — silent */ }
    }
    // Arming the tracker.  The iframe 'load' event alone is NOT enough:
    // 'load' waits for every subresource to finish, and the product page
    // pulls several multi-MB MP4 diagram videos.  Measured >4s there, and
    // far worse on a slow connection — during that whole window clicks in
    // the preview silently did nothing, which read as "the feature only
    // works on Home" (Home was usually already loaded when it was tried).
    //
    // So we poll instead.  installPreviewClickTracker() no-ops once the
    // flag is set on a document, and a page switch swaps in a fresh
    // document with no flag — so this re-arms within 400ms of any
    // navigation, long before the videos have finished downloading.
    if (preview) preview.addEventListener('load', installPreviewClickTracker);
    setInterval(installPreviewClickTracker, 400);
    // And once immediately, in case the iframe is already interactive.
    installPreviewClickTracker();

    // ---- Page switching (collapsible picker) ----
    // Collapsed state (default): only the active row is visible.
    // Clicking the active row toggles the expanded state.
    // Clicking a non-active row switches to that page and collapses again.
    var pickerButtonsEl = document.querySelector('.fx-picker-buttons');
    pickerBtns.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();   // don't let the outside-click handler close us on this click
            if (btn.classList.contains('is-active')) {
                if (pickerButtonsEl) pickerButtonsEl.classList.toggle('is-expanded');
                return;
            }
            var page = btn.getAttribute('data-page');
            pickerBtns.forEach(function (b) { b.classList.toggle('is-active', b === btn); });
            if (pickerButtonsEl) pickerButtonsEl.classList.remove('is-expanded');
            pageBlocks.forEach(function (blk) {
                blk.classList.toggle('is-active', blk.getAttribute('data-page') === page);
            });
            currentPage = page;
            var label = (window.FX.pageLabels && window.FX.pageLabels[page]) || page;
            previewLbl.textContent = 'Preview · ' + label + ' (draft)';
            preview.src = 'preview.php?page=' + encodeURIComponent(page) + '&ts=' + Date.now();
        });
    });
    // Collapse on outside click (event bubbles up past picker-btn stopPropagation
    // above, so this fires only for clicks outside the button group).
    document.addEventListener('click', function (e) {
        if (!pickerButtonsEl || !pickerButtonsEl.classList.contains('is-expanded')) return;
        if (pickerButtonsEl.contains(e.target)) return;
        pickerButtonsEl.classList.remove('is-expanded');
    });

    // ---- Device toggle (Desktop / Tablet / Mobile preview width) ----
    deviceBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var device = btn.getAttribute('data-device');
            deviceBtns.forEach(function (b) { b.classList.toggle('is-active', b === btn); });
            if (frameWrap)   frameWrap.setAttribute('data-device', device);
            if (previewWrap) previewWrap.setAttribute('data-device', device);
        });
    });

    // ---- Desktop zoom (only affects desktop mode — CSS uses --fx-zoom) ----
    if (zoomSelect && frameWrap) {
        zoomSelect.addEventListener('change', function () {
            var z = parseFloat(zoomSelect.value) || 1;
            frameWrap.style.setProperty('--fx-zoom', String(z));
        });
    }

    // ---- Expand/collapse all in the CURRENT view (shared + active page) ----
    function currentAccordions() {
        var sharedBlock = document.querySelector('.fx-shared-block');
        var pageBlock   = document.querySelector('.fx-page-sections.is-active');
        var out = [];
        if (sharedBlock) out = out.concat(Array.prototype.slice.call(sharedBlock.querySelectorAll('.fx-acc')));
        if (pageBlock)   out = out.concat(Array.prototype.slice.call(pageBlock.querySelectorAll('.fx-acc')));
        return out;
    }
    expandAll.addEventListener('click', function () {
        currentAccordions().forEach(function (a) { a.classList.add('is-open'); var h = a.querySelector('.fx-acc-head'); if (h) h.setAttribute('aria-expanded', 'true'); });
    });
    collapseAll.addEventListener('click', function () {
        currentAccordions().forEach(function (a) { a.classList.remove('is-open'); var h = a.querySelector('.fx-acc-head'); if (h) h.setAttribute('aria-expanded', 'false'); });
    });

    // ---- Dirty tracking ----
    document.addEventListener('input', function (e) {
        if (e.target && e.target.classList && e.target.classList.contains('fx-track')) markDirty();
    });
    window.addEventListener('beforeunload', function (e) {
        if (dirty) { e.preventDefault(); e.returnValue = ''; }
    });
    // The preview is rendered server-side from the saved file, so it cannot
    // reflect unsaved edits.  Rather than let the pane silently contradict
    // what was just typed, say so plainly and offer the fix in the same bar.
    var staleBar = document.getElementById('fx-stale-bar');
    function setStale(isStale) {
        if (staleBar) staleBar.hidden = !isStale;
    }
    function markDirty() { dirty = true; setStatus('Unsaved changes', 'is-dirty'); setStale(true); }
    function markClean() { dirty = false; setStale(false); }
    function setStatus(msg, cls) { status.textContent = msg; status.className = 'fx-status ' + (cls || ''); }
    function setBusy(busy) {
        [saveBtn, publishBtn, restoreBtn, refreshBtn].forEach(function (b) {
            if (!b) return;
            // Never re-enable the publish button when publishing is disabled
            // by config — otherwise setBusy(false) would silently un-disable
            // the server-rendered disabled state.
            if (b === publishBtn && window.FX && window.FX.publishingEnabled === false) {
                b.disabled = true;
                return;
            }
            b.disabled = !!busy;
        });
    }

    // ---- Collect current field values into nested object (shared + per page) ----
    function collectContent() {
        var out = {};
        Array.prototype.forEach.call(document.querySelectorAll('.fx-track'), function (el) {
            var key = el.getAttribute('data-field'); // e.g. "shared.nav.phone" or "home.hero.headline_1"
            if (!key) return;
            var parts = key.split('.');
            if (parts.length !== 3) return;
            var top = parts[0], sect = parts[1], name = parts[2];
            if (!out[top]) out[top] = {};
            if (!out[top][sect]) out[top][sect] = {};
            out[top][sect][name] = el.value;
        });
        return out;
    }

    function post(url, body) {
        return fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF': window.FX.csrf,
                'X-Requested-With': 'fetch'
            },
            body: JSON.stringify(body || {})
        }).then(function (r) { return r.text(); })
          .then(function (txt) {
              try { return JSON.parse(txt); }
              catch (_) { return { ok: false, error: 'Bad server response: ' + txt.slice(0, 200) }; }
          });
    }

    saveBtn.addEventListener('click', function () {
        setBusy(true); setStatus('Saving…', 'is-busy');
        post('save.php', { content: collectContent() }).then(function (r) {
            setBusy(false);
            if (!r.ok) { setStatus(r.error || 'Save failed', 'is-err'); return; }
            markClean(); setStatus('Draft saved · preview updated', 'is-ok');
            refreshPreview();
        }).catch(function (e) {
            setBusy(false); setStatus('Save error: ' + (e && e.message ? e.message : 'unknown'), 'is-err');
        });
    });

    // ---- Sidebar collapse -------------------------------------------------
    // Hands the full width to the preview for reviewing, then gives the panel
    // back for editing.  Remembered per browser; a storage failure just means
    // it starts expanded, which is the safe default.
    var sidebarToggle = document.getElementById('fx-sidebar-toggle');
    var mainEl = document.querySelector('.fx-main');
    if (sidebarToggle && mainEl) {
        function setSidebarCollapsed(collapsed) {
            mainEl.classList.toggle('is-sidebar-collapsed', collapsed);
            sidebarToggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            sidebarToggle.setAttribute('aria-label',
                collapsed ? 'Show the editing panel' : 'Hide the editing panel');
            sidebarToggle.setAttribute('title',
                collapsed ? 'Show the editing panel'
                          : 'Hide the editing panel to see the preview full width');
            try { localStorage.setItem('fxSidebarCollapsed', collapsed ? '1' : '0'); } catch (e) {}
        }
        var startCollapsed = false;
        try { startCollapsed = localStorage.getItem('fxSidebarCollapsed') === '1'; } catch (e) {}
        if (startCollapsed) setSidebarCollapsed(true);

        sidebarToggle.addEventListener('click', function () {
            setSidebarCollapsed(!mainEl.classList.contains('is-sidebar-collapsed'));
        });
    }

    // ---- First-run explainer --------------------------------------------
    // Draft-vs-live is the concept everything else depends on, and nothing in
    // the UI used to state it.  Shown until dismissed; the flag is per-browser
    // and failure to read/write it just means the panel shows again, which is
    // harmless — so localStorage access is wrapped rather than trusted.
    var introEl = document.getElementById('fx-intro');
    if (introEl) {
        var seen = false;
        try { seen = localStorage.getItem('fxIntroDismissed') === '1'; } catch (e) {}
        if (!seen) introEl.hidden = false;
        var introX = document.getElementById('fx-intro-dismiss');
        if (introX) introX.addEventListener('click', function () {
            introEl.hidden = true;
            try { localStorage.setItem('fxIntroDismissed', '1'); } catch (e) {}
        });
    }

    // The stale-preview bar carries its own Save so the fix is one click away
    // from where the problem is noticed, not back up in the toolbar.
    var staleSaveBtn = document.getElementById('fx-stale-save');
    if (staleSaveBtn) staleSaveBtn.addEventListener('click', function () { saveBtn.click(); });

    publishBtn.addEventListener('click', function () {
        // Client-side kill-switch: if publishing is disabled at the server
        // (config.php: publishing_enabled=false), don't even prompt.  The
        // server refuses too — this just avoids a needless confirm dialog
        // and a wasted network round-trip.
        if (window.FX && window.FX.publishingEnabled === false) {
            setStatus('Publishing is temporarily disabled by the site administrator. Save draft still works.', 'is-err');
            return;
        }
        if (dirty && !confirm('You have unsaved changes. Save them before publishing?')) return;
        if (dirty) { saveBtn.click(); setTimeout(publish, 1200); }
        else publish();
    });
    function publish() {
        if (!confirm('Publish the current draft to ALL live pages?\n\nEvery page gets a timestamped backup and can be restored in one click.')) return;
        setBusy(true); setStatus('Publishing…', 'is-busy');
        post('publish.php', {}).then(function (r) {
            setBusy(false);
            if (!r.ok) { setStatus(r.error || 'Publish failed', 'is-err'); return; }
            var files = r.files ? r.files.length : 0;
            setStatus('Published ' + files + ' page(s) live · backups saved', 'is-ok');
            refreshPreview();
        });
    }

    restoreBtn.addEventListener('click', function () {
        if (!confirm('Restore the live pages to the previous published version?\n\nYour current draft will not be lost — only the live pages revert.')) return;
        setBusy(true); setStatus('Restoring…', 'is-busy');
        post('restore.php', {}).then(function (r) {
            setBusy(false);
            if (!r.ok) { setStatus(r.error || 'Restore failed', 'is-err'); return; }
            var files = r.files ? r.files.length : 0;
            setStatus('Restored ' + files + ' page(s) from backup', 'is-ok');
        });
    });

    refreshBtn.addEventListener('click', function () { refreshPreview(); });
    function refreshPreview() {
        preview.src = 'preview.php?page=' + encodeURIComponent(currentPage) + '&ts=' + Date.now();
    }

    document.addEventListener('change', function (e) {
        var input = e.target;
        if (!input.classList || !input.classList.contains('fx-file')) return;
        var file = input.files && input.files[0];
        if (!file) return;
        var fieldKey = input.getAttribute('data-field-key');
        var kind     = input.getAttribute('data-media-type');

        var maxMb = (kind === 'video') ? 30 : 5;
        if (file.size > maxMb * 1024 * 1024) {
            setStatus('File too large (max ' + maxMb + ' MB)', 'is-err');
            input.value = ''; return;
        }

        setBusy(true); setStatus('Uploading ' + file.name + '…', 'is-busy');
        var form = new FormData();
        form.append('file', file);
        form.append('kind', kind);
        form.append('field_key', fieldKey);
        fetch('upload.php', {
            method: 'POST', credentials: 'same-origin',
            headers: { 'X-CSRF': window.FX.csrf, 'X-Requested-With': 'fetch' },
            body: form
        }).then(function (r) { return r.text(); })
          .then(function (txt) { try { return JSON.parse(txt); } catch (_) { return { ok: false, error: 'Bad upload response' }; } })
          .then(function (r) {
              setBusy(false); input.value = '';
              if (!r.ok) { setStatus(r.error || 'Upload failed', 'is-err'); return; }
              var wrap = document.querySelector('[data-field-key="' + cssEsc(fieldKey) + '"]');
              if (!wrap) return;
              var hidden = wrap.querySelector('input.fx-track[type="hidden"]');
              var previousValue = hidden ? hidden.value : '';   // for crop Discard
              if (hidden) hidden.value = r.filename;
              var fname = wrap.querySelector('.fx-media-filename');
              if (fname) fname.textContent = r.filename;
              var previewEl = wrap.querySelector('.fx-media-preview');
              if (previewEl) {
                  var url = '../assets/' + encodePath(r.filename) + '?t=' + Date.now();
                  if (kind === 'video') {
                      previewEl.innerHTML = '<video src="' + escAttr(url) + '" controls muted playsinline preload="metadata"></video>';
                  } else {
                      previewEl.innerHTML = '<img src="' + escAttr(url) + '" alt=""/>';
                  }
              }
              markDirty();
              setStatus('Uploaded · click Save to store', 'is-ok');
              // Straight into the cropper if this slot has a fixed shape, so a
              // freshly uploaded photo gets framed before it ever hits the page.
              if (kind === 'image') maybeOfferCrop(fieldKey, r.filename, previousValue);
          });
    });

    function cssEsc(s) {
        if (window.CSS && CSS.escape) return CSS.escape(s);
        return String(s).replace(/[^a-zA-Z0-9_-]/g, '\\$&');
    }
    function escAttr(s) {
        return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }
    function encodePath(p) { return String(p).split('/').map(encodeURIComponent).join('/'); }

    // ======================================================================
    // IMAGE CROPPER
    // ----------------------------------------------------------------------
    // Crops and resizes entirely in the browser (canvas -> toBlob) and
    // uploads the result through the normal upload endpoint, so it needs no
    // GD/ImageMagick on the host.  The original file is never modified — the
    // crop is written as a new file and the field re-pointed at it.
    //
    // Slots whose CSS crops with `cover` declare the shape they display at
    // (data-crop-w/h), and the crop box locks to that ratio so what you frame
    // is exactly what the site shows.  Slots with no declared shape get a
    // free-form box.
    // ======================================================================
    var cropOverlay = document.getElementById('fx-crop-overlay');
    var cropImg     = document.getElementById('fx-crop-img');
    var cropBox     = document.getElementById('fx-crop-box');
    var cropWrap    = document.getElementById('fx-crop-imgwrap');
    var cropShade   = document.getElementById('fx-crop-shade');
    var cropSub     = document.getElementById('fx-crop-sub');
    var cropStatus  = document.getElementById('fx-crop-status');

    var cropState = {
        fieldKey: null,
        ratio: 0,          // 0 = free-form
        outW: 0, outH: 0,  // target output pixels (0 = keep source scale)
        box: { x: 0, y: 0, w: 0, h: 0 },   // in displayed-image pixels
        drag: null,
        // When the cropper auto-opens after a pick, remember what the field
        // held before so "Discard" can genuinely undo choosing the wrong file.
        revertTo: null,
        revertKind: null,
    };

    function cropSetStatus(msg, cls) {
        if (!cropStatus) return;
        cropStatus.textContent = msg || '';
        cropStatus.className = 'fx-crop-status' + (cls ? ' ' + cls : '');
    }

    function openCropper(fieldKey, url, w, h) {
        cropState.fieldKey = fieldKey;
        cropState.revertTo = null;      // set by maybeOfferCrop on the auto path only
        showCropDiscard(false);
        cropState.outW = w || 0;
        cropState.outH = h || 0;
        cropState.ratio = (w && h) ? (w / h) : 0;

        cropSub.textContent = cropState.ratio
            ? 'This slot displays at ' + w + ' x ' + h + ' (' + cropState.ratio.toFixed(2) +
              ':1). The box is locked to that shape — drag to choose what stays visible.'
            : 'No fixed shape for this slot — drag any area you want to keep.';

        cropSetStatus('');
        cropOverlay.hidden = false;
        document.body.classList.add('fx-lib-open');

        // Reading pixels back off a canvas is blocked if the image was loaded
        // cross-origin without CORS headers.  Nearly everything here is
        // same-origin (/assets/), but a couple of fields point at Unsplash.
        // Requesting anonymous CORS is a no-op for same-origin and lets the
        // remote ones through when the host allows it.
        cropImg.crossOrigin = 'anonymous';
        // Load fresh (cache-bust) so a re-crop doesn't reuse a stale bitmap.
        cropImg.onload = function () { resetCropBox(); };
        cropImg.onerror = function () {
            cropSetStatus('Could not load that image for cropping. If it is hosted on another site, ' +
                          'download it and upload it here first.', 'is-err');
        };
        cropImg.src = url + (url.indexOf('?') === -1 ? '?' : '&') + 'c=' + Date.now();
    }
    function closeCropper() {
        cropOverlay.hidden = true;
        document.body.classList.remove('fx-lib-open');
        cropState.fieldKey = null;
    }

    /** Largest box of the target ratio that fits, centred. */
    function resetCropBox() {
        var iw = cropImg.clientWidth, ih = cropImg.clientHeight;
        if (!iw || !ih) return;
        var w, h;
        if (cropState.ratio) {
            if (iw / ih > cropState.ratio) { h = ih; w = h * cropState.ratio; }
            else                           { w = iw; h = w / cropState.ratio; }
        } else {
            w = iw; h = ih;
        }
        cropState.box = { x: (iw - w) / 2, y: (ih - h) / 2, w: w, h: h };
        drawCropBox();
    }

    function drawCropBox() {
        var b = cropState.box;
        cropBox.style.left   = b.x + 'px';
        cropBox.style.top    = b.y + 'px';
        cropBox.style.width  = b.w + 'px';
        cropBox.style.height = b.h + 'px';
        // Dim everything outside the box.
        if (cropShade) {
            cropShade.style.clipPath =
                'polygon(0 0, 100% 0, 100% 100%, 0 100%, 0 0,' +
                b.x + 'px ' + b.y + 'px,' +
                b.x + 'px ' + (b.y + b.h) + 'px,' +
                (b.x + b.w) + 'px ' + (b.y + b.h) + 'px,' +
                (b.x + b.w) + 'px ' + b.y + 'px,' +
                b.x + 'px ' + b.y + 'px)';
        }
        // Live pixel readout — warns when the source can't fill the target.
        if (cropImg.naturalWidth && cropState.outW) {
            var scale = cropImg.naturalWidth / cropImg.clientWidth;
            var srcW = Math.round(b.w * scale);
            if (srcW < cropState.outW * 0.75) {
                cropSetStatus('Heads up: this area is only ' + srcW + 'px wide, less than the ' +
                              cropState.outW + 'px this slot uses — it may look soft.', 'is-err');
            } else {
                cropSetStatus('Selected area: ' + srcW + 'px wide', '');
            }
        }
    }

    function clampBox() {
        var b = cropState.box;
        var iw = cropImg.clientWidth, ih = cropImg.clientHeight;
        if (b.w > iw) { b.w = iw; if (cropState.ratio) b.h = b.w / cropState.ratio; }
        if (b.h > ih) { b.h = ih; if (cropState.ratio) b.w = b.h * cropState.ratio; }
        if (b.x < 0) b.x = 0;
        if (b.y < 0) b.y = 0;
        if (b.x + b.w > iw) b.x = iw - b.w;
        if (b.y + b.h > ih) b.y = ih - b.h;
    }

    if (cropOverlay) {
        // Drag to move, or grab a corner to resize.
        cropWrap.addEventListener('mousedown', function (e) {
            if (cropOverlay.hidden) return;
            var handle = e.target.closest('.fx-crop-handle');
            var inBox  = e.target.closest('.fx-crop-box');
            if (!handle && !inBox) return;
            e.preventDefault();
            cropState.drag = {
                mode: handle ? 'resize' : 'move',
                corner: handle ? handle.getAttribute('data-h') : null,
                sx: e.clientX, sy: e.clientY,
                start: { x: cropState.box.x, y: cropState.box.y, w: cropState.box.w, h: cropState.box.h },
            };
        });
        document.addEventListener('mousemove', function (e) {
            var d = cropState.drag;
            if (!d) return;
            var dx = e.clientX - d.sx, dy = e.clientY - d.sy;
            var b = cropState.box, s = d.start;

            if (d.mode === 'move') {
                b.x = s.x + dx;
                b.y = s.y + dy;
            } else {
                // Resize from the grabbed corner; the opposite corner is the anchor.
                var right  = d.corner === 'ne' || d.corner === 'se';
                var bottom = d.corner === 'sw' || d.corner === 'se';
                var newW = right ? s.w + dx : s.w - dx;
                if (newW < 40) newW = 40;
                var newH = cropState.ratio ? newW / cropState.ratio
                                           : (bottom ? s.h + dy : s.h - dy);
                if (newH < 40) { newH = 40; if (cropState.ratio) newW = newH * cropState.ratio; }
                b.w = newW;
                b.h = newH;
                b.x = right  ? s.x : s.x + (s.w - newW);
                b.y = bottom ? s.y : s.y + (s.h - newH);
            }
            clampBox();
            drawCropBox();
        });
        document.addEventListener('mouseup', function () { cropState.drag = null; });

        document.getElementById('fx-crop-close').addEventListener('click', closeCropper);
        document.getElementById('fx-crop-skip').addEventListener('click', closeCropper);
        document.getElementById('fx-crop-reset').addEventListener('click', resetCropBox);
        cropOverlay.addEventListener('click', function (e) { if (e.target === cropOverlay) closeCropper(); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !cropOverlay.hidden) closeCropper();
        });

        // Manual Crop button on any image field.
        document.addEventListener('click', function (e) {
            var btn = e.target.closest && e.target.closest('.fx-crop-btn');
            if (!btn) return;
            e.preventDefault();
            var key = btn.getAttribute('data-field-key');
            var track = document.querySelector('.fx-track[data-field="' + cssEsc(key) + '"]');
            if (!track || !track.value) { setStatus('Nothing to crop — add an image first.', 'is-err'); return; }
            openCropper(key, '../assets/' + encodePath(track.value),
                        parseInt(btn.getAttribute('data-crop-w'), 10) || 0,
                        parseInt(btn.getAttribute('data-crop-h'), 10) || 0);
        });

        document.getElementById('fx-crop-apply').addEventListener('click', applyCrop);
        document.getElementById('fx-crop-discard').addEventListener('click', discardCropPick);
    }

    function applyCrop() {
        if (!cropState.fieldKey) return;
        var b = cropState.box;
        var scale = cropImg.naturalWidth / cropImg.clientWidth;   // displayed -> source px
        var sx = Math.max(0, Math.round(b.x * scale));
        var sy = Math.max(0, Math.round(b.y * scale));
        var sw = Math.round(b.w * scale);
        var sh = Math.round(b.h * scale);
        if (sx + sw > cropImg.naturalWidth)  sw = cropImg.naturalWidth  - sx;
        if (sy + sh > cropImg.naturalHeight) sh = cropImg.naturalHeight - sy;
        if (sw < 2 || sh < 2) { cropSetStatus('Selection is too small.', 'is-err'); return; }

        // Output at the slot's size, but never upscale beyond what we cut.
        var outW = cropState.outW || sw;
        var outH = cropState.outH || sh;
        if (outW > sw) { outH = Math.round(outH * (sw / outW)); outW = sw; }

        var canvas = document.createElement('canvas');
        canvas.width = outW; canvas.height = outH;
        var ctx = canvas.getContext('2d');
        ctx.imageSmoothingQuality = 'high';
        // White under the crop so transparent PNGs don't turn black as JPEG.
        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(0, 0, outW, outH);
        try {
            ctx.drawImage(cropImg, sx, sy, sw, sh, 0, 0, outW, outH);
        } catch (err) {
            cropSetStatus('Could not read that image (it may be cross-origin).', 'is-err');
            return;
        }

        cropSetStatus('Saving cropped image…', '');
        // toBlob — not drawImage — is what throws on a tainted canvas, so the
        // guard has to sit here.
        var toBlobOk = true;
        try {
            canvas.toBlob(onCropBlob, 'image/jpeg', 0.9);
        } catch (err) {
            toBlobOk = false;
            cropSetStatus('This image is hosted on another site, so it cannot be cropped here. ' +
                          'Upload a copy to the Library first.', 'is-err');
        }
        if (!toBlobOk) return;

        function onCropBlob(blob) {
            if (!blob) { cropSetStatus('Could not encode the crop.', 'is-err'); return; }
            var track = document.querySelector('.fx-track[data-field="' + cssEsc(cropState.fieldKey) + '"]');
            var base  = (track && track.value ? track.value.split('/').pop() : 'image')
                          .replace(/\.[a-z0-9]+$/i, '');
            var fd = new FormData();
            fd.append('file', blob, base + '-crop.jpg');
            fd.append('kind', 'image');
            fd.append('dest', 'uploads');
            fetch('upload.php', {
                method: 'POST', credentials: 'same-origin',
                headers: { 'X-CSRF': window.FX.csrf }, body: fd
            }).then(function (r) { return r.json(); })
              .then(function (d) {
                  if (!d.ok) { cropSetStatus(d.error || 'Upload failed.', 'is-err'); return; }
                  if (track) {
                      track.value = d.filename;
                      var media = track.closest('.fx-media');
                      if (media) {
                          var nameEl = media.querySelector('.fx-media-filename');
                          if (nameEl) nameEl.textContent = d.filename;
                          var prev = media.querySelector('.fx-media-preview');
                          if (prev) prev.innerHTML = '<img src="../assets/' + encodePath(d.filename) + '" alt=""/>';
                      }
                      track.dispatchEvent(new Event('input', { bubbles: true }));
                  }
                  markDirty();
                  setStatus('Cropped · click Save to store', 'is-ok');
                  closeCropper();
              })
              .catch(function () { cropSetStatus('Network error saving the crop.', 'is-err'); });
        }
    }

    /** Offered right after a new image lands in a slot that has a fixed shape. */
    function maybeOfferCrop(fieldKey, filename, previousValue) {
        var track = document.querySelector('.fx-track[data-field="' + cssEsc(fieldKey) + '"]');
        var media = track && track.closest('.fx-media');
        if (!media) return;
        var w = parseInt(media.getAttribute('data-crop-w'), 10) || 0;
        var h = parseInt(media.getAttribute('data-crop-h'), 10) || 0;
        if (!w || !h) return;                       // no declared shape — don't nag
        if (/\.svg$/i.test(filename)) return;       // vector: scales cleanly, nothing to crop
        openCropper(fieldKey, '../assets/' + encodePath(filename), w, h);
        // Auto-opened, so offer a genuine way out: put the field back to what
        // it held before the pick.  Only meaningful in this path — a manual
        // Crop on an existing image has nothing to revert to.
        cropState.revertTo = (typeof previousValue === 'string') ? previousValue : null;
        showCropDiscard(cropState.revertTo !== null);
    }

    /** The auto-open path gets a Discard button; the manual Crop path doesn't. */
    function showCropDiscard(show) {
        var btn = document.getElementById('fx-crop-discard');
        if (btn) btn.hidden = !show;
    }

    /** Undo the pick that triggered an auto-opened cropper. */
    function discardCropPick() {
        var key = cropState.fieldKey, prev = cropState.revertTo;
        closeCropper();
        if (key === null || prev === null) return;
        var track = document.querySelector('.fx-track[data-field="' + cssEsc(key) + '"]');
        if (!track) return;
        track.value = prev;
        var media = track.closest('.fx-media');
        if (media) {
            var nameEl = media.querySelector('.fx-media-filename');
            if (nameEl) nameEl.textContent = prev;
            var prevEl = media.querySelector('.fx-media-preview');
            if (prevEl) {
                prevEl.innerHTML = prev
                    ? '<img src="../assets/' + encodePath(prev) + '" alt=""/>'
                    : '<span class="fx-media-empty">(no file selected)</span>';
            }
        }
        track.dispatchEvent(new Event('input', { bubbles: true }));
        setStatus(prev ? 'Reverted to the previous image' : 'Image removed', '');
    }

    // ======================================================================
    // VIDEO FOCAL POINT
    // ----------------------------------------------------------------------
    // A video can't be re-encoded in the browser, so where an image gets a
    // real crop, a video gets an anchor instead: the point that stays visible
    // when CSS crops it to fill.  Saved as an object-position pair ("50% 30%")
    // into a companion <name>_focus field and applied by render.php.
    //
    // Offered only on the two videos the layout actually crops — the hero and
    // company backgrounds.  The other six are letterboxed (object-fit:contain)
    // and already show every pixel, so an anchor there would do nothing.
    // ======================================================================
    var focusOverlay = document.getElementById('fx-focus-overlay');
    var focusVideo   = document.getElementById('fx-focus-video');
    var focusWrap    = document.getElementById('fx-focus-wrap');
    var focusMarker  = document.getElementById('fx-focus-marker');
    var focusStatus  = document.getElementById('fx-focus-status');
    var focusState   = { storeKey: null, x: 50, y: 50 };

    function focusDraw() {
        focusMarker.style.left = focusState.x + '%';
        focusMarker.style.top  = focusState.y + '%';
        if (focusStatus) {
            focusStatus.textContent = 'Anchor: ' + Math.round(focusState.x) + '% from left, ' +
                                      Math.round(focusState.y) + '% from top';
        }
    }

    function openFocus(videoKey, storeKey) {
        var track = document.querySelector('.fx-track[data-field="' + cssEsc(videoKey) + '"]');
        if (!track || !track.value) { setStatus('Add a video first.', 'is-err'); return; }
        var store = document.querySelector('.fx-track[data-field="' + cssEsc(storeKey) + '"]');
        focusState.storeKey = storeKey;

        // Parse any previously saved pair; fall back to dead centre.
        var cur = (store && store.value ? store.value : '').match(/([\d.]+)%\s+([\d.]+)%/);
        focusState.x = cur ? parseFloat(cur[1]) : 50;
        focusState.y = cur ? parseFloat(cur[2]) : 50;

        focusVideo.src = '../assets/' + encodePath(track.value);
        focusOverlay.hidden = false;
        document.body.classList.add('fx-lib-open');
        focusDraw();
    }
    function closeFocus() {
        focusOverlay.hidden = true;
        document.body.classList.remove('fx-lib-open');
        try { focusVideo.pause(); } catch (e) {}
        focusState.storeKey = null;
    }

    if (focusOverlay) {
        document.addEventListener('click', function (e) {
            var btn = e.target.closest && e.target.closest('.fx-focus-btn');
            if (!btn) return;
            e.preventDefault();
            openFocus(btn.getAttribute('data-field-key'), btn.getAttribute('data-focus-key'));
        });

        // Click anywhere on the frame to move the anchor there.
        focusWrap.addEventListener('click', function (e) {
            var r = focusVideo.getBoundingClientRect();
            if (!r.width || !r.height) return;
            var x = ((e.clientX - r.left) / r.width) * 100;
            var y = ((e.clientY - r.top) / r.height) * 100;
            focusState.x = Math.max(0, Math.min(100, x));
            focusState.y = Math.max(0, Math.min(100, y));
            focusDraw();
        });

        document.getElementById('fx-focus-center').addEventListener('click', function () {
            focusState.x = 50; focusState.y = 50; focusDraw();
        });
        document.getElementById('fx-focus-close').addEventListener('click', closeFocus);
        document.getElementById('fx-focus-cancel').addEventListener('click', closeFocus);
        focusOverlay.addEventListener('click', function (e) { if (e.target === focusOverlay) closeFocus(); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !focusOverlay.hidden) closeFocus();
        });

        document.getElementById('fx-focus-apply').addEventListener('click', function () {
            if (!focusState.storeKey) return;
            var store = document.querySelector('.fx-track[data-field="' + cssEsc(focusState.storeKey) + '"]');
            if (store) {
                store.value = Math.round(focusState.x) + '% ' + Math.round(focusState.y) + '%';
                store.dispatchEvent(new Event('input', { bubbles: true }));
            }
            markDirty();
            setStatus('Focal point set · click Save to store', 'is-ok');
            closeFocus();
        });
    }

    // ---- Clear a media field --------------------------------------------
    // Until now a media slot could only be overwritten, never emptied, so
    // there was no way to go from 4 slideshow images back to 3.  This wipes
    // the tracked value and resets the preview + filename to the same empty
    // state the server renders for an unset field.
    document.addEventListener('click', function (e) {
        var btn = e.target.closest && e.target.closest('.fx-clear-btn');
        if (!btn) return;
        e.preventDefault();
        var key   = btn.getAttribute('data-field-key');
        var track = document.querySelector('.fx-track[data-field="' + cssEsc(key) + '"]');
        if (!track) return;
        if (!track.value) return;                       // already empty
        track.value = '';
        var media = track.closest('.fx-media');
        if (media) {
            var nameEl = media.querySelector('.fx-media-filename');
            if (nameEl) nameEl.textContent = '';
            var prev = media.querySelector('.fx-media-preview');
            if (prev) prev.innerHTML = '<span class="fx-media-empty">(no file selected)</span>';
        }
        track.dispatchEvent(new Event('input', { bubbles: true }));
        markDirty();
        setStatus('Removed · click Save to store', 'is-ok');
    });

    // ======================================================================
    // CONDITIONAL FIELDS
    // ----------------------------------------------------------------------
    // A field carrying data-show-when="<other field key>" +
    // data-show-when-is="<value>" is only displayed while that other field
    // holds that value.  Used to make the hero's Background type a real
    // either/or: pick Video and the slideshow slots disappear, pick Image
    // slideshow and the video field does.
    //
    // Hidden fields keep their inputs in the DOM, so their values persist and
    // still save — flipping between modes never discards what was set.
    //
    // A group heading whose fields are ALL hidden is hidden too, otherwise
    // you'd be left with an "Image slideshow" heading and nothing under it.
    // ======================================================================
    function applyConditionalFields() {
        var conditional = document.querySelectorAll('.fx-field[data-show-when]');
        for (var i = 0; i < conditional.length; i++) {
            var f = conditional[i];
            var ctrlKey = f.getAttribute('data-show-when');
            var want    = f.getAttribute('data-show-when-is');
            var ctrl    = document.querySelector('.fx-track[data-field="' + cssEsc(ctrlKey) + '"]');
            // If the controlling field is missing, fail open — better to show
            // a field than to silently hide something the client needs.
            var show = !ctrl || String(ctrl.value) === want;
            f.classList.toggle('is-cond-hidden', !show);
        }

        // Second pass: hide any group heading with no visible fields beneath
        // it.  Fields sit as siblings after their .fx-group-head, up to the
        // next heading.
        var heads = document.querySelectorAll('.fx-group-head');
        for (var h = 0; h < heads.length; h++) {
            var head = heads[h];
            var anyVisible = false;
            var n = head.nextElementSibling;
            while (n && !n.classList.contains('fx-group-head')) {
                if (n.classList.contains('fx-field') && !n.classList.contains('is-cond-hidden')) {
                    anyVisible = true;
                    break;
                }
                n = n.nextElementSibling;
            }
            head.classList.toggle('is-cond-hidden', !anyVisible);
        }
    }

    // Re-evaluate whenever any tracked field changes (cheap: a couple of
    // hundred attribute reads), and once on boot.
    document.addEventListener('change', function (e) {
        if (e.target && e.target.classList && e.target.classList.contains('fx-track')) {
            applyConditionalFields();
        }
    });
    document.addEventListener('input', function (e) {
        if (e.target && e.target.tagName === 'SELECT' && e.target.classList.contains('fx-track')) {
            applyConditionalFields();
        }
    });
    applyConditionalFields();

    // ======================================================================
    // MEDIA LIBRARY
    // ----------------------------------------------------------------------
    // Browses /assets/ through editor/library.php.  Two ways to look at the
    // same files:
    //   • Folders  — the real directory tree on disk (breadcrumb navigation)
    //   • Category — a virtual filter ("Homepage", "Product page", …) derived
    //                server-side from which fields reference each file, so it
    //                can never drift out of sync with content.php.
    // Picking a file writes its path into the field that opened the picker,
    // exactly as an upload would, then marks the draft dirty.
    // ======================================================================
    var libOverlay = document.getElementById('fx-lib-overlay');
    var libBody    = document.getElementById('fx-lib-body');
    var libCrumbs  = document.getElementById('fx-lib-crumbs');
    var libCats    = document.getElementById('fx-lib-cats');
    var libStatus  = document.getElementById('fx-lib-status');
    var libUseBtn  = document.getElementById('fx-lib-use');
    var libSearch  = document.getElementById('fx-lib-search');
    var libUploadIn= document.getElementById('fx-lib-upload');
    var libSubtitle= document.getElementById('fx-lib-sub');

    var libState = {
        fieldKey: null,     // which field opened us
        mediaType: null,    // 'image' | 'video' — filters what we show
        path: '',           // current folder, '' = library root
        category: null,     // null = folder browsing; else a category key
        selected: null,     // { path, kind, name }
        files: [],
        folders: [],
        cats: [],
        query: ''
    };

    function libSetStatus(msg, cls) {
        if (!libStatus) return;
        libStatus.textContent = msg || '';
        libStatus.className = 'fx-lib-status' + (cls ? ' ' + cls : '');
    }

    function libOpen(fieldKey, mediaType) {
        libState.fieldKey  = fieldKey;
        libState.mediaType = mediaType;
        libState.path      = '';
        libState.selected  = null;
        libState.query     = '';
        // Open on the images already used by the page being edited, rather
        // than dumping the user into a raw folder listing.  The field key is
        // "<page>.<section>.<field>", so its first segment is the category —
        // except shared sections, which map to the Brand & Shared bucket.
        // libLoad falls back to Folders if that category turns out empty.
        var scope = String(fieldKey).split('.')[0];
        libState.category = (scope === 'shared') ? 'shared' : scope;
        libState.autoCategory = libState.category;
        if (libSearch) libSearch.value = '';
        if (libUploadIn) {
            libUploadIn.setAttribute('accept',
                mediaType === 'video' ? 'video/mp4' : 'image/jpeg,image/png,image/webp');
        }
        if (libSubtitle) {
            libSubtitle.textContent = mediaType === 'video'
                ? 'Showing videos. Choose one, or upload a new MP4.'
                : 'Showing images. Choose one, or upload a new JPG / PNG / WebP.';
        }
        libOverlay.hidden = false;
        document.body.classList.add('fx-lib-open');
        libUseBtn.disabled = true;
        libSetStatus('');
        libLoad();
    }
    function libClose() {
        libOverlay.hidden = true;
        document.body.classList.remove('fx-lib-open');
        libState.fieldKey = null;
        libState.selected = null;
    }

    /** Fetch either one folder (browse mode) or the whole tree (category mode). */
    function libLoad() {
        libBody.innerHTML = '<p class="fx-lib-empty">Loading…</p>';
        var url = libState.category
            ? 'library.php?action=all'
            : 'library.php?action=list&path=' + encodeURIComponent(libState.path);

        fetch(url, { credentials: 'same-origin', headers: { 'X-CSRF': window.FX.csrf } })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d.ok) { libBody.innerHTML = '<p class="fx-lib-empty">' + escAttr(d.error || 'Could not load.') + '</p>'; return; }
                libState.files   = d.files || [];
                libState.folders = d.folders || [];
                libState.cats    = d.categories || libState.cats;
                if (typeof d.path === 'string') libState.path = d.path;

                // If we auto-opened on a category that holds nothing of this
                // media type (e.g. a page with no videos yet), don't strand the
                // user on an empty grid — drop back to browsing folders.
                if (libState.autoCategory && libState.category === libState.autoCategory) {
                    var usable = libState.files.filter(function (f) {
                        if (libState.mediaType && f.kind !== libState.mediaType) return false;
                        return (f.cats || []).indexOf(libState.category) !== -1;
                    });
                    libState.autoCategory = null;          // only auto-correct once
                    if (!usable.length) {
                        libState.category = null;
                        libLoad();
                        return;
                    }
                }

                libRenderCats();
                libRenderCrumbs(d.breadcrumb || null);
                libRenderGrid();
            })
            .catch(function () {
                libBody.innerHTML = '<p class="fx-lib-empty">Network error loading the library.</p>';
            });
    }

    function libRenderCats() {
        if (!libCats) return;
        var html = '<button type="button" class="fx-lib-chip' + (libState.category === null ? ' is-on' : '') + '" data-cat="">Folders</button>';
        libState.cats.forEach(function (c) {
            html += '<button type="button" class="fx-lib-chip' + (libState.category === c.key ? ' is-on' : '') +
                    '" data-cat="' + escAttr(c.key) + '">' + escAttr(c.label) + '</button>';
        });
        libCats.innerHTML = html;
    }

    function libRenderCrumbs(crumbs) {
        if (!libCrumbs) return;
        if (libState.category) {
            var lbl = 'All files';
            libState.cats.forEach(function (c) { if (c.key === libState.category) lbl = c.label; });
            libCrumbs.innerHTML = '<span class="fx-lib-crumb is-cur">' + escAttr(lbl) + '</span>';
            return;
        }
        var parts = (crumbs || [{ label: 'Library', path: '' }]).map(function (c, i, arr) {
            var last = (i === arr.length - 1);
            return last
                ? '<span class="fx-lib-crumb is-cur">' + escAttr(c.label) + '</span>'
                : '<button type="button" class="fx-lib-crumb" data-path="' + escAttr(c.path) + '">' + escAttr(c.label) + '</button>';
        });
        libCrumbs.innerHTML = parts.join('<span class="fx-lib-crumb-sep">/</span>');
    }

    function libRenderGrid() {
        var q = libState.query.toLowerCase();

        // Only offer files matching the field's media type.
        var files = libState.files.filter(function (f) {
            if (libState.mediaType && f.kind !== libState.mediaType) return false;
            if (libState.category && (f.cats || []).indexOf(libState.category) === -1) return false;
            if (q && f.name.toLowerCase().indexOf(q) === -1) return false;
            return true;
        });
        // Hide folders with nothing the picker can show (e.g. "Isometric PDF",
        // whose contents are all filtered out) — otherwise they're dead clicks.
        var folders = libState.category ? [] : libState.folders.filter(function (fo) {
            if (!fo.count) return false;
            return !q || fo.name.toLowerCase().indexOf(q) !== -1;
        });

        if (!folders.length && !files.length) {
            libBody.innerHTML = '<p class="fx-lib-empty">Nothing here' +
                (libState.mediaType ? ' — no ' + libState.mediaType + 's in this view.' : '.') + '</p>';
            return;
        }

        var html = '';
        if (folders.length) {
            html += '<div class="fx-lib-grid">';
            folders.forEach(function (fo) {
                html += '<div class="fx-lib-card fx-lib-folder" data-folder="' + escAttr(fo.path) + '">' +
                          '<button type="button" class="fx-lib-del" data-del="' + escAttr(fo.path) + '" title="Delete folder">&times;</button>' +
                          '<div class="fx-lib-thumb fx-lib-thumb-folder">' +
                            '<svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/></svg>' +
                          '</div>' +
                          '<div class="fx-lib-name">' + escAttr(fo.name) + '</div>' +
                          '<div class="fx-lib-meta">' + fo.count + ' item' + (fo.count === 1 ? '' : 's') + '</div>' +
                        '</div>';
            });
            html += '</div>';
        }
        if (files.length) {
            if (folders.length) html += '<div class="fx-lib-divider">Files</div>';
            html += '<div class="fx-lib-grid">';
            files.forEach(function (f) {
                var sel = (libState.selected && libState.selected.path === f.path) ? ' is-sel' : '';
                var thumb = (f.kind === 'video')
                    ? '<div class="fx-lib-thumb fx-lib-thumb-video"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><polygon points="6 4 20 12 6 20 6 4"/></svg></div>'
                    : '<div class="fx-lib-thumb"><img src="' + escAttr(f.url) + '" alt="" loading="lazy"/></div>';
                html += '<div class="fx-lib-card fx-lib-file' + sel + '" data-file="' + escAttr(f.path) +
                          '" data-kind="' + escAttr(f.kind) + '" data-name="' + escAttr(f.name) + '">' +
                          '<button type="button" class="fx-lib-del" data-del="' + escAttr(f.path) + '" title="Delete file">&times;</button>' +
                          thumb +
                          '<div class="fx-lib-name" title="' + escAttr(f.path) + '">' + escAttr(f.name) + '</div>' +
                          '<div class="fx-lib-meta">' + libFmtBytes(f.bytes) +
                            (f.uses ? ' · <span class="fx-lib-used">in use</span>' : '') +
                          '</div>' +
                        '</div>';
            });
            html += '</div>';
        }
        libBody.innerHTML = html;
    }

    function libFmtBytes(b) {
        b = Number(b) || 0;
        if (b < 1024) return b + ' B';
        if (b < 1024 * 1024) return (b / 1024).toFixed(0) + ' KB';
        return (b / 1024 / 1024).toFixed(1) + ' MB';
    }

    // ---- Library event wiring (all delegated) ----
    if (libOverlay) {
        // Open from any media field's Library button.
        document.addEventListener('click', function (e) {
            var btn = e.target.closest && e.target.closest('.fx-lib-btn');
            if (!btn) return;
            e.preventDefault();
            libOpen(btn.getAttribute('data-field-key'), btn.getAttribute('data-media-type'));
        });

        // Close: X, Cancel, backdrop click, Escape.
        document.getElementById('fx-lib-close').addEventListener('click', libClose);
        document.getElementById('fx-lib-cancel').addEventListener('click', libClose);
        libOverlay.addEventListener('click', function (e) { if (e.target === libOverlay) libClose(); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !libOverlay.hidden) libClose();
        });

        // Category chips.
        libCats.addEventListener('click', function (e) {
            var chip = e.target.closest('.fx-lib-chip');
            if (!chip) return;
            var cat = chip.getAttribute('data-cat');
            libState.category = cat === '' ? null : cat;
            libState.selected = null;
            libUseBtn.disabled = true;
            if (libState.category === null) libState.path = '';
            libLoad();
        });

        // Breadcrumb navigation.
        libCrumbs.addEventListener('click', function (e) {
            var c = e.target.closest('.fx-lib-crumb[data-path]');
            if (!c) return;
            libState.path = c.getAttribute('data-path');
            libState.selected = null;
            libUseBtn.disabled = true;
            libLoad();
        });

        // Grid: open folder / select file / delete.
        libBody.addEventListener('click', function (e) {
            var del = e.target.closest('.fx-lib-del');
            if (del) {
                e.stopPropagation();
                libDelete(del.getAttribute('data-del'));
                return;
            }
            var folder = e.target.closest('.fx-lib-folder');
            if (folder) {
                libState.path = folder.getAttribute('data-folder');
                libState.category = null;
                libState.selected = null;
                libUseBtn.disabled = true;
                libLoad();
                return;
            }
            var file = e.target.closest('.fx-lib-file');
            if (file) {
                libState.selected = {
                    path: file.getAttribute('data-file'),
                    kind: file.getAttribute('data-kind'),
                    name: file.getAttribute('data-name')
                };
                libUseBtn.disabled = false;
                libRenderGrid();
                libSetStatus('Selected: ' + libState.selected.name, '');
            }
        });
        // Double-click a file = pick it immediately.
        libBody.addEventListener('dblclick', function (e) {
            if (e.target.closest('.fx-lib-file')) libUse();
        });

        // Search.
        if (libSearch) {
            libSearch.addEventListener('input', function () {
                libState.query = libSearch.value || '';
                libRenderGrid();
            });
        }

        // New folder.
        document.getElementById('fx-lib-mkdir').addEventListener('click', function () {
            if (libState.category) { libSetStatus('Switch to Folders view to create a folder.', 'is-err'); return; }
            var name = window.prompt('New folder name:');
            if (!name) return;
            var fd = new FormData();
            fd.append('path', libState.path);
            fd.append('name', name);
            fetch('library.php?action=mkdir', {
                method: 'POST', credentials: 'same-origin',
                headers: { 'X-CSRF': window.FX.csrf }, body: fd
            }).then(function (r) { return r.json(); })
              .then(function (d) {
                  if (!d.ok) { libSetStatus(d.error || 'Could not create folder.', 'is-err'); return; }
                  libSetStatus('Folder created.', 'is-ok');
                  libLoad();
              })
              .catch(function () { libSetStatus('Network error.', 'is-err'); });
        });

        // Upload into the folder currently being browsed.
        if (libUploadIn) {
            libUploadIn.addEventListener('change', function () {
                var file = libUploadIn.files && libUploadIn.files[0];
                if (!file) return;
                if (libState.category) { libSetStatus('Switch to Folders view to choose an upload destination.', 'is-err'); libUploadIn.value = ''; return; }
                var fd = new FormData();
                fd.append('file', file);
                fd.append('kind', libState.mediaType || 'image');
                fd.append('dest', libState.path);
                libSetStatus('Uploading…', '');
                fetch('upload.php', {
                    method: 'POST', credentials: 'same-origin',
                    headers: { 'X-CSRF': window.FX.csrf }, body: fd
                }).then(function (r) { return r.json(); })
                  .then(function (d) {
                      libUploadIn.value = '';
                      if (!d.ok) { libSetStatus(d.error || 'Upload failed.', 'is-err'); return; }
                      libSetStatus('Uploaded.', 'is-ok');
                      libState.selected = { path: d.filename, kind: libState.mediaType, name: d.filename };
                      libLoad();
                  })
                  .catch(function () { libUploadIn.value = ''; libSetStatus('Network error.', 'is-err'); });
            });
        }

        // Use the selected file.
        libUseBtn.addEventListener('click', libUse);
    }

    function libDelete(path) {
        if (!path) return;
        if (!window.confirm('Delete "' + path + '"?\n\nThis removes the file from the server permanently.')) return;
        var fd = new FormData();
        fd.append('path', path);
        fetch('library.php?action=delete', {
            method: 'POST', credentials: 'same-origin',
            headers: { 'X-CSRF': window.FX.csrf }, body: fd
        }).then(function (r) { return r.json(); })
          .then(function (d) {
              if (!d.ok) {
                  // In-use refusal comes back with the exact field list.
                  if (d.in_use && d.in_use.length) {
                      window.alert(
                          d.error + '\n\nStill used by:\n\n  • ' + d.in_use.join('\n  • ') +
                          (d.in_use_total > d.in_use.length ? '\n  … and ' + (d.in_use_total - d.in_use.length) + ' more' : '') +
                          '\n\nPoint those fields at a different file first, then delete this one.'
                      );
                      libSetStatus('Delete blocked — file still in use.', 'is-err');
                  } else {
                      libSetStatus(d.error || 'Could not delete.', 'is-err');
                  }
                  return;
              }
              libSetStatus('Deleted.', 'is-ok');
              if (libState.selected && libState.selected.path === path) {
                  libState.selected = null;
                  libUseBtn.disabled = true;
              }
              libLoad();
          })
          .catch(function () { libSetStatus('Network error.', 'is-err'); });
    }

    function libUse() {
        if (!libState.selected || !libState.fieldKey) return;
        var val   = libState.selected.path;
        var track = document.querySelector('.fx-track[data-field="' + cssEsc(libState.fieldKey) + '"]');
        if (!track) { libClose(); return; }

        var previousValue = track.value;   // for Discard, if the cropper auto-opens
        track.value = val;
        var media = track.closest('.fx-media');
        var nameEl = media && media.querySelector('.fx-media-filename');
        if (nameEl) nameEl.textContent = val;

        // Swap the little preview next to the field.
        var previewEl = media && media.querySelector('.fx-media-preview');
        if (previewEl) {
            var url = '../assets/' + encodePath(val);
            previewEl.innerHTML = (libState.selected.kind === 'video')
                ? '<video src="' + escAttr(url) + '" controls muted playsinline preload="metadata"></video>'
                : '<img src="' + escAttr(url) + '" alt=""/>';
        }

        track.dispatchEvent(new Event('input', { bubbles: true }));
        markDirty();
        setStatus('Picked from library · click Save to store', 'is-ok');
        var pickedKey = libState.fieldKey;
        var pickedKind = libState.selected.kind;
        libClose();
        // Same as after an upload: offer the crop while the choice is fresh,
        // carrying the pre-pick value so Discard can undo a wrong choice.
        if (pickedKind === 'image') maybeOfferCrop(pickedKey, val, previousValue);
    }

    setStatus('Ready', '');
})();
