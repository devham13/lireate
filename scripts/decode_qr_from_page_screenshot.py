#!/usr/bin/env python3
"""Декод QR из PNG со страницы: ищет светлый блок lx-qr-box и сверяет с payload."""

import json
import sys
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(REPO / "scripts"))
from test_lireate_patent_payment import build_st00012  # noqa: E402

PROFILES = json.loads(
    (REPO / "pages/services/_shared/lireate-patent-payment-profiles.json").read_text(encoding="utf-8")
)


def find_qr_crop(img):
    from PIL import Image

    px = img.load()
    w, h = img.size
    # Ищем крупный почти белый прямоугольник (фон qr-box).
    step = max(8, w // 120)
    best = None
    for y in range(0, h - 80, step):
        for x in range(0, w - 80, step):
            white = 0
            total = 0
            for dy in range(0, min(240, h - y), step):
                for dx in range(0, min(240, w - x), step):
                    r, g, b = px[x + dx, y + dy][:3]
                    if r > 240 and g > 240 and b > 240:
                        white += 1
                    total += 1
            if total and white / total > 0.92:
                box = (x, y, min(w, x + 240), min(h, y + 240))
                area = (box[2] - box[0]) * (box[3] - box[1])
                if not best or area > best[0]:
                    best = (area, box)
    if not best:
        return None
    return best[1]


def decode_path(path: Path):
    from PIL import Image, ImageOps
    from pyzbar.pyzbar import decode as zbar_decode

    img = Image.open(path).convert("RGB")
    codes = zbar_decode(img)
    crop = None
    if not codes:
        crop = find_qr_crop(img)
        if not crop:
            raise SystemExit(f"QR region not found in {path}")
        region = img.crop(crop)
        region = ImageOps.grayscale(region)
        region = ImageOps.autocontrast(region)
        codes = zbar_decode(region)
    if not codes:
        raise SystemExit(f"pyzbar: no QR in {path}")
    text = codes[0].data.decode("utf-8")
    return text, crop


def main():
    if len(sys.argv) < 2:
        print("Usage: decode_qr_from_page_screenshot.py <screenshot.png> [expected_contains]")
        sys.exit(2)
    path = Path(sys.argv[1])
    text, crop = decode_path(path)
    print("CROP", crop)
    print("DECODED:", text)
    if len(sys.argv) > 2:
        needle = sys.argv[2]
        if needle not in text:
            raise SystemExit("payload mismatch")
        print("MATCH_OK")
    else:
        print("OK")


if __name__ == "__main__":
    main()