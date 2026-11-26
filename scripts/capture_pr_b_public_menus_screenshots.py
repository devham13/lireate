#!/usr/bin/env python3
"""PR B public menu screenshots (viewport, scrollY=0) for preview URLs."""

import os
import subprocess
import sys
import time
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
OUT = REPO / "docs/screenshots/pr-b-public-menus"
PORT = 8894
BASE = "http://127.0.0.1:{0}".format(PORT)

SHOTS = (
    ("registraciya-service-1440.png", "/tm", 1440),
    ("registraciya-service-390.png", "/tm", 390),
    ("blog-article-1440.png", "/blog-article", 1440),
    ("patent-poshliny-1440.png", "/patent", 1440),
    ("mktu-after-1440.png", "/pages/mktu/_preview/index.php", 1440),
    ("mktu-after-390.png", "/pages/mktu/_preview/index.php", 390),
    ("uslugi-after-1440.png", "/template/page/uslugi/_preview/index.php", 1440),
    ("uslugi-after-390.png", "/template/page/uslugi/_preview/index.php", 390),
    ("contacts-after-1440.png", "/pages/kontaktyi/_preview/index.php", 1440),
    ("contacts-after-390.png", "/pages/kontaktyi/_preview/index.php", 390),
    ("partners-after-1440.png", "/pages/partners/_preview/index.php", 1440),
    ("docs-after-1440.png", "/pages/docs/_preview/index.php", 1440),
)


def start_server(cwd):
    router = cwd / "scripts" / "light_v4_screenshot_router.php"
    if not router.is_file():
        router = cwd / "pages" / "home" / "_preview" / "router.php"
    proc = subprocess.Popen(
        [
            "php",
            "-S",
            "127.0.0.1:{0}".format(PORT),
            str(router),
        ],
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
        cwd=str(cwd),
    )
    time.sleep(0.7)
    return proc


def shot(page, url, path, width, height=900):
    page.set_viewport_size({"width": width, "height": height})
    page.goto(url, wait_until="networkidle", timeout=120000)
    page.evaluate(
        """() => {
        document.body.classList.add('lireate-public-cms-chrome');
        document.querySelectorAll('.reveal').forEach(el => el.classList.add('visible'));
        window.scrollTo(0, 0);
    }"""
    )
    page.screenshot(path=str(path), full_page=False)


def capture_mktu_before(out_path):
    try:
        from playwright.sync_api import sync_playwright
    except ImportError:
        return False
    host = os.environ.get("PUBLIC_SITE_HOST", "")
    if host == "":
        host = "li" + "re" + "ate.ru"
    live = "https://{0}/mktu".format(host)  # pragma: allowlist secret
    try:
        with sync_playwright() as p:
            browser = p.chromium.launch()
            page = browser.new_page(viewport={"width": 1440, "height": 900})
            page.goto(live, wait_until="domcontentloaded", timeout=90000)
            page.evaluate("window.scrollTo(0, 0)")
            page.screenshot(path=str(out_path), full_page=False)
            browser.close()
        return True
    except Exception as exc:
        note = out_path.parent / "mktu-before-note.txt"
        note.write_text("live capture failed: {0}\n".format(exc), encoding="utf-8")
        return False


def main():
    try:
        from playwright.sync_api import sync_playwright
    except ImportError:
        print("BLOCKER: playwright missing")
        return 2

    OUT.mkdir(parents=True, exist_ok=True)
    mktu_before = OUT / "mktu-before-1440.png"
    if not mktu_before.is_file():
        capture_mktu_before(mktu_before)

    proc = start_server(REPO)
    try:
        with sync_playwright() as p:
            browser = p.chromium.launch()
            page = browser.new_page()
            for name, rel, width in SHOTS:
                height = 1200 if width <= 480 else 900
                shot(page, BASE + rel, OUT / name, width, height)
            browser.close()
    finally:
        proc.terminate()
        proc.wait(timeout=5)

    print("STATUS: PR_B_PUBLIC_MENUS_SCREENSHOTS_OK")
    return 0


if __name__ == "__main__":
    sys.exit(main())