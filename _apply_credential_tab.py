"""
Replace the "Portfolio" dropdown in the header (and mobile nav) with two
direct top-level entries:
  - Credential  → portfolio.html
  - Portfolio   → portfolio-track-record.html

Idempotent — uses unique anchor strings ("Certificate Credential" in the
old dropdown menu) to detect whether the swap has already been applied.
"""

import re
from pathlib import Path

ROOT = Path('/Users/ericcheah/Furutec/Furutec website 3')

# Maps each page to which top-level tab should be the active one in the
# new nav. None = no active highlight on Credential/Portfolio tabs.
ACTIVE_TAB = {
    'index.html':                 None,
    'product.html':               None,
    'portfolio.html':             'credential',   # Certs page
    'portfolio-track-record.html': 'portfolio',   # Track record page
    'quote.html':                 None,
}


def desktop_links(active: str | None) -> str:
    """Return the HTML for the two replacement nav links (Credential, Portfolio)."""

    def link(label: str, href: str, is_active: bool) -> str:
        color = '#2E3192' if is_active else '#4A4A52'
        underline = (
            '\n        <span style="position:absolute;top:0;left:50%;'
            'transform:translateX(-50%);width:24px;height:3px;background:#2E3192;"></span>'
            if is_active else ''
        )
        return (
            f'      <a href="{href}" style="font-family:var(--font-sans);'
            f'font-size:13px;font-weight:500;text-decoration:none;padding:22px 0;'
            f'transition:color 200ms ease;color:{color};position:relative;">\n'
            f'        {label}{underline}\n'
            f'      </a>'
        )

    cred  = link('Credential', 'portfolio.html',              active == 'credential')
    folio = link('Portfolio',  'portfolio-track-record.html', active == 'portfolio')
    return cred + '\n' + folio


# Matches the entire old Portfolio dropdown block — from the opening
# <div class="ft-nav-dropdown"> wrapper through to the closing </div>
# that follows the inner ft-nav-menu. The "Certificate Credential" string
# inside is the unique marker so we don't accidentally match the Our
# Products dropdown.
OLD_DESKTOP_RE = re.compile(
    r'      <div class="ft-nav-dropdown">\s*'
    r'<a href="portfolio\.html"[^>]*>\s*Portfolio[\s\S]*?'
    r'Certificate Credential[\s\S]*?</div>\s*</div>',
    re.MULTILINE,
)


def patch_desktop(text: str, active: str | None) -> tuple[str, int]:
    return OLD_DESKTOP_RE.subn(desktop_links(active), text, count=1)


# Mobile nav surgery.  Old line (varies):
#   <a href="portfolio.html">Portfolio</a>
#   <a href="portfolio.html" class="active">Portfolio</a>
def patch_mobile(text: str, active: str | None) -> tuple[str, int]:
    # Detect "Portfolio" link in #ft-mobile-nav
    pat = re.compile(
        r'<a href="portfolio\.html"(?:\s+class="active")?>Portfolio</a>',
    )
    match = pat.search(text)
    if not match:
        return text, 0

    cred_cls   = ' class="active"' if active == 'credential' else ''
    folio_cls  = ' class="active"' if active == 'portfolio'  else ''
    replacement = (
        f'<a href="portfolio.html"{cred_cls}>Credential</a>\n'
        f'    <a href="portfolio-track-record.html"{folio_cls}>Portfolio</a>'
    )
    new_text = text[:match.start()] + replacement + text[match.end():]
    return new_text, 1


def apply(page: str, active: str | None) -> str:
    path = ROOT / page
    text = path.read_text(encoding='utf-8')
    if 'Certificate Credential' not in text:
        return 'skip (already applied or marker missing)'

    text, n1 = patch_desktop(text, active)
    text, n2 = patch_mobile(text, active)

    if n1 == 0:
        return 'fail (desktop dropdown not found)'
    path.write_text(text, encoding='utf-8')
    return f'updated  (desktop={n1}, mobile={n2})'


if __name__ == '__main__':
    for page, active in ACTIVE_TAB.items():
        p = ROOT / page
        if not p.exists():
            print(f'  {page}: missing')
            continue
        print(f'  {page}: {apply(page, active)}')
