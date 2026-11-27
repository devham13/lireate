#!/usr/bin/env python3
"""Convert owner JPEG originals (with background) to hero WebP for services-v4."""

from __future__ import annotations

import io
from pathlib import Path

from PIL import Image

REPO = Path(__file__).resolve().parent.parent
ORIG = REPO / "template/images/services-v4/originals"
OUT = REPO / "template/images/services-v4"
TARGET = (800, 1200)
TARGET_RATIO = TARGET[0] / TARGET[1]
MAX_BYTES = 180 * 1024
QUALITY_START = 82

# top_frac, height_frac of source image (face kept in upper area)
CROP = {
    "specialist-copyright": (0.0, 0.46),
    "specialist-opposition": (0.02, 0.44),
    "specialist-registraciya": (0.0, 0.52),
}


def crop_portrait(im: Image.Image, top_frac: float, height_frac: float) -> Image.Image:
    w, h = im.size
    crop_h = max(1, int(h * height_frac))
    crop_w = int(crop_h * TARGET_RATIO)
    if crop_w > w:
        crop_w = w
        crop_h = int(crop_w / TARGET_RATIO)
    x0 = (w - crop_w) // 2
    y0 = min(max(0, int(h * top_frac)), h - crop_h)
    return im.crop((x0, y0, x0 + crop_w, y0 + crop_h))


def save_webp(im: Image.Image, dest: Path) -> int:
    im = im.convert("RGB").resize(TARGET, Image.Resampling.LANCZOS)
    quality = QUALITY_START
    while quality >= 68:
        buf = io.BytesIO()
        im.save(buf, "WEBP", quality=quality, method=6)
        size = buf.tell()
        if size <= MAX_BYTES:
            dest.write_bytes(buf.getvalue())
            return size
        quality -= 4
    dest.write_bytes(buf.getvalue())
    return buf.tell()


def main() -> None:
    for stem, (top, height) in CROP.items():
        src = ORIG / f"{stem}.jpg"
        if not src.is_file():
            raise SystemExit(f"missing {src}")
        im = Image.open(src)
        out_im = crop_portrait(im, top, height)
        dest = OUT / f"{stem}.webp"
        nbytes = save_webp(out_im, dest)
        check = Image.open(dest)
        print(f"OK {dest.name} {check.size} {check.mode} {nbytes // 1024} KiB q~{QUALITY_START}")


if __name__ == "__main__":
    main()