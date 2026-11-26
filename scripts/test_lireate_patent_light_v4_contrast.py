#!/usr/bin/env python3
"""WCAG contrast on live CMS shell + injected patent Light V4 (Playwright)."""

from __future__ import print_function

import importlib.util
import os
import re
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
_MIN = 4.5
_PAGES = (("patent", "/patent"), ("patent_prom_obr", "/patent_prom_obr"))
_SHOT = ROOT / "scripts" / "lireate_patent_light_v4_live_screenshots.py"


def _load_shot():
    spec = importlib.util.spec_from_file_location("patent_shot", _SHOT)
    mod = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(mod)
    return mod


def _lum(c):
    c = c / 255.0
    return c / 12.92 if c <= 0.03928 else ((c + 0.055) / 1.055) ** 2.4


def contrast_ratio(fg, bg):
    l1 = 0.2126 * _lum(fg[0]) + 0.7152 * _lum(fg[1]) + 0.0722 * _lum(fg[2])
    l2 = 0.2126 * _lum(bg[0]) + 0.7152 * _lum(bg[1]) + 0.0722 * _lum(bg[2])
    lighter = max(l1, l2)
    darker = min(l1, l2)
    return (lighter + 0.05) / (darker + 0.05)


def _parse_color(css_color):
    if not css_color or css_color in ("transparent", "rgba(0, 0, 0, 0)"):
        return None
    m = re.match(r"rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)", css_color)
    if m:
        return (int(m.group(1)), int(m.group(2)), int(m.group(3)))
    return None


def _has_playwright():
    try:
        import playwright  # noqa: F401

        return True
    except ImportError:
        return False


def _has_live_url():
    return bool(os.environ.get("LIREATE_PUBLIC_SITE_URL", "").strip())


def _measure_page(shot, key, path):
    cache = ROOT / ".cache"
    cache.mkdir(parents=True, exist_ok=True)
    base = shot.site_base()
    raw = cache / ("contrast-live-" + key + ".html")
    if not raw.is_file() or raw.stat().st_size < 1000:
        shot.fetch_html(base + path, raw)
    html = shot.inject_pr_assets(raw.read_text(encoding="utf-8", errors="replace"), key)
    fixture = cache / ("contrast-pr-" + key + ".html")
    fixture.write_text(html, encoding="utf-8")

    from playwright.sync_api import sync_playwright

    reports = []
    with sync_playwright() as p:
        browser = p.chromium.launch(args=["--no-sandbox"])
        page = browser.new_page(viewport={"width": 1440, "height": 900})
        page.goto(fixture.resolve().as_uri(), wait_until="domcontentloaded", timeout=90000)
        page.wait_for_selector("#lireate-patent-v4 h1", timeout=20000)
        page.wait_for_timeout(400)

        samples = (
            ("h1", "#lireate-patent-v4 h1"),
            ("hero", "#lireate-patent-v4 .lp4-hero-subtitle, #lireate-patent-v4 .lp4-hero-lead"),
            ("body_p", "#lireate-patent-v4 .lp4-info-card p"),
            ("body_li", "#lireate-patent-v4 .lp4-guarantee-list li, #lireate-patent-v4 .lp4-question-list li"),
        )
        for label, sel in samples:
            loc = page.locator(sel).first
            if loc.count() == 0:
                continue
            fg = _parse_color(loc.evaluate("el => getComputedStyle(el).color"))
            bg = _parse_color(
                loc.evaluate(
                    """el => {
                      let n = el;
                      while (n) {
                        const c = getComputedStyle(n).backgroundColor;
                        if (c && c !== 'rgba(0, 0, 0, 0)' && c !== 'transparent') return c;
                        n = n.parentElement;
                      }
                      return getComputedStyle(document.body).backgroundColor;
                    }"""
                )
            )
            if fg is None or bg is None:
                continue
            ratio = contrast_ratio(fg, bg)
            reports.append((key, label, ratio, fg, bg))

        wrapper = page.locator("#wrapper-ap, .wrapper, #page-content-wrapper").first
        if wrapper.count():
            wbg = _parse_color(wrapper.evaluate("el => getComputedStyle(el).backgroundColor"))
            if wbg:
                reports.append((key, "wrapper_bg", contrast_ratio((255, 255, 255), wbg), wbg, (255, 255, 255)))

        browser.close()
    return reports


@unittest.skipUnless(_has_playwright(), "playwright not installed")
@unittest.skipUnless(_has_live_url(), "LIREATE_PUBLIC_SITE_URL not set")
class PatentLightV4ContrastTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.shot = _load_shot()
        cls.all_reports = []
        for key, path in _PAGES:
            cls.all_reports.extend(_measure_page(cls.shot, key, path))

    def test_wcag_min_contrast(self):
        self.assertGreaterEqual(len(self.all_reports), 6)
        for key, label, ratio, fg, bg in self.all_reports:
            if label == "wrapper_bg":
                self.assertGreaterEqual(ratio, 3.0, "%s wrapper not light enough %s" % (key, fg))
                continue
            self.assertGreaterEqual(
                ratio,
                _MIN,
                "%s %s contrast %.2f fg=%s bg=%s" % (key, label, ratio, fg, bg),
            )


class PatentDarkExcludeInstallTests(unittest.TestCase):
    def test_dark_exclude_removes_patent_pages(self):
        spec = importlib.util.spec_from_file_location(
            "patinst", ROOT / "scripts" / "lireate_patent_light_v4_install.py"
        )
        mod = importlib.util.module_from_spec(spec)
        spec.loader.exec_module(mod)
        sample = "<?php $_lx = array('home','patent','patent_prom_obr','mktu'); ?>"
        out = mod.patch_main_dark_exclusion(sample)
        self.assertIn(mod.DARK_EXCLUDE_MARKER, out)
        self.assertNotIn("'patent'", out.split("'mktu'")[0])


if __name__ == "__main__":
    unittest.main(verbosity=2)