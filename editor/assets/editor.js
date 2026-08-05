// Furutec Editor — client-side glue.
(function () {
    'use strict';

    var tabs   = document.getElementById('fx-tabs');
    var panels = document.querySelectorAll('.fx-panel');
    var status = document.getElementById('fx-status');
    var preview = document.getElementById('fx-preview');
    var saveBtn    = document.getElementById('fx-save');
    var publishBtn = document.getElementById('fx-publish');
    var restoreBtn = document.getElementById('fx-restore');
    var refreshBtn = document.getElementById('fx-refresh-preview');
    var dirty = false;

    // ---- Section tab switching ----
    tabs.addEventListener('click', function (e) {
        var btn = e.target.closest('.fx-tab');
        if (!btn) return;
        var target = btn.getAttribute('data-tab');
        Array.prototype.forEach.call(tabs.querySelectorAll('.fx-tab'), function (t) {
            t.classList.toggle('is-active', t === btn);
        });
        Array.prototype.forEach.call(panels, function (p) {
            p.classList.toggle('is-active', p.getAttribute('data-panel') === target);
        });
    });

    // ---- Dirty tracking ----
    document.addEventListener('input', function (e) {
        if (e.target && e.target.classList && e.target.classList.contains('fx-track')) {
            markDirty();
        }
    });
    window.addEventListener('beforeunload', function (e) {
        if (dirty) { e.preventDefault(); e.returnValue = ''; }
    });

    function markDirty() {
        dirty = true;
        setStatus('Unsaved changes', 'is-dirty');
    }
    function markClean() {
        dirty = false;
    }
    function setStatus(msg, cls) {
        status.textContent = msg;
        status.className = 'fx-status ' + (cls || '');
    }
    function setBusy(busy) {
        [saveBtn, publishBtn, restoreBtn, refreshBtn].forEach(function (b) { if (b) b.disabled = !!busy; });
    }

    // ---- Collect current field values into a nested object ----
    function collectContent() {
        var out = {};
        Array.prototype.forEach.call(document.querySelectorAll('.fx-track'), function (el) {
            var key = el.getAttribute('data-field'); // e.g. "hero.headline_1"
            if (!key) return;
            var parts = key.split('.');
            var sect = parts[0], name = parts[1];
            if (!out[sect]) out[sect] = {};
            out[sect][name] = el.value;
        });
        return out;
    }

    // ---- Post JSON helper ----
    function post(url, body, opts) {
        opts = opts || {};
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
              catch (_) { return { ok: false, error: 'Bad response from server: ' + txt.slice(0, 200) }; }
          });
    }

    // ---- Save draft ----
    saveBtn.addEventListener('click', function () {
        setBusy(true);
        setStatus('Saving…', 'is-busy');
        var content = collectContent();
        post('save.php', { content: content }).then(function (r) {
            setBusy(false);
            if (!r.ok) { setStatus(r.error || 'Save failed', 'is-err'); return; }
            markClean();
            setStatus('Draft saved · preview updated', 'is-ok');
            refreshPreview();
        }).catch(function (e) {
            setBusy(false);
            setStatus('Save error: ' + (e && e.message ? e.message : 'unknown'), 'is-err');
        });
    });

    // ---- Publish ----
    publishBtn.addEventListener('click', function () {
        if (dirty && !confirm('You have unsaved changes. Save them before publishing?')) return;
        if (dirty) {
            saveBtn.click();
            setTimeout(publish, 1200);
        } else {
            publish();
        }
    });
    function publish() {
        if (!confirm('Publish the current draft to the live homepage?\n\nThe current live page will be backed up automatically and can be restored in one click.')) return;
        setBusy(true);
        setStatus('Publishing…', 'is-busy');
        post('publish.php', {}).then(function (r) {
            setBusy(false);
            if (!r.ok) { setStatus(r.error || 'Publish failed', 'is-err'); return; }
            setStatus('Published live · backup saved', 'is-ok');
            refreshPreview();
        });
    }

    // ---- Restore ----
    restoreBtn.addEventListener('click', function () {
        if (!confirm('Restore the live homepage to the previous published version?\n\nYour current draft will not be lost — only the live page reverts.')) return;
        setBusy(true);
        setStatus('Restoring…', 'is-busy');
        post('restore.php', {}).then(function (r) {
            setBusy(false);
            if (!r.ok) { setStatus(r.error || 'Restore failed', 'is-err'); return; }
            setStatus('Live page restored from backup', 'is-ok');
        });
    });

    // ---- Refresh preview ----
    refreshBtn.addEventListener('click', function () { refreshPreview(); });
    function refreshPreview() {
        preview.src = 'preview.php?ts=' + Date.now();
    }

    // ---- File upload for image/video fields ----
    document.addEventListener('change', function (e) {
        var input = e.target;
        if (!input.classList || !input.classList.contains('fx-file')) return;
        var file = input.files && input.files[0];
        if (!file) return;
        var fieldKey = input.getAttribute('data-field-key');
        var kind     = input.getAttribute('data-media-type');

        // Basic client-side guard.
        var maxMb = (kind === 'video') ? 30 : 5;
        if (file.size > maxMb * 1024 * 1024) {
            setStatus('File too large (max ' + maxMb + ' MB)', 'is-err');
            input.value = '';
            return;
        }

        setBusy(true);
        setStatus('Uploading ' + file.name + '…', 'is-busy');

        var form = new FormData();
        form.append('file', file);
        form.append('kind', kind);
        form.append('field_key', fieldKey);
        fetch('upload.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-CSRF': window.FX.csrf, 'X-Requested-With': 'fetch' },
            body: form
        }).then(function (r) { return r.text(); })
          .then(function (txt) {
              try { return JSON.parse(txt); }
              catch (_) { return { ok: false, error: 'Bad upload response' }; }
          })
          .then(function (r) {
              setBusy(false);
              input.value = '';
              if (!r.ok) { setStatus(r.error || 'Upload failed', 'is-err'); return; }
              // Update the hidden input + preview.
              var wrap = document.querySelector('[data-field-key="' + cssEsc(fieldKey) + '"]');
              if (!wrap) return;
              var hidden = wrap.querySelector('input.fx-track[type="hidden"]');
              if (hidden) hidden.value = r.filename;
              var fname = wrap.querySelector('.fx-media-filename');
              if (fname) fname.textContent = r.filename;
              var preview = wrap.querySelector('.fx-media-preview');
              if (preview) {
                  var url = '../assets/' + encodePath(r.filename) + '?t=' + Date.now();
                  if (kind === 'video') {
                      preview.innerHTML = '<video src="' + escAttr(url) + '" controls muted playsinline preload="metadata"></video>';
                  } else {
                      preview.innerHTML = '<img src="' + escAttr(url) + '" alt=""/>';
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
    function encodePath(p) {
        return String(p).split('/').map(encodeURIComponent).join('/');
    }

    setStatus('Ready', '');
})();
