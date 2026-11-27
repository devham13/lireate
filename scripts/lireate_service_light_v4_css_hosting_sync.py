#!/usr/bin/env python3
"""Push pages/services/_shared/lireate-light-v4.css to every copy on docroot (no index.php)."""
from __future__ import print_function

import os
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SRC = ROOT / "pages/services/_shared/lireate-light-v4.css"


def main():
    docroot = Path(os.environ.get("LIREATE_DOCROOT", "")).expanduser()
    if not docroot.is_dir():
        print("BLOCKER: set LIREATE_DOCROOT")
        return 2
    if not SRC.is_file():
        print("BLOCKER: missing", SRC)
        return 2
    data = SRC.read_bytes()
    copied = []
    for path in docroot.rglob("lireate-light-v4.css"):
        rel = str(path.relative_to(docroot)).replace("\\", "/")
        if rel == "_shared/lireate-light-v4.css":
            dst = path
        elif "/assets/lireate-shared/lireate-light-v4.css" in rel:
            dst = path
        elif rel.endswith("/_shared/lireate-light-v4.css"):
            dst = path
        else:
            continue
        dst.write_bytes(data)
        try:
            os.utime(str(dst), None)
        except OSError:
            pass
        copied.append(rel)
    print("SYNC_LIGHT_V4", len(copied))
    for rel in sorted(copied)[:40]:
        print(" ", rel)
    if len(copied) > 40:
        print(" ...", len(copied) - 40, "more")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())