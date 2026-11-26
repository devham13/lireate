#!/usr/bin/env python3
"""Install LIREATE Sidebar V7 across CMS and standalone public pages.

Default: dry-run; only with --apply are files written.  Patches only an
explicit whitelist: template/main.php, published service manifests, and
known standalone legacy routes (findtz, dogovory, etc.).  Never scans
separate landing trees (everest, reg-tov-znak, …).
No database writes. Creates an external backup and supports --rollback.
"""
from __future__ import print_function
import argparse
import datetime
import hashlib
import json
import os
from pathlib import Path
import re
import shutil
import subprocess
import tempfile

ROOT = Path(__file__).resolve().parent.parent
VERSION = "lireate-sitewide-sidebar-v7-20261126"
MAIN = Path("template/main.php")
ASSETS = (
    Path("template/css/lireate-sitewide-sidebar-v7.css"),
    Path("template/js/lireate-sitewide-sidebar-v7.js"),
    Path("template/lireate/lireate-sidebar-v7-content.inc.php"),
    Path("template/lireate/lireate-sidebar-v7-fragment.php"),
    Path("template/lireate/lireate-sidebar-v7.html"),
    Path("template/images/logo/logo-light-v7.svg"),
)
HEAD = (
    "\n<!-- " + VERSION + " -->\n"
    '<link rel="stylesheet" href="/template/css/lireate-sitewide-sidebar-v7.css?v=20261126">\n'
    '<script defer src="/template/js/lireate-sitewide-sidebar-v7.js?v=20261126"></script>\n'
)
# Top-level DOCROOT directories that must never be patched (separate landings / infra).
FORBIDDEN_TOP_DIRS = frozenset({
    "everest", "reg-tov-znak", "reg-tov-znak1(test)", "reg-tov-znak1.ru",
    "lireatecom", "copy", "sms", "captcha", "engine", "mail_parse",
    "lireate-analytics",
})
# Files that must never be modified by this installer.
NEVER_PATCH_REL = frozenset({
    Path("index.php"),
    Path(".htaccess"),
    Path("mail.php"),
    Path("consent-gate.php"),
    Path("inc/lireate-amo-lead.php"),
    Path("uslugi/index.php"),
})
# Legacy standalone routes on production (not always in pages/services manifests).
EXTRA_STANDALONE_ROUTES = (
    "dogovory",
    "registraciya_programm_evm",
)
TEMPLATE_SLUG_MARKERS = ("SLUG-ЗАМЕНИТЬ", "SLUG-ZAMENIT")

V7_HEAD_BLOCK_RE = re.compile(
    r"\n<!--\s*" + re.escape(VERSION) + r"\s*-->\s*"
    r'<link rel="stylesheet" href="/template/css/lireate-sitewide-sidebar-v7\.css\?v=20261126">\s*'
    r'<script defer src="/template/js/lireate-sitewide-sidebar-v7\.js\?v=20261126"></script>\s*\n',
    re.IGNORECASE | re.DOTALL,
)

def sha(data):
    return hashlib.sha256(data).hexdigest()

def rel_is_template_placeholder(rel):
    joined = str(rel).replace("\\", "/")
    for marker in TEMPLATE_SLUG_MARKERS:
        if marker in joined:
            return True
    return False

def rel_under_forbidden_top(rel):
    if not rel.parts:
        return False
    top = rel.parts[0].lower()
    return top in FORBIDDEN_TOP_DIRS or top.startswith("reg-tov-znak")

def is_allowed_patch_target(rel):
    if rel in NEVER_PATCH_REL:
        return False
    if rel_under_forbidden_top(rel):
        return False
    if rel_is_template_placeholder(rel):
        return False
    if rel.is_absolute() or ".." in rel.parts:
        return False
    return True

def doc_file(docroot, rel, allow_assets=False):
    if rel == MAIN or rel in ASSETS:
        pass
    elif not is_allowed_patch_target(rel):
        raise ValueError("BLOCKER unsafe or excluded path: " + str(rel))
    dest = docroot / rel
    if not str(dest.resolve()).startswith(str(docroot) + os.sep):
        raise ValueError("BLOCKER path outside DOCROOT: " + str(rel))
    for ancestor in [dest] + list(dest.parents):
        if ancestor == docroot.parent:
            break
        if ancestor.is_symlink():
            raise ValueError("BLOCKER symlink: " + str(rel))
    if dest.exists() and not dest.is_file():
        raise ValueError("BLOCKER target is not a regular file: " + str(rel))
    return dest

