#!/usr/bin/env python3
"""Build markdown inventory: CMS page | sidebar | subnav | template entry."""

import argparse
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
DEFAULT_OUT = ROOT / "docs/pr-b-public-menu-inventory.ru.md"
SIDEBAR_FIXTURE = ROOT / "tests/fixtures/live-render/after/home.html"
TEMPLATE_PAGE = ROOT / "template/page"

PAGE_CMS = {
    "team": "template/page/team/lireate-team-cms.inc.php",
    "o_kompanii": "template/page/o_kompanii/lireate-about-cms.inc.php",
    "partners": "template/page/partners/lireate-partners-cms.inc.php",
    "docs": "template/page/docs/lireate-docs-cms.inc.php",
    "kontaktyi": "template/page/kontaktyi/lireate-contacts-cms.inc.php",
    "contacts": "template/page/kontaktyi/lireate-contacts-cms.inc.php",
    "uslugi": "template/page/uslugi/lireate-uslugi-cms.inc.php",
    "news": "template/page/news/lireate-blog-list.inc.php",
    "reviews": "template/page/lireate-reviews-cms.inc.php",
    "portfolio": "template/page/lireate-portfolio-cms.inc.php",
    "mktu": "pages/mktu/_preview/index.php",
    "services": "pages/services/*/index.php",
}

BUTTON_RE = re.compile(
    r'<a\s+href="([^"]+)"\s+class="leftSideNaviButton[^"]*"\s*[^>]*>([^<]+)</a>',
    re.IGNORECASE,
)


def normalize_href(href):
    href = href.strip()
    if href.startswith("http"):
        from urllib.parse import urlparse

        path = urlparse(href).path or "/"
        return path if path.startswith("/") else "/" + path
    return href


def sidebar_rows(html):
    rows = []
    for href, label in BUTTON_RE.findall(html):
        label = label.strip()
        if not label or label.startswith("+"):
            continue
        if "tochka.com" in href.lower() or "оплата услуг" in label.lower():
            continue
        rows.append((normalize_href(href), label))
    return rows


def detect_pages():
    rows = []
    sidebar = sidebar_rows(SIDEBAR_FIXTURE.read_text(encoding="utf-8", errors="replace"))

    for key, tpl in sorted(PAGE_CMS.items()):
        rows.append((key, "yes", "yes", tpl if tpl != "pages/services/*/index.php" else "`pages/services/*/index.php`"))

    rows.append(("—", "—", "—", "—"))
    for href, label in sidebar:
        rows.append(("(public)", "yes", "yes", "{0} ({1})".format(href, label)))
    return rows


def render_md(rows):
    lines = [
        "# PR B — публичное меню и шаблоны",
        "",
        "Источник sidebar: `pages/_shared/lireate-public-cms-sidebar.inc.php` (без Tochka).",
        "subnav: `lireate_lh4_include_subnav()` на публичных CMS и услугах.",
        "БД (`data/db.php`) не используется.",
        "",
        "| page / раздел | sidebar | subnav | template / URL |",
        "|---|---|---|---|",
    ]
    for a, b, c, d in rows:
        lines.append("| {0} | {1} | {2} | `{3}` |".format(a, b, c, d))
    lines.append("")
    return "\n".join(lines)


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--out", default=str(DEFAULT_OUT))
    args = parser.parse_args()
    out = Path(args.out)
    out.parent.mkdir(parents=True, exist_ok=True)
    out.write_text(render_md(detect_pages()), encoding="utf-8")
    print("wrote", out)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())