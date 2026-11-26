#!/usr/bin/env python3
"""Contrast check + screenshots for /mktu QA V6 preview (1440 and 390)."""

import math
import subprocess
import sys
import time
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
OUT = Path("/opt/cursor/artifacts/screenshots/mktu-qa-v6-20261126c")
HOST = "127.0.0.1"
PORT = 8801
URL_PATH = "/pages/mktu/_preview/cms-shell.php"
BASE = "http://{0}:{1}".format(HOST, PORT)
WIDTHS = (1440, 390)

SELECTORS = (
    "h1.sub-title",
    ".txt-content.news-content h2",
    ".txt-content.news-content p",
    ".txt-content.news-content a",
    ".block_contact h3",
    ".block_contact p",
    "#lx-mktu-search",
)


def srgb_to_lin(channel):
    c = channel / 255.0
    if c <= 0.04045:
        return c / 12.92
    return ((c + 0.055) / 1.055) ** 2.4


def rel_luminance(rgb):
    r, g, b = rgb
    return 0.2126 * srgb_to_lin(r) + 0.7152 * srgb_to_lin(g) + 0.0722 * srgb_to_lin(b)


def contrast_ratio(fg_rgb, bg_rgb):
    l1 = rel_luminance(fg_rgb)
    l2 = rel_luminance(bg_rgb)
    lighter = max(l1, l2)
    darker = min(l1, l2)
    return (lighter + 0.05) / (darker + 0.05)


def parse_rgb(style_color):
    parts = [int(float(x)) for x in style_color.replace("rgba(", "").replace("rgb(", "").replace(")", "").split(",")[:3]]
    return tuple(parts)


def main():
    try:
        from playwright.sync_api import sync_playwright
    except ImportError:
        print("BLOCKER: playwright not installed")
        return 2

    OUT.mkdir(parents=True, exist_ok=True)
    server = subprocess.Popen(
        ["php", "-S", "{0}:{1}".format(HOST, PORT), "-t", str(REPO)],
        cwd=str(REPO),
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
    )
    time.sleep(0.6)
    ok = True
    failures = []
    try:
        with sync_playwright() as p:
            browser = p.chromium.launch()
            for w in WIDTHS:
                page = browser.new_page(viewport={"width": w, "height": 900})
                resp = page.goto(BASE + URL_PATH, wait_until="networkidle", timeout=120000)
                if not resp or resp.status >= 400:
                    failures.append("HTTP {0} at width {1}".format(resp.status if resp else "none", w))
                    ok = False
                for sel in SELECTORS:
                    loc = page.locator(sel).first
                    if loc.count() == 0:
                        failures.append("missing {0} width {1}".format(sel, w))
                        ok = False
                        continue
                    fg = parse_rgb(loc.evaluate("el => getComputedStyle(el).color"))
                    bg = parse_rgb(
                        loc.evaluate(
                            """el => {
                              let n = el;
                              while (n) {
                                const bg = getComputedStyle(n).backgroundColor;
                                if (bg && bg !== 'rgba(0, 0, 0, 0)' && bg !== 'transparent') return bg;
                                n = n.parentElement;
                              }
                              return 'rgb(255, 255, 255)';
                            }"""
                        )
                    )
                    ratio = contrast_ratio(fg, bg)
                    if ratio < 4.5:
                        failures.append(
                            "{0} @{1}px contrast {2:.2f} fg={3} bg={4}".format(sel, w, ratio, fg, bg)
                        )
                        ok = False
                tag = "desktop" if w == 1440 else "mobile"
                shot = OUT / "mktu-{0}-{1}.png".format(tag, w)
                page.screenshot(path=str(shot), full_page=True)
                print("saved", shot)
                page.close()
            browser.close()
    finally:
        server.terminate()
        server.wait(timeout=5)

    if failures:
        for line in failures:
            print("FAIL", line)
    else:
        print("OK contrast >= 4.5 for all probes")
    return 0 if ok else 2


if __name__ == "__main__":
    raise SystemExit(main())