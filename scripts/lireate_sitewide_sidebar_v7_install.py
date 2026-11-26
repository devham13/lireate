#!/usr/bin/env python3
"""Install LIREATE Sidebar V7 across CMS and standalone public pages.

Default: dry-run; only with --apply are files written.  Detects PHP/HTML
front controllers in DOCROOT (depth<=3), plus all service manifest routes.
Never touches admin/API/cache/uploads/third-party code or existing page content.
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
EXCLUDED_SEGMENTS = {
    "admin","administrator","api","ajax","auth","backups","backup",
    "bitrix","cache","cgi-bin","cgi","cron","data","dist","assets",
    "includes","inc","lib","logs","node_modules","old","temp","tmp",
    "private","test","tests","preview","vendor","wp-admin","wp-content",
    "wp-includes","upload","uploads","media",".git",".well-known",
    "template","storage","system","_shared","scripts","config",
}
# Only the actual DOCROOT's top-level public routes and their child routes.
SCAN_DEPTH = 3
EXTENSIONS = {".php",".html",".htm"}

def sha(data):
    return hashlib.sha256(data).hexdigest()

def is_safe_rel(rel):
    if rel.is_absolute() or ".." in rel.parts:
        return False
    return not any(part.lower() in EXCLUDED_SEGMENTS or part.startswith(".")
                   for part in rel.parts)

def doc_file(docroot, rel, allow_assets=False):
    if rel == MAIN or rel in ASSETS:
        pass
    elif not is_safe_rel(rel):
        raise ValueError("BLOCKER unsafe path: " + str(rel))
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
    """Use the real publish.manifest.json url_path mapping, not guessed slugs."""
    service_root = ROOT / "pages" / "services"
    seen = set()
    for child in sorted(service_root.iterdir()):
        if not child.is_dir() or not (child / "index.php").is_file():
            continue
        manifest = child / "publish.manifest.json"
        if not manifest.is_file():
            continue
        record = json.loads(manifest.read_text(encoding="utf-8"))
        path = record.get("url_path", "").strip("/")
        rel = Path(path) / "index.php"
        if not path or not is_safe_rel(rel):
            raise ValueError("BLOCKER bad service url_path in manifest: " + str(manifest))
        if rel in seen:
            raise ValueError("BLOCKER duplicate URL: " + str(rel))
        seen.add(rel)
    return seen

def discover_public_frontends(docroot):
    """Only HTML-producing index.php/index.html in shallow public directories."""
    discovered = set()
    for current, dirs, names in os.walk(str(docroot), followlinks=False):
        rel_dir = Path(current).relative_to(docroot)
        depth = len(rel_dir.parts)
        if depth >= SCAN_DEPTH:
            dirs[:] = []
        else:
            dirs[:] = [d for d in dirs if
                       d.lower() not in EXCLUDED_SEGMENTS and
                       not d.startswith(".") and not (Path(current)/d).is_symlink()]
        if depth > SCAN_DEPTH or (depth and not is_safe_rel(rel_dir)):
            continue
        for name in ("index.php", "index.html", "index.htm"):
            if name not in names:
                continue
            rel = rel_dir / name
            if rel == Path("index.php"):
                # CMS front controller is patched through template/main.php.
                continue
            dest = doc_file(docroot, rel)
            if dest.stat().st_size > 3_000_000:
                continue
            data = dest.read_bytes()
            lower = data.lower()
            if b"</head>" in lower and b"<body" in lower:
                discovered.add(rel)
    return discovered

def patch_head(old, rel):
    # Preserve templates, metadata, the original CMS menu, all forms & analytics.
    # If a source contains a modern V7 tag, do not append a duplicate.
    if VERSION.encode() in old or b'lireate-sitewide-sidebar-v7.js' in old:
        return old
    encoding = "utf-8"
    try:
        original = old.decode(encoding)
    except UnicodeDecodeError:
        raise ValueError("BLOCKER non-UTF-8 entrypoint " + str(rel))
    matches = list(re.finditer(r"</head\s*>", original, flags=re.I))
    if len(matches) != 1:
        raise ValueError("BLOCKER expected exactly one </head> in " + str(rel))
    # Don't alter external headless/static fragments.
    if "<body" not in original.lower():
        raise ValueError("BLOCKER no body tag in standalone " + str(rel))
    pos = matches[0].start()
    return (original[:pos] + HEAD + original[pos:]).encode(encoding)

def plan(docroot, scan=True):
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
        changes[MAIN] = new_main;before[MAIN] = sha(old_main)
    for rel in ASSETS:
        source = ROOT / rel
        if not source.is_file():
            raise ValueError("BLOCKER source missing: " + str(source))
        new = source.read_bytes()
        target = doc_file(docroot, rel, allow_assets=True)
        old = target.read_bytes() if target.is_file() else None
        if old == new:
            continue
        # Existing shared assets may belong to later versions; don't overwrite.
        if old is not None:
            raise ValueError("BLOCKER existing V7 shared asset differs: " + str(rel))
        changes[rel] = new;before[rel] = None
    known = service_route_paths()
    found = discover_public_frontends(docroot) if scan else set()
    for rel in sorted(known | found):
        dest = doc_file(docroot, rel)
        if not dest.is_file():
            notes.append("SKIP_NOT_PUBLISHED " + str(rel))
            continue
        old = dest.read_bytes()
        if b"</head>" not in old.lower() or b"<body" not in old.lower():
            notes.append("SKIP_NON_STANDALONE " + str(rel))
            continue
        updated = patch_head(old,rel)
        if updated!=old:
            changes[rel] = updated;before[rel] = sha(old)
    return changes,before,notes

def atomic(dest, data):
    dest.parent.mkdir(parents=True,exist_ok=True)
    fd,tmp=tempfile.mkstemp(prefix=".lsv7-",dir=str(dest.parent))
    try:
        with os.fdopen(fd,"wb") as f:f.write(data)
        os.chmod(tmp,dest.stat().st_mode & 0o777 if dest.is_file() else 0o644)
        os.replace(tmp,dest)
    finally:
        if os.path.exists(tmp):os.unlink(tmp)

def rollback(docroot,backup):
    manifest=json.loads((backup/"manifest.json").read_text(encoding="utf-8"))
    if manifest.get("version")!=VERSION:raise ValueError("BLOCKER backup version mismatch")
    for entry in manifest["files"]:
        rel=Path(entry["path"])
        # Restore only paths originally changed by this installer.
        dest=doc_file(docroot,rel,allow_assets=True)
        if entry["exists"]:
            original=backup/rel
            if not original.is_file():raise ValueError("BLOCKER missing backup "+str(rel))
            atomic(dest,original.read_bytes())
        elif dest.is_file():dest.unlink()
    print("ROLLED_BACK",backup)

def run(argv=None):
    ap=argparse.ArgumentParser(description=__doc__)
    ap.add_argument("--docroot",required=True)
    ap.add_argument("--backup-root")
    ap.add_argument("--apply",action="store_true")
    ap.add_argument("--rollback",metavar="BACKUP_DIRECTORY")
    ap.add_argument("--no-scan",action="store_true",help="only manifest-listed services plus CMS main")
    ap.add_argument("--php-bin",default="php")
    args=ap.parse_args(argv)
    docroot=Path(args.docroot).expanduser().resolve()
    if not docroot.is_dir():raise ValueError("BLOCKER DOCROOT not found")
    backup_root=(Path(args.backup_root).expanduser().resolve() if args.backup_root
                 else docroot.parent/"lireate-sidebar-v7-backups")
    if backup_root==docroot or docroot in backup_root.parents:
        raise ValueError("BLOCKER backup must be outside DOCROOT")
    if args.rollback:
        rollback(docroot,Path(args.rollback).expanduser().resolve());return 0
    changes,before,notes=plan(docroot,scan=not args.no_scan)
    for line in notes:print(line)
    for rel in changes:print(("APPLY" if args.apply else "DRY_RUN"),rel)
    if not changes:print("NO_CHANGES");return 0
    if not args.apply:
        print("DRY_RUN_ONLY; no files changed");return 0
    php=shutil.which(args.php_bin) or (args.php_bin if Path(args.php_bin).is_file() else None)
    if not php:raise ValueError("BLOCKER PHP CLI unavailable")
    stamp=datetime.datetime.now(datetime.timezone.utc).strftime("%Y%m%dT%H%M%S%fZ")
    backup=backup_root/stamp
    backup.mkdir(parents=True,exist_ok=False)
    manifest={"version":VERSION,"files":[]}
    for rel in changes:
        dest=doc_file(docroot,rel,allow_assets=True)
        had=dest.is_file()
        manifest["files"].append({"path":str(rel),"exists":had})
        if had:
            archived=backup/rel
            archived.parent.mkdir(parents=True,exist_ok=True)
            shutil.copy2(dest,archived)
    (backup/"manifest.json").write_text(json.dumps(manifest,ensure_ascii=False,indent=2),encoding="utf-8")
    try:
        for rel,data in changes.items():
            dest=doc_file(docroot,rel,allow_assets=True)
            existing=sha(dest.read_bytes()) if dest.is_file() else None
            if existing!=before[rel]:
                raise ValueError("BLOCKER concurrent edit detected " + str(rel))
            atomic(dest,data)
        for rel in changes:
            if rel.suffix!=".php":continue
            proc=subprocess.run([php,"-l",str(docroot/rel)],capture_output=True,text=True,timeout=30)
            if proc.returncode:
                raise RuntimeError("BLOCKER php -l failed "+str(rel)+": "+proc.stdout+" "+proc.stderr)
    except Exception:
        rollback(docroot,backup)
        raise
    print("APPLIED backup="+str(backup))
    print("MANDATORY: run browser audit; skipped routes and JS/CSS conflicts may need Cursor review")
    return 0

if __name__=="__main__":
    raise SystemExit(run())
