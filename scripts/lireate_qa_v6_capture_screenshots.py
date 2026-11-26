#!/usr/bin/env python3
"""QA V6 preview screenshots at 1440 and 390 for company pages + patent catalog."""

import os
import subprocess
import sys
import time
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
OUT = Path("/opt/cursor/artifacts/screenshots/qa-v6-20261126")
HOST = "127.0.0.1"
PORT = 8799
BASE = "http://{0}:{1}".format(HOST, PORT)

PAGES = (
    ("contacts", "/pages/kontaktyi/_preview/index.php"),
    ("partners", "/pages/partners/_preview/index.php"),
    ("team", "/pages/team/_preview/index.php"),
    ("o_kompanii", "/pages/o_kompanii/_preview/index.php"),
    ("patentnye-zadachi", "/pages/services/patentnye-zadachi/index.php"),
)
WIDTHS = (1440, 390)


def main():
    try:
        from playwright.sync_api import sync_playwright
    except ImportError:
        print("BLOCKER: playwright not installed")
        return 2

    OUT.mkdir(parents=True, exist_ok=True)
    server = subprocess.Popen(
        ["php", "-S", "{0}:{1}".format(HOST, PORT), "-t", str(REPO)],
        cwd=str(REPO),
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
    )
    time.sleep(0.8)
    ok = True
    try:
        with sync_playwright() as p:
            browser = p.chromium.launch()
            for slug, path in PAGES:
                url = BASE + path
                for w in WIDTHS:
                    page = browser.new_page(viewport={"width": w, "height": 900})
                    resp = page.goto(url, wait_until="networkidle", timeout=120000)
                    if not resp or resp.status >= 400:
                        print("BLOCKER {0} width {1}: HTTP {2}".format(slug, w, resp.status if resp else "none"))
                        ok = False
                    out = OUT / "{0}-desktop-{1}.png".format(slug, w)
                    if w == 390:
                        out = OUT / "{0}-mobile-{1}.png".format(slug, w)
                    page.screenshot(path=str(out), full_page=True)
                    print("saved", out)
                    page.close()
            browser.close()
    finally:
        server.terminate()
        server.wait(timeout=5)
    return 0 if ok else 2


if __name__ == "__main__":
    raise SystemExit(main())