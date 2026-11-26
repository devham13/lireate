#!/usr/bin/env python3
"""Nav V3 honest captures: live site HTML + PR #129 overlay, live CSS + local PR assets."""
from __future__ import annotations

import json
import subprocess
import sys
import time
from pathlib import Path
ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "docs/screenshots/nav-v3/production"
HTML_DIR = OUT / "html"

PAGES = [
    ("home", "/"),
    ("service-rtz", "/registraciya_tovarnogo_znaka/"),
    ("findtz", "/findtz/"),
    ("prodlenie", "/prodlenie_tovarnogo_znaka/"),
    ("mktu", "/mktu"),
    ("news", "/news"),
    ("team", "/team"),
    ("contacts", "/contacts"),
    ("portfolio", "/portfolio"),
    ("patent", "/patent"),
]


def pick_port():
    import socket

    with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
        s.bind(("127.0.0.1", 0))
        return s.getsockname()[1]


def ensure_bs4():
    try:
        import bs4  # noqa: F401
    except ImportError:
        subprocess.check_call([sys.executable, "-m", "pip", "install", "beautifulsoup4", "-q"])


def start_server(port):
    import http.server
    import socketserver
    import threading

    handler = http.server.SimpleHTTPRequestHandler
    httpd = socketserver.TCPServer(("127.0.0.1", port), handler)
    httpd.allow_reuse_address = True
    thread = threading.Thread(target=httpd.serve_forever, daemon=True)
    thread.start()
    time.sleep(0.25)
    return httpd


def main():
    ensure_bs4()
    sys.path.insert(0, str(ROOT / "scripts"))
    from lireate_nav_v3_live_overlay import apply_overlay, fetch_live

    OUT.mkdir(parents=True, exist_ok=True)
    HTML_DIR.mkdir(parents=True, exist_ok=True)
    port = pick_port()
    local_origin = "http://127.0.0.1:%d" % port
    orig_cwd = Path.cwd()
    import os

    os.chdir(ROOT)
    httpd = start_server(port)
    validations = []
    shots = []

    try:
        for slug, path in PAGES:
            live_html = fetch_live(path)
            overlay = apply_overlay(live_html, path, local_origin)
            html_path = HTML_DIR / ("%s.html" % slug)
            html_path.write_text(overlay, encoding="utf-8")

        from playwright.sync_api import sync_playwright

        with sync_playwright() as p:
            browser = p.chromium.launch(args=["--no-sandbox"])
            home_sidebar_w = None

            def route_all(route, request):
                url = request.url
                blocked = (
                    "mc.yandex",
                    "metrika",
                    "google-analytics",
                    "googletagmanager",
                    "doubleclick",
                    "facebook.net",
                )
                if any(b in url for b in blocked):
                    route.abort()
                    return
                route.continue_()

            for slug, path in PAGES:
                slug_dir = OUT / slug
                slug_dir.mkdir(parents=True, exist_ok=True)
                for w in (1440, 390):
                    suffixes = [""]
                    if w == 390 and slug == "findtz":
                        suffixes.append("-sections-open")
                    for suffix in suffixes:
                        page = browser.new_page(
                            viewport={"width": w, "height": 900 if w >= 768 else 844},
                            is_mobile=w <= 768,
                            has_touch=w <= 768,
                        )
                        page.route("**/*", route_all)
                        url = "%s/docs/screenshots/nav-v3/production/html/%s.html" % (
                            local_origin,
                            slug,
                        )
                        page.goto(url, wait_until="domcontentloaded", timeout=90000)
                        page.wait_for_timeout(1500)
                        if suffix == "-sections-open":
                            page.click("#anchorToggle", timeout=8000)
                            page.wait_for_timeout(400)
                        metrics = page.evaluate(
                            """() => {
                            const body = document.body;
                            const sb = document.querySelector('#leftSideBar.lireate-public-cms-sidebar') || document.querySelector('#leftSideBar');
                            const ff = body ? getComputedStyle(body).fontFamily : '';
                            const wrap = document.querySelector('.lireate-public-cms-content');
                            const main = document.querySelector('main') || document.querySelector('.page_container');
                            const wrapText = wrap ? wrap.innerText.trim().length : 0;
                            const mainH = main ? main.getBoundingClientRect().height : 0;
                            return {
                              fontFamily: ff,
                              sidebarW: sb ? sb.getBoundingClientRect().width : 0,
                              sidebarVisible: sb ? getComputedStyle(sb).display !== 'none' && sb.getBoundingClientRect().width > 50 : false,
                              hasContent: wrapText >= 50 || mainH > 100,
                              wrapTextLen: wrapText,
                            };
                        }"""
                        )
                        if slug == "home" and w == 1440:
                            home_sidebar_w = metrics["sidebarW"]
                        min_wrap = 20 if w <= 390 else 40
                        has_content = (
                            metrics.get("wrapTextLen", 0) >= min_wrap
                            or metrics.get("mainH", 0) > 100
                        )
                        ok = (
                            "Times New Roman" not in metrics["fontFamily"]
                            and metrics["sidebarVisible"]
                            and has_content
                        )
                        if home_sidebar_w and w == 1440 and slug != "home":
                            ok = ok and abs(metrics["sidebarW"] - home_sidebar_w) < 25
                        validations.append(
                            {"slug": slug, "w": w, "suffix": suffix, "ok": ok, **metrics}
                        )
                        if not ok:
                            print("WARN validation", slug, w, suffix, metrics)
                        out = slug_dir / ("%d%s.png" % (w, suffix))
                        page.screenshot(path=str(out), full_page=False)
                        shots.append(str(out.relative_to(ROOT)))
                        page.close()
            browser.close()
    finally:
        httpd.shutdown()
        os.chdir(orig_cwd)

    (OUT / "manifest.json").write_text(
        json.dumps({"shots": shots, "validations": validations}, ensure_ascii=False, indent=2)
        + "\n",
        encoding="utf-8",
    )
    failed = [v for v in validations if not v["ok"]]
    if failed:
        print("VALIDATION_FAILED", len(failed))
        for v in failed:
            print(v)
        raise SystemExit(2)
    print("WROTE", OUT)


if __name__ == "__main__":
    main()