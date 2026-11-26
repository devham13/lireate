#!/usr/bin/env python3
"""Safe installation/rollback of LIREATE Light V5 information pages.

Dry-run by default. Before --apply, detects unexpected changes to live PHP
templates: NEVER overwrites local edits by Grok/Cursor/hosting unattended.
Copies only five existing page components + shared CSS + recoloured SVG logo.
No database, SEO meta, forms, contact details, analytics or menus are replaced.
"""
import argparse
import datetime
import hashlib
import json
import os
from pathlib import Path
import shutil
import subprocess
import tempfile

REPO = Path(__file__).resolve().parents[1]
VERSION = "lireate-company-v5-20261126"
PHP = (
    Path("template/page/o_kompanii/lireate-about-page.inc.php"),
    Path("template/page/team/lireate-team-page.inc.php"),
    Path("template/page/partners/lireate-partners-page.inc.php"),
    Path("template/page/docs/lireate-docs-page.inc.php"),
    Path("template/page/kontaktyi/lireate-contacts-page.inc.php"),
    Path("pages/o_kompanii/_shared/lireate-about-page.inc.php"),
    Path("pages/team/_shared/lireate-team-page.inc.php"),
    Path("pages/partners/_shared/lireate-partners-page.inc.php"),
    Path("pages/docs/_shared/lireate-docs-page.inc.php"),
    Path("pages/kontaktyi/_shared/lireate-contacts-page.inc.php"),
)
ASSETS = (
    Path("template/css/lireate-company-pages-v5.css"),
    Path("template/css/lireate-public-cms-chrome.css"),
    Path("template/images/logo/logo-light-v5.svg"),
)
FILES = PHP + ASSETS

# Git blob IDs for original main-branch PHP versions reviewed on 2026-11-26.
# Refuse to overwrite a live PHP file with a different blob ID (concurrent
# edits / different CMS version). The installer does not force through drift.
BASE_BLOB = {
    str(PHP[0]): "e619f9bce860b36b03a8a18cef964a778427787f",
    str(PHP[1]): "fac6de2bdc683d5a617be33536dfee0f33b615d2",
    str(PHP[2]): "5b952e2a7c1323a32f0e5307e6c663315c1e73f2",
    str(PHP[3]): "b00637c168530d0e43fb288c8c8a71489b9b15a7",
    str(PHP[4]): "2c36b465077012f5a5844811664f949d1f486c9c",
    str(PHP[5]): "aae9678d2bb526bd9f5fd83b4cd58363396218b3",
    str(PHP[6]): "271ba364ddad4336cbd1f5910372000032c64f18",
    str(PHP[7]): "4c0a93a1d55f2dbd079190fbe64e417ee04e4721",
    str(PHP[8]): "0c0353d76dbf98735fc45b41055a7406895bf718",
    str(PHP[9]): "62baf790c44b528a6c5610aee69f50588161386a",
    str(ASSETS[1]): "5a3242c525f22eee671b266405d2ef07644db8d4",
}


def blob_id(data):
    return hashlib.sha1(b"blob " + str(len(data)).encode("ascii") + b"\0" + data).hexdigest()


def target(docroot, relative):
    if relative not in FILES:
        raise ValueError("BLOCKER: unexpected path")
    path = docroot / relative
    for item in [path] + list(path.parents):
        if item == docroot.parent:
            break
        if item.is_symlink():
            raise ValueError("BLOCKER: symlink on target path: " + str(relative))
    if path.exists() and not path.is_file():
        raise ValueError("BLOCKER: non-file target: " + str(relative))
    return path


def plan(docroot):
    changes = {}
    for rel in FILES:
        src = REPO / rel
        if not src.is_file():
            raise ValueError("BLOCKER: missing repository asset: " + str(rel))
        desired = src.read_bytes()
        dest = target(docroot, rel)
        if dest.is_file():
            existing = dest.read_bytes()
            if existing == desired:
                continue
            if str(rel) not in BASE_BLOB:
                raise ValueError("BLOCKER: existing unexpected shared asset: " + str(rel))
            if blob_id(existing) != BASE_BLOB[str(rel)]:
                raise ValueError(
                    "BLOCKER: live PHP differs from reviewed main version: " +
                    str(rel) + "; inspect/rebase changes with Cursor instead of overwriting"
                )
        elif rel in PHP:
            raise ValueError("BLOCKER: expected live PHP template missing: " + str(rel))
        changes[rel] = desired
    return changes


