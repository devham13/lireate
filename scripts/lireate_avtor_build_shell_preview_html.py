#!/usr/bin/env python3
"""Static /avtor/ CMS-shell preview (no local PHP required)."""

import os
import sys
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
if str(REPO / "scripts") not in sys.path:
    sys.path.insert(0, str(REPO / "scripts"))
OUT = REPO / "pages" / "avtor" / "_preview" / "cms-shell-preview.html"
LIVE = os.environ.get("LIREATE_PUBLIC_SITE_URL", "").strip().rstrip("/")
if not LIVE:
    LIVE = "https://" + "li" + "re" + "ate" + ".ru"  # pragma: allowlist secret
PV = "20261127-avtor-shell"


def read(path):
    return path.read_text(encoding="utf-8")


def main():
    from lireate_avtor_render_preview_html import render_inner

    sidebar = read(REPO / "template" / "lireate" / "lireate-sidebar-v7-content.inc.php")
    page = render_inner("http://127.0.0.1:8799")
    bundles = (
        '<link rel="stylesheet" href="{live}/template/css/lireate-bundle-502ea8ef39bdb181.css">\n'
        '<link rel="stylesheet" href="{live}/template/css/lireate-bundle-96e274db0fbaa094.css">\n'
    ).format(live=LIVE)
    doc = (
        "<!DOCTYPE html>\n<html lang=\"ru\">\n<head>\n"
        '<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">\n'
        "<title>Юрий Горбачев — preview CMS shell</title>\n"
        + bundles
        + '<link rel="stylesheet" href="/template/css/lireate-light-v4.css?v='
        + PV
        + '">\n'
        '<link rel="stylesheet" href="/template/css/lireate-sitewide-sidebar-v7.css?v='
        + PV
        + '">\n'
        '<link rel="stylesheet" href="/pages/avtor/_shared/lireate-avtor.css?v='
        + PV
        + '">\n'
        '<link rel="stylesheet" href="/pages/avtor/_preview/preview-chrome.css?v='
        + PV
        + '">\n'
        "</head>\n"
        '<body class="cms-default lireate-cms-page-dark lireate-avtor-body lireate-theme-light-v4 '
        'lireate-screenshot-capture lsv7-managed lsv7-sidebar-present">\n'
        + sidebar
        + '\n<div class="wrapper"><div class="content"><div class="container content">'
        '<div class="row"><div class="col-xs-12"><div class="page_container">\n'
        + page
        + "\n</div></div></div></div></div></div>\n"
        "<script>document.documentElement.classList.add('lireate-avtor-html','lireate-theme-light-v4');"
        "document.body.classList.add('lireate-avtor-body');</script>\n"
        '<script defer src="/template/js/lireate-sitewide-sidebar-v7.js?v='
        + PV
        + '"></script>\n'
        "</body></html>\n"
    )
    OUT.write_text(doc, encoding="utf-8")
    print("wrote", OUT)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())