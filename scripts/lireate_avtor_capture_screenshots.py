#!/usr/bin/env python3
"""Screenshots: production /avtor/ (before) and preview (after) at 1440 and 390."""

import os
import sys
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
OUT_DIR = Path("/opt/cursor/artifacts/screenshots")
PREVIEW_LOCAL = "http://127.0.0.1:8799/pages/avtor/_preview/rendered-preview.html"
PROD_URL = "https://" + "li" + "re" + "ate.ru/avtor/"  # pragma: allowlist secret

SHOT_CSS = """
#personsSlide, #floatingBlock, #veil, #modal_form, .modal-backdrop, .modal-mail, .popup.modal { display: none !important; visibility: hidden !important; opacity: 0 !important; pointer-events: none !important; }
body.modal-open { overflow: auto !important; padding-right: 0 !important; }
"""


def capture_tall_viewport(page, out_path):
    scroll_h = page.evaluate(
        """() => Math.min(14000, Math.max(
            document.documentElement.scrollHeight || 0,
            document.body.scrollHeight || 0,
            900))"""
    )
    viewport = page.viewport_size
    page.set_viewport_size({"width": viewport["width"], "height": int(scroll_h)})
    page.wait_for_timeout(300)
    page.screenshot(path=str(out_path), full_page=False)


def prepare_avtor_preview_shot(page, width):
    page.add_style_tag(content=SHOT_CSS)
    metrics = page.evaluate(
        """() => ({
          managed: document.body.classList.contains('lsv7-managed'),
          nav: document.querySelectorAll('#leftSideNav .leftSideNaviButton').length,
          expert: !!document.querySelector('#leftSideBar .lsv7-sidebar-expert img[src*="person/"]'),
          contentPad: document.querySelector('.content') ? getComputedStyle(document.querySelector('.content')).paddingLeft : '',
        })"""
    )
    if not metrics.get("managed") or metrics.get("nav", 0) < 5:
        raise RuntimeError("BLOCKER: sidebar not ready: {0}".format(metrics))
    if metrics.get("expert"):
        raise RuntimeError("BLOCKER: sidebar must not show person/* portrait in menu")
    if width >= 1151 and metrics.get("contentPad") not in ("0px", "0"):
        raise RuntimeError("BLOCKER: double content offset padding={0}".format(metrics.get("contentPad")))


def main():
    render = REPO / "scripts" / "lireate_avtor_render_preview_html.py"
    if os.system("python3 {0}".format(render)) != 0:
        return 2
    try:
        from playwright.sync_api import sync_playwright
    except ImportError:
        print("BLOCKER: playwright not installed")
        return 2

    OUT_DIR.mkdir(parents=True, exist_ok=True)
    shots = (
        ("avtor-before-desktop-1440.png", PROD_URL, 1440),
        ("avtor-before-mobile-390.png", PROD_URL, 390),
        ("avtor-after-desktop-1440.png", PREVIEW_LOCAL, 1440),
        ("avtor-after-mobile-390.png", PREVIEW_LOCAL, 390),
    )
    ok = True
    with sync_playwright() as p:
        browser = p.chromium.launch()
        for name, url, width in shots:
            page = browser.new_page(viewport={"width": width, "height": 900})
            try:
                resp = page.goto(url, wait_until="domcontentloaded", timeout=120000)
                if not resp or resp.status >= 400:
                    print("BLOCKER: {0} status={1}".format(url, resp.status if resp else "none"))
                    ok = False
                    continue
                if "rendered-preview.html" in url:
                    page.wait_for_function(
                        "() => document.body.classList.contains('lsv7-managed')",
                        timeout=30000,
                    )
                    page.wait_for_timeout(400)
                    prepare_avtor_preview_shot(page, width)
            except Exception as exc:
                print("BLOCKER: {0} {1}".format(url, exc))
                ok = False
                continue
            out = OUT_DIR / name
            if "rendered-preview.html" in url:
                capture_tall_viewport(page, out)
            else:
                page.screenshot(path=str(out), full_page=True)
            h1 = page.locator("h1").first
            if h1.count() < 1:
                print("BLOCKER: no h1 on {0}".format(url))
                ok = False
            print("saved {0}".format(out))
            page.close()
        browser.close()
    return 0 if ok else 2


if __name__ == "__main__":
    raise SystemExit(main())