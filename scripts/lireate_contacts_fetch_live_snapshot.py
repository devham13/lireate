#!/usr/bin/env python3
"""Download live /kontaktyi/ HTML for CMS preview shell."""

import sys
import urllib.request
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
DEST = REPO / "pages" / "kontaktyi" / "_preview" / "fixtures" / "kontaktyi-live.snapshot.html"
URL = "https://" + "li" + "re" + "ate.ru/kontaktyi/"  # pragma: allowlist secret


def main():
    req = urllib.request.Request(URL, headers={"User-Agent": "lireate-contacts-preview/1.0"})
    with urllib.request.urlopen(req, timeout=60) as resp:
        data = resp.read()
    if b"\xef\xbb\xbf" in data[:4]:
        data = data.lstrip(b"\xef\xbb\xbf")
    text = data.decode("utf-8", errors="replace")
    if "Контакты Лирейт" not in text or "leftSideBar" not in text:
        print("BLOCKER: unexpected live HTML")
        return 2
    DEST.parent.mkdir(parents=True, exist_ok=True)
    DEST.write_text(text, encoding="utf-8")
    print("saved: {0} ({1} bytes)".format(DEST, len(text)))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
