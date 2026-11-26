"""Remove «Оплата услуг» (Tochka) from CMS sidebar and burger copies in DOCROOT PHP.

Does not touch data/db.php.
"""

import re
from pathlib import Path

from lireate_hosting_menu_add import _iter_php_files

OPLATA_HREF = "https://x.tochka.com/myprofile/lireat"
REMOVE_MARKER = "lireate-menu-remove-oplata:v1"
FORBIDDEN = {Path("data/db.php")}

OPLATA_ANCHOR_RE = re.compile(
    r"\s*<a\b[^>]*href=[\"']"
    + re.escape(OPLATA_HREF)
    + r"[\"'][^>]*class=[\"'][^\"']*leftSideNaviButton[^\"']*[\"'][^>]*>.*?</a>\s*",
    re.IGNORECASE | re.DOTALL,
)

# Burger / mobile duplicate may omit target="_blank" but same href + class
OPLATA_ANY_NAV_RE = re.compile(
    r"\s*<a\b[^>]*href=[\"']"
    + re.escape(OPLATA_HREF)
    + r"[\"'][^>]*>.*?Оплата\s+услуг.*?</a>\s*",
    re.IGNORECASE | re.DOTALL,
)


def file_references_oplata(text):
    return OPLATA_HREF in text and "Оплата" in text


def find_templates(docroot):
    hits = []
    for path in _iter_php_files(docroot):
        rel = path.relative_to(docroot)
        if rel in FORBIDDEN or str(rel).startswith("data" + str(Path("/"))):
            continue
        try:
            text = path.read_text(encoding="utf-8", errors="replace")
        except OSError:
            continue
        if file_references_oplata(text):
            hits.append(path)
    return hits


def remove_oplata_links(text):
    changed = False
    new_text = text
    for pattern in (OPLATA_ANCHOR_RE, OPLATA_ANY_NAV_RE):
        while True:
            m = pattern.search(new_text)
            if not m:
                break
            new_text = new_text[: m.start()] + "\n" + new_text[m.end() :]
            changed = True
    return new_text, changed