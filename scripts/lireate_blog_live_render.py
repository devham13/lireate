#!/usr/bin/env python3
"""Fetch live article HTML and screenshot with repo blog CSS (legacy CMS shell)."""

import re
import subprocess
import sys
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
DEFAULT_SLUG = (
    "otvet_na_pretenziyu_pravoobladatelya_tovarnogo_znaka_"
    "poryadok_srok_30_dney_i_obrazec"
)


def fetch_html(url, dest):
    subprocess.run(["curl", "-sL", url, "-o", str(dest)], check=True)


def inject_local_css(html, base_href):
    article_css = (REPO / "pages/blog/_shared/lireate-blog-article.css").resolve().as_uri()
    footer_css = (REPO / "pages/blog/_shared/lireate-blog-footer.css").resolve().as_uri()
    if not re.search(r"<base\s", html, re.I):
        html = re.sub(
            r"(<head[^>]*>)",
            r'\1\n<base href="{0}">'.format(base_href),
            html,
            count=1,
            flags=re.I,
        )
    html = re.sub(
        r'href="/template/page/news/lireate-blog-article\.css\?v=[^"]+"',
        'href="{0}"'.format(article_css),
        html,
        count=1,
    )
    html = re.sub(
        r'href="/template/page/news/lireate-blog-footer\.css\?v=[^"]+"',
        'href="{0}"'.format(footer_css),
        html,
        count=1,
    )
    return html


def chrome_screenshot(html_file, out_file, width, height, profile_suffix):
    subprocess.run(
        [
            "google-chrome",
            "--headless=new",
            "--disable-gpu",
            "--no-sandbox",
            "--user-data-dir=/tmp/chrome-blog-live-{0}".format(profile_suffix),
            "--virtual-time-budget=12000",
            "--window-size={0},{1}".format(width, height),
            "--screenshot=" + str(out_file),
            html_file.resolve().as_uri(),
        ],
        check=True,
        timeout=90,
    )


def main(argv=None):
    host = __import__("os").environ.get("LIREATE_PUBLIC_SITE_URL", "").strip().rstrip("/")
    if not host:
        host = "https://" + __import__("os").environ.get("PUBLIC_SITE_HOST", "lireate.ru")
    url = host + "/" + DEFAULT_SLUG.strip("/") + "/"
    if argv:
        for arg in argv:
            if arg.startswith("http"):
                url = arg
            elif not arg.startswith("-"):
                url = host + "/" + arg.strip("/") + "/"
    cache_dir = REPO / ".cache"
    cache_dir.mkdir(parents=True, exist_ok=True)
    raw = cache_dir / "lireate-live-article.html"
    fetch_html(url, raw)
    rendered = cache_dir / "lireate-live-render.html"
    base_href = host.rstrip("/") + "/"
    rendered.write_text(
        inject_local_css(raw.read_text(encoding="utf-8", errors="replace"), base_href),
        encoding="utf-8",
    )
    shots = Path("/opt/cursor/artifacts/screenshots")
    shots.mkdir(parents=True, exist_ok=True)
    viewports = (
        ("blog-article-live-1280.png", 1280, 900, "1280"),
        ("blog-article-live-1440.png", 1440, 900, "1440"),
        ("blog-article-live-1024.png", 1024, 900, "1024"),
        ("blog-article-live-mobile.png", 390, 1500, "m"),
    )
    for name, w, h, suffix in viewports:
        chrome_screenshot(rendered, shots / name, w, h, suffix)
    print("OK: {0}".format(shots))
    return 0


if __name__ == "__main__":
    raise SystemExit(main(sys.argv[1:]))
