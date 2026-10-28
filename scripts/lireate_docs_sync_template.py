#!/usr/bin/env python3
"""Sync docs CMS assets into template/page/docs/."""

import hashlib
import shutil
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
SHARED = REPO / "pages" / "docs" / "_shared"
DEST = REPO / "template" / "page" / "docs"
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
    if not SHARED.is_dir():
        print("BLOCKER: missing {0}".format(SHARED))
        return 2
    for path in sorted(SHARED.iterdir()):
        if path.is_file() and path.suffix in (".php", ".css", ".js"):
            dst = DEST / path.name
            if copy_if_changed(path, dst):
                changed.append(str(dst.relative_to(REPO)))
    if METRIKA_SRC.is_file():
        dst = DEST / "lireate-metrika-goals.js"
        if copy_if_changed(METRIKA_SRC, dst):
            changed.append(str(dst.relative_to(REPO)))
    if changed:
        print("synced: " + ", ".join(changed))
    else:
        print("OK: docs template up to date")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
