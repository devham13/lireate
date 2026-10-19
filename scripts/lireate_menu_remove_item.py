#!/usr/bin/env python3
"""Remove a CMS left-nav menu item from hosting DOCROOT (check / apply / rollback)."""

import argparse
import json
import os
import sys
from pathlib import Path

SCRIPT_DIR = Path(__file__).resolve().parent
REPO_ROOT = SCRIPT_DIR.parent
if str(SCRIPT_DIR) not in sys.path:
    sys.path.insert(0, str(SCRIPT_DIR))

import lireate_blog_install_lib as blog_lib  # noqa: E402
import lireate_menu_remove_item_lib as lib  # noqa: E402


def resolve_office_repo():
    explicit = os.environ.get("LIREATE_OFFICE_REPO", "").strip()
    if explicit:
        return Path(explicit).expanduser().resolve()
    autopub = os.environ.get("LIREATE_AUTOPUB_HOME", "").strip()
    if autopub:
        candidate = Path(autopub).expanduser() / "office-repo"
        if candidate.is_dir():
            return candidate.resolve()
    return REPO_ROOT


def _truthy(name: str) -> bool:
    v = (os.environ.get(name) or "").strip().lower()
    return v in ("1", "yes", "true", "on")


def cmd_check(office_repo, docroot, slug: str):
    try:
        slug, href, title = lib.resolve_target(office_repo, slug)
    except (ValueError, json.JSONDecodeError) as exc:
        print("BLOCKER: {0}".format(exc))
        return 2

    print("=== MENU REMOVE PLAN ===")
    print("slug: {0}".format(slug))
    print("href: {0}".format(href))
    print("title: {0}".format(title))
    print("docroot: {0}".format(docroot))
    print("")
    print(
        "live reference (lireate.ru): "
        '<a href="{0}" class="leftSideNaviInSection">{1}</a>'.format(href, title)
    )
    print("marker: <!-- {0}{1} -->".format(lib.MENU_MARKER_PREFIX, slug))
    print("")

    templates = lib.find_sidebar_templates(docroot, slug, href, title)
    if not templates:
        print(
            "BLOCKER: sidebar template not found under DOCROOT "
            "(leftSideNaviInSection + marker or href)"
        )
        print(
            "hint: menu is rendered from a PHP template in DOCROOT/template/, "
            "not from data/db.php (see docs/lireate-sidebar-menu-prodlenie.ru.md)"
        )
        return 2

    any_change = False
    for template in templates:
        rel = template.relative_to(docroot)
        original = template.read_text(encoding="utf-8", errors="replace")
        patched, changed = lib.remove_menu_item(original, slug, href, title)
        print("=== TEMPLATE ===")
        print("file: {0}".format(rel))
        if not changed:
            print("status: no changes needed (item already absent)")
        else:
            any_change = True
            print("status: would remove menu block")
            diff = blog_lib.unified_diff(original, patched, str(rel))
            if diff:
                print(diff.rstrip())
        print("")

    copies = lib.scan_office_repo_sidebar_copies(office_repo, href, title)
    print("=== OFFICE-REPO SIDEBAR COPIES ===")
    if copies:
        for p in copies:
            print("found: {0}".format(p.relative_to(office_repo)))
        print(
            "note: only DOCROOT CMS template is patched by --apply; "
            "remove hard-coded copies in repo manually if listed"
        )
    else:
        print("none (no leftSideNaviInSection + this href in office-repo PHP)")
    print("")
    print("SKIP (never touched): data/db.php, docroot/.htaccess")

    if not any_change:
        print("STATUS: MENU_ALREADY_ABSENT")
        return 0
    print("STATUS: DRY_RUN — use --apply and LIREATE_ALLOW_MENU_EDIT=yes to write")
    return 0


