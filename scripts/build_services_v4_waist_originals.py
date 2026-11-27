#!/usr/bin/env python3
"""Build specialist WebP from owner waist JPEGs (originals/waist/)."""

from __future__ import annotations

import io
from pathlib import Path

import cv2
from PIL import Image

from reframe_services_v4_chest_crop import (
    FRAME_RATIO,
    TOP_MARGIN_FRAC,
    CROWN_ABOVE_FACE,
    save_webp,
    load_bgr,
    detect_face,
)

REPO = Path(__file__).resolve().parent.parent
WAIST = REPO / "originals/waist"
OUT = REPO / "template/images/services-v4"
CHEST_SRC = OUT / "_chest-src"

WAIST_MULT = 2.95

SOURCES = {
    "specialist-registraciya.webp": WAIST / "kvasova-anna-shelf.jpg",
    "specialist-prolongation.webp": WAIST / "uldanova-alena-leopard.jpg",
    "specialist-opposition.webp": WAIST / "uldanova-alena-black-blazer-on.jpg",
    "specialist-copyright.webp": WAIST / "uldanova-alena-blazer-over-arm.jpg",
}


def waist_crop(bgr):
    h, w = bgr.shape[:2]
    gray = cv2.cvtColor(bgr, cv2.COLOR_BGR2GRAY)
    face = detect_face(gray, scale=1.08)
    meta: dict = {"source_h": h, "source_w": w}
    if face is None:
        crop_h = int(w / FRAME_RATIO)
        top = max(0, (h - crop_h) // 4)
        crop = bgr[top : top + crop_h, 0:w]
        meta["note"] = "face_not_detected"
    else:
        x, y, fw, fh = face
        cx = x + fw // 2
        crown = y - int(CROWN_ABOVE_FACE * fh)
        waist_y = min(h, int(y + fh + WAIST_MULT * fh))
        ideal_top = int(round(crown - TOP_MARGIN_FRAC * int((waist_y - crown) / (1.0 - TOP_MARGIN_FRAC))))
        needed_h = int(round((waist_y - crown) / (1.0 - TOP_MARGIN_FRAC)))
        needed_w = int(round(needed_h * FRAME_RATIO))
        if needed_w <= w and needed_h <= h:
            crop_h, crop_w = needed_h, needed_w
        else:
            crop_h = int(round(w / FRAME_RATIO))
            crop_w = w
        top = int(round(crown - TOP_MARGIN_FRAC * crop_h))
        if needed_w <= w and needed_h <= h:
            top = ideal_top
        if top < 0:
            top = 0
        if top + crop_h > h:
            top = max(0, h - crop_h)
        waist_anchor = waist_y - crop_h
        if waist_anchor > top and waist_anchor <= h - crop_h:
            if waist_anchor <= ideal_top + 12:
                top = max(0, waist_anchor)
        if top > ideal_top + 8:
            top = max(0, min(ideal_top, h - crop_h))
        x0 = max(0, min(w - crop_w, cx - crop_w // 2))
        crop = bgr[top : top + crop_h, x0 : x0 + crop_w]
        meta.update(
            waist_y=waist_y,
            crop_top=top,
            crop_bottom=top + crop_h,
            waist_reached=bool((top + crop_h) + 5 >= waist_y),
            crown=int(crown),
        )
    rgb = cv2.cvtColor(crop, cv2.COLOR_BGR2RGB)
    return Image.fromarray(rgb), meta


def main() -> int:
    CHEST_SRC.mkdir(parents=True, exist_ok=True)
    for dest_name, src in SOURCES.items():
        if not src.is_file():
            raise SystemExit(f"missing {src}")
        bgr = load_bgr(src)
        im, meta = waist_crop(bgr)
        dest = OUT / dest_name
        nbytes = save_webp(im, dest)
        backup = CHEST_SRC / dest_name
        work_h = int(800 / FRAME_RATIO)
        scaled = im.resize((800, work_h), Image.Resampling.LANCZOS)
        buf = io.BytesIO()
        scaled.save(buf, "WEBP", quality=88)
        backup.write_bytes(buf.getvalue())
        print(
            f"OK {dest_name} <- {src.name} waist_reached={meta.get('waist_reached')} "
            f"{nbytes // 1024} KiB"
        )
    return 0


if __name__ == "__main__":
    raise SystemExit(main())