#!/usr/bin/env python3
"""Apply SEO-audit CMS metadata (P1) and content replacements on DOCROOT or check mode."""

from __future__ import annotations

import argparse
import json
import os
import sys
from pathlib import Path

SCRIPT_DIR = Path(__file__).resolve().parent
REPO_ROOT = SCRIPT_DIR.parent
if str(SCRIPT_DIR) not in sys.path:
    sys.path.insert(0, str(SCRIPT_DIR))

import lireate_seo_audit_meta_lib as lib  # noqa: E402


def resolve_office_repo() -> Path:
    explicit = os.environ.get("LIREATE_OFFICE_REPO", "").strip()
    if explicit:
        return Path(explicit).expanduser().resolve()
    return REPO_ROOT


def resolve_docroot(args: argparse.Namespace) -> Path | None:
    if args.scope == "office":
        return resolve_office_repo()
    raw = args.docroot or os.environ.get("LIREATE_DOCROOT", "").strip()
    if not raw:
        raise SystemExit("BLOCKER: set --docroot or LIREATE_DOCROOT")
    return Path(raw).expanduser().resolve()


def cmd_meta_check(root: Path, office_repo: Path) -> int:
    hits = lib.scan_meta_tree(root, office_repo)
    print("cms_routes: {0}".format(lib.CMS_ROUTES_REL))
    print("metadata: {0}".format(lib.META_MANIFEST_REL))
    print("root: {0}".format(root))
    print("pages matched: {0}".format(len(hits)))
    for path, meta, n in hits:
        try:
            rel = path.relative_to(root)
        except ValueError:
            rel = path
        print("  {0} <- {1} ({2} fields)".format(rel, meta.get("path"), n))
    missing = {m["path"] for m in lib.load_cms_metadata(office_repo)} - {h[1]["path"] for h in hits}
    for path in sorted(missing):
        print("  MISSING template for {0}".format(path))
    print("")
    print("SKIP (never touched): data/db.php")
    return 0


def cmd_meta_apply(root: Path, office_repo: Path, backups_root: Path) -> int:
    hits = lib.scan_meta_tree(root, office_repo)
    if not hits:
        print("STATUS: no CMS metadata changes needed")
        return 0
    backup_id = lib.make_backup_id()
    backup_dir = backups_root / backup_id
    lib.backup_files(backup_dir, root, [p for p, _, _ in hits])
    applied = lib.apply_meta_tree(root, office_repo)
    manifest = {
        "backup_id": backup_id,
        "files": [str(p.relative_to(root)) for p, _, _ in applied],
    }
    (backup_dir / "created.json").write_text(
        json.dumps(manifest, ensure_ascii=False, indent=2) + "\n",
        encoding="utf-8",
    )
    print("backup: {0}".format(backup_dir))
    print("patched: {0} files".format(len(applied)))
    for path, meta, n in applied:
        print("  {0} ({1})".format(path.relative_to(root), meta.get("path")))
    return 0


def cmd_content_check(root: Path, office_repo: Path, include_html: bool) -> int:
    hits = lib.scan_content_tree(root, office_repo, include_html=include_html)
    print("content manifest: {0}".format(lib.CONTENT_MANIFEST_REL))
    print("root: {0}".format(root))
    print("files with text changes: {0}".format(len(hits)))
    for path, n in hits[:50]:
        try:
            rel = path.relative_to(root)
        except ValueError:
            rel = path
        print("  {0} ({1} replacements)".format(rel, n))
    return 0


def cmd_content_apply(root: Path, office_repo: Path, include_html: bool, backups_root: Path) -> int:
    hits = lib.scan_content_tree(root, office_repo, include_html=include_html)
    if not hits:
        print("STATUS: no content replacements needed")
        return 0
    backup_id = lib.make_backup_id()
    backup_dir = backups_root / backup_id
    lib.backup_files(backup_dir, root, [p for p, _ in hits])
    applied = lib.apply_content_tree(root, office_repo, include_html=include_html)
    (backup_dir / "created.json").write_text(
        json.dumps(
            {
                "backup_id": backup_id,
                "files": [str(p.relative_to(root)) for p, _ in applied],
            },
            ensure_ascii=False,
            indent=2,
        )
        + "\n",
        encoding="utf-8",
    )
    print("backup: {0}".format(backup_dir))
    print("patched: {0} files".format(len(applied)))
    return 0


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--check", action="store_true", help="dry-run")
    parser.add_argument("--apply", action="store_true", help="backup and patch")
    parser.add_argument(
        "--mode",
        choices=("meta", "content", "all"),
        default="all",
        help="meta = Title/Description/H1 CMS pages; content = text manifest (1478)",
    )
    parser.add_argument("--scope", choices=("docroot", "office"), default="docroot")
    parser.add_argument("--docroot", help="Hosting DOCROOT")
    parser.add_argument("--include-html", action="store_true")
    parser.add_argument(
        "--backups-root",
        default=os.environ.get("LIREATE_AUTOPUB_HOME", str(Path.home() / "lireate-autopub"))
        + "/backups/seo-audit-meta",
    )
    args = parser.parse_args()
    if not args.check and not args.apply:
        parser.error("use --check or --apply")
    office_repo = resolve_office_repo()
    root = resolve_docroot(args)
    backups_root = Path(args.backups_root)
    if args.mode in ("meta", "all"):
        if args.check:
            cmd_meta_check(root, office_repo)
        elif args.apply:
            cmd_meta_apply(root, office_repo, backups_root)
    if args.mode in ("content", "all"):
        if args.check:
            cmd_content_check(root, office_repo, args.include_html)
        elif args.apply:
            cmd_content_apply(root, office_repo, args.include_html, backups_root)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())