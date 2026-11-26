#!/usr/bin/env python3
"""Review/install/rollback portfolio listing + 14 case pages Light V5 (CMS).

No database, SEO metadata, service pages, existing case data, form handlers or
video embeds are changed. Dry-run is the default. A verified backup is required.
Tested against Python 3.8+ and the existing PHP CMS template/main.php.
"""
import argparse
import datetime
import json
import os
import pathlib
import re
import shutil
import subprocess
import tempfile

REPO = pathlib.Path(__file__).resolve().parent.parent
VERSION = "lireate-portfolio-v5-20261126"
MAIN = pathlib.Path("template/main.php")
CSS = pathlib.Path("template/css/lireate-portfolio-v5.css")
JS = pathlib.Path("template/js/lireate-portfolio-v5.js")
FILES = [MAIN, CSS, JS]
HEAD = (
    "<!-- " + VERSION + " -->\n"
    "<?php /* " + VERSION + " portfolio head: listing + 14 case slugs only */ "
    "$_lx_pf5 = (isset($temp['page']) && ($temp['page'] === 'portfolio' || "
    "strpos((string) $temp['page'], 'portfolio/') === 0)); "
    "if (!$_lx_pf5) { "
    "$_lx_pf5_ru = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : ''; "
    "$_lx_pf5 = (bool) preg_match('#^/portfolio(?:[/?]|$)#', $_lx_pf5_ru); } "
    "if ($_lx_pf5): ?>\n"
    '<link rel="preconnect" href="https://fonts.googleapis.com" />\n'
    '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />\n'
    '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" />\n'
    '<link rel="stylesheet" href="/template/css/lireate-portfolio-v5.css?v=20261126c">\n'
    '<script defer src="/template/js/lireate-portfolio-v5.js?v=20261126c"></script>\n'
    "<?php endif; ?>\n"
)


def dest_for(docroot, relative):
    docroot = pathlib.Path(docroot).resolve()
    result = docroot / relative
    if result.is_symlink() or any(p.is_symlink() for p in result.parents if p != docroot and docroot in p.parents):
        raise ValueError("BLOCKER: symlink in target path " + str(relative))
    if result.exists() and not result.is_file():
        raise ValueError("BLOCKER: not a regular file " + str(relative))
    return result


def build_plan(docroot):
    main = dest_for(docroot, MAIN)
    if not main.is_file():
        raise ValueError("BLOCKER: live template/main.php not found")
    text = main.read_text(encoding="utf-8")
    if VERSION in text:
        patched = text
    else:
        if len(re.findall(r"</head\s*>", text, flags=re.I)) != 1:
            raise ValueError("BLOCKER: expected exactly one closing </head> in main.php")
        patched = re.sub(r"</head\s*>", lambda m: HEAD + m.group(0), text, count=1, flags=re.I)
    desired = {MAIN: patched.encode("utf-8")}
    for rel in (CSS, JS):
        src = REPO / rel
        if not src.is_file():
            raise ValueError("BLOCKER: missing source asset " + str(rel))
        desired[rel] = src.read_bytes()
    return {rel: data for rel, data in desired.items()
            if not dest_for(docroot, rel).is_file()
            or dest_for(docroot, rel).read_bytes() != data}


def atomic_replace(path, data):
    path.parent.mkdir(parents=True, exist_ok=True)
    fd, tmp = tempfile.mkstemp(prefix=".lxpf5-", dir=str(path.parent))
    try:
        with os.fdopen(fd, "wb") as stream:
            stream.write(data)
        os.chmod(tmp, path.stat().st_mode & 0o777 if path.exists() else 0o644)
        os.replace(tmp, str(path))
    finally:
        if os.path.exists(tmp):
            os.unlink(tmp)


