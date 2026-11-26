#!/usr/bin/env python3
"""Live CMS shell + PR patent Light V4 assets (for QA screenshots)."""

from __future__ import print_function

import os
import re
import subprocess
import sys
import tempfile
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
OUT = Path(os.environ.get("LIREATE_PATENT_SHOTS_DIR", "/opt/cursor/artifacts/screenshots"))

PAGES = (
    ("patent", "/patent"),
    ("patent_prom_obr", "/patent_prom_obr"),
)


def site_base():
    host = os.environ.get("LIREATE_PUBLIC_SITE_URL", "").strip().rstrip("/")
    if not host:
        raise RuntimeError("Set LIREATE_PUBLIC_SITE_URL (https://…)")
    return host


def fetch_html(url, dest):
    subprocess.run(["curl", "-fsSL", url, "-o", str(dest)], check=True)


def render_include(page_key):
    boot = "<?php\n$_SERVER['DOCUMENT_ROOT'] = %s;\n$lireate_patent_key = %s;\nrequire %s;\n" % (
        repr(str(REPO)),
        repr(page_key),
        repr(str(REPO / "pages/patents/_shared/lireate-patent-page.inc.php")),
    )
    proc = subprocess.run(
        ["php"],
        input=boot,
        capture_output=True,
        text=True,
    )
    if proc.returncode != 0:
        raise RuntimeError(proc.stderr or proc.stdout)
    return proc.stdout


def inject_pr_assets(html, page_key):
    css_uri = (REPO / "template/css/lireate-patent-light-v4.css").resolve().as_uri()
    js_uri = (REPO / "template/js/lireate-patent-light-v4.js").resolve().as_uri()
    fragment = render_include(page_key)
    for name in ("patent-inventions.webp", "patent-industrial-design.webp"):
        rel = "/template/images/patent-v4/" + name
        file_uri = (REPO / "template/images/patent-v4" / name).resolve().as_uri()
        fragment = fragment.replace(rel, file_uri)
    hide_legacy = (
        "<style id=\"lireate-patent-v4-shot\">"
        "body.lireate-patent-light-v4-inject #lireate-patent-v4{display:block!important;}"
        "body.lireate-patent-light-v4-inject #main_back,"
        "body.lireate-patent-light-v4-inject #main_back~*,"
        "body.lireate-patent-light-v4-inject .lireate-cms-hero,"
        "body.lireate-patent-light-v4-inject .lireate-vk-cases{display:none!important;}"
        "body.lireate-patent-light-v4-inject .page_container{display:block!important;}"
        "</style>"
    )
    if "<base " not in html.lower():
        html = re.sub(
            r"(<head[^>]*>)",
            r'\1\n<base href="%s/">' % site_base(),
            html,
            count=1,
            flags=re.I,
        )
    html = re.sub(
        r"</head>",
        hide_legacy
        + '\n<link rel="stylesheet" href="'
        + css_uri
        + '">\n<script defer src="'
        + js_uri
        + '"></script>\n</head>',
        html,
        count=1,
        flags=re.I,
    )
    if "lireate-patent-light-v4-inject" not in html:
        html = re.sub(
            r"<body([^>]*)>",
            r"<body\1 class=\"lireate-patent-light-v4-inject\">",
            html,
            count=1,
            flags=re.I,
        )
    needles = (
        'id="main_back"',
        '<div class="row main-screen main_back',
        '<div id="page-content-wrapper">',
        '<div class="wrapper-ap">',
        '<div class="wrapper">',
    )
    pos = -1
    for needle in needles:
        pos = html.find(needle)
        if pos >= 0:
            break
    if pos >= 0:
        html = html[:pos] + fragment + "\n" + html[pos:]
    else:
        html = html.replace("</body>", fragment + "\n</body>", 1)
    return html


def chrome_screenshot(html_file, out_file, width, height, suffix):
    args = [
        "timeout",
        "70",
        "google-chrome",
        "--headless=new",
        "--disable-gpu",
        "--no-sandbox",
        "--user-data-dir=/tmp/chrome-patent-v4-" + suffix,
        "--virtual-time-budget=8000",
        "--window-size=%d,%d" % (width, height),
        "--screenshot=" + str(out_file),
        html_file.resolve().as_uri(),
    ]
    subprocess.run(args, check=False)
    if not out_file.is_file() or out_file.stat().st_size < 8000:
        raise RuntimeError("screenshot failed or too small: " + str(out_file))


def main():
    OUT.mkdir(parents=True, exist_ok=True)
    cache = REPO / ".cache"
    cache.mkdir(parents=True, exist_ok=True)
    base = site_base()
    saved = []
    for key, path in PAGES:
        raw = cache / ("live-" + key + ".html")
        fetch_html(base + path, raw)
        rendered = cache / ("pr-" + key + ".html")
        rendered.write_text(
            inject_pr_assets(raw.read_text(encoding="utf-8", errors="replace"), key),
            encoding="utf-8",
        )
        for width, tag in ((1440, "desktop"), (390, "mobile")):
            name = "patent-v4-%s-%s-%d.png" % (key.replace("_", "-"), tag, width)
            out = OUT / name
            chrome_screenshot(
                rendered,
                out,
                width,
                900 if width > 400 else 844,
                key + tag,
            )
            saved.append(out)
    for path in saved:
        print("OK", path)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())