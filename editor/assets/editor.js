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
    var dirty      = false;
    var currentPage = pickerBtns.length ? pickerBtns[0].getAttribute('data-page') : 'home';

    // ---- Accordion open/close (delegated) ----
    document.addEventListener('click', function (e) {
        var head = e.target.closest && e.target.closest('.fx-acc-head');
        if (!head) return;
        var acc = head.closest('.fx-acc');
        if (!acc) return;
        var open = !acc.classList.contains('is-open');
        acc.classList.toggle('is-open', open);
        head.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    // ---- Page switching ----
    pickerBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var page = btn.getAttribute('data-page');
            pickerBtns.forEach(function (b) { b.classList.toggle('is-active', b === btn); });
            pageBlocks.forEach(function (blk) {
                blk.classList.toggle('is-active', blk.getAttribute('data-page') === page);
            });
            currentPage = page;
            var label = (window.FX.pageLabels && window.FX.pageLabels[page]) || page;
            previewLbl.textContent = 'Preview · ' + label + ' (draft)';
            preview.src = 'preview.php?page=' + encodeURIComponent(page) + '&ts=' + Date.now();
        });
    });

    // ---- Device toggle (Desktop / Tablet / Mobile preview width) ----
    deviceBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var device = btn.getAttribute('data-device');
            deviceBtns.forEach(function (b) { b.classList.toggle('is-active', b === btn); });
            if (frameWrap) frameWrap.setAttribute('data-device', device);
        });
    });

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
    function markDirty() { dirty = true; setStatus('Unsaved changes', 'is-dirty'); }
    function markClean() { dirty = false; }
    function setStatus(msg, cls) { status.textContent = msg; status.className = 'fx-status ' + (cls || ''); }
    function setBusy(busy) {
        [saveBtn, publishBtn, restoreBtn, refreshBtn].forEach(function (b) { if (b) b.disabled = !!busy; });
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

    publishBtn.addEventListener('click', function () {
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

    setStatus('Ready', '');
})();
