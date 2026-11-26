#!/usr/bin/env python3
"""PR A hero screenshots: first viewport, scrollY=0, CMS chrome + sidebar (not full-page scroll)."""

import os
import subprocess
import sys
import time
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "docs/screenshots/pr-a-home-hero"
MAIN_WT = Path("/agent/repos/lireate-main-wt")
PORT = int(os.environ.get("LIREATE_PR_A_SHOT_PORT", "19081"))
ROUTER = ROOT / "pages/home/_preview/router.php"
VIEWPORTS = (
    ("1440", 1440, 900),
    ("1280", 1280, 800),
    ("390", 390, 844),
)


def start_server(repo, router):
    env = os.environ.copy()
    for key in ("LIREATE_PUBLIC_SITE_URL", "LIREATE_PUBLIC_SITE_HOST"):
        env.pop(key, None)
    proc = subprocess.Popen(
        ["php", "-S", "127.0.0.1:{0}".format(PORT), str(router)],
        cwd=str(repo),
        env=env,
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
    )
    time.sleep(0.5)
    return proc


def shot_page(page, url, out_path, w, h):
    page.set_viewport_size({"width": w, "height": h})
    page.goto(url, wait_until="networkidle", timeout=120000)
    page.evaluate(
        """() => {
        document.body.classList.add('lireate-screenshot-capture');
        document.querySelectorAll('.reveal').forEach(el => el.classList.add('visible'));
        window.scrollTo(0, 0);
    }"""
    )
    img = page.locator(".expert-photo img").first
    img.wait_for(state="attached", timeout=15000)
    natural = img.evaluate(
        "(el) => ({ w: el.naturalWidth, h: el.naturalHeight, complete: el.complete })"
    )
    if not natural.get("complete") or natural.get("w", 0) < 10:
        raise RuntimeError("hero portrait failed to load: {0}".format(natural))
    page.screenshot(path=str(out_path), full_page=False)


def capture_set(label, proc_repo):
    base = "http://127.0.0.1:{0}/".format(PORT)
    proc = start_server(proc_repo, ROUTER if proc_repo == ROOT else ROUTER)
    try:
        from playwright.sync_api import sync_playwright
    except ImportError:
        print("BLOCKER: playwright missing")
        return 2
    with sync_playwright() as p:
        browser = p.chromium.launch()
        page = browser.new_page()
        for suffix, w, h in VIEWPORTS:
            out = OUT / ("{0}-hero-{1}.png".format(label, suffix))
            shot_page(page, base, out, w, h)
            print("wrote {0}".format(out))
        browser.close()
    proc.terminate()
    proc.wait(timeout=5)
    return 0


def capture_before_static():
    """Frozen before/home.html from live-render (main-era hero)."""
    html = ROOT / "tests/fixtures/live-render/before/home.html"
    if not html.is_file():
        print("WARN: skip before — no before/home.html")
        return 0
    proc = subprocess.Popen(
        ["python3", "-m", "http.server", str(PORT), "--bind", "127.0.0.1"],
        cwd=str(ROOT),
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
    )
    time.sleep(0.3)
    try:
        from playwright.sync_api import sync_playwright
    except ImportError:
        return 2
    rel = "/tests/fixtures/live-render/before/home.html"
    base = "http://127.0.0.1:{0}{1}".format(PORT, rel)
    with sync_playwright() as p:
        browser = p.chromium.launch()
        page = browser.new_page()
        for suffix, w, h in VIEWPORTS:
            page.set_viewport_size({"width": w, "height": h})
            page.goto(base, wait_until="networkidle", timeout=180000)
            page.evaluate("() => { window.scrollTo(0, 0); }")
            out = OUT / ("before-hero-{0}.png".format(suffix))
            page.screenshot(path=str(out), full_page=False)
            print("wrote {0}".format(out))
        browser.close()
    proc.terminate()
    proc.wait(timeout=5)
    return 0


def main():
    OUT.mkdir(parents=True, exist_ok=True)
    capture_before_static()
    rc = capture_set("after", ROOT)
    return rc


if __name__ == "__main__":
    sys.exit(main())