def atomic_write(dest, data):
    dest.parent.mkdir(parents=True, exist_ok=True)
    fd, tmp = tempfile.mkstemp(prefix=".lc5-", dir=str(dest.parent))
    try:
        with os.fdopen(fd, "wb") as stream:
            stream.write(data)
        os.chmod(tmp, dest.stat().st_mode & 0o777 if dest.is_file() else 0o644)
        os.replace(tmp, dest)
    finally:
        if os.path.exists(tmp):
            os.unlink(tmp)


def create_backup(docroot, changes, backup):
    backup.mkdir(parents=True, exist_ok=False)
    manifest = {"version": VERSION, "files": []}
    for rel in changes:
        dest = target(docroot, rel)
        present = dest.is_file()
        manifest["files"].append({"path": str(rel), "existed": present})
        if present:
            copy = backup / rel
            copy.parent.mkdir(parents=True, exist_ok=True)
            shutil.copy2(dest, copy)
    (backup / "manifest.json").write_text(
        json.dumps(manifest, ensure_ascii=False, indent=2), encoding="utf-8"
    )


def rollback(docroot, backup):
    doc = backup / "manifest.json"
    if not doc.is_file():
        raise ValueError("BLOCKER: backup manifest not found")
    manifest = json.loads(doc.read_text(encoding="utf-8"))
    if manifest.get("version") != VERSION:
        raise ValueError("BLOCKER: backup belongs to another installer")
    for entry in manifest.get("files", []):
        rel = Path(entry["path"])
        dest = target(docroot, rel)
        if entry["existed"]:
            copy = backup / rel
            if not copy.is_file():
                raise ValueError("BLOCKER: missing backup source " + str(rel))
            atomic_write(dest, copy.read_bytes())
        elif dest.exists():
            dest.unlink()
    print("ROLLED_BACK", backup)


def run(argv=None):
    ap = argparse.ArgumentParser(description=__doc__)
    ap.add_argument("--docroot", required=True)
    ap.add_argument("--backup-root")
    ap.add_argument("--php-bin", default="php")
    ap.add_argument("--apply", action="store_true")
    ap.add_argument("--rollback", metavar="BACKUP_DIRECTORY")
    args = ap.parse_args(argv)
    docroot = Path(args.docroot).expanduser().resolve()
    if not docroot.is_dir():
        raise ValueError("BLOCKER: DOCROOT directory not found")
    backup_root = (Path(args.backup_root).expanduser().resolve()
                   if args.backup_root else docroot.parent / "lireate-v5-backups")
    if backup_root == docroot or docroot in backup_root.parents:
        raise ValueError("BLOCKER: backups must be outside DOCROOT")
    if args.rollback:
        rollback(docroot, Path(args.rollback).expanduser().resolve())
        return 0
    changes = plan(docroot)
    if not changes:
        print("NO_CHANGES")
        return 0
    for rel in changes:
        print(("APPLY" if args.apply else "DRY_RUN"), str(rel))
    if not args.apply:
        print("DRY_RUN_ONLY; no site files were changed")
        return 0
    php_bin = shutil.which(args.php_bin) or (
        args.php_bin if Path(args.php_bin).is_file() else None
    )
    if not php_bin:
        raise ValueError("BLOCKER: PHP binary missing; pass --php-bin")
    stamp = datetime.datetime.now(datetime.timezone.utc).strftime("%Y%m%dT%H%M%SZ")
    backup = backup_root / stamp
    create_backup(docroot, changes, backup)
    try:
        for rel, data in changes.items():
            atomic_write(target(docroot, rel), data)
        for rel in PHP:
            check = subprocess.run(
                [php_bin, "-l", str(docroot / rel)],
                capture_output=True, text=True, timeout=30
            )
            if check.returncode:
                raise RuntimeError("PHP lint failed for " + str(rel) +
                                   ": " + check.stdout + check.stderr)
    except Exception:
        rollback(docroot, backup)
        raise
    print("INSTALLED; backup=" + str(backup))
    print("VERIFY five live pages (desktop/mobile), original forms and sidebar logo")
    return 0


if __name__ == "__main__":
    raise SystemExit(run())
