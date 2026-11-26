#!/usr/bin/env python3
"""Home Light V4 layout guards (subnav order, sidebar height CSS)."""

import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
HOME_INC = ROOT / "pages" / "home" / "_shared" / "lireate-home-page.inc.php"
HOME_CSS = ROOT / "template" / "css" / "lireate-home-light-v4.css"
CMS_CSS = ROOT / "template" / "css" / "lireate-cms-pages-light-v4.css"


class HomeLightV4LayoutTests(unittest.TestCase):
    def test_subnav_before_hero_in_markup(self):
        text = HOME_INC.read_text(encoding="utf-8")
        sub = text.find('class="lh4-subnav"')
        hero = text.find('<section class="hero">')
        self.assertGreater(sub, 0)
        self.assertGreater(hero, 0)
        self.assertLess(sub, hero, "lh4-subnav must precede hero section")

    def test_sidebar_full_viewport_css(self):
        home_css = HOME_CSS.read_text(encoding="utf-8")
        cms_css = CMS_CSS.read_text(encoding="utf-8")
        self.assertIn("min-height: 100vh", home_css)
        self.assertIn("#leftSideBar", home_css)
        self.assertIn("min-height: 100vh", cms_css)

    def test_tablet_header_breakpoint_hides_subnav_through_1150(self):
        """992–1150px: #topBar visible — lh4-subnav must stay hidden (no double header)."""
        css = HOME_CSS.read_text(encoding="utf-8")
        self.assertIn(".lh4-subnav", css)
        block_start = css.find("@media (max-width: 1150px)")
        self.assertGreater(block_start, 0)
        block = css[block_start : block_start + 400]
        self.assertIn(".lh4-subnav", block)
        self.assertIn("display: none", block)
        stale = css.find("@media (max-width: 991px)")
        if stale >= 0:
            stale_block = css[stale : stale + 400]
            self.assertNotIn(".lh4-subnav", stale_block)

    def test_subnav_shown_only_from_1151_css(self):
        """Subnav visible only above lu-viewport 1150px (#topBar breakpoint)."""
        css = HOME_CSS.read_text(encoding="utf-8")
        hide_at = css.find("@media (max-width: 1150px)")
        subnav_rules = css.find(".lh4-subnav", hide_at)
        self.assertGreater(subnav_rules, hide_at)
        self.assertNotRegex(
            css,
            r"@media\s*\(\s*max-width:\s*1151px\s*\)[\s\S]{0,500}\.lh4-subnav[\s\S]{0,120}display:\s*none",
        )

    def test_lu_viewport_topbar_breakpoint_documented_at_1150(self):
        """Home subnav hide breakpoint must match theme #topBar (1150px in vendored lu-shell CSS)."""
        la = ROOT / "tests/fixtures/live-render/live-assets/template/css"
        chunks = []
        for name in (
            "lireate-bundle-f80a3f772af93748.css",
            "lireate-menu-redesign.css",
            "main/style3.css",
        ):
            path = la / name
            if path.is_file():
                chunks.append(path.read_text(encoding="utf-8", errors="replace"))
        text = "\n".join(chunks)
        self.assertTrue(chunks, "expected vendored lu-shell CSS under live-assets")
        compact = text.replace(" ", "")
        self.assertIn("max-width:1150px", compact)
        self.assertTrue("topBar" in text or "#leftSideBar" in text)


def _has_playwright():
    try:
        import playwright  # noqa: F401

        return True
    except ImportError:
        return False


@unittest.skipUnless(_has_playwright(), "playwright not installed")
class HomeLightV4HeaderPlaywrightTests(unittest.TestCase):
    """Optional: fixture home at 992/1024/1150/1151 — single visible header bar."""

    FIXTURE = ROOT / "tests" / "fixtures" / "live-render" / "after" / "home.html"
    WIDTHS_HIDE = (992, 1024, 1150)
    WIDTH_SHOW = (1151,)

    def test_subnav_display_matches_viewport(self):
        from playwright.sync_api import sync_playwright

        if not self.FIXTURE.is_file():
            self.skipTest("fixture home.html missing")

        with sync_playwright() as p:
            browser = p.chromium.launch(args=["--no-sandbox"])
            for width in self.WIDTHS_HIDE:
                page = browser.new_page(viewport={"width": width, "height": 900})
                page.goto(self.FIXTURE.as_uri())
                page.wait_for_timeout(800)
                sub = page.locator(".lh4-subnav").first
                if sub.count():
                    self.assertEqual(
                        sub.evaluate("el => getComputedStyle(el).display"),
                        "none",
                        "subnav must be hidden at {}px".format(width),
                    )
                top = page.locator("#topBar").first
                if top.count():
                    self.assertNotEqual(
                        top.evaluate("el => getComputedStyle(el).display"),
                        "none",
                        "#topBar expected visible at {}px".format(width),
                    )
                page.close()
            for width in self.WIDTH_SHOW:
                page = browser.new_page(viewport={"width": width, "height": 900})
                page.goto(self.FIXTURE.as_uri())
                page.wait_for_timeout(800)
                sub = page.locator(".lh4-subnav").first
                if sub.count():
                    self.assertNotEqual(
                        sub.evaluate("el => getComputedStyle(el).display"),
                        "none",
                        "subnav must show at {}px".format(width),
                    )
                page.close()
            browser.close()


if __name__ == "__main__":
    unittest.main()