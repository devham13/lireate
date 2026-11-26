#!/usr/bin/env python3
"""Wire lireate-public-cms-chrome + lh4 subnav into pages/services/*/index.php (PHP 5.6)."""

import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SERVICES = ROOT / "pages" / "services"
MARKER = "lireate-public-cms-chrome-wire:v1"
INSERT = (
    '<?php /* %s */ require_once dirname(__DIR__) . '
    "'/_shared/lireate-public-cms-chrome-wire.inc.php'; "
    "lireate_public_cms_chrome_wire_open(); ?>\n"
)
CLOSE = '<?php /* lireate-public-cms-chrome-wire:v1 */ lireate_public_cms_chrome_wire_close(); ?>'


def patch_file(path: Path, dry_run: bool) -> str:
    text = path.read_text(encoding="utf-8")
    if MARKER in text:
        return "skip"
    m = re.search(r"(<body[^>]*>\s*\n?)", text, re.IGNORECASE)
    if not m:
        return "no-body"
    insert = INSERT % MARKER
    text = text[: m.end()] + insert + text[m.end() :]
    if "</body>" in text:
        text = text.replace("</body>", CLOSE + "\n</body>", 1)
    elif "lireate-patent-tool-footer.inc.php" in text:
        text = re.sub(
            r"(<\?php\s+include __DIR__ \. '/blocks/lireate-patent-tool-footer\.inc\.php'[^;]*;\s*\?>)",
            CLOSE + "\n\\1",
            text,
            count=1,
        )
    else:
        text = text.rstrip() + "\n" + CLOSE + "\n"
    if not dry_run:
        path.write_text(text, encoding="utf-8")
    return "patched"


def main():
    dry = "--apply" not in sys.argv
    changed = []
    for path in sorted(SERVICES.glob("*/index.php")):
        if path.parent.name == "_template":
            continue
        status = patch_file(path, dry)
        if status == "patched":
            changed.append(path.relative_to(ROOT))
        elif status not in ("skip",):
            print("WARN", path, status)
    action = "DRY_RUN" if dry else "APPLIED"
    print("STATUS: PR_B_WIRE_SERVICES_{0} count={1}".format(action, len(changed)))
    for rel in changed:
        print(rel)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())