#!/usr/bin/env python3
"""Rebuild services-v4 specialist WebP as opaque RGB (no alpha).

Original owner JPEGs with studio background were not committed (only cutouts in PR).
This script flattens any residual alpha onto the home-style soft backdrop and applies
light framing crops so hero cards can use object-fit:cover / center top.
"""
from __future__ import annotations

from pathlib import Path

from PIL import Image

REPO = Path(__file__).resolve().parent.parent
OUT_DIR = REPO / "template/images/services-v4"
BACKDROP = (234, 242, 252)  # #eaf2fc, aligns with .lx-photo-v4-expert-media

# left, top, right, bottom in source pixels (800×1200 baseline)
CROPS = {
    "specialist-copyright": (90, 40, 710, 1200),
    "specialist-opposition": (120, 80, 780, 1200),
    "specialist-registraciya": (70, 220, 730, 1200),
    "specialist-certificate": (0, 60, 800, 1200),
    "specialist-prolongation": (40, 40, 760, 1200),
}


def flatten_rgb(path: Path) -> Image.Image:
    im = Image.open(path)
    if im.mode == "RGBA":
        bg = Image.new("RGBA", im.size, BACKDROP + (255,))
        im = Image.alpha_composite(bg, im).convert("RGB")
    else:
        im = im.convert("RGB")
    box = CROPS.get(path.stem)
    if box:
        im = im.crop(box)
    im = im.resize((800, 1200), Image.Resampling.LANCZOS)
    return im


def main() -> None:
    for name in CROPS:
        src = OUT_DIR / f"{name}.webp"
        if not src.is_file():
            raise SystemExit(f"missing {src}")
        out = flatten_rgb(src)
        out.save(src, "WEBP", quality=88, method=6)
        check = Image.open(src)
        if check.mode != "RGB":
            raise SystemExit(f"{src} must be RGB, got {check.mode}")
        print(f"OK {src.name} {check.size} {check.mode}")


if __name__ == "__main__":
    main()