def service_route_paths():
    """Published service url_path → index.php (excludes template placeholder)."""
    service_root = ROOT / "pages" / "services"
    seen = set()
    for child in sorted(service_root.iterdir()):
        if not child.is_dir() or child.name.startswith("_"):
            continue
        if not (child / "index.php").is_file():
            continue
        manifest = child / "publish.manifest.json"
        if not manifest.is_file():
            continue
        record = json.loads(manifest.read_text(encoding="utf-8"))
        path = record.get("url_path", "").strip("/")
        if not path:
            continue
        rel = Path(path) / "index.php"
        if rel_is_template_placeholder(rel) or not is_allowed_patch_target(rel):
            continue
        if rel in seen:
            raise ValueError("BLOCKER duplicate URL: " + str(rel))
        seen.add(rel)
    return seen

def whitelist_standalone_paths():
    """Explicit whitelist of standalone public index.php entrypoints."""
    paths = set(service_route_paths())
    for route in EXTRA_STANDALONE_ROUTES:
        rel = Path(route) / "index.php"
        if is_allowed_patch_target(rel):
            paths.add(rel)
    return {p for p in paths if is_allowed_patch_target(p)}

def strip_v7_head_block(data):
    """Remove the v7 head injection block without reverting the whole file."""
    if VERSION.encode() not in data and b"lireate-sitewide-sidebar-v7.js" not in data:
        return data
    try:
        original = data.decode("utf-8")
    except UnicodeDecodeError:
        raise ValueError("BLOCKER non-UTF-8 file during rollback strip")
    stripped, count = V7_HEAD_BLOCK_RE.subn("", original, count=1)
    if count != 1:
        raise ValueError("BLOCKER could not locate v7 head block to remove")
    return stripped.encode("utf-8")

def patch_head(old, rel):
    # Idempotent: marker or script URL present → no change (compatible post PR #132).
    if VERSION.encode() in old or b"lireate-sitewide-sidebar-v7.js" in old:
        return old
    encoding = "utf-8"
    try:
        original = old.decode(encoding)
    except UnicodeDecodeError:
        raise ValueError("BLOCKER non-UTF-8 entrypoint " + str(rel))
    matches = list(re.finditer(r"</head\s*>", original, flags=re.I))
    if len(matches) != 1:
        raise ValueError("BLOCKER expected exactly one </head> in " + str(rel))
    if "<body" not in original.lower():
        raise ValueError("BLOCKER no body tag in standalone " + str(rel))
    pos = matches[0].start()
    return (original[:pos] + HEAD + original[pos:]).encode(encoding)

def plan(docroot, scan=True):
    # scan= kept for CLI compatibility; whitelist is always used (no directory walk).
    del scan
    changes = {}
    before = {}
    notes = []
    main_path = doc_file(docroot, MAIN, allow_assets=True)
    if not main_path.is_file():
        raise ValueError("BLOCKER template/main.php not found")
    old_main = main_path.read_bytes()
    new_main = patch_head(old_main, MAIN) if b"</head>" in old_main.lower() else None
    if new_main is None:
        raise ValueError("BLOCKER main.php has no </head>; inspect the live CMS template")
    if new_main != old_main:
        changes[MAIN] = new_main
        before[MAIN] = sha(old_main)
    for rel in ASSETS:
        source = ROOT / rel
        if not source.is_file():
            raise ValueError("BLOCKER source missing: " + str(source))
        new = source.read_bytes()
        target = doc_file(docroot, rel, allow_assets=True)
        old = target.read_bytes() if target.is_file() else None
        if old == new:
            continue
        if old is not None:
            raise ValueError("BLOCKER existing V7 shared asset differs: " + str(rel))
        changes[rel] = new
        before[rel] = None
    known = whitelist_standalone_paths()
    for rel in sorted(known):
        dest = doc_file(docroot, rel)
        if not dest.is_file():
            notes.append("SKIP_NOT_PUBLISHED " + str(rel))
            continue
        old = dest.read_bytes()
        if b"</head>" not in old.lower() or b"<body" not in old.lower():
            notes.append("SKIP_NON_STANDALONE " + str(rel))
            continue
        updated = patch_head(old, rel)
        if updated != old:
            changes[rel] = updated
            before[rel] = sha(old)
    return changes, before, notes

def entry_mode_for(rel):
    if rel in ASSETS:
        return "asset-new"
    return "head-patch"

def resolve_apply_bytes(rel, dest, planned, expected_sha):
    """Re-read target; verify or rebuild patch immediately before write."""
    if rel in ASSETS:
        if dest.is_file():
            if expected_sha is not None and sha(dest.read_bytes()) != expected_sha:
                raise ValueError("BLOCKER concurrent edit detected " + str(rel))
            raise ValueError("BLOCKER asset already on disk: " + str(rel))
        return planned
    if not dest.is_file():
        raise ValueError("BLOCKER missing patch target " + str(rel))
    existing = dest.read_bytes()
    current = sha(existing)
    if current != expected_sha:
        try:
            rebuilt = patch_head(existing, rel)
        except ValueError:
            raise ValueError("BLOCKER concurrent edit detected " + str(rel))
        if rebuilt == existing:
            return None
        return rebuilt
    rebuilt = patch_head(existing, rel)
    if rebuilt == existing:
        return None
    return rebuilt

