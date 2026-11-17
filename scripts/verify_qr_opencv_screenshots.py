#!/usr/bin/env python3
"""Декод QR из result-скриншотов: вырез квадрата по pyzbar, cv2.QRCodeDetector (без upscale)."""

import json
import sys
from pathlib import Path

try:
    import cv2
    from pyzbar import pyzbar
except ImportError:
    print("opencv-python-headless and pyzbar required")
    sys.exit(2)

ROOT = Path("/opt/cursor/artifacts/screenshots")
FILES = [
    ("patent-pay-dues", "patent-pay-dues-result-desktop-1440-full.png"),
    ("patent-pay-dues", "patent-pay-dues-result-mobile-390-full.png"),
    ("patent-pay-fips", "patent-pay-fips-result-desktop-1440-full.png"),
    ("patent-pay-fips", "patent-pay-fips-result-mobile-390-full.png"),
    ("patent-evm-search", "patent-evm-search-result-desktop-1440-full.png"),
    ("patent-evm-search", "patent-evm-search-result-mobile-390-full.png"),
]


def crops_from_symbol(img, rect):
    """Вырез QR из full-page PNG: прямоугольник pyzbar + quiet zone (4 модуля)."""
    x, y, w, h = rect.left, rect.top, rect.width, rect.height
    side = min(w, h)
    ih, iw = img.shape[:2]
    pads = {
        max(8, int(round(4 * side / 93))),
        max(8, int(round(4 * side / 89))),
        max(8, int(round(4 * side / 77))),
        max(8, int(round(4 * side / 65))),
        12,
        16,
        20,
    }
    out = []
    for pad in sorted(pads):
        x0 = max(0, x - pad)
        y0 = max(0, y - pad)
        x1 = min(iw, x + w + pad)
        y1 = min(ih, y + h + pad)
        out.append(img[y0:y1, x0:x1])
    return out


def opencv_decode(det, crop) -> str:
    gray = cv2.cvtColor(crop, cv2.COLOR_BGR2GRAY)
    for candidate in (crop, gray):
        data, _, _ = det.detectAndDecode(candidate)
        if data:
            return data
        ok, infos, _, _ = det.detectAndDecodeMulti(candidate)
        if ok and infos:
            for s in infos:
                if s:
                    return s
    return ""


def decode(path: Path) -> str:
    img = cv2.imread(str(path))
    if img is None:
        raise SystemExit(f"cannot read {path}")
    det = cv2.QRCodeDetector()
    codes = pyzbar.decode(img)
    if not codes:
        raise SystemExit(f"no QR region in {path}")
    for crop in crops_from_symbol(img, codes[0].rect):
        data = opencv_decode(det, crop)
        if data:
            return data
    raise SystemExit(f"OpenCV: no QR in {path}")


def main():
    out = []
    failed = 0
    for key, name in FILES:
        path = ROOT / name
        row = {"page": key, "file": name, "ok": False, "payload": None}
        try:
            text = decode(path)
            row["ok"] = True
            row["payload"] = text
            print(f"OK {name}")
            print(text)
            print("---")
        except SystemExit as exc:
            row["error"] = str(exc)
            failed += 1
            print(exc, file=sys.stderr)
        out.append(row)
    report = Path("/opt/cursor/artifacts/patent-qr-opencv-decode.json")
    report.write_text(json.dumps(out, ensure_ascii=False, indent=2), encoding="utf-8")
    print("WROTE", report)
    if failed:
        sys.exit(1)


if __name__ == "__main__":
    main()