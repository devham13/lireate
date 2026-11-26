#!/usr/bin/env python3
"""Remove Tochka «Оплата услуг» menu item from hosting DOCROOT (check / apply)."""

import argparse
import shutil
import sys
from datetime import datetime, timezone
from pathlib import Path

SCRIPT_DIR = Path(__file__).resolve().parent
REPO = SCRIPT_DIR.parent
sys.path.insert(0, str(SCRIPT_DIR))

import lireate_menu_remove_oplata_lib as lib  # noqa: E402


def main():
    parser = argparse.ArgumentParser(description="Remove CMS sidebar Oplata Tochka link")
    parser.add_argument("--docroot", required=True)
    parser.add_argument("--check", action="store_true")
    parser.add_argument("--apply", action="store_true")
    args = parser.parse_args()

    docroot = Path(args.docroot).expanduser().resolve()
    templates = lib.find_templates(docroot)
    print("=== MENU REMOVE OPLATA ===")
    print("href:", lib.OPLATA_HREF)
    print("files:", len(templates))
    print("data/db.php: NOT TOUCHED")

    any_change = False
    for path in templates:
        rel = path.relative_to(docroot)
        original = path.read_text(encoding="utf-8", errors="replace")
        patched, changed = lib.remove_oplata_links(original)
        print("---", rel, "---")
        if changed:
            any_change = True
            print("would remove oplata anchor(s)")
        else:
            print("no oplata link")
        if args.apply and changed:
            backups = Path.home() / "lireate-autopub" / "backups" / (
                "menu-remove-oplata-" + datetime.now(timezone.utc).strftime("%Y%m%d-%H%M%S")
            )
            backups.mkdir(parents=True, exist_ok=True)
            bak = backups / rel
            bak.parent.mkdir(parents=True, exist_ok=True)
            shutil.copy2(path, bak)
            path.write_text(patched, encoding="utf-8")
            print("applied; backup:", bak)

    if args.check or not args.apply:
        print("STATUS:", "MENU_OPLATA_WOULD_CHANGE" if any_change else "MENU_OPLATA_ALREADY_ABSENT")
        return 0
    print("STATUS: MENU_OPLATA_APPLIED" if any_change else "MENU_OPLATA_ALREADY_ABSENT")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())