def cmd_apply(office_repo, docroot, backups_root, slug: str):
    if not _truthy("LIREATE_ALLOW_MENU_EDIT"):
        print("BLOCKER: LIREATE_ALLOW_MENU_EDIT is not yes")
        return 1

    try:
        slug, href, title = lib.resolve_target(office_repo, slug)
    except (ValueError, json.JSONDecodeError) as exc:
        print("BLOCKER: {0}".format(exc))
        return 2

    templates = lib.find_sidebar_templates(docroot, slug, href, title)
    if not templates:
        print("BLOCKER: sidebar template not found")
        return 2

    to_patch: list[tuple[Path, str, str]] = []
    for template in templates:
        original = template.read_text(encoding="utf-8", errors="replace")
        patched, changed = lib.remove_menu_item(original, slug, href, title)
        if changed:
            to_patch.append((template, original, patched))

    if not to_patch:
        print("STATUS: MENU_ALREADY_ABSENT")
        return 0

    backup_id = blog_lib.make_backup_id()
    backup_dir = lib.backup_dir_for_id(backups_root, backup_id)
    rels = [t.relative_to(docroot) for t, _, _ in to_patch]
    blog_lib.backup_files(backup_dir, docroot, rels)

    for template, _, patched in to_patch:
        template.write_text(patched, encoding="utf-8")
        print("patched: {0}".format(template.relative_to(docroot)))

    base = blog_lib.site_base_url()
    if not base:
        print("WARN: no public URL in env — skipping HTTP checks")
        print("backup: {0}".format(backup_dir))
        print("STATUS: OK (files patched, backup {0})".format(backup_id))
        return 0

    page_path = href if href.startswith("/") else "/" + href
    errors = lib.verify_menu_http(base, href, title, page_path)
    if errors:
        print("HTTP verify failed:")
        for err in errors:
            print("  - {0}".format(err))
        print("rolling back backup {0}...".format(backup_id))
        blog_lib.restore_backup(backup_dir, docroot)
        print("STATUS: ROLLED BACK ({0})".format(backup_id))
        return 1

    print("HTTP OK: homepage without menu link; {0}{1} → 200".format(base, page_path))
    print("backup: {0}".format(backup_dir))
    print("STATUS: OK (backup {0})".format(backup_id))
    return 0


def cmd_rollback(backups_root, docroot, backup_id: str):
    backup_dir = lib.backup_dir_for_id(backups_root, backup_id)
    if not backup_dir.is_dir():
        print("BLOCKER: backup not found: {0}".format(backup_dir))
        return 2
    blog_lib.restore_backup(backup_dir, docroot)
    print("STATUS: restored backup {0}".format(backup_id))
    return 0


def main(argv=None):
    parser = argparse.ArgumentParser(
        description="Remove LIREATE CMS left sidebar menu item (hosting DOCROOT)"
    )
    parser.add_argument(
        "--env-file",
        default=os.environ.get("LIREATE_ENV_FILE", ""),
        help="Path to .env with LIREATE_DOCROOT",
    )
    parser.add_argument(
        "--slug",
        default=lib.DEFAULT_REMOVE_SLUG,
        help="Service slug (default: {0})".format(lib.DEFAULT_REMOVE_SLUG),
    )
    parser.add_argument(
        "--check",
        action="store_true",
        help="Dry run: show template path and diff (default)",
    )
    parser.add_argument("--apply", action="store_true", help="Backup, patch, verify HTTP")
    parser.add_argument(
        "--rollback",
        metavar="TIMESTAMP",
        help="Restore backup menu-remove-TIMESTAMP",
    )
    args = parser.parse_args(argv)

    env_file = args.env_file.strip()
    if not env_file:
        raise SystemExit("BLOCKER: set LIREATE_ENV_FILE or pass --env-file")

    blog_lib.load_env_file(env_file)
    office_repo = resolve_office_repo()
    office_repo, docroot, backups_root, _ = blog_lib.resolve_paths(office_repo, env_file)
    slug = args.slug.strip()

    if args.rollback:
        return cmd_rollback(backups_root, docroot, args.rollback.strip())
    if args.apply:
        return cmd_apply(office_repo, docroot, backups_root, slug)
    return cmd_check(office_repo, docroot, slug)


if __name__ == "__main__":
    raise SystemExit(main())
