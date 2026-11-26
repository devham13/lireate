#!/usr/bin/env python3
"""Before/after screenshots for PR B uslugi + mktu previews (1440 / 390)."""

import os
import subprocess
import sys
import time
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
OUT = REPO / "docs/screenshots/pr-b-public-menus"
MAIN_WT = Path("/agent/repos/lireate-main-wt")
PORT = 8893
BASE = "http://127.0.0.1:{0}".format(PORT)


def start_server(cwd):
    proc = subprocess.Popen(
        ["php", "-S", "127.0.0.1:{0}".format(PORT), "-t", str(cwd)],
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
        cwd=str(cwd),
    )
    time.sleep(0.6)
    return proc


def shot(page, url, path, width, height=900):
    page.set_viewport_size({"width": width, "height": height})
    page.goto(url, wait_until="networkidle", timeout=120000)
    page.screenshot(path=str(path), full_page=True)


def main():
    try:
        from playwright.sync_api import sync_playwright
    except ImportError:
        print("BLOCKER: playwright missing")
        return 2

    OUT.mkdir(parents=True, exist_ok=True)
    proc = start_server(REPO)
    try:
        with sync_playwright() as p:
            browser = p.chromium.launch()
            page = browser.new_page()
            shot(page, BASE + "/template/page/uslugi/_preview/index.php", OUT / "uslugi-after-1440.png", 1440)
            shot(page, BASE + "/template/page/uslugi/_preview/index.php", OUT / "uslugi-after-390.png", 390, 1200)
            shot(page, BASE + "/pages/mktu/_preview/index.php", OUT / "mktu-after-1440.png", 1440)
            shot(page, BASE + "/pages/mktu/_preview/index.php", OUT / "mktu-after-390.png", 390, 1200)
            browser.close()
    finally:
        proc.terminate()
        proc.wait(timeout=5)

    if not MAIN_WT.is_dir():
        print("WARN: main worktree missing, skip before shots")
        return 0

    before_html = MAIN_WT / "tmp-pr-b-uslugi-before-preview.php"
    before_html.write_text(
        """<?php
$repo = dirname(__DIR__);
$_SERVER['DOCUMENT_ROOT'] = $repo;
require_once $repo . '/template/page/uslugi/lireate-uslugi-lib.php';
?><!DOCTYPE html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body class="cms-default lireate-uslugi-body lireate-cms-page-dark"><div id="lireate-uslugi">
<?php require $repo . '/template/page/uslugi/lireate-uslugi-cms.inc.php'; ?>
</div></body></html>""",
        encoding="utf-8",
    )
    proc2 = start_server(MAIN_WT)
    try:
        with sync_playwright() as p:
            browser = p.chromium.launch()
            page = browser.new_page()
            url = BASE + "/tmp-pr-b-uslugi-before-preview.php"
            shot(page, url, OUT / "uslugi-before-1440.png", 1440)
            shot(page, url, OUT / "uslugi-before-390.png", 390, 1200)
            browser.close()
    finally:
        proc2.terminate()
        proc2.wait(timeout=5)
        if before_html.is_file():
            before_html.unlink()

    mktu_before = OUT / "mktu-before-1440.png"
    if not mktu_before.is_file():
        # MKTU before: dark CMS shell placeholder (full catalog only on hosting)
        note = OUT / "mktu-before-note.txt"
        note.write_text(
            "Before: legacy /mktu in template/main.php (DB HTML); локальный preview только after Light V4 CSS.\n",
            encoding="utf-8",
        )
    print("STATUS: PR_B_SCREENSHOTS_OK")
    return 0


if __name__ == "__main__":
    sys.exit(main())