#!/usr/bin/env python3
"""Install LIREATE team CMS shell into hosting DOCROOT (check / apply / rollback)."""

import argparse
import os
import sys
from pathlib import Path

SCRIPT_DIR = Path(__file__).resolve().parent
REPO_ROOT = SCRIPT_DIR.parent
if str(SCRIPT_DIR) not in sys.path:
    sys.path.insert(0, str(SCRIPT_DIR))

import lireate_team_install_lib as lib  # noqa: E402


def resolve_office_repo():
    explicit = os.environ.get("LIREATE_OFFICE_REPO", "").strip()
    if explicit:
        return Path(explicit).expanduser().resolve()
    return REPO_ROOT


def cmd_check(office_repo, docroot):
    php_bin = lib.resolve_php_bin()
    pairs = lib.copy_plan(office_repo)
    template = lib.find_team_template(docroot)
    print("docroot: {0}".format(docroot))
    print("office_repo: {0}".format(office_repo))
    print("php_bin: {0}".format(php_bin))
    print("=== COPY (office-repo → DOCROOT) ===")
    for src, rel in pairs:
        print("{0}  →  {1}".format(src, docroot / rel))
    print("")
    print("=== CMS TEAM TEMPLATE PATCH ===")
    exit_code = 0
    if template is None:
        print("WARN: no CMS team template found under {0}/template".format(docroot))
        print("      Manual: patch file with #teamFace / #teams (often template/main.php)")
    else:
        rel = template.relative_to(docroot)
        print("file: {0}".format(rel))
        original = template.read_text(encoding="utf-8", errors="replace")
        planned = lib.plan_team_template_content(original)
        print("status: {0}".format(lib.team_template_patch_status(original, planned)))
        if planned != original:
            diff = lib.unified_diff(original, planned, str(rel))
            if diff:
                print(diff.rstrip())
        lint_err = lib.validate_planned_php(php_bin, original, planned, str(rel))
        if lint_err:
            print("FAIL: {0}".format(lint_err))
            exit_code = 1
        else:
            print("OK: php -l planned patch")
    print("")
    print("SKIP (never touched): data/db.php, leftSideNav.php, topBar.php, .htaccess")
    return exit_code


def cmd_apply(office_repo, docroot, backups_root):
    php_bin = lib.resolve_php_bin()
    backup_id = lib.make_backup_id()
    backup_dir = backups_root / backup_id
    pairs = lib.copy_plan(office_repo)
    rels = [rel for _src, rel in pairs]
    template = lib.find_team_template(docroot)
    if template is not None:
        rels.append(template.relative_to(docroot))
    lib.validate_team_patch_planned(docroot, template, php_bin=php_bin)
    lib.backup_files(backup_dir, docroot, rels)
    created, notes, _tpl = lib.apply_team_install(office_repo, docroot, php_bin=php_bin)
    lib.write_created_manifest(backup_dir, created)
    print("backup: {0}".format(backup_dir))
    if created:
        print("created: " + ", ".join(str(r) for r in created))
    for line in notes:
        print(line)
    return 0


def cmd_rollback(docroot, backups_root, backup_id):
    backup_dir = backups_root / backup_id
    manifest = backup_dir / "manifest.txt"
    if not manifest.is_file():
        raise SystemExit("BLOCKER: backup manifest not found: {0}".format(manifest))
    docroot = Path(docroot).resolve()
    template = lib.find_team_template(docroot)
    notes = lib.rollback_team_install(docroot, template)
    for rel in manifest.read_text(encoding="utf-8").splitlines():
        rel = rel.strip()
        if not rel:
            continue
        src = backup_dir / rel
        dst = docroot / rel
        if src.is_file():
            dst.parent.mkdir(parents=True, exist_ok=True)
            dst.write_bytes(src.read_bytes())
    notes.extend(lib.remove_created_files(docroot, backup_dir))
    for line in notes:
        print(line)
    print("rollback restored backup {0}".format(backup_id))
    return 0


def main():
    parser = argparse.ArgumentParser(description="LIREATE team CMS install")
    parser.add_argument("--check", action="store_true")
    parser.add_argument("--apply", action="store_true")
    parser.add_argument("--rollback", metavar="BACKUP_ID")
    parser.add_argument("--docroot", default=os.environ.get("LIREATE_DOCROOT", ""))
    parser.add_argument("--backups-root", default=os.environ.get("LIREATE_BACKUPS_ROOT", ""))
    args = parser.parse_args()

    office_repo = resolve_office_repo()
    docroot = args.docroot.strip()
    if args.check or args.apply or args.rollback:
        if not docroot:
            raise SystemExit("BLOCKER: set --docroot or LIREATE_DOCROOT")
        docroot = Path(docroot).expanduser().resolve()

    backups_root = args.backups_root.strip() or os.environ.get(
        "LIREATE_BACKUPS_ROOT", str(Path.home() / "lireate-autopub" / "backups" / "team-install")
    )
    backups_root = Path(backups_root).expanduser()

    if args.check:
        return cmd_check(office_repo, docroot)
    if args.apply:
        return cmd_apply(office_repo, docroot, backups_root)
    if args.rollback:
        return cmd_rollback(docroot, backups_root, args.rollback)
    parser.print_help()
    return 2


if __name__ == "__main__":
    raise SystemExit(main())