def backup_files(docroot, plan, backup_dir):
    backup_dir.mkdir(parents=True, exist_ok=False)
    manifest = {"version": VERSION, "entries": []}
    for rel in plan:
        path = dest_for(docroot, rel)
        existed = path.is_file()
        manifest["entries"].append({"path": str(rel), "existed": existed})
        if existed:
            dest = backup_dir / rel
            dest.parent.mkdir(parents=True, exist_ok=True)
            shutil.copy2(str(path), str(dest))
    (backup_dir / "manifest.json").write_text(
        json.dumps(manifest, ensure_ascii=False, indent=2), encoding="utf-8")
    return manifest


def rollback(docroot, backup_dir):
    manifest_path = backup_dir / "manifest.json"
    if not manifest_path.is_file():
        raise ValueError("BLOCKER: backup manifest not found")
    manifest = json.loads(manifest_path.read_text(encoding="utf-8"))
    if manifest.get("version") != VERSION:
        raise ValueError("BLOCKER: wrong backup version")
    for entry in manifest["entries"]:
        rel = pathlib.Path(entry["path"])
        if rel not in FILES:
            raise ValueError("BLOCKER: unexpected backup entry " + str(rel))
        dst = dest_for(docroot, rel)
        if entry["existed"]:
            src = backup_dir / rel
            if not src.is_file():
                raise ValueError("BLOCKER: missing backed-up file " + str(rel))
            atomic_replace(dst, src.read_bytes())
        elif dst.is_file():
            dst.unlink()
    print("ROLLED_BACK " + str(backup_dir))


def cli(argv=None):
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--docroot", required=True, help="real live DOCROOT, not office repo")
    parser.add_argument("--backup-root", default=None, help="directory outside DOCROOT")
    parser.add_argument("--apply", action="store_true", help="perform host writes after dry-run")
    parser.add_argument("--rollback", metavar="BACKUP_DIR", help="restore backup generated by --apply")
    parser.add_argument("--php-bin", default="php", help="PHP CLI executable for post-install lint")
    args = parser.parse_args(argv)
    docroot = pathlib.Path(args.docroot).expanduser().resolve()
    backups = (pathlib.Path(args.backup_root).expanduser().resolve() if args.backup_root
               else (docroot.parent / "lireate-portfolio-backups").resolve())
    if backups == docroot or docroot in backups.parents:
        raise ValueError("BLOCKER: backup root must be outside DOCROOT")
    if args.rollback:
        rollback(docroot, pathlib.Path(args.rollback).expanduser().resolve())
        return 0
    plan = build_plan(docroot)
    for rel in plan:
        print(("APPLY" if args.apply else "DRY_RUN") + " " + str(rel))
    if not plan:
        print("NO_CHANGES")
        return 0
    if not args.apply:
        print("DRY_RUN_ONLY no files changed")
        return 0
    php_bin = shutil.which(args.php_bin) or (args.php_bin if pathlib.Path(args.php_bin).is_file() else None)
    if not php_bin:
        raise ValueError("BLOCKER: PHP CLI not available; supply --php-bin path")
    stamp = datetime.datetime.now(datetime.timezone.utc).strftime("%Y%m%dT%H%M%SZ")
    backup_dir = backups / stamp
    backup_files(docroot, plan, backup_dir)
    try:
        for rel, data in plan.items():
            atomic_replace(dest_for(docroot, rel), data)
        lint = subprocess.run([php_bin, "-l", str(docroot / MAIN)],
                              stdout=subprocess.PIPE, stderr=subprocess.PIPE,
                              universal_newlines=True, timeout=30)
        if lint.returncode:
            raise RuntimeError("PHP syntax error: " + lint.stdout + lint.stderr)
    except Exception:
        rollback(docroot, backup_dir)
        raise
    print("INSTALLED backup=" + str(backup_dir))
    print("VERIFY: /portfolio and 14 detail URLs on 390px and 1440px; no page edits beyond global CSS/JS.")
    return 0


if __name__ == "__main__":
    raise SystemExit(cli())
