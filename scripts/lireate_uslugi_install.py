#!/usr/bin/env python3
"""Install LIREATE /uslugi/ Light V4 hub into hosting DOCROOT (check / apply / rollback)."""

import argparse
import shutil
import sys
from pathlib import Path

SCRIPT_DIR = Path(__file__).resolve().parent
REPO_ROOT = SCRIPT_DIR.parent
if str(SCRIPT_DIR) not in sys.path:
    sys.path.insert(0, str(SCRIPT_DIR))

import lireate_uslugi_install_lib as lib  # noqa: E402


def cmd_check(office_repo, docroot):
    pairs, err = lib.copy_plan(office_repo)
    if err:
        print(err)
        return 2
    main = docroot / lib.MAIN_REL
    print("docroot: {0}".format(docroot))
    print("=== COPY ===")
    for src, rel in pairs:
        print("{0} -> {1}".format(src.relative_to(office_repo), docroot / rel))
    if main.is_file():
        planned, changed = lib.patch_main_uslugi_light(main.read_text(encoding="utf-8", errors="replace"))
        print("main.php uslugi light patch: {0}".format("yes" if changed else "noop"))
    else:
        print("WARN: {0} missing".format(lib.MAIN_REL))
    return 0


def cmd_apply(office_repo, docroot, backups_root):
    pairs, err = lib.copy_plan(office_repo)
    if err:
        print(err)
        return 2
    backup_id = lib.make_backup_id()
    backup_dir = backups_root / ("uslugi-install-" + backup_id)
    backup_dir.mkdir(parents=True, exist_ok=True)
    manifest = []
    for _src, rel in pairs:
        dst = docroot / rel
        if dst.is_file():
            bak = backup_dir / rel
            bak.parent.mkdir(parents=True, exist_ok=True)
            shutil.copy2(dst, bak)
            manifest.append(str(rel))
    main = docroot / lib.MAIN_REL
    if main.is_file():
        bak = backup_dir / lib.MAIN_REL
        bak.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(main, bak)
        manifest.append(str(lib.MAIN_REL))
    (backup_dir / "manifest.txt").write_text("\n".join(manifest) + "\n", encoding="utf-8")
    for src, rel in pairs:
        dst = docroot / rel
        dst.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(src, dst)
        print("copied {0}".format(rel))
    if main.is_file():
        original = main.read_text(encoding="utf-8", errors="replace")
        planned, _ = lib.patch_main_uslugi_light(original)
        if planned != original:
            main.write_text(planned, encoding="utf-8")
            print("patched {0}".format(lib.MAIN_REL))
    print("backup: {0}".format(backup_dir))
    print("rollback: python3 scripts/lireate_uslugi_install.py --docroot ... --rollback {0}".format(backup_id))
    print("STATUS: USLUGI_INSTALL_APPLIED")
    return 0


def cmd_rollback(docroot, backups_root, backup_id):
    backup_dir = backups_root / ("uslugi-install-" + backup_id)
    manifest = backup_dir / "manifest.txt"
    if not manifest.is_file():
        print("BLOCKER: manifest missing: {0}".format(manifest))
        return 2
    for line in manifest.read_text(encoding="utf-8").splitlines():
        rel = line.strip()
        if not rel:
            continue
        src = backup_dir / rel
        dst = docroot / rel
        if src.is_file():
            dst.parent.mkdir(parents=True, exist_ok=True)
            shutil.copy2(src, dst)
            print("restored {0}".format(rel))
    print("STATUS: USLUGI_INSTALL_ROLLED_BACK")
    return 0


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--docroot", type=Path, required=True)
    parser.add_argument("--office-repo", type=Path, default=REPO_ROOT)
    parser.add_argument("--backups-root", type=Path, required=True)
    parser.add_argument("--apply", action="store_true")
    parser.add_argument("--rollback", metavar="BACKUP_ID")
    args = parser.parse_args()
    docroot = args.docroot.expanduser().resolve()
    backups_root = args.backups_root.expanduser().resolve()
    office_repo = args.office_repo.expanduser().resolve()
    if args.rollback:
        return cmd_rollback(docroot, backups_root, args.rollback)
    if args.apply:
        return cmd_apply(office_repo, docroot, backups_root)
    return cmd_check(office_repo, docroot)


if __name__ == "__main__":
    raise SystemExit(main())