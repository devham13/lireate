#!/usr/bin/env python3
"""Генерация QR по ST00012 и сверка полей (decode при наличии pyzbar)."""

import json
import sys
import tempfile
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(REPO / "scripts"))
from test_lireate_patent_payment import build_st00012  # noqa: E402

PROFILES = json.loads(
  (REPO / "pages" / "services" / "_shared" / "lireate-patent-payment-profiles.json").read_text(
    encoding="utf-8"
  )
)
fields = PROFILES["profiles"]["rospatent_patent_dues"]["fields"]
payload = build_st00012(fields, 12000, "п.2.16 изменения по ТЗ")
print("PAYLOAD:", payload)

try:
  import qrcode
except ImportError:
  print("SKIP QR image: pip install qrcode[pil]")
  sys.exit(0)

out = Path("/opt/cursor/artifacts/patent-payment-qr-sample.png")
out.parent.mkdir(parents=True, exist_ok=True)
img = qrcode.make(payload)
img.save(out)
print("WROTE", out)

decoded = None
try:
  from PIL import Image
  from pyzbar.pyzbar import decode as zbar_decode

  decoded = zbar_decode(Image.open(out))
except Exception as exc:
  print("DECODE SKIP:", exc)

if decoded:
  text = decoded[0].data.decode("utf-8", errors="replace")
  print("DECODED:", text)
  assert text == payload, "QR payload mismatch"
  print("QR_DECODE_OK")
else:
  print("MANUAL_VERIFY_ONLY (install pyzbar for decode)")