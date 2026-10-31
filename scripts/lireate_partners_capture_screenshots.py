#!/usr/bin/env python3
"""Render preview, verify visible content, save full-page screenshots."""

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
        probe = browser.new_page()
        css_resp = probe.goto(css_url, wait_until="load", timeout=60000)
        if not css_resp or css_resp.status != 200:
            print("BLOCKER: CSS not served at {0} (status={1})".format(
                css_url, css_resp.status if css_resp else None
            ))
            ok = False
        probe.close()

        for w in widths:
            page = browser.new_page(viewport={"width": w, "height": 800})
            page.goto(url, wait_until="networkidle", timeout=120000)
            page.evaluate("window.scrollTo(0, 0)")
            metrics = page.evaluate(
                """() => {
                const root = document.querySelector('.lireate-partners-page');
                const h1 = document.querySelector('.lireate-partners-h1');
                const h1Rect = h1 ? h1.getBoundingClientRect() : null;
                const h1Style = h1 ? getComputedStyle(h1) : null;
                const container = document.querySelector('.page_container');
                const cStyle = container ? getComputedStyle(container) : null;
                const cards = document.querySelectorAll('.lireate-partners-card').length;
                const team = document.querySelectorAll('.lireate-partners-team-card').length;
                const overflowX = Math.max(0, document.documentElement.scrollWidth - document.documentElement.clientWidth);
                const pageHeight = Math.max(document.body.scrollHeight, document.documentElement.scrollHeight);
                return {
                  bg: root ? getComputedStyle(root).backgroundColor : '',
                  cards,
                  team,
                  overflowX,
                  pageHeight,
                  containerHeight: cStyle ? cStyle.height : null,
                  containerOverflow: cStyle ? cStyle.overflow : null,
                  h1Top: h1Rect ? h1Rect.top : null,
                  h1Height: h1Rect ? h1Rect.height : null,
                  h1Opacity: h1Style ? h1Style.opacity : null,
                  h1Visible: !!(h1 && h1Rect && h1Rect.height > 10 && h1Rect.top < 600 && h1Rect.top >= -20)
                };
            }"""
            )
            if metrics["cards"] != 27 or metrics["team"] != 15:
                print("BLOCKER width {0}: cards={1} team={2}".format(
                    w, metrics["cards"], metrics["team"]
                ))
                ok = False
            if metrics["overflowX"] > 0:
                print("BLOCKER width {0}: overflowX={1}".format(w, metrics["overflowX"]))
                ok = False
            if metrics["pageHeight"] < 1800:
                print("BLOCKER width {0}: page too short ({1}px)".format(w, metrics["pageHeight"]))
                ok = False
            if not metrics["h1Visible"]:
                print("BLOCKER width {0}: hero not visible (top={1}, h={2}, opacity={3}, containerH={4})".format(
                    w,
                    metrics["h1Top"],
                    metrics["h1Height"],
                    metrics["h1Opacity"],
                    metrics["containerHeight"],
                ))
                ok = False
            kicker_ok = page.evaluate(
                """() => {
                  const k = document.querySelector('.lireate-partners-kicker');
                  if (!k) return false;
                  const r = k.getBoundingClientRect();
                  return r.top >= 4 && r.height > 0 && getComputedStyle(k).visibility !== 'hidden';
                }"""
            )
            if not kicker_ok:
                print("BLOCKER width {0}: LIREATE kicker not visible under top bar".format(w))
                ok = False
            modal_src = page.evaluate(
                """() => {
                  const img = document.getElementById('lp-modal-photo');
                  return img ? img.getAttribute('src') : null;
                }"""
            )
            if modal_src == "":
                print("BLOCKER width {0}: empty modal img src".format(w))
                ok = False
            shot = OUT_DIR / "partners-redesign-{0}.png".format(w)
            page.screenshot(path=str(shot), full_page=True)
            height_px = shot.stat().st_size
            print("OK {0}: {1} bytes={2} pageH={3} h1Top={4}".format(
                w, shot.name, height_px, metrics["pageHeight"], metrics["h1Top"]
            ))
            page.close()
        browser.close()

    return 0 if ok else 2


if __name__ == "__main__":
    raise SystemExit(main())
