#!/usr/bin/env python3
"""Deploy template/llms.txt to the site document root (GEO/AEO index file).

Safe, idempotent copy with backup. Does not change CMS DB or page content.
"""
from __future__ import annotations

import argparse
import hashlib
import shutil
import sys
from datetime import datetime, timezone
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
SOURCE = REPO / "template" / "llms.txt"
TARGET_NAME = "llms.txt"
BUILD_SCRIPT = REPO / "scripts" / "lireate_seo_build_llms_txt.py"


def sha256(path: Path) -> str:
    return hashlib.sha256(path.read_bytes()).hexdigest()


def _build_combined(staging: Path, docroot: Path) -> None:
    import subprocess as sp

    if not BUILD_SCRIPT.is_file():
        shutil.copy2(SOURCE, staging)
        return
    sp.check_call(
        [
            sys.executable,
            str(BUILD_SCRIPT),
            "--output",
            str(staging),
            "--docroot",
            str(docroot),
        ],
    )


def install(docroot: Path, dry_run: bool = False) -> int:
    if not SOURCE.is_file():
        raise SystemExit("BLOCKER: missing {0}".format(SOURCE))
    target = docroot / TARGET_NAME
    staging = docroot / (TARGET_NAME + ".build-staging")
    if dry_run:
        print("llms.txt: would build+install -> {0}".format(target))
        return 0
    docroot.mkdir(parents=True, exist_ok=True)
    _build_combined(staging, docroot)
    if target.is_file() and sha256(target) == sha256(staging):
        staging.unlink(missing_ok=True)
        print("llms.txt: already up to date")
        return 0
    if target.is_file():
        stamp = datetime.now(timezone.utc).strftime("%Y%m%dT%H%M%SZ")
        backup = docroot / (TARGET_NAME + ".bak-" + stamp)
        shutil.copy2(target, backup)
        print("backup:", backup)
    shutil.copy2(staging, target)
    staging.unlink(missing_ok=True)
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