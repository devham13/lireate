#!/usr/bin/env python3
"""Render preview, verify dark styles loaded, save width screenshots."""

import os
import sys
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
OUT_DIR = Path("/opt/cursor/artifacts/screenshots")
DEFAULT_LOCAL = "http://127.0.0.1:8799"


def main():
    os.environ.setdefault("LIREATE_PARTNERS_PREVIEW_ORIGIN", DEFAULT_LOCAL)
    render = REPO / "scripts" / "lireate_partners_render_preview_html.py"
    if os.system("python3 {0}".format(render)) != 0:
        return 2

    try:
        from playwright.sync_api import sync_playwright
    except ImportError:
        print("BLOCKER: playwright not installed")
        return 2

    url = DEFAULT_LOCAL + "/pages/partners/_preview/rendered-preview.html"
    css_url = DEFAULT_LOCAL + "/template/page/partners/lireate-partners.css"
    OUT_DIR.mkdir(parents=True, exist_ok=True)
    widths = [360, 768, 1280, 1920]
    ok = True

    with sync_playwright() as p:
        browser = p.chromium.launch()
        ctx = browser.new_context()
        probe = ctx.new_page()
        css_resp = probe.goto(css_url, wait_until="load", timeout=60000)
        if not css_resp or css_resp.status != 200:
            print("BLOCKER: CSS not served at {0} (status={1})".format(
                css_url, css_resp.status if css_resp else None
            ))
            ok = False
        probe.close()

        for w in widths:
            page = browser.new_page(viewport={"width": w, "height": 900})
            page.goto(url, wait_until="networkidle", timeout=120000)
            metrics = page.evaluate(
                """() => {
                const root = document.querySelector('.lireate-partners-page');
                const bg = root ? getComputedStyle(root).backgroundColor : '';
                const cards = document.querySelectorAll('.lireate-partners-card').length;
                const team = document.querySelectorAll('.lireate-partners-team-card').length;
                const overflowX = Math.max(0, document.documentElement.scrollWidth - document.documentElement.clientWidth);
                return { bg, cards, team, overflowX };
            }"""
            )
            if metrics["cards"] < 20 or metrics["team"] != 15:
                print("BLOCKER width {0}: cards={1} team={2}".format(
                    w, metrics["cards"], metrics["team"]
                ))
                ok = False
            if metrics["overflowX"] > 0:
                print("BLOCKER width {0}: overflowX={1}".format(w, metrics["overflowX"]))
                ok = False
            if "11, 11, 31" not in metrics["bg"] and "rgb(11, 11, 31)" not in metrics["bg"]:
                print("BLOCKER width {0}: background not dark ({1})".format(w, metrics["bg"]))
                ok = False
            shot = OUT_DIR / "partners-redesign-{0}.png".format(w)
            page.screenshot(path=str(shot), full_page=True)
            print("OK {0}: {1} cards={2} team={3} bg={4}".format(
                w, shot.name, metrics["cards"], metrics["team"], metrics["bg"]
            ))
            page.close()
        browser.close()

    return 0 if ok else 2


if __name__ == "__main__":
    raise SystemExit(main())
