"""
Convert assets/Logo.jpeg (dark text on white background) into a
clean white-on-transparent PNG so it reads well on the new brand-
blue nav bar.

Strategy:
  - White-ish pixels → fully transparent
  - Coloured (dark) pixels → white, with alpha proportional to how
    far from white they are (preserves anti-aliasing on letterforms)

Output: assets/Logo-white.png
"""

from PIL import Image
from pathlib import Path

SRC = Path('/Users/ericcheah/Furutec/Furutec website 3/assets/Logo.jpeg')
DST = Path('/Users/ericcheah/Furutec/Furutec website 3/assets/Logo-white.png')


def convert() -> None:
    """
    Convert to PLAIN white:
      - Any pixel that's clearly part of the logo (brightness < INK_CUT)
        becomes fully opaque white #FFFFFFFF.
      - Any pixel that's clearly background (brightness >= BG_CUT)
        becomes fully transparent.
      - Pixels between those two thresholds keep a partial alpha so the
        anti-aliased letter edges stay smooth.
    Result: solid white logo, no grey/translucent tint in the body.
    """
    INK_CUT = 180   # below this → solid white
    BG_CUT  = 235   # above this → transparent
    img = Image.open(SRC).convert('RGBA')
    px = img.load()
    w, h = img.size
    span = BG_CUT - INK_CUT
    for y in range(h):
        for x in range(w):
            r, g, b, _ = px[x, y]
            brightness = (r + g + b) / 3
            if brightness < INK_CUT:
                # Inside the letterforms — pure opaque white.
                px[x, y] = (255, 255, 255, 255)
            elif brightness >= BG_CUT:
                # Background — fully transparent.
                px[x, y] = (255, 255, 255, 0)
            else:
                # Anti-aliased edge — smooth fade from solid to transparent.
                t = (brightness - INK_CUT) / span      # 0 = ink edge, 1 = bg edge
                alpha = int(round(255 * (1 - t)))
                px[x, y] = (255, 255, 255, alpha)
    img.save(DST, 'PNG', optimize=True)
    print(f'wrote {DST.relative_to(SRC.parent.parent)} ({DST.stat().st_size} bytes)')


if __name__ == '__main__':
    convert()
