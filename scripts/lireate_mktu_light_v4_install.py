#!/usr/bin/env python3
"""Install Light V4 CSS for /mktu on hosting DOCROOT (main.php head patch only)."""

import argparse
import shutil
import sys
from datetime import datetime, timezone
from pathlib import Path

SCRIPT_DIR = Path(__file__).resolve().parent
REPO = SCRIPT_DIR.parent
sys.path.insert(0, str(SCRIPT_DIR))

import lireate_mktu_light_v4_install_lib as lib  # noqa: E402
def main():
    parser = argparse.ArgumentParser(description="LIREATE /mktu Light V4 install")
    parser.add_argument("--docroot", required=True, help="Hosting DOCUMENT_ROOT")
    parser.add_argument("--check", action="store_true", help="Dry-run")
    parser.add_argument("--apply", action="store_true", help="Write files + patch main.php")
    args = parser.parse_args()

    docroot = Path(args.docroot).expanduser().resolve()
    office_repo = REPO
    main_path = docroot / lib.MAIN_REL
    backups_root = Path.home() / "lireate-autopub" / "backups"

    if not main_path.is_file():
        print("BLOCKER: missing", main_path)
        return 2

    pairs = lib.copy_plan(office_repo)
    original = main_path.read_text(encoding="utf-8", errors="replace")
    planned, changed, note = lib.plan_main_head_patch(original)

    print("=== MKTU LIGHT V4 PLAN ===")
    print(note)
    for _src, rel in pairs:
        print("copy:", rel)

    if args.check or not args.apply:
        if "BLOCKER" in note:
            print(note)
            return 2
        print("STATUS: MKTU_LIGHT_V4_CHECK_OK")
        return 0

    if "BLOCKER" in note:
        print(note)
        return 2

    backup_id = datetime.now(timezone.utc).strftime("%Y%m%d-%H%M%S")
    backup_dir = backups_root / ("mktu-light-v4-" + backup_id)
    backup_dir.mkdir(parents=True, exist_ok=True)
    shutil.copy2(main_path, backup_dir / "template" / "main.php")

    lib.apply_css_copies(office_repo, docroot, pairs)
    if changed:
        main_path.write_text(planned, encoding="utf-8")
    php_bin = lib.resolve_php_bin()
    lint_err = lib.lint_php_content(php_bin, main_path.read_text(encoding="utf-8"), "main.php")
    if lint_err:
        print("BLOCKER:", lint_err)
        return 2

    print("backup:", backup_dir)
    print("rollback: python3 scripts/lireate_mktu_light_v4_install.py --docroot ... --rollback", backup_id)
    print("STATUS: MKTU_LIGHT_V4_APPLIED")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())