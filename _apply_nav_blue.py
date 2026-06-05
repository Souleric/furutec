"""
Top-nav polish script.

v6: ROLL BACK to the white nav with the original Furutec blue logo.
The brand-blue background, white text, and white-logo tile that v3-v5
applied are gone — those overrides used `!important`, so we just strip
the legacy block from each page and inject a minimal v6 block that
keeps the orange "Get A Quote" CTA (the only nav element worth
preserving on the white background).

Idempotent: re-running strips v1-v5 plus any earlier v6 block before
re-injecting the current v6.
"""

import re
from pathlib import Path

ROOT = Path('/Users/ericcheah/Furutec/Furutec website 3')
PAGES = [
    'index.html',
    'product.html',
    'portfolio.html',
    'portfolio-track-record.html',
    'quote.html',
]
MARKER = '<!-- nav-blue v6 -->'

CSS_BLOCK = '''<!-- nav-blue v6 -->
<style id="nav-blue-v6">
/* ============================================================
   White top nav — restores the default white background, dark
   text, blue Furutec logo, and only KEEPS the orange Get A Quote
   CTA as the persistent override.  v1-v5 brand-blue treatment is
   stripped by the polish script before this block is injected.
   ============================================================ */

/* Get A Quote — brand-orange pill (kept consistent with the
   orange CTAs used elsewhere on the homepage). */
#ft-quote-btn {
  background: #F58220 !important;
  color: #FFFFFF !important;
  border: 1.5px solid #F58220 !important;
  transition: background .25s ease, color .25s ease, border-color .25s ease !important;
  box-shadow: 0 4px 14px rgba(245,130,32,0.30);
}
#ft-quote-btn:hover {
  background: transparent !important;
  color: #F58220 !important;
  border-color: #F58220 !important;
  box-shadow: none;
}
#ft-quote-btn .btn-arrow > span svg { stroke: currentColor !important; }
</style>
'''


def strip_legacy(text: str) -> str:
    """Drop any earlier nav-blue v1..v6 blocks so this run starts clean."""
    for ver in ('v1', 'v2', 'v3', 'v4', 'v5', 'v6'):
        pattern = re.compile(
            rf'<!--\s*nav-blue {ver}\s*-->\s*<style id="nav-blue-{ver}">.*?</style>\s*',
            re.DOTALL,
        )
        text = pattern.sub('', text)
    return text


def revert_logo(text: str) -> str:
    """Switch the header logo image back from the white PNG to the
    original blue JPEG. Only touches the header (`ft-logo-img`), not
    the footer (which uses the white logo on the dark gradient)."""
    return text.replace(
        '<img class="ft-logo-img" src="assets/Logo-white.png"',
        '<img class="ft-logo-img" src="assets/Logo.jpeg"',
    )


def apply_to(path: Path) -> str:
    text = path.read_text(encoding='utf-8')
    new = strip_legacy(text)
    new = revert_logo(new)
    if '</head>' not in new:
        return 'skip (no </head> found)'
    new = new.replace('</head>', f'{CSS_BLOCK}\n</head>', 1)
    if new == text:
        return 'no-op'
    path.write_text(new, encoding='utf-8')
    return 'updated'


if __name__ == '__main__':
    for page in PAGES:
        p = ROOT / page
        if not p.exists():
            print(f'  {page}: missing')
            continue
        print(f'  {page}: {apply_to(p)}')
