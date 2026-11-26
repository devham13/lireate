#!/usr/bin/env python3
"""Capture Nav V3 BEFORE (live) and AFTER (local preview) screenshots."""
from __future__ import annotations

import argparse
import os
import subprocess
import sys
import time
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "docs/screenshots/nav-v3"
WIDTHS = (320, 375, 390, 768, 1280, 1440, 1920)

PAGES = (
    ("home", "/", "/"),
    ("team", "/team/", "/team"),
    ("uslugi", "/uslugi/", "/uslugi"),
    ("mktu", "/mktu", "/mktu"),
    ("service-rtz", "/registraciya_tovarnogo_znaka/", "/registraciya_tovarnogo_znaka"),
    ("blog", "/news/", "/news"),
)


def site_base() -> str:
    base = os.environ.get("LIREATE_PUBLIC_SITE_URL", "").strip().rstrip("/")
    if not base:
        raise SystemExit("BLOCKER: set LIREATE_PUBLIC_SITE_URL for BEFORE shots")
    return base


def start_preview_server(port: int) -> subprocess.Popen:
    router = ROOT / "pages/_preview/nav-v3-router.php"
    cmd = ["php", "-S", "127.0.0.1:%d" % port, str(router)]
    proc = subprocess.Popen(
        cmd,
        cwd=str(ROOT),
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
    )
    time.sleep(0.6)
    return proc


def capture(browser, url: str, out: Path, width: int, mobile_menu: bool) -> None:
    page = browser.new_page(viewport={"width": width, "height": 900})
    page.goto(url, wait_until="networkidle", timeout=120000)
    page.wait_for_timeout(900)
    if mobile_menu and width <= 768:
        burger = page.locator("#lireatePublicCmsBurger, .lireate-public-cms-burger").first
        if burger.count():
            burger.click()
            page.wait_for_timeout(400)
    out.parent.mkdir(parents=True, exist_ok=True)
    page.screenshot(path=str(out), full_page=True)
    page.close()


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--after-only", action="store_true")
    parser.add_argument("--before-only", action="store_true")
    parser.add_argument("--port", type=int, default=19081)
    args = parser.parse_args()
    from playwright.sync_api import sync_playwright

    run_before = not args.after_only
    run_after = not args.before_only
    base = site_base() if run_before else ""

    preview_proc = None
    if run_after:
        preview_proc = start_preview_server(args.port)
    try:
        with sync_playwright() as p:
            browser = p.chromium.launch(args=["--no-sandbox"])
            for slug, live_path, preview_path in PAGES:
                for width in WIDTHS:
                    if run_before:
                        before = OUT / "before" / slug / ("%d.png" % width)
                        capture(
                            browser,
                            base + live_path,
                            before,
                            width,
                            False,
                        )
                    if run_after:
                        after = OUT / "after" / slug / ("%d.png" % width)
                        preview_url = "http://127.0.0.1:%d%s" % (
                            args.port,
                            preview_path if preview_path != "/" else "/",
                        )
                        capture(browser, preview_url, after, width, False)
                        if width <= 390:
                            menu_out = OUT / "after" / slug / ("%d-menu-open.png" % width)
                            capture(browser, preview_url, menu_out, width, True)
            browser.close()
    finally:
        if preview_proc is not None:
            preview_proc.terminate()
            preview_proc.wait(timeout=5)
    print("WROTE", OUT)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())