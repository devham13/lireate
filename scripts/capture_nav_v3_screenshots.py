#!/usr/bin/env python3
"""Capture Nav V3 BEFORE (live) and AFTER (local preview) — viewport only."""
from __future__ import annotations

import argparse
import json
import os
import subprocess
import time
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "docs/screenshots/nav-v3"
QA = OUT / "capture-qa.json"
WIDTHS = (320, 375, 390, 768, 1280, 1440, 1920)
VIEWPORT_HEIGHT = 900

REQUIRED_CSS = (
    "/template/css/lireate-navigation-dual-v3.css",
    "/template/css/lireate-public-cms-chrome.css",
    "/template/css/lireate-home-light-v4.css",
)

BLOG_ARTICLE_PATH = os.environ.get(
    "LIREATE_NAV_BLOG_ARTICLE_PATH",
    "/proverka_tovarnogo_znaka_pered_registraciey_chek_list_po_reestram_rospatenta/",
).rstrip("/")

PAGES = (
    ("home", "/", "/"),
    ("team", "/team/", "/team"),
    ("uslugi", "/uslugi/", "/uslugi"),
    ("mktu", "/mktu", "/mktu"),
    ("service-rtz", "/registraciya_tovarnogo_znaka/", "/registraciya_tovarnogo_znaka"),
    ("blog-article", BLOG_ARTICLE_PATH + "/", BLOG_ARTICLE_PATH),
)


def response_is_blocker(url: str) -> bool:
    if "fonts.googleapis.com" in url or "fonts.gstatic.com" in url:
        return False
    return True


def site_base() -> str:
    base = os.environ.get("LIREATE_PUBLIC_SITE_URL", "").strip().rstrip("/")
    if not base:
        raise SystemExit("BLOCKER: set LIREATE_PUBLIC_SITE_URL for BEFORE shots")
    return base


def start_preview_server(port: int) -> subprocess.Popen:
    router = ROOT / "pages/_preview/nav-v3-router.php"
    cmd = [
        "php",
        "-S",
        "127.0.0.1:%d" % port,
        "-t",
        str(ROOT),
        str(router),
    ]
    proc = subprocess.Popen(
        cmd,
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
    )
    time.sleep(0.6)
    for css in REQUIRED_CSS:
        import urllib.request

        url = "http://127.0.0.1:%d%s?v=qa" % (port, css)
        try:
            with urllib.request.urlopen(url, timeout=5) as resp:
                if resp.status != 200:
                    raise SystemExit("BLOCKER: preview CSS not 200: " + url)
        except Exception as exc:
            raise SystemExit("BLOCKER: preview CSS fetch failed " + url + " " + str(exc))
    return proc


