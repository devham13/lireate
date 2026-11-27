#!/usr/bin/env python3
"""One-off: certificate hero WebP from owner JPEG (dark background, full figure)."""

from __future__ import annotations

import io
import sys
from pathlib import Path

from PIL import Image

REPO = Path(__file__).resolve().parent.parent
SRC = REPO / "originals/certificate-original.jpg"
DEST = REPO / "template/images/services-v4/specialist-certificate.webp"
TARGET = (800, 1200)
TARGET_RATIO = TARGET[0] / TARGET[1]
MAX_BYTES = 180 * 1024


def cover_crop(im: Image.Image, top_frac: float, height_frac: float) -> Image.Image:
    w, h = im.size
    crop_h = max(1, min(h, int(h * height_frac)))
    crop_w = int(crop_h * TARGET_RATIO)
    if crop_w > w:
        crop_w = w
        crop_h = int(crop_w / TARGET_RATIO)
    x0 = (w - crop_w) // 2
    y0 = min(max(0, int(h * top_frac)), h - crop_h)
    return im.crop((x0, y0, x0 + crop_w, y0 + crop_h))


def save_webp(im: Image.Image, dest: Path) -> int:
    im = im.convert("RGB").resize(TARGET, Image.Resampling.LANCZOS)
    quality = 82
    while quality >= 68:
        buf = io.BytesIO()
        im.save(buf, "WEBP", quality=quality, method=6)
        if buf.tell() <= MAX_BYTES:
            dest.write_bytes(buf.getvalue())
            return buf.tell()
        quality -= 4
    dest.write_bytes(buf.getvalue())
    return buf.tell()


def main() -> int:
    if not SRC.is_file():
        print("missing", SRC, file=sys.stderr)
        return 1
    im = Image.open(SRC)
    framed = cover_crop(im, top_frac=0.0, height_frac=0.94)
    nbytes = save_webp(framed, DEST)
    meta = Image.open(DEST)
    print(f"OK {DEST.name} {meta.size} {meta.mode} {nbytes // 1024} KiB")
    SRC.unlink()
    print("removed", SRC)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())