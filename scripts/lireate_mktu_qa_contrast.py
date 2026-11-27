#!/usr/bin/env python3
"""Contrast check + screenshots for /mktu QA V6 preview (1440 and 390)."""

import subprocess
import sys
import time
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
OUT = Path("/opt/cursor/artifacts/screenshots/mktu-qa-v6-20261126d")
HOST = "127.0.0.1"
PORT = 8801
URL_PATH = "/pages/mktu/_preview/cms-shell.php"
BASE = "http://{0}:{1}".format(HOST, PORT)
WIDTHS = (1440, 390)
MIN_RATIO = 4.5

SELECTORS = (
    "h1.sub-title",
    ".mktu_head h4",
    ".txt-content.news-content h2",
    ".txt-content.news-content p",
    ".txt-content.news-content a",
    ".block_contact h3",
    ".block_contact p",
    ".block_contact .content-button",
    "#lx-mktu-search",
)

BG_EVAL = """el => {
  const st = getComputedStyle(el);
  const own = st.backgroundColor;
  if (own && own !== 'rgba(0, 0, 0, 0)' && own !== 'transparent') return own;
  const img = st.backgroundImage || '';
  if (img.includes('gradient')) {
    if (img.includes('6d28d9') || img.includes('109, 40, 217')) return 'rgb(109, 40, 217)';
    if (img.includes('2563eb')) return 'rgb(37, 99, 235)';
    return 'rgb(37, 99, 235)';
  }
  let n = el.parentElement;
  while (n) {
    const bg = getComputedStyle(n).backgroundColor;
    if (bg && bg !== 'rgba(0, 0, 0, 0)' && bg !== 'transparent') return bg;
    n = n.parentElement;
  }
  return 'rgb(255, 255, 255)';
}"""


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
    parts = [
        int(float(x))
        for x in style_color.replace("rgba(", "")
        .replace("rgb(", "")
        .replace(")", "")
        .split(",")[:3]
    ]
    return tuple(parts)


def probe(page, selector, width, failures):
    loc = page.locator(selector).first
    if loc.count() == 0:
        failures.append("missing {0} width {1}".format(selector, width))
        return
    fg = parse_rgb(loc.evaluate("el => getComputedStyle(el).color"))
    bg = parse_rgb(loc.evaluate(BG_EVAL))
    ratio = contrast_ratio(fg, bg)
    if ratio < MIN_RATIO:
        failures.append(
            "{0} @{1}px contrast {2:.2f} fg={3} bg={4}".format(selector, width, ratio, fg, bg)
        )


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
                page = browser.new_page(viewport={"width": w, "height": 1200})
                resp = page.goto(BASE + URL_PATH, wait_until="networkidle", timeout=120000)
                if not resp or resp.status >= 400:
                    failures.append("HTTP {0} at width {1}".format(resp.status if resp else "none", w))
                for sel in SELECTORS:
                    probe(page, sel, w, failures)
                h4s = page.locator(".mktu_head h4")
                for i in range(h4s.count()):
                    probe(page, ".mktu_head h4 >> nth={0}".format(i), w, failures)
                ssilka_count = page.locator(".mktu_head a.ssilka").count()
                if ssilka_count < 45:
                    failures.append("expected 45 a.ssilka, got {0} width {1}".format(ssilka_count, w))
                for i in (0, 5, 17, 33, 34, 44):
                    probe(page, ".mktu_head a.ssilka >> nth={0}".format(i), w, failures)
                tag = "desktop" if w == 1440 else "mobile"
                full = OUT / "mktu-{0}-{1}.png".format(tag, w)
                page.screenshot(path=str(full), full_page=True)
                print("saved", full)
                head = page.locator("section.mktu_head")
                if head.count():
                    clip_shot = OUT / "mktu-head-circles-{0}-{1}.png".format(tag, w)
                    head.screenshot(path=str(clip_shot))
                    print("saved", clip_shot)
                page.close()
            browser.close()
    finally:
        server.terminate()
        server.wait(timeout=5)

    if failures:
        ok = False
        for line in failures:
            print("FAIL", line)
    else:
        print("OK contrast >= {0} for all probes".format(MIN_RATIO))
    return 0 if ok else 2


if __name__ == "__main__":
    raise SystemExit(main())