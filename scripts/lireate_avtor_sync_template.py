#!/usr/bin/env python3
"""Sync avtor CMS assets into template/page/avtor/."""

import hashlib
import shutil
import sys
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
SHARED = REPO / "pages" / "avtor" / "_shared"
DEST = REPO / "template" / "page" / "avtor"

COPY_FILES = (
    "lireate-avtor-config.php",
    "lireate-avtor-data.php",
    "lireate-avtor-lib.php",
    "lireate-avtor-page.inc.php",
    "lireate-avtor-cms.inc.php",
    "lireate-avtor.css",
)


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
    if changed:
        print("synced: " + ", ".join(changed))
    else:
        print("OK: avtor template up to date")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())