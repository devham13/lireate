#!/usr/bin/env python3
"""Isolated /avtor/ install (check only — no publish from Cloud)."""

import argparse
import os
import sys
from pathlib import Path

SCRIPT_DIR = Path(__file__).resolve().parent
REPO_ROOT = SCRIPT_DIR.parent
if str(SCRIPT_DIR) not in sys.path:
    sys.path.insert(0, str(SCRIPT_DIR))

import lireate_avtor_install_lib as lib  # noqa: E402


def resolve_office_repo():
    explicit = os.environ.get("LIREATE_OFFICE_REPO", "").strip()
    return Path(explicit).expanduser().resolve() if explicit else REPO_ROOT


def cmd_check(office_repo, docroot):
    php_bin = lib.resolve_php_bin()
    pairs = lib.copy_plan(office_repo)
    print("=== DEPLOY MANIFEST (/avtor/ only) ===")
    for rel in lib.deploy_file_list():
        print("  ", rel)
    print("\n=== NEVER touch on /avtor/ rollout ===")
    for rel in lib.NEVER_DEPLOY:
        print("  ", rel)
    print("\n=== COPY plan ===")
    for src, rel in pairs:
        print("{0} -> {1}".format(src, Path(docroot) / rel))
    main = lib.find_main_template(docroot)
    if main is None:
        print("WARN: no template/main.php under docroot")
        return 1
    original = main.read_text(encoding="utf-8", errors="replace")
    planned = lib.plan_main_php(original)
    err = lib.lint_php_content(php_bin, planned, "main.php")
    if err:
        print("FAIL:", err)
        return 1
    print("OK: php -l planned main.php patch")
    return 0


def cmd_list():
    print("AVTOR_ISOLATED_DEPLOY_FILES:")
    for rel in lib.deploy_file_list():
        print(rel)
    return 0


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--check", action="store_true")
    parser.add_argument("--list", action="store_true", help="Print isolated deploy file list")
    parser.add_argument("--docroot", default=os.environ.get("LIREATE_DOCROOT", ""))
    args = parser.parse_args()
    office_repo = resolve_office_repo()
    if args.list:
        return cmd_list()
    if args.check:
        if not args.docroot.strip():
            raise SystemExit("BLOCKER: --docroot or LIREATE_DOCROOT")
        return cmd_check(office_repo, Path(args.docroot).expanduser().resolve())
    parser.print_help()
    return 0


if __name__ == "__main__":
    raise SystemExit(main())