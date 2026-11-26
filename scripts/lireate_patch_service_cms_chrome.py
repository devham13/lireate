#!/usr/bin/env python3
"""Idempotent: wire Nav V3 CMS chrome into standalone service index.php files."""
from pathlib import Path
import re

ROOT = Path(__file__).resolve().parents[1]
SERVICES = ROOT / "pages/services"
MARKER = "lireate_service_cms_chrome_wire_begin"


def patch_index(path: Path) -> bool:
    text = path.read_text(encoding="utf-8")
    if MARKER in text:
        return False
    if "lx-nav-shell" not in text:
        return False
    slug = path.parent.name
    if slug.startswith("_"):
        return False
    if "lireate-service-cms-chrome.inc.php" not in text:
        needle = "require_once dirname(__DIR__) . '/_shared/lireate-service-messenger-include.inc.php';"
        if needle not in text:
            return False
        insert = (
            needle
            + "\nrequire_once dirname(__DIR__) . '/_shared/lireate-service-cms-chrome.inc.php';\n"
            + f"$lx_service_slug = '{slug}';"
        )
        text = text.replace(needle, insert, 1)
    body_re = re.compile(
        r"(?P<indent>)(?P<open><body\s+class=\"[^\"]+\")(?P<rest>\s*>)",
        re.I,
    )
    m = body_re.search(text)
    if not m:
        return False
    prelude = (
        f"<?php $lx_cms_chrome_active = lireate_service_cms_chrome_wire_begin($lx_service_slug); ?>\n"
    )
    new_body = (
        m.group("open")
        + "<?php echo lireate_service_cms_chrome_body_class($lx_cms_chrome_active); ?>"
        + m.group("rest")
    )
    text = text[: m.start()] + prelude + new_body + text[m.end() :]
    shell_start = '<div class="lx-nav-shell">'
    if shell_start in text and "if (!$lx_cms_chrome_active)" not in text:
        text = text.replace(
            shell_start,
            "<?php if (!$lx_cms_chrome_active) : ?>\n" + shell_start,
            1,
        )
        close_nav = "</div>\n\n<main"
        if close_nav in text:
            text = text.replace(
                close_nav,
                "</div>\n<?php endif; ?>\n\n<main",
                1,
            )
    if "lireate_service_cms_chrome_wire_end" not in text:
        main_close = "</main>"
        if main_close in text:
            text = text.replace(
                main_close,
                "</main>\n<?php lireate_service_cms_chrome_wire_end($lx_cms_chrome_active); ?>",
                1,
            )
    path.write_text(text, encoding="utf-8")
    return True


def main():
    changed = []
    for index in sorted(SERVICES.glob("*/index.php")):
        if patch_index(index):
            changed.append(str(index.relative_to(ROOT)))
    for rel in changed:
        print("PATCHED", rel)
    if not changed:
        print("NO_CHANGES")


if __name__ == "__main__":
    main()