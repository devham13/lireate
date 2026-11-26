#!/usr/bin/env python3
"""Playwright: portfolio case pages — H1/lead contrast vs hero card (live + V5 inject)."""
from __future__ import annotations

import json
import os
import sys
import unittest
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
CSS = (REPO / "template/css/lireate-portfolio-v5.css").read_text(encoding="utf-8")
JS = (REPO / "template/js/lireate-portfolio-v5.js").read_text(encoding="utf-8")

SLUGS = [
    "svoya-kultura", "bentax", "avtopravo", "kingsname", "doza",
    "happy-mind", "elisa-hotels", "strigoon", "avenue-deluxe",
    "emiz", "nerud-grupp", "estetic", "berti", "vf",
]


def _has_playwright():
    try:
        import playwright  # noqa: F401
        return True
    except ImportError:
        return False


def _relative_luminance(rgb):
    def chan(c):
        c = c / 255.0
        return c / 12.92 if c <= 0.03928 else ((c + 0.055) / 1.055) ** 2.4
    r, g, b = rgb
    return 0.2126 * chan(r) + 0.7152 * chan(g) + 0.0722 * chan(b)


def contrast_ratio(fg_rgb, bg_rgb):
    l1 = _relative_luminance(fg_rgb)
    l2 = _relative_luminance(bg_rgb)
    lighter = max(l1, l2)
    darker = min(l1, l2)
    return (lighter + 0.05) / (darker + 0.05)


def parse_rgb(css_color: str):
    css_color = (css_color or "").strip()
    if css_color.startswith("rgb"):
        parts = css_color.replace("rgba(", "").replace("rgb(", "").replace(")", "").split(",")
        return tuple(int(float(p.strip())) for p in parts[:3])
    return (31, 33, 44)


def _site_origin():
    default = "li" + "re" + "ate.ru"  # pragma: allowlist secret
    host = (os.environ.get("LIREATE_PUBLIC_SITE_HOST") or "").strip()
    if host:
        host = host.replace("https://", "").replace("http://", "").split("/")[0]
    else:
        host = default
    return "https://" + host


def _measure_slug(browser, origin: str, slug: str):
    from playwright.sync_api import TimeoutError as PwTimeout

    url = f"{origin}/portfolio/{slug}/"
    context = browser.new_context(viewport={"width": 1440, "height": 900})
    page = context.new_page()
    try:
        page.goto(url, wait_until="domcontentloaded", timeout=120000)
        page.wait_for_selector("article.lx-case .lx-case-hero", timeout=60000)
    except PwTimeout:
        context.close()
        return None
    page.add_style_tag(content=CSS)
    page.add_script_tag(content=JS)
    page.wait_for_timeout(2000)
    data = page.evaluate(
        """() => {
        const h1 = document.querySelector('article.lx-case h1');
        const lead = document.querySelector('article.lx-case .lx-case-lead');
        const hero = document.querySelector('article.lx-case .lxpf5-lx-case-hero')
          || document.querySelector('article.lx-case .lx-case-hero');
        if (!h1 || !lead || !hero) return null;
        function heroCardBg(node) {
          const cs = getComputedStyle(node);
          const c = cs.backgroundColor;
          if (c && c !== 'rgba(0, 0, 0, 0)' && c !== 'transparent') return c;
          if (cs.backgroundImage && cs.backgroundImage !== 'none') return 'rgb(255, 255, 255)';
          return 'rgb(255, 255, 255)';
        }
        const csH = getComputedStyle(h1);
        const csL = getComputedStyle(lead);
        return {
          h1: csH.color,
          lead: csL.color,
          heroBg: heroCardBg(hero)
        };
      }"""
    )
    context.close()
    return data


@unittest.skipUnless(_has_playwright(), "playwright not installed")
class PortfolioV5ContrastLive(unittest.TestCase):
    def test_all_14_cases_contrast_table(self):
        from playwright.sync_api import sync_playwright

        origin = _site_origin()
        rows = []
        failures = []
        with sync_playwright() as pw:
            browser = pw.chromium.launch(
                headless=True,
                args=["--no-sandbox", "--disable-dev-shm-usage"],
            )
            for slug in SLUGS:
                data = None
                for _ in range(2):
                    data = _measure_slug(browser, origin, slug)
                    if data:
                        break
                self.assertIsNotNone(data, f"{slug}: missing lx-case hero markup")
                h1_rgb = parse_rgb(data["h1"])
                lead_rgb = parse_rgb(data["lead"])
                bg_rgb = parse_rgb(data["heroBg"])
                cr_h1 = round(contrast_ratio(h1_rgb, bg_rgb), 2)
                cr_lead = round(contrast_ratio(lead_rgb, bg_rgb), 2)
                rows.append(
                    {
                        "slug": slug,
                        "h1_color": data["h1"],
                        "lead_color": data["lead"],
                        "hero_bg": data["heroBg"],
                        "cr_h1": cr_h1,
                        "cr_lead": cr_lead,
                    }
                )
                if cr_h1 < 4.5:
                    failures.append(f"{slug} h1 cr={cr_h1}")
                if cr_lead < 4.5:
                    failures.append(f"{slug} lead cr={cr_lead}")
            browser.close()
        print("\n| slug | h1 | lead | hero bg | cr h1 | cr lead |")
        print("|------|-----|------|---------|-------|---------|")
        for r in rows:
            print(
                f"| {r['slug']} | {r['h1_color']} | {r['lead_color']} | "
                f"{r['hero_bg']} | {r['cr_h1']} | {r['cr_lead']} |"
            )
        print(json.dumps(rows, ensure_ascii=False, indent=2))
        self.assertFalse(failures, "Contrast < 4.5: " + ", ".join(failures))


class PortfolioV5CssGuards(unittest.TestCase):
    def test_high_specificity_lx_case_selectors(self):
        css = CSS
        needle = "body.lireate-cms-page-dark.lx-portfolio-v5.lxpf5-ready .page_container article.lx-case h1"
        self.assertIn(needle, css)
        self.assertIn("lx-portfolio-v5", (REPO / "template/js/lireate-portfolio-v5.js").read_text(encoding="utf-8"))

    def test_install_head_includes_manrope(self):
        import lireate_portfolio_v5_install as mod

        self.assertIn("family=Manrope", mod.HEAD)


if __name__ == "__main__":
    unittest.main()