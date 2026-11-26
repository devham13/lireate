#!/usr/bin/env python3
"""Re-crop services-v4 specialist WebP for chest-up hero (home-like frame)."""

from __future__ import annotations

import io
from pathlib import Path

import cv2
import numpy as np
from PIL import Image

REPO = Path(__file__).resolve().parent.parent
DIR = REPO / "template/images/services-v4"
SRC_DIR = DIR / "_chest-src"
# Measured on live home .expert-photo @ 1440 (2026-11-26)
FRAME_W, FRAME_H = 453, 378
FRAME_RATIO = FRAME_W / FRAME_H
TOP_MARGIN_FRAC = 0.06
OUT_W = 906
OUT_H = int(round(OUT_W / FRAME_RATIO))
MAX_BYTES = 200 * 1024

HAIR_ABOVE_FRAC = 0.6
CHEST_BELOW_MULT = {
    "specialist-certificate": 2.2,
    "specialist-registraciya": 1.6,
    "specialist-prolongation": 1.6,
    "specialist-opposition": 1.6,
    "specialist-copyright": 1.6,
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


def chest_crop(bgr: np.ndarray, stem: str) -> Image.Image:
    h, w = bgr.shape[:2]
    gray = cv2.cvtColor(bgr, cv2.COLOR_BGR2GRAY)
    face = detect_face(gray)
    chest_mult = CHEST_BELOW_MULT.get(stem, 1.6)

    if face is None:
        ch = int(w / FRAME_RATIO)
        y0 = max(0, (h - ch) // 5)
        crop = bgr[y0 : y0 + ch, 0:w]
    else:
        x, y, fw, fh = face
        cx = x + fw // 2
        face_bottom = y + fh
        chest_y = min(h, int(face_bottom + chest_mult * fh))
        # Haar box tops sit near brows; hair is a little above, not 0.6·fh of bookshelf.
        hair_line = y - int(min(HAIR_ABOVE_FRAC, 0.18) * fh)
        crop_h = int(round(w / FRAME_RATIO))
        crop_w = w
        forehead_top = max(0, int(round(y - 0.06 * fh)))
        top = max(0, chest_y - crop_h)
        top = min(top, forehead_top)
        if top + crop_h > h:
            top = max(0, h - crop_h)
        x0 = max(0, min(w - crop_w, cx - crop_w // 2))
        crop = bgr[top : top + crop_h, x0 : x0 + crop_w]

    rgb = cv2.cvtColor(crop, cv2.COLOR_BGR2RGB)
    return Image.fromarray(rgb)


def save_webp(im: Image.Image, dest: Path) -> int:
    im = im.resize((OUT_W, OUT_H), Image.Resampling.LANCZOS)
    quality = 84
    while quality >= 68:
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
    for webp in sorted(DIR.glob("specialist-*.webp")):
        src = source_path(webp.name)
        bgr = load_bgr(src)
        cropped = chest_crop(bgr, webp.stem)
        nbytes = save_webp(cropped, webp)
        check = Image.open(webp)
        print(f"OK {webp.name} from {src.name} {check.size} {nbytes // 1024} KiB")
    print(f"FRAME_RATIO={FRAME_RATIO:.4f} ({FRAME_W}x{FRAME_H})")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())