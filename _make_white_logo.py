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
    img = Image.open(SRC).convert('RGBA')
    px = img.load()
    w, h = img.size
    for y in range(h):
        for x in range(w):
            r, g, b, _ = px[x, y]
            # Brightness on 0..255 — used to decide how "ink" the pixel is.
            # Pure white ≈ 255 → alpha 0 (transparent), pure black → alpha 255.
            brightness = (r + g + b) / 3
            if brightness >= 248:
                # Treat as background — fully transparent.
                px[x, y] = (255, 255, 255, 0)
            else:
                # Anti-aliased edge — keep partial alpha proportional to darkness.
                alpha = int(round(255 * (1 - brightness / 255)))
                # Clamp very faint pixels so we don't keep JPEG noise.
                if alpha < 8:
                    px[x, y] = (255, 255, 255, 0)
                else:
                    px[x, y] = (255, 255, 255, alpha)
    img.save(DST, 'PNG', optimize=True)
    print(f'wrote {DST.relative_to(SRC.parent.parent)} ({DST.stat().st_size} bytes)')


if __name__ == '__main__':
    convert()
