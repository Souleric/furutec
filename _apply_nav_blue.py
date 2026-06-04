"""
Re-theme the sticky top nav across every page:
  - Bar background → brand blue (#2E3192)
  - Nav links + phone + burger → white
  - 'Get A Quote' button → white pill with brand-blue text
    (replaces the old #141414 black button); hover inverts to outline
  - Logo gets a soft white tile so the JPEG (white-bg) sits cleanly on blue
Idempotent — re-running is a no-op (versioned marker).
"""

from pathlib import Path

ROOT = Path('/Users/ericcheah/Furutec/Furutec website 3')
PAGES = [
    'index.html',
    'product.html',
    'portfolio.html',
    'portfolio-track-record.html',
    'quote.html',
]
MARKER = '<!-- nav-blue v4 -->'

CSS_BLOCK = '''<!-- nav-blue v4 -->
<style id="nav-blue-v4">
/* ============================================================
   Nav bar — brand blue background, white type, white CTA pill
   v4: logo swapped to assets/Logo-white.png (true white-on-
   transparent PNG), so the white tile that v3 wrapped around
   the JPEG is no longer needed.
   ============================================================ */
header {
  background: #2E3192 !important;
  border-bottom: 1px solid rgba(255,255,255,0.10) !important;
  box-shadow: 0 2px 12px rgba(15,17,75,0.10) !important;
}

/* --- Nav links: default + hover + active --- */
#ft-nav > a,
#ft-nav .ft-nav-dropdown > a {
  color: rgba(255,255,255,0.82) !important;
  transition: color 180ms ease !important;
}
#ft-nav > a:hover,
#ft-nav .ft-nav-dropdown > a:hover,
#ft-nav .ft-nav-dropdown:hover > a {
  color: #FFFFFF !important;
}
/* Existing "current page" underline used Furutec blue — recolour to white */
#ft-nav > a > span[style*="background:#2E3192"] {
  background: #FFFFFF !important;
}
/* And the current-page text itself */
#ft-nav > a[style*="color:#2E3192"] {
  color: #FFFFFF !important;
}

/* Dropdown chevron strokes (currentColor on white now reads as muted on blue) */
#ft-nav .ft-nav-dropdown > a svg { stroke: currentColor !important; }

/* --- Phone number block --- */
#ft-phone { color: #FFFFFF !important; }
#ft-phone svg { stroke: #FFFFFF !important; }

/* --- Burger (mobile) goes white --- */
#ft-burger span,
#ft-burger span::before,
#ft-burger span::after {
  background: #FFFFFF !important;
}

/* --- Get A Quote — white pill on blue (was solid black) --- */
#ft-quote-btn {
  background: #FFFFFF !important;
  color: #2E3192 !important;
  border: 1.5px solid #FFFFFF !important;
  transition: background .25s ease, color .25s ease, border-color .25s ease !important;
  box-shadow: 0 2px 8px rgba(0,0,0,0.10);
}
#ft-quote-btn:hover {
  background: transparent !important;
  color: #FFFFFF !important;
  border-color: #FFFFFF !important;
  box-shadow: none;
}
/* Arrow tracks the button's text colour automatically via currentColor */
#ft-quote-btn .btn-arrow > span svg { stroke: currentColor !important; }

/* --- Logo: now a true white PNG, so no tile needed --- */
.ft-logo-img { background: transparent; padding: 0; border-radius: 0; box-shadow: none; }

/* --- Mobile nav drawer panel stays white (readable list) --- */
#ft-mobile-nav { background: #FFFFFF; border-top: 1px solid rgba(255,255,255,0.10); }

/* --- Dropdown menu panels stay white (already are) but bump shadow contrast --- */
.ft-nav-menu { box-shadow: 0 12px 36px rgba(15,17,75,0.22) !important; }
</style>
'''


import re


def strip_legacy(text: str) -> str:
    # Drop any earlier nav-blue blocks so v4 doesn't stack on top of them.
    for ver in ('v1', 'v2', 'v3'):
        pattern = re.compile(
            rf'<!--\s*nav-blue {ver}\s*-->\s*<style id="nav-blue-{ver}">.*?</style>\s*',
            re.DOTALL,
        )
        text = pattern.sub('', text)
    return text


def apply_to(path: Path) -> str:
    text = path.read_text(encoding='utf-8')
    text = strip_legacy(text)
    if MARKER in text:
        path.write_text(text, encoding='utf-8')
        return 'skip (already applied)'
    if '</head>' not in text:
        return 'skip (no </head> found)'
    text = text.replace('</head>', f'{CSS_BLOCK}\n</head>', 1)
    path.write_text(text, encoding='utf-8')
    return 'updated'


if __name__ == '__main__':
    for page in PAGES:
        p = ROOT / page
        if not p.exists():
            print(f'  {page}: missing')
            continue
        print(f'  {page}: {apply_to(p)}')
