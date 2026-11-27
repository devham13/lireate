#!/usr/bin/env python3
"""Playwright: prod sidebar unchanged (no person/*) + /avtor/ preview after PR."""

import json
import os
import sys
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
OUT = Path("/opt/cursor/artifacts/screenshots")
SITE = os.environ.get("LIREATE_PUBLIC_SITE_URL", "").rstrip("/")

PROD_PATHS = [
    "/",
    "/mktu",
    "/registraciya_tovarnogo_znaka/",
    "/prodlenie_tovarnogo_znaka/",
    "/findtz/",
    "/patent",
    "/news",
]

SHOT_CSS = "#personsSlide,#floatingBlock,#veil,.modal-backdrop{display:none!important}"


def sidebar_metrics(page):
    return page.evaluate(
        """() => {
          const bar = document.querySelector('#leftSideBar') || document.querySelector('#leftSideNav');
          if (!bar) return {ok:false, reason:'no-sidebar'};
          const imgs = Array.from(bar.querySelectorAll('img[src*="person/"]')).map(i => i.getAttribute('src'));
          const expert = bar.querySelector('.lsv7-sidebar-expert');
          return {
            ok: imgs.length===0 && !expert,
            imgs,
            hasExpert: !!expert,
            navCount: bar.querySelectorAll('.leftSideNaviButton, .leftSideNaviInSection').length
          };
        }"""
    )


def main():
    if not SITE:
        print("BLOCKER: LIREATE_PUBLIC_SITE_URL")
        return 2
    try:
        from playwright.sync_api import sync_playwright
    except ImportError:
        print("BLOCKER: playwright missing")
        return 2

    OUT.mkdir(parents=True, exist_ok=True)
    report = {"prod": {}, "avtor_preview": {}}
    ok = True

    with sync_playwright() as p:
        browser = p.chromium.launch()
        for path in PROD_PATHS:
            page = browser.new_page(viewport={"width": 1440, "height": 900})
            url = SITE + path
            resp = page.goto(url, wait_until="domcontentloaded", timeout=120000)
            page.wait_for_timeout(1500)
            page.add_style_tag(content=SHOT_CSS)
            m = sidebar_metrics(page)
            slug = path.strip("/").replace("/", "_") or "home"
            shot = OUT / "regression-prod-{0}-sidebar-1440.png".format(slug)
            loc = page.locator("#leftSideBar, #leftSideNav").first
            if loc.count():
                loc.screenshot(path=str(shot))
            report["prod"][path] = {
                "http": resp.status if resp else None,
                "sidebar": m,
                "screenshot": str(shot),
            }
            if m.get("reason") == "no-sidebar":
                print("SKIP(no-sidebar)", path)
            elif not m.get("ok"):
                ok = False
                print("FAIL", path, m)
            else:
                print("OK", path, "nav", m.get("navCount"))
            page.close()

        render = REPO / "scripts" / "lireate_avtor_render_preview_html.py"
        if os.system("python3 {0}".format(render)) != 0:
            return 2
        preview = "http://127.0.0.1:8799/pages/avtor/_preview/rendered-preview.html"
        for width, name in ((1440, "regression-avtor-after-desktop-1440.png"), (390, "regression-avtor-after-mobile-390.png")):
            page = browser.new_page(viewport={"width": width, "height": 900})
            resp = page.goto(preview, wait_until="domcontentloaded", timeout=60000)
            page.wait_for_timeout(1000)
            m = sidebar_metrics(page)
            path = OUT / name
            page.screenshot(path=str(path), full_page=True)
            report["avtor_preview"][name] = {"http": resp.status if resp else None, "sidebar": m, "path": str(path)}
            if m.get("imgs") or m.get("hasExpert"):
                ok = False
            page.close()
        browser.close()

    manifest = OUT / "avtor-regression-report.json"
    manifest.write_text(json.dumps(report, ensure_ascii=False, indent=2), encoding="utf-8")
    print("report", manifest)
    return 0 if ok else 1


if __name__ == "__main__":
    raise SystemExit(main())