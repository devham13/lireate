#!/usr/bin/env python3
"""Deploy template/llms.txt to the site document root (GEO/AEO index file).

Safe, idempotent copy with backup. Does not change CMS DB or page content.
"""
from __future__ import annotations

import argparse
import hashlib
import shutil
from datetime import datetime, timezone
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
SOURCE = REPO / "template" / "llms.txt"
TARGET_NAME = "llms.txt"


def sha256(path: Path) -> str:
    return hashlib.sha256(path.read_bytes()).hexdigest()


def install(docroot: Path, dry_run: bool = False) -> int:
    if not SOURCE.is_file():
        raise SystemExit("BLOCKER: missing {0}".format(SOURCE))
    target = docroot / TARGET_NAME
    if target.is_file() and sha256(target) == sha256(SOURCE):
        print("llms.txt: already up to date")
        return 0
    if dry_run:
        print("llms.txt: would install -> {0}".format(target))
        return 0
    docroot.mkdir(parents=True, exist_ok=True)
    if target.is_file():
        stamp = datetime.now(timezone.utc).strftime("%Y%m%dT%H%M%SZ")
        backup = docroot / (TARGET_NAME + ".bak-" + stamp)
        shutil.copy2(target, backup)
        print("backup:", backup)
    shutil.copy2(SOURCE, target)
    print("llms.txt: installed ({0} bytes)".format(target.stat().st_size))
    return 0


def main() -> int:
    ap = argparse.ArgumentParser()
    ap.add_argument("--docroot", required=True, type=Path, help="Hosting DOCUMENT_ROOT")
    ap.add_argument("--check", action="store_true", help="Dry-run only")
    args = ap.parse_args()
    return install(args.docroot.expanduser().resolve(), dry_run=args.check)


if __name__ == "__main__":
    raise SystemExit(main())