#!/usr/bin/env python3
"""Full-page /avtor/ preview with prod-like CMS shell (not rendered-preview.html)."""

import os
import subprocess
import sys
import time
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
OUT = Path("/opt/cursor/artifacts/avtor-final")
BUILD = REPO / "scripts" / "lireate_avtor_build_shell_preview_html.py"
PREVIEW = "http://127.0.0.1:8799/pages/avtor/_preview/cms-shell-preview.html"
SHOT_CSS = """
#modal_form,#modalMail,.modal-mail,.popup.modal,.modal-backdrop,
#floatingBlock,#personsSlide,.jivo-iframe-container,[id*="jivo"],
[class*="whatsapp-widget"],[class*="wa-chat"]{display:none!important;visibility:hidden!important}
body.modal-open{overflow:auto!important;padding-right:0!important}
"""


def ensure_server():
    proc = subprocess.Popen(
        [sys.executable, "-m", "http.server", "8799", "--directory", str(REPO)],
        cwd=str(REPO),
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
    )
    time.sleep(0.5)
    return proc


def main():
    if os.system("python3 {0}".format(BUILD)) != 0:
        return 2
    OUT.mkdir(parents=True, exist_ok=True)
    try:
        from playwright.sync_api import sync_playwright
    except ImportError:
        print("BLOCKER: playwright")
        return 2

    server = ensure_server()
    ok = True
    try:
        with sync_playwright() as p:
            browser = p.chromium.launch()
            for width, name in ((1440, "avtor-final-desktop-1440.png"), (390, "avtor-final-mobile-390.png")):
                page = browser.new_page(viewport={"width": width, "height": 900})
                resp = page.goto(PREVIEW, wait_until="networkidle", timeout=120000)
                page.add_style_tag(content=SHOT_CSS)
                page.wait_for_function(
                    "() => document.querySelector('#leftSideBar') && document.body.classList.contains('lsv7-managed')",
                    timeout=30000,
                )
                imgs = page.evaluate(
                    """() => Array.from(document.querySelectorAll('#leftSideBar img[src*="person/"]')).map(i => i.src)"""
                )
                if imgs:
                    print("BLOCKER: sidebar person imgs", imgs)
                    ok = False
                modal = page.evaluate(
                    """() => {
                      const m = document.querySelector('#modal_form');
                      if (!m) return false;
                      const s = getComputedStyle(m);
                      return s.display !== 'none' && s.visibility !== 'hidden';
                    }"""
                )
                if modal:
                    print("BLOCKER: modal visible")
                    ok = False
                height = page.evaluate(
                    """() => Math.min(16000, Math.max(document.documentElement.scrollHeight, document.body.scrollHeight, 900))"""
                )
                page.set_viewport_size({"width": width, "height": int(height)})
                page.wait_for_timeout(400)
                out = OUT / name
                page.screenshot(path=str(out), full_page=True)
                print("saved", out, "http", resp.status if resp else None)
                page.close()
            browser.close()
    finally:
        server.terminate()
        server.wait(timeout=5)
    print("NOTE: regression-avtor-after-* used rendered-preview.html — QA artifact only; use avtor-final/*")
    return 0 if ok else 1


if __name__ == "__main__":
    raise SystemExit(main())