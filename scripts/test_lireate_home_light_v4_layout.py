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


if __name__ == "__main__":
    unittest.main()