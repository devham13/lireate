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

    def test_mobile_single_header_hides_subnav(self):
        """390px viewport: only CMS #topBar, not duplicate lh4-subnav strip."""
        css = HOME_CSS.read_text(encoding="utf-8")
        self.assertIn("max-width: 991px", css)
        self.assertIn(".lh4-subnav", css)
        self.assertIn("display: none", css)
        block_start = css.find("@media (max-width: 991px)")
        self.assertGreater(block_start, 0)
        block = css[block_start : block_start + 400]
        self.assertIn(".lh4-subnav", block)
        self.assertIn("display: none", block)


if __name__ == "__main__":
    unittest.main()