def capture(
    browser,
    url: str,
    out: Path,
    width: int,
    qa_rows: list,
    *,
    mobile_menu: bool = False,
    side_collapsed: bool = False,
    anchor_open: bool = False,
    is_after: bool = False,
) -> None:
    is_mobile = width <= 768
    page = browser.new_page(
        viewport={"width": width, "height": VIEWPORT_HEIGHT},
        device_scale_factor=1,
        is_mobile=is_mobile,
        has_touch=is_mobile,
    )
    failed_css: list[str] = []
    if is_after:

        def on_response(response):
            req = response.request
            if req.resource_type != "stylesheet":
                return
            if response.status != 200:
                failed_css.append(req.url)

        page.on("response", on_response)

    page.goto(url, wait_until="networkidle", timeout=120000)
    page.wait_for_timeout(700)
    page.evaluate("window.scrollTo(0, 0)")
    page.wait_for_timeout(200)

    if side_collapsed and width >= 1151:
        collapse = page.locator("#collapseSide").first
        if collapse.count() and collapse.is_visible():
            if not page.evaluate("document.body.classList.contains('side-collapsed')"):
                collapse.click()
                page.wait_for_timeout(350)
    if anchor_open and width <= 1150:
        toggle = page.locator("#anchorToggle").first
        if toggle.count() and toggle.is_visible():
            toggle.click()
            page.wait_for_timeout(350)
    if mobile_menu and width <= 768:
        burger = page.locator("#lireatePublicCmsBurger, .lireate-public-cms-burger").first
        if burger.count() and burger.is_visible():
            burger.click()
            page.wait_for_timeout(400)
        page.evaluate(
            """() => {
            const bar = document.getElementById('leftSideBar');
            if (!bar) return;
            const logo = bar.querySelector('.leftSideLogo');
            if (logo) logo.scrollIntoView({block: 'start'});
            const soc = bar.querySelector('.leftSideSoc');
            if (soc) soc.scrollIntoView({block: 'end'});
            }"""
        )
        page.wait_for_timeout(250)

    metrics = page.evaluate(
        """() => ({
            scrollY: window.scrollY,
            innerWidth: window.innerWidth,
            scrollWidth: document.documentElement.scrollWidth,
            hasNavCss: !!document.querySelector('link[href*="lireate-navigation-dual-v3"]'),
            hasChrome: !!document.getElementById('leftSideBar')
        })"""
    )
    overflow = int(metrics["scrollWidth"]) > int(metrics["innerWidth"])
    row = {
        "file": str(out.relative_to(ROOT)),
        "url": "(live)" if not is_after else url,
        "width": width,
        "scrollY": metrics["scrollY"],
        "innerWidth": metrics["innerWidth"],
        "scrollWidth": metrics["scrollWidth"],
        "overflow_x": overflow,
        "hasNavCss": metrics["hasNavCss"],
        "failed_css": failed_css,
    }
    qa_rows.append(row)
    if is_after and failed_css:
        blocker = [u for u in failed_css if response_is_blocker(u)]
        if blocker:
            raise SystemExit("BLOCKER: CSS not loaded for " + str(out) + ": " + ", ".join(blocker))
    if overflow and is_mobile and not mobile_menu:
        raise SystemExit(
            "BLOCKER: horizontal overflow on mobile %s: scrollWidth=%s innerWidth=%s"
            % (out, metrics["scrollWidth"], metrics["innerWidth"])
        )

    out.parent.mkdir(parents=True, exist_ok=True)
    page.screenshot(path=str(out), full_page=False)
    shot_w = page.evaluate("() => document.documentElement.clientWidth")
    if int(shot_w) != int(width):
        page.close()
        raise SystemExit("BLOCKER: viewport width mismatch %s != %s for %s" % (shot_w, width, out))
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
    qa_rows: list = []

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
                            qa_rows,
                            is_after=False,
                        )
                    if run_after:
                        preview_url = "http://127.0.0.1:%d%s" % (
                            args.port,
                            preview_path if preview_path != "/" else "/",
                        )
                        after = OUT / "after" / slug / ("%d.png" % width)
                        capture(
                            browser,
                            preview_url,
                            after,
                            width,
                            qa_rows,
                            is_after=True,
                        )
                        if width <= 768:
                            menu_out = OUT / "after" / slug / ("%d-menu-open.png" % width)
                            capture(
                                browser,
                                preview_url,
                                menu_out,
                                width,
                                qa_rows,
                                mobile_menu=True,
                                is_after=True,
                            )
                        if width in (320, 375, 390, 768):
                            anchor_out = OUT / "after" / slug / ("%d-sections-open.png" % width)
                            capture(
                                browser,
                                preview_url,
                                anchor_out,
                                width,
                                qa_rows,
                                anchor_open=True,
                                is_after=True,
                            )
                        if width == 1440:
                            collapsed_out = OUT / "after" / slug / ("1440-side-collapsed.png")
                            capture(
                                browser,
                                preview_url,
                                collapsed_out,
                                width,
                                qa_rows,
                                side_collapsed=True,
                                is_after=True,
                            )
            browser.close()
    finally:
        if preview_proc is not None:
            preview_proc.terminate()
            preview_proc.wait(timeout=5)

    OUT.mkdir(parents=True, exist_ok=True)
    QA.write_text(json.dumps(qa_rows, ensure_ascii=False, indent=2), encoding="utf-8")
    print("WROTE", OUT)
    print("QA", QA)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())