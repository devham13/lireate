#!/usr/bin/env python3
"""Install LIREATE docs CMS shell into hosting DOCROOT (check / apply / rollback)."""

import argparse
import os
import sys
from pathlib import Path

SCRIPT_DIR = Path(__file__).resolve().parent
REPO_ROOT = SCRIPT_DIR.parent
if str(SCRIPT_DIR) not in sys.path:
    sys.path.insert(0, str(SCRIPT_DIR))

import lireate_docs_install_lib as lib  # noqa: E402


def resolve_office_repo():
    explicit = os.environ.get("LIREATE_OFFICE_REPO", "").strip()
    if explicit:
        return Path(explicit).expanduser().resolve()
    return REPO_ROOT


def default_env_file_path():
    explicit = os.environ.get("LIREATE_ENV_FILE", "").strip()
    if explicit:
        return Path(explicit).expanduser()
    autopub = os.environ.get("LIREATE_AUTOPUB_HOME", "").strip() or str(Path.home() / "lireate-autopub")
    return Path(autopub).expanduser() / ".env"


def bootstrap_env(env_file):
    from lireate_dotenv import load_env_file

    env_file = Path(env_file).expanduser()
    if not env_file.is_file():
        raise SystemExit("BLOCKER: env file not found: {0}".format(env_file))
    load_env_file(env_file)
    return env_file


def cmd_check(office_repo, docroot):
    php_bin = lib.resolve_php_bin()
    pairs = lib.copy_plan(office_repo)
    index_path = docroot / lib.TEMPLATE_REL / "index.php"
    main_path = docroot / lib.MAIN_REL
    print("docroot: {0}".format(docroot))
    print("office_repo: {0}".format(office_repo))
    print("php_bin: {0}".format(php_bin))
    print("=== COPY (office-repo → DOCROOT) ===")
    for src, rel in pairs:
        print("{0}  →  {1}".format(src, docroot / rel))
    print("")
    exit_code = 0
    print("=== DOCS INDEX PATCH (template/page/docs/index.php) ===")
    if not index_path.is_file():
        print("WARN: {0} missing — using office fixture for php -l only".format(index_path))
        fixture = office_repo / "scripts/fixtures/cms-docs-index-fragment.php"
        original = fixture.read_text(encoding="utf-8", errors="replace")
    else:
        original = index_path.read_text(encoding="utf-8", errors="replace")
    planned_index = lib.plan_docs_index_content(original)
    if planned_index != original:
        diff = lib.unified_diff(original, planned_index, "template/page/docs/index.php")
        if diff:
            print(diff.rstrip())
    lint_err = lib.validate_planned_php(php_bin, original, planned_index, "template/page/docs/index.php")
    print("=== php -l (docs index.php planned) ===")
    if lint_err:
        print("FAIL: {0}".format(lint_err))
        exit_code = 1
    else:
        print("OK")
    print("")
    print("=== MAIN.PHP BODY CLASS PATCH ===")
    live_fixture = office_repo / "scripts/fixtures/cms-main-live.snapshot.php"
    if main_path.is_file():
        main_original = main_path.read_text(encoding="utf-8", errors="replace")
        main_label = "template/main.php"
    elif live_fixture.is_file():
        main_original = live_fixture.read_text(encoding="utf-8", errors="replace")
        main_label = "scripts/fixtures/cms-main-live.snapshot.php (production snapshot)"
        print("WARN: docroot main.php missing — linting live snapshot fixture")
    else:
        print("BLOCKER: no main.php for body-class patch check")
        return 1
    planned_main = lib.plan_main_body_content(main_original)
    lint_main = lib.validate_planned_php(php_bin, main_original, planned_main, main_label)
    print("=== php -l (main.php planned) ===")
    if lint_main:
        print("FAIL: {0}".format(lint_main))
        exit_code = 1
    else:
        print("OK: No syntax errors detected in {0}".format(main_label))
    print("")
    print("SKIP (never touched): data/db.php, docroot/.htaccess")
    return exit_code


def cmd_apply(office_repo, docroot, backups_root):
    php_bin = lib.resolve_php_bin()
    backup_id = lib.make_backup_id()
    backup_dir = backups_root / backup_id
    pairs = lib.copy_plan(office_repo)
    rels = [rel for _src, rel in pairs]
    index_path = lib.find_docs_index_template(docroot)
    main_path = lib.find_main_template(docroot)
    if index_path is not None:
        rels.append(index_path.relative_to(docroot))
        orig = index_path.read_text(encoding="utf-8", errors="replace")
        planned = lib.plan_docs_index_content(orig)
        err = lib.validate_planned_php(php_bin, orig, planned, index_path.relative_to(docroot))
        if err:
            raise SystemExit("BLOCKER: refuse apply — docs index.php php -l failed\n{0}".format(err))
    if main_path is not None:
        rels.append(main_path.relative_to(docroot))
        orig = main_path.read_text(encoding="utf-8", errors="replace")
        planned = lib.plan_main_body_content(orig)
        err = lib.validate_planned_php(php_bin, orig, planned, main_path.relative_to(docroot))
        if err:
            raise SystemExit("BLOCKER: refuse apply — main.php php -l failed\n{0}".format(err))
    lib.backup_files(backup_dir, docroot, rels)
    created, notes, _idx, _main = lib.apply_docs_install(office_repo, docroot, php_bin=php_bin)
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
    index_path = lib.find_docs_index_template(docroot)
    main_path = lib.find_main_template(docroot)
    notes = lib.rollback_docs_install(docroot, index_path, main_path)
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
    parser = argparse.ArgumentParser(description="LIREATE docs CMS install")
    parser.add_argument("--check", action="store_true")
    parser.add_argument("--apply", action="store_true")
    parser.add_argument("--rollback", metavar="BACKUP_ID")
    parser.add_argument("--docroot", default=os.environ.get("LIREATE_DOCROOT", ""))
    parser.add_argument("--env-file", default="")
    parser.add_argument("--backups-root", default=os.environ.get("LIREATE_BACKUPS_ROOT", ""))
    args = parser.parse_args()

    office_repo = resolve_office_repo()
    if args.env_file or args.apply or args.rollback:
        env_file = Path(args.env_file).expanduser() if args.env_file else default_env_file_path()
        bootstrap_env(env_file)

    docroot = args.docroot.strip()
    if not docroot and (args.apply or args.check or args.rollback):
        docroot = os.environ.get("LIREATE_DOCROOT", "").strip()
    if args.check or args.apply or args.rollback:
        if not docroot:
            raise SystemExit("BLOCKER: set --docroot or LIREATE_DOCROOT")
        docroot = Path(docroot).expanduser().resolve()

    backups_root = args.backups_root.strip() or os.environ.get(
        "LIREATE_BACKUPS_ROOT", str(Path.home() / "lireate-autopub" / "backups" / "docs-install")
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