def atomic(dest, data):
    dest.parent.mkdir(parents=True, exist_ok=True)
    fd, tmp = tempfile.mkstemp(prefix=".lsv7-", dir=str(dest.parent))
    try:
        with os.fdopen(fd, "wb") as f:
            f.write(data)
        os.chmod(tmp, dest.stat().st_mode & 0o777 if dest.is_file() else 0o644)
        os.replace(tmp, dest)
    finally:
        if os.path.exists(tmp):
            os.unlink(tmp)

def rollback(docroot, backup):
    manifest = json.loads((backup / "manifest.json").read_text(encoding="utf-8"))
    if manifest.get("version") != VERSION:
        raise ValueError("BLOCKER backup version mismatch")
    for entry in manifest["files"]:
        rel = Path(entry["path"])
        dest = doc_file(docroot, rel, allow_assets=True)
        mode = entry.get("mode") or entry_mode_for(rel)
        if mode == "head-patch":
            if not dest.is_file():
                raise ValueError("BLOCKER missing patched file " + str(rel))
            atomic(dest, strip_v7_head_block(dest.read_bytes()))
            continue
        if mode == "asset-new":
            if dest.is_file():
                dest.unlink()
            continue
        if entry["exists"]:
            original = backup / rel
            if not original.is_file():
                raise ValueError("BLOCKER missing backup " + str(rel))
            atomic(dest, original.read_bytes())
        elif dest.is_file():
            dest.unlink()
    print("ROLLED_BACK", backup)

def run(argv=None):
    ap = argparse.ArgumentParser(description=__doc__)
    ap.add_argument("--docroot", required=True)
    ap.add_argument("--backup-root")
    ap.add_argument("--apply", action="store_true")
    ap.add_argument("--rollback", metavar="BACKUP_DIRECTORY")
    ap.add_argument("--no-scan", action="store_true",
                    help="legacy flag (installer always uses manifest whitelist)")
    ap.add_argument("--php-bin", default="php")
    args = ap.parse_args(argv)
    docroot = Path(args.docroot).expanduser().resolve()
    if not docroot.is_dir():
        raise ValueError("BLOCKER DOCROOT not found")
    backup_root = (Path(args.backup_root).expanduser().resolve() if args.backup_root
                   else docroot.parent / "lireate-sidebar-v7-backups")
    if backup_root == docroot or docroot in backup_root.parents:
        raise ValueError("BLOCKER backup must be outside DOCROOT")
    if args.rollback:
        rollback(docroot, Path(args.rollback).expanduser().resolve())
        return 0
    changes, before, notes = plan(docroot, scan=not args.no_scan)
    for line in notes:
        print(line)
    for rel in changes:
        print(("APPLY" if args.apply else "DRY_RUN"), rel)
    if not changes:
        print("NO_CHANGES")
        return 0
    if not args.apply:
        print("DRY_RUN_ONLY; no files changed")
        return 0
    php = shutil.which(args.php_bin) or (args.php_bin if Path(args.php_bin).is_file() else None)
    if not php:
        raise ValueError("BLOCKER PHP CLI unavailable")
    stamp = datetime.datetime.now(datetime.timezone.utc).strftime("%Y%m%dT%H%M%S%fZ")
    backup = backup_root / stamp
    backup.mkdir(parents=True, exist_ok=False)
    manifest = {"version": VERSION, "files": []}
    for rel in changes:
        dest = doc_file(docroot, rel, allow_assets=True)
        had = dest.is_file()
        entry_mode = entry_mode_for(rel)
        manifest["files"].append({"path": str(rel), "exists": had, "mode": entry_mode})
        if had:
            archived = backup / rel
            archived.parent.mkdir(parents=True, exist_ok=True)
            shutil.copy2(dest, archived)
    (backup / "manifest.json").write_text(
        json.dumps(manifest, ensure_ascii=False, indent=2), encoding="utf-8")
    try:
        for rel, planned in changes.items():
            dest = doc_file(docroot, rel, allow_assets=True)
            payload = resolve_apply_bytes(rel, dest, planned, before[rel])
            if payload is None:
                continue
            atomic(dest, payload)
        for rel in changes:
            if rel.suffix != ".php":
                continue
            proc = subprocess.run([php, "-l", str(docroot / rel)],
                                  capture_output=True, text=True, timeout=30)
            if proc.returncode:
                raise RuntimeError("BLOCKER php -l failed " + str(rel) + ": "
                                   + proc.stdout + " " + proc.stderr)
    except Exception:
        rollback(docroot, backup)
        raise
    print("APPLIED backup=" + str(backup))
    print("MANDATORY: run browser audit; skipped routes and JS/CSS conflicts may need Cursor review")
    return 0

if __name__ == "__main__":
    raise SystemExit(run())