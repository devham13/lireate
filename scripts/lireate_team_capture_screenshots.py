#!/usr/bin/env python3
"""Full-page team preview screenshots at 360/768/1280/1920."""

import os
import sys
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
OUT_DIR = Path("/opt/cursor/artifacts/screenshots")
DEFAULT_LOCAL = "http://127.0.0.1:8799"


def main():
    os.environ.setdefault("LIREATE_TEAM_PREVIEW_ORIGIN", DEFAULT_LOCAL)
    render = REPO / "scripts" / "lireate_team_render_preview_html.py"
    if os.system("python3 {0}".format(render)) != 0:
        return 2

    try:
        from playwright.sync_api import sync_playwright
    except ImportError:
        print("BLOCKER: playwright not installed")
        return 2

    url = DEFAULT_LOCAL + "/pages/team/_preview/rendered-preview.html"
    css_url = DEFAULT_LOCAL + "/template/page/team/lireate-team.css"
    OUT_DIR.mkdir(parents=True, exist_ok=True)
    widths = [360, 768, 1280, 1920]
    ok = True

    with sync_playwright() as p:
        browser = p.chromium.launch()
        probe = browser.new_page()
        css_resp = probe.goto(css_url, wait_until="load", timeout=60000)
        if not css_resp or css_resp.status != 200:
            print("BLOCKER: CSS not served at {0}".format(css_url))
            ok = False
        probe.close()

        for w in widths:
            page = browser.new_page(viewport={"width": w, "height": 800})
            page.goto(url, wait_until="networkidle", timeout=120000)
            metrics = page.evaluate(
                """() => {
                const h1 = document.querySelector('.lireate-team-h1');
                const h1Rect = h1 ? h1.getBoundingClientRect() : null;
                const members = document.querySelectorAll('.lireate-team-member-card').length;
                const overflowX = Math.max(0, document.documentElement.scrollWidth - document.documentElement.clientWidth);
                const pageHeight = Math.max(document.body.scrollHeight, document.documentElement.scrollHeight);
                return {
                  members,
                  overflowX,
                  pageHeight,
                  h1Visible: !!(h1 && h1Rect && h1Rect.height > 10 && h1Rect.top < 600)
                };
            }"""
            )
            if metrics["members"] != 15:
                print("BLOCKER width {0}: members={1}".format(w, metrics["members"]))
                ok = False
            if metrics["overflowX"] > 0:
                print("BLOCKER width {0}: overflowX={1}".format(w, metrics["overflowX"]))
                ok = False
            if not metrics["h1Visible"]:
                print("BLOCKER width {0}: h1 not visible".format(w))
                ok = False
            out_path = OUT_DIR / "team-page-{0}.png".format(w)
            page.screenshot(path=str(out_path), full_page=True)
            print("saved {0} (height={1})".format(out_path, metrics["pageHeight"]))
            page.close()
        browser.close()

    return 0 if ok else 2


if __name__ == "__main__":
    raise SystemExit(main())
