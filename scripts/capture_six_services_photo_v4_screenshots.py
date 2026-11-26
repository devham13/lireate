#!/usr/bin/env python3
"""Live CMS HTML + photo-v4 overlay screenshots (1440×900, 390×900)."""

from __future__ import annotations

import re
import shutil
import subprocess
import tempfile
import time
import urllib.request
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "docs/screenshots/six-services-v4"
PORT = int(__import__("os").environ.get("LIREATE_SIX_PHOTO_V4_PORT", "19140"))
MARKER = "lireate-six-services-photo-v4-20261126"
CACHE_VER = "20261126i"


def live_host() -> str:
    host = __import__("os").environ.get("LIREATE_PUBLIC_SITE_URL", "").strip().rstrip("/")
    if not host:
        raise SystemExit("Set LIREATE_PUBLIC_SITE_URL for live HTML capture")
    return host

ROUTES = (
    "findtz",
    "registraciya_tovarnogo_znaka",
    "prodlenie_tovarnogo_znaka",
    "vozrazhenie_protiv_registracii_tovarnogo_znaka",
    "zashchita_avtorskih_prav",
    "dogovory",
)

INJECT = (
    "\n<!-- {marker} -->\n"
    '<link rel="stylesheet" href="/template/css/lireate-six-services-photo-v4.css?v={ver}">\n'
    '<script defer src="/template/js/lireate-six-services-photo-v4.js?v={ver}"></script>\n'
).format(marker=MARKER, ver=CACHE_VER)

ROUTER_PHP = """<?php
$root = {root!r};
$preview = {preview!r};
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path === '/sheet.html' && is_file($preview . '/sheet.html')) {{
    header('Content-Type: text/html; charset=utf-8');
    readfile($preview . '/sheet.html');
    return true;
}}
if (strpos($path, '/template/') === 0) {{
    $file = $root . $path;
    if (is_file($file)) {{
        if (preg_match('/\\.css$/', $file)) header('Content-Type: text/css; charset=utf-8');
        if (preg_match('/\\.(js|mjs)$/', $file)) header('Content-Type: application/javascript; charset=utf-8');
        if (preg_match('/\\.webp$/', $file)) header('Content-Type: image/webp');
        readfile($file);
        return true;
    }}
    http_response_code(404);
    echo 'missing';
    return true;
}}
$slug = trim($path, '/');
if ($slug !== '' && is_file($preview . '/' . $slug . '/index.html')) {{
    header('Content-Type: text/html; charset=utf-8');
    readfile($preview . '/' . $slug . '/index.html');
    return true;
}}
return false;
"""


def fetch_live(route: str) -> str:
    req = urllib.request.Request(
        f"{live_host()}/{route}/",
        headers={"User-Agent": "lireate-six-photo-v4-preview/1.0"},
    )
    with urllib.request.urlopen(req, timeout=120) as resp:
        return resp.read().decode("utf-8", "replace")


def inject_overlay(html: str) -> str:
    html = re.sub(
        r"\n?<!--\s*" + re.escape(MARKER) + r"\s*-->\s*",
        "\n",
        html,
        flags=re.I,
    )
    html = re.sub(
        r"<link[^>]+lireate-six-services-photo-v4\.css[^>]*>\s*",
        "",
        html,
        flags=re.I,
    )
    html = re.sub(
        r"<script[^>]+lireate-six-services-photo-v4\.js[^>]*>\s*</script>\s*",
        "",
        html,
        flags=re.I,
    )
    return re.sub(r"</head>", INJECT + "</head>", html, count=1, flags=re.I)


def prepare_docroot(base: Path) -> None:
    for route in ROUTES:
        (base / route).mkdir(parents=True, exist_ok=True)
        html = inject_overlay(fetch_live(route))
        (base / route / "index.html").write_text(html, encoding="utf-8")
    tpl = base / "template"
    shutil.copytree(ROOT / "template/css", tpl / "css", dirs_exist_ok=True)
    shutil.copytree(ROOT / "template/js", tpl / "js", dirs_exist_ok=True)
    shutil.copytree(ROOT / "template/images", tpl / "images", dirs_exist_ok=True)


def verify_five_photos(page, base_url: str) -> None:
    """Sanity frames for 5 WebP at 1440 / 1280 / 768 / 390 via findtz+routes."""
    photo_routes = {
        "specialist-certificate": "findtz",
        "specialist-registraciya": "registraciya_tovarnogo_znaka",
        "specialist-prolongation": "prodlenie_tovarnogo_znaka",
        "specialist-opposition": "vozrazhenie_protiv_registracii_tovarnogo_znaka",
        "specialist-copyright": "zashchita_avtorskih_prav",
    }
  # snapshot photo crops in OUT verify subfolder
    vdir = OUT / "verify-frames"
    vdir.mkdir(parents=True, exist_ok=True)
    for vp_w, tag in ((1440, "1440"), (1280, "1280"), (768, "768"), (390, "390")):
        for photo, route in photo_routes.items():
            page.set_viewport_size({"width": vp_w, "height": 900})
            page.goto(f"{base_url}/{route}/", wait_until="networkidle", timeout=120000)
            page.wait_for_selector(".lx-photo-v4-expert-media img", timeout=20000)
            loc = page.locator(".lx-photo-v4-expert-media").first
            loc.screenshot(path=str(vdir / f"{photo}-{tag}.png"))


