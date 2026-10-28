#!/usr/bin/env python3
"""Sync contacts CMS assets into template/page/kontaktyi/."""

import hashlib
import shutil
import sys
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
SHARED = REPO / "pages" / "kontaktyi" / "_shared"
DEST = REPO / "template" / "page" / "kontaktyi"

COPY_FILES = (
    "lireate-contacts-config.php",
    "lireate-contacts-data.php",
    "lireate-contacts-lib.php",
    "lireate-contacts-page.inc.php",
    "lireate-contacts-cms.inc.php",
    "lireate-contacts.css",
    "lireate-contacts.js",
)
METRIKA_SRC = REPO / "pages" / "services" / "_shared" / "lireate-metrika-goals.js"


def sha(path):
    h = hashlib.sha256()
    with path.open("rb") as fh:
        for chunk in iter(lambda: fh.read(65536), b""):
            h.update(chunk)
    return h.hexdigest()


def copy_if_changed(src, dst):
    if dst.is_file() and sha(src) == sha(dst):
        return False
    dst.parent.mkdir(parents=True, exist_ok=True)
    shutil.copy2(str(src), str(dst))
    return True


def main():
    changed = []
    DEST.mkdir(parents=True, exist_ok=True)
    for name in COPY_FILES:
        src = SHARED / name
        if not src.is_file():
            print("BLOCKER: missing {0}".format(src))
            return 2
        dst = DEST / name
        if copy_if_changed(src, dst):
            changed.append(str(dst.relative_to(REPO)))
    if METRIKA_SRC.is_file():
        dst = DEST / "lireate-metrika-goals.js"
        if copy_if_changed(METRIKA_SRC, dst):
            changed.append(str(dst.relative_to(REPO)))
    if changed:
        print("synced: " + ", ".join(changed))
    else:
        print("OK: contacts template up to date")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
