#!/usr/bin/env python3
"""Apply SEO-audit link manifest to hosting DOCROOT or office-repo PHP (not data/db.php)."""

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

import lireate_seo_audit_links_lib as lib  # noqa: E402


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


def resolve_manifest(args: argparse.Namespace) -> Path:
    if getattr(args, "queue", "p1") == "p2":
        return lib.MANIFEST_P2_REL
    return lib.MANIFEST_REL


def cmd_check(root: Path, office_repo: Path, include_html: bool, manifest_rel: Path) -> int:
    replacements = lib.load_replacements(office_repo, manifest_rel)
    hits = lib.scan_tree(root, replacements, include_html=include_html)
    print("manifest: {0}".format(manifest_rel))
    print("root: {0}".format(root))
    print("rules: {0}".format(len(replacements)))
    print("files with href changes: {0}".format(len(hits)))
    for path, n in hits[:40]:
        try:
            rel = path.relative_to(root)
        except ValueError:
            rel = path
        print("  {0} ({1} href)".format(rel, n))
    if len(hits) > 40:
        print("  … +{0} more".format(len(hits) - 40))
    print("")
    print("SKIP (never touched): data/db.php")
    return 0


def cmd_apply(root: Path, office_repo: Path, include_html: bool, backups_root: Path, manifest_rel: Path) -> int:
    replacements = lib.load_replacements(office_repo, manifest_rel)
    hits = lib.scan_tree(root, replacements, include_html=include_html)
    if not hits:
        print("STATUS: no href changes needed")
        return 0
    backup_id = lib.make_backup_id()
    backup_dir = backups_root / backup_id
    lib.backup_files(backup_dir, root, [p for p, _ in hits])
    applied = lib.apply_tree(root, replacements, include_html=include_html)
    manifest = {
        "backup_id": backup_id,
        "files": [str(p.relative_to(root)) for p, _ in applied],
    }
    (backup_dir / "created.json").write_text(
        json.dumps(manifest, ensure_ascii=False, indent=2) + "\n",
        encoding="utf-8",
    )
    print("backup: {0}".format(backup_dir))
    print("patched: {0} files".format(len(applied)))
    for path, n in applied[:30]:
        print("  {0} ({1})".format(path.relative_to(root), n))
    return 0


def cmd_office_sync(office_repo: Path, manifest_rel: Path) -> int:
    """Patch trackable PHP/HTML in office repo (service pages, fixtures)."""
    replacements = lib.load_replacements(office_repo, manifest_rel)
    applied = lib.apply_tree(office_repo, replacements, include_html=True)
    print("office-repo: {0}".format(office_repo))
    print("patched: {0}".format(len(applied)))
    return 0


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--check", action="store_true", help="dry-run: list files to patch")
    parser.add_argument("--apply", action="store_true", help="backup and patch DOCROOT or office scope")
    parser.add_argument(
        "--scope",
        choices=("docroot", "office"),
        default="docroot",
        help="docroot = hosting site; office = this repository",
    )
    parser.add_argument("--docroot", help="Hosting DOCROOT (scope=docroot)")
    parser.add_argument("--include-html", action="store_true", help="Also scan .html (office backups)")
    parser.add_argument(
        "--office-sync",
        action="store_true",
        help="Apply manifest to office-repo files (CI/dev)",
    )
    parser.add_argument(
        "--backups-root",
        default=os.environ.get("LIREATE_AUTOPUB_HOME", str(Path.home() / "lireate-autopub"))
        + "/backups/seo-audit-links",
    )
    parser.add_argument(
        "--queue",
        choices=("p1", "p2"),
        default="p1",
        help="p1 = link-replacements.json; p2 = link-replacements-p2.json",
    )
    args = parser.parse_args()
    office_repo = resolve_office_repo()
    manifest_rel = resolve_manifest(args)
    if args.office_sync:
        return cmd_office_sync(office_repo, manifest_rel)
    if not args.check and not args.apply:
        parser.error("use --check, --apply, or --office-sync")
    root = resolve_docroot(args)
    if args.check:
        return cmd_check(root, office_repo, args.include_html, manifest_rel)
    return cmd_apply(root, office_repo, args.include_html, Path(args.backups_root).expanduser(), manifest_rel)


if __name__ == "__main__":
    raise SystemExit(main())