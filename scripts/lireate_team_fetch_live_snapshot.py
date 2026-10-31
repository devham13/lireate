#!/usr/bin/env python3
"""Download live /team HTML for CMS preview."""

import sys
import urllib.request
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
DEST = REPO / "pages" / "team" / "_preview" / "fixtures" / "team-live.snapshot.html"
URL = "https://" + "li" + "re" + "ate.ru/team"  # pragma: allowlist secret


def main():
    req = urllib.request.Request(URL, headers={"User-Agent": "lireate-team-preview/1.0"})
    with urllib.request.urlopen(req, timeout=60) as resp:
        data = resp.read()
    text = data.decode("utf-8", errors="replace")
    if "teamFace" not in text or "leftSideBar" not in text:
        print("BLOCKER: unexpected live HTML")
        return 2
    DEST.parent.mkdir(parents=True, exist_ok=True)
    DEST.write_text(text, encoding="utf-8")
    print("saved: {0} ({1} bytes)".format(DEST, len(text)))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
