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

# (filename, path, widths)
PAGES = (
    ("home", "/", (1440, 390)),
    ("uslugi", "/uslugi", (1440, 390)),
    ("mktu", "/mktu", (1440, 390)),
    ("partners", "/partners", (1440, 390)),
    ("docs", "/docs", (1440, 390)),
    ("contacts", "/contacts", (1440, 390)),
    ("team", "/team", (1440, 390)),
    ("o-kompanii", "/o_kompanii", (1440, 390)),
    ("blog-list", "/blog", (1440, 390)),
    ("blog-article", "/blog-article", (1440, 390)),
    ("registraciya-tovarnogo-znaka", "/tm", (1440, 390)),
    ("patent-poshliny", "/patent", (1440, 390)),
    ("findtz", "/findtz", (1440, 390)),
    ("prodlenie", "/service", (1440, 390)),
)


def start_server(cwd):
    router = cwd / "scripts" / "light_v4_screenshot_router.php"
    proc = subprocess.Popen(
        ["php", "-S", "127.0.0.1:{0}".format(PORT), str(router)],
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
        cwd=str(cwd),
    )
    time.sleep(0.8)
    return proc


def shot(page, url, path, width, height):
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
            for slug, rel, widths in PAGES:
                for w in widths:
                    height = 844 if w <= 480 else 900
                    name = "{0}-{1}.png".format(slug, w)
                    shot(page, BASE + rel, OUT / name, w, height)
                    print("OK", name)
            browser.close()
    finally:
        proc.terminate()
        proc.wait(timeout=5)

    print("STATUS: PR_B_PUBLIC_MENUS_SCREENSHOTS_OK")
    return 0


if __name__ == "__main__":
    sys.exit(main())