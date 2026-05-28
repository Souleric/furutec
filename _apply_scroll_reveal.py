"""Inject scroll-reveal auto-tagging + IntersectionObserver into all main pages.

v2: suppresses the initial transition while applying the `.reveal` class,
so elements don't briefly fade-out before fading-in. Re-runnable — if v1
is detected the block is replaced with v2.

Every page already has the `.reveal` CSS defined (now normalised to 800 ms).
"""

import re
from pathlib import Path

ROOT = Path("/Users/ericcheah/Furutec/Furutec website 3")
PAGES = ["index.html", "product.html", "portfolio.html",
         "portfolio-track-record.html", "quote.html"]

MARKER_V1 = "furutec-scroll-reveal v1"
MARKER_V2 = "furutec-scroll-reveal v2"
MARKER_V3 = "furutec-scroll-reveal v3"
MARKER_V4 = "furutec-scroll-reveal v4"

# v4: forces the reveal transition INLINE so it overrides any element's
# own `transition: ...` rule (e.g. .prod-card has `transition: background
# 200ms ease` which was wiping out the reveal's opacity/transform timing,
# causing cards to pop in instead of fade). After the animation finishes
# the inline transition is cleared so hover transitions resume normally.

SCRIPT_BLOCK = f"""<script>
/* === {MARKER_V4} === */
(function () {{
  var SELECTORS = [
    /* === Cards (replicate Products-page behaviour everywhere) === */
    '.cp-card-feature', '.cp-card-mini',
    '.prod-card',
    '.ind-card',
    '.cert-card',
    '.cert-stand',

    /* === Product page section blocks === */
    '.ps-info', '.ps-spec-card', '.ps-video-card', '.ps-diagram-band',
    '.ps-tag',
    '.ps-features-list > li',

    /* === Product Overview interactive diagram (homepage) === */
    '.po-figure', '.po-detail', '.po-grid > div',

    /* === Homepage section columns (Company Profile + R&D split) === */
    '.cp-grid > div',

    /* === Homepage stats === */
    '.ft-stat-num', '.ft-stat-label',

    /* === Homepage Regional Projects tabs === */
    '.rp-tab',

    /* === Homepage world map === */
    '#world-map-dots',

    /* === Quote / contact === */
    '.gi-card', '.gi-field', '.gi-loc', '.gi-direct-cell',

    /* === Page-level + section-level major titles === */
    'section h1',
    'section h2',

    /* === Eyebrows everywhere === */
    '.eyebrow',

    /* === Explicit opt-in (add data-reveal attribute to any element) === */
    '[data-reveal]',

    /* === Footer (subtle) === */
    'footer h4', 'footer ul li'
  ];

  // The transition we want for the reveal — forced inline so it wins
  // against any element's existing `transition: ...` rule.
  var REVEAL_TRANSITION =
    'opacity 800ms cubic-bezier(.22,1,.36,1), '
    + 'transform 800ms cubic-bezier(.22,1,.36,1)';

  var els = document.querySelectorAll(SELECTORS.join(','));

  // Suppress transition while applying the initial invisible state so the
  // elements snap to opacity:0 instantly (no visible flash from 1 -> 0 -> 1).
  els.forEach(function (el) {{
    if (!el.classList) return;
    el.style.transition = 'none';
    el.classList.add('reveal');
  }});
  if (els.length) {{ void els[0].offsetWidth; }}

  if (!('IntersectionObserver' in window)) {{
    els.forEach(function (el) {{
      el.style.transition = '';
      el.classList.add('is-visible');
    }});
    return;
  }}

  requestAnimationFrame(function () {{
    var io = new IntersectionObserver(function (entries) {{
      entries.forEach(function (e) {{
        if (!e.isIntersecting) return;
        var el = e.target;
        var parent = el.parentElement;
        var siblings = parent ? Array.prototype.filter.call(
          parent.children,
          function (c) {{ return c.classList && c.classList.contains('reveal'); }}
        ) : [el];
        var idx = siblings.indexOf(el);
        var delay = Math.min(idx * 80, 640);

        // Force the reveal transition inline so it overrides any rule like
        // `.prod-card {{ transition: background 200ms ease; }}`.
        el.style.transition = REVEAL_TRANSITION;
        el.style.transitionDelay = delay + 'ms';
        el.classList.add('is-visible');

        // Once the animation ends, restore the element's original transition
        // CSS so its hover effects work as before.
        var clearAt = 800 + delay + 80;
        setTimeout(function () {{
          el.style.transition = '';
          el.style.transitionDelay = '';
        }}, clearAt);

        io.unobserve(el);
      }});
    }}, {{ threshold: 0.15, rootMargin: '0px 0px -8% 0px' }});

    els.forEach(function (el) {{ io.observe(el); }});
  }});
}})();
</script>
"""

# Match any previous version of the script so re-runs replace cleanly
OLD_SCRIPT_RE = re.compile(
    r'<script>\s*/\*\s*===\s*furutec-scroll-reveal v[1234]\s*===\s*\*/.*?</script>\s*',
    re.DOTALL,
)

for name in PAGES:
    p = ROOT / name
    html = p.read_text()
    changed = False

    new_html, removed = OLD_SCRIPT_RE.subn("", html)
    if removed:
        html = new_html
        changed = True

    if MARKER_V4 not in html:
        new_html = html.replace("</body>", SCRIPT_BLOCK + "\n</body>", 1)
        if new_html != html:
            html = new_html
            changed = True

    if changed:
        p.write_text(html)
        print(f"{name}: replaced old block(s)={removed}, v4 injected.")
    else:
        print(f"{name}: already v4, no change.")
