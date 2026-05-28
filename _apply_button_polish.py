"""Apply UI Kit button polish to all main pages.

v2: bumps the arrow to 28px wide/tall and switches from text "→" to a
custom SVG with a half-length stem (M9 12h4) + standard head.

The script is idempotent — if it sees the v1 marker it will remove the v1
block before injecting v2. Re-runnable any number of times.
"""

import re
from pathlib import Path

ROOT = Path("/Users/ericcheah/Furutec/Furutec website 3")
PAGES = ["index.html", "product.html", "portfolio.html",
         "portfolio-track-record.html", "quote.html"]

CSS_MARKER_V1 = "/* === furutec-btn-polish v1 === */"
CSS_MARKER_V2 = "/* === furutec-btn-polish v2 === */"

CSS_BLOCK = f"""<style>
{CSS_MARKER_V2}
.btn-dark, .btn-outline, .btn-blue, .btn-submit-now,
.btn-white-filled, .btn-white-outline,
.ft-hero-btn-primary, .ft-hero-btn-secondary,
.ps-btn-primary, .ps-btn-secondary {{
  border-radius: 8px !important;
}}

.btn-arrow {{
  position: relative; display: inline-block;
  width: 28px; height: 28px; overflow: hidden;
  line-height: 1;
  vertical-align: middle;
  flex-shrink: 0;
}}
.btn-arrow > span {{
  position: absolute; inset: 0;
  display: inline-flex; align-items: center; justify-content: center;
  transition: transform .35s cubic-bezier(.22,1,.36,1);
}}
.btn-arrow > span svg {{
  width: 24px; height: 24px;
}}
.btn-arrow > .a {{ transform: translateX(0); }}
.btn-arrow > .b {{ transform: translateX(-110%); }}

.btn-dark:hover  .btn-arrow > .a,
.btn-outline:hover  .btn-arrow > .a,
.btn-blue:hover  .btn-arrow > .a,
.btn-submit-now:hover  .btn-arrow > .a,
.btn-white-filled:hover  .btn-arrow > .a,
.btn-white-outline:hover  .btn-arrow > .a,
.ft-hero-btn-primary:hover  .btn-arrow > .a,
.ft-hero-btn-secondary:hover  .btn-arrow > .a,
.ps-btn-primary:hover  .btn-arrow > .a,
.ps-btn-secondary:hover  .btn-arrow > .a {{
  transform: translateX(110%);
}}
.btn-dark:hover  .btn-arrow > .b,
.btn-outline:hover  .btn-arrow > .b,
.btn-blue:hover  .btn-arrow > .b,
.btn-submit-now:hover  .btn-arrow > .b,
.btn-white-filled:hover  .btn-arrow > .b,
.btn-white-outline:hover  .btn-arrow > .b,
.ft-hero-btn-primary:hover  .btn-arrow > .b,
.ft-hero-btn-secondary:hover  .btn-arrow > .b,
.ps-btn-primary:hover  .btn-arrow > .b,
.ps-btn-secondary:hover  .btn-arrow > .b {{
  transform: translateX(0);
}}
</style>
"""

# Old (v1) text arrow markup → new SVG arrow markup
ARROW_HTML_V1_RE = re.compile(
    r'<span class="btn-arrow">'
    r'<span class="a">→</span>'
    r'<span class="b">→</span>'
    r'</span>'
)
ARROW_SVG_HTML = (
    '<span class="btn-arrow">'
    '<span class="a"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" '
    'stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">'
    '<path d="M9 12h4"/><path d="M13 6l6 6-6 6"/></svg></span>'
    '<span class="b"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" '
    'stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">'
    '<path d="M9 12h4"/><path d="M13 6l6 6-6 6"/></svg></span>'
    '</span>'
)

# Patterns that may still exist for never-polished buttons (in case a new
# button is added later with the old text-arrow or SVG-arrow markup).
LEGACY_SVG_RE = re.compile(
    r'<svg[^>]*viewBox="0 0 24 24"[^>]*>'
    r'<path d="M5 12h14"\s*/>'
    r'<path d="M13 6l6 6-6 6"\s*/>'
    r'</svg>'
)
LEGACY_TEXT_SPAN_RE = re.compile(r'<span style="font-size:14px;line-height:1;">→</span>')
LEGACY_NBSP_RE = re.compile(r'&nbsp;→')
INLINE_RADIUS_RE = re.compile(r'border-radius:\s*0\s*;')

# Match the entire v1 <style> block so we can swap it for v2 on re-run.
V1_STYLE_RE = re.compile(
    r'<style>\s*' + re.escape(CSS_MARKER_V1) + r'.*?</style>\s*',
    re.DOTALL,
)

for page in PAGES:
    p = ROOT / page
    html = p.read_text()
    changed = False

    # 1. If v1 block is present, remove it (we'll inject v2)
    new_html, removed = V1_STYLE_RE.subn("", html)
    if removed:
        html = new_html
        changed = True

    # 2. Inject v2 CSS if not already there
    if CSS_MARKER_V2 not in html:
        html = html.replace("</head>", CSS_BLOCK + "</head>", 1)
        changed = True

    # 3. Upgrade any v1 text-arrow markup → v2 SVG arrow
    new_html, upgrade_count = ARROW_HTML_V1_RE.subn(ARROW_SVG_HTML, html)
    if upgrade_count:
        html = new_html
        changed = True

    # 4. Catch legacy patterns introduced after the last polish run
    new_html, svg_count = LEGACY_SVG_RE.subn(ARROW_SVG_HTML, html)
    if svg_count:
        html = new_html
        changed = True

    new_html, text_count = LEGACY_TEXT_SPAN_RE.subn(ARROW_SVG_HTML, html)
    if text_count:
        html = new_html
        changed = True

    new_html, nbsp_count = LEGACY_NBSP_RE.subn(" " + ARROW_SVG_HTML, html)
    if nbsp_count:
        html = new_html
        changed = True

    # 5. Flip inline border-radius:0 → 8px
    new_html, rad_count = INLINE_RADIUS_RE.subn("border-radius:8px;", html)
    if rad_count:
        html = new_html
        changed = True

    if changed:
        p.write_text(html)
        print(f"{page}: v1-removed={bool(removed)}, "
              f"upgraded={upgrade_count}, "
              f"legacy svg={svg_count}, text={text_count}, nbsp={nbsp_count}, "
              f"radii={rad_count}")
    else:
        print(f"{page}: no changes needed.")
