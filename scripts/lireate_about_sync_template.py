#!/usr/bin/env python3
"""Sync about CMS assets into template/page/o_kompanii/."""
import shutil
import sys
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
SHARED = REPO / "pages" / "o_kompanii" / "_shared"
DEST = REPO / "template" / "page" / "o_kompanii"


def main():
    DEST.mkdir(parents=True, exist_ok=True)
    copied = 0
    for path in sorted(SHARED.iterdir()):
        if path.is_file() and path.suffix in (".php", ".css", ".js"):
            shutil.copy2(str(path), str(DEST / path.name))
            copied += 1
    metrika = REPO / "pages" / "services" / "_shared" / "lireate-metrika-goals.js"
    if metrika.is_file():
        shutil.copy2(str(metrika), str(DEST / "lireate-metrika-goals.js"))
        copied += 1
    print("synced {0} files to {1}".format(copied, DEST.relative_to(REPO)))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
