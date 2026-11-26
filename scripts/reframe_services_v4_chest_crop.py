#!/usr/bin/env python3
"""Re-crop services-v4 specialist WebP: mid-torso frame (waist), head margin, centered."""

from __future__ import annotations

import io
import json
from pathlib import Path

import cv2
import numpy as np
from PIL import Image

REPO = Path(__file__).resolve().parent.parent
DIR = REPO / "template/images/services-v4"
SRC_DIR = DIR / "_chest-src"
META_PATH = DIR / "reframe-midbody-meta.json"
FRAME_W, FRAME_H = 453, 378
FRAME_RATIO = FRAME_W / FRAME_H
TOP_MARGIN_FRAC = 0.08
CROWN_ABOVE_FACE = 0.14
OUT_W = 906
OUT_H = int(round(OUT_W / FRAME_RATIO))
MAX_BYTES = 220 * 1024

WAIST_MULT = {
    "specialist-certificate": 3.05,
    "specialist-registraciya": 2.85,
    "specialist-prolongation": 2.85,
    "specialist-opposition": 2.85,
    "specialist-copyright": 2.85,
}


def load_bgr(path: Path) -> np.ndarray:
    data = np.frombuffer(path.read_bytes(), dtype=np.uint8)
    bgr = cv2.imdecode(data, cv2.IMREAD_COLOR)
    if bgr is None:
        raise RuntimeError(f"cannot decode {path}")
    return bgr


def detect_face(gray: np.ndarray, scale: float = 1.05):
    h = gray.shape[0]
    cascade = cv2.CascadeClassifier(
        cv2.data.haarcascades + "haarcascade_frontalface_default.xml"
    )
    profiles = cv2.CascadeClassifier(
        cv2.data.haarcascades + "haarcascade_profileface.xml"
    )
    faces = cascade.detectMultiScale(gray, scaleFactor=scale, minNeighbors=4, minSize=(72, 72))
    if len(faces) == 0:
        faces = profiles.detectMultiScale(gray, scaleFactor=scale, minNeighbors=4, minSize=(72, 72))
    if len(faces) == 0:
        return None
    plausible = [f for f in faces if f[3] <= int(0.38 * h)]
    if plausible:
        faces = plausible
    return max(faces, key=lambda r: r[2] * r[3])


def midbody_crop(bgr: np.ndarray, stem: str) -> tuple[Image.Image, dict]:
    h, w = bgr.shape[:2]
    meta: dict = {"stem": stem, "source_h": h, "source_w": w}
    gray = cv2.cvtColor(bgr, cv2.COLOR_BGR2GRAY)
    face = detect_face(gray)
    waist_mult = WAIST_MULT.get(stem, 2.85)
    crop_h = int(round(w / FRAME_RATIO))
    crop_w = w

    if face is None:
        y0 = max(0, h - crop_h)
        crop = bgr[y0 : y0 + crop_h, 0:w]
        meta.update(
            face=False,
            waist_reached=False,
            note="face_not_detected; crop lowered to image bottom",
        )
    else:
        x, y, fw, fh = face
        cx = x + fw // 2
        face_bottom = y + fh
        crown = y - int(CROWN_ABOVE_FACE * fh)
        waist_y = min(h, int(face_bottom + waist_mult * fh))
        ideal_top = int(round(crown - TOP_MARGIN_FRAC * crop_h))
        lowest_top = max(0, h - crop_h)
        waist_top = int(waist_y - crop_h)
        top = min(ideal_top, waist_top)
        top = max(0, min(top, lowest_top))
        if waist_y > top + crop_h + 8:
            if lowest_top < ideal_top:
                top = lowest_top
            meta["waist_reached"] = False
            meta["note"] = (
                "source shorter than waist; frame lowered maximally without clipping crown"
            )
        else:
            meta["waist_reached"] = True
            meta["note"] = "waist aligned when source allows"
        if top > ideal_top + 2:
            meta["crown_margin_reduced"] = True
        meta.update(
            face=True,
            fh=fh,
            waist_y=waist_y,
            crop_top=top,
            crop_bottom=top + crop_h,
            ideal_top=ideal_top,
        )
        x0 = max(0, min(w - crop_w, cx - crop_w // 2))
        crop = bgr[top : top + crop_h, x0 : x0 + crop_w]

    rgb = cv2.cvtColor(crop, cv2.COLOR_BGR2RGB)
    return Image.fromarray(rgb), meta


def save_webp(im: Image.Image, dest: Path) -> int:
    im = im.resize((OUT_W, OUT_H), Image.Resampling.LANCZOS)
    quality = 84
    while quality >= 64:
        buf = io.BytesIO()
        im.save(buf, "WEBP", quality=quality, method=6)
        if buf.tell() <= MAX_BYTES:
            dest.write_bytes(buf.getvalue())
            return buf.tell()
        quality -= 3
    dest.write_bytes(buf.getvalue())
    return buf.tell()


def source_path(name: str) -> Path:
    backup = SRC_DIR / name
    if backup.is_file():
        return backup
    return DIR / name


def main() -> int:
    SRC_DIR.mkdir(parents=True, exist_ok=True)
    all_meta: dict = {}
    for webp in sorted(DIR.glob("specialist-*.webp")):
        src = source_path(webp.name)
        bgr = load_bgr(src)
        cropped, meta = midbody_crop(bgr, webp.stem)
        nbytes = save_webp(cropped, webp)
        check = Image.open(webp)
        meta["bytes"] = nbytes
        meta["out_size"] = check.size
        all_meta[webp.stem] = meta
        print(f"OK {webp.name} {meta.get('note', '')} {check.size} {nbytes // 1024} KiB")
    META_PATH.write_text(
        json.dumps(all_meta, ensure_ascii=False, indent=2, default=int),
        encoding="utf-8",
    )
    print(f"FRAME_RATIO={FRAME_RATIO:.4f} meta -> {META_PATH}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())