def main() -> int:
    OUT.mkdir(parents=True, exist_ok=True)
    docroot = Path(tempfile.mkdtemp(prefix="six-live-overlay-"))
    prepare_docroot(docroot)
    router = docroot / "router.php"
    router.write_text(ROUTER_PHP.format(root=str(ROOT), preview=str(docroot)), encoding="utf-8")
    proc = subprocess.Popen(
        ["php", "-S", f"127.0.0.1:{PORT}", str(router)],
        cwd=str(docroot),
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
    )
    time.sleep(0.5)
    base = f"http://127.0.0.1:{PORT}"
    try:
        from playwright.sync_api import sync_playwright
    except ImportError:
        proc.terminate()
        print("BLOCKER: playwright missing")
        return 2
    try:
        with sync_playwright() as p:
            browser = p.chromium.launch()
            page = browser.new_page()
            page.set_viewport_size({"width": 1440, "height": 900})
            page.goto(f"{live_host()}/", wait_until="networkidle", timeout=120000)
            home = page.locator(
                ".lh4-root .hero-right .expert-card, .hero-right .expert-card"
            ).first
            home.wait_for(state="visible", timeout=30000)
            page.evaluate("window.scrollTo(0,0)")
            home.screenshot(path=str(OUT / "home-expert-card-1440.png"))
            print("wrote", OUT / "home-expert-card-1440.png")
            for route in ROUTES:
                for tag, w, h in (("1440", 1440, 900), ("390", 390, 900)):
                    page.set_viewport_size({"width": w, "height": h})
                    page.goto(f"{base}/{route}/", wait_until="networkidle", timeout=120000)
                    page.wait_for_selector(".lx-photo-v4-expert-card img", timeout=30000)
                    page.wait_for_function(
                        """() => {
                          const m = document.querySelector('.lx-photo-v4-expert-media');
                          if (!m) return false;
                          const r = m.getBoundingClientRect();
                          const img = m.querySelector('img');
                          return r.height > 0 && r.width > 0 && img && img.naturalWidth > 0;
                        }""",
                        timeout=20000,
                    )
                    if tag == "390":
                        page.locator(".lx-photo-v4-expert-card").first.scroll_into_view_if_needed()
                        page.wait_for_timeout(250)
                    else:
                        page.evaluate("window.scrollTo(0,0)")
                    out = OUT / f"{route}-{tag}.png"
                    page.screenshot(path=str(out), full_page=False)
                    print("wrote", out)
            verify_five_photos(page, base)
            # contact sheet
            sheet = OUT / "specialists-on-light.png"
            rows = []
            for name in sorted((ROOT / "template/images/services-v4").glob("specialist-*.webp")):
                rows.append(
                    f'<figure><img src="/template/images/services-v4/{name.name}" alt="">'
                    f"<figcaption>{name.name}</figcaption></figure>"
                )
            html = (
                "<!DOCTYPE html><html><head><style>"
                "body{margin:0;background:#fff;font-family:Manrope,sans-serif}"
                "main{padding:24px;display:grid;grid-template-columns:repeat(5,1fr);gap:16px}"
                "figure{margin:0}img{width:100%;aspect-ratio:683/1024;object-fit:cover;"
                "object-position:var(--f,50% 26%);border-radius:16px;border:1px solid #dae6f6}"
                ".specialist-certificate{--f:50% 28%}.specialist-registraciya{--f:50% 22%}"
                ".specialist-prolongation{--f:50% 22%}.specialist-opposition{--f:50% 22%}"
                ".specialist-copyright{--f:50% 22%}"
                "figcaption{font-size:10px;color:#52637b;margin-top:6px}</style></head>"
                f"<body><main>{''.join(rows)}</main></body></html>"
            )
            (docroot / "sheet.html").write_text(html, encoding="utf-8")
            page.set_viewport_size({"width": 1440, "height": 900})
            page.goto(f"{base}/sheet.html", wait_until="networkidle")
            page.screenshot(path=str(sheet), full_page=True)
            print("wrote", sheet)
            browser.close()
    finally:
        proc.terminate()
        proc.wait(timeout=5)
        shutil.rmtree(docroot, ignore_errors=True)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())