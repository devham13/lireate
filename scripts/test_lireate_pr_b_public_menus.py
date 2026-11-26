#!/usr/bin/env python3
"""PR B guards: lh4 subnav + Light V4 on public CMS shells."""

import re
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SUBNAV_CSS = ROOT / "template/css/lireate-global-subnav-light-v4.css"
CHROME_CSS = ROOT / "template/css/lireate-public-cms-chrome.css"
SIDEBAR = ROOT / "pages/_shared/lireate-public-cms-sidebar.inc.php"
SERVICE_SAMPLE = ROOT / "pages/services/registraciya-tovarnogo-znaka/index.php"
PAGES = (
    ROOT / "pages/partners/_shared/lireate-partners-page.inc.php",
    ROOT / "pages/docs/_shared/lireate-docs-page.inc.php",
    ROOT / "pages/kontaktyi/_shared/lireate-contacts-page.inc.php",
)
USLUGI = ROOT / "template/page/uslugi/lireate-uslugi-cms.inc.php"


def _hex_to_rgb(h):
    h = h.lstrip("#")
    return tuple(int(h[i : i + 2], 16) for i in (0, 2, 4))


def _lum(c):
    c = c / 255.0
    return c / 12.92 if c <= 0.03928 else ((c + 0.055) / 1.055) ** 2.4


def contrast(fg, bg):
    l1 = 0.2126 * _lum(fg[0]) + 0.7152 * _lum(fg[1]) + 0.0722 * _lum(fg[2])
    l2 = 0.2126 * _lum(bg[0]) + 0.7152 * _lum(bg[1]) + 0.0722 * _lum(bg[2])
    lighter, darker = max(l1, l2), min(l1, l2)
    return (lighter + 0.05) / (darker + 0.05)


class PrBPublicMenusTests(unittest.TestCase):
    def test_global_subnav_css_cms_scope(self):
        sub_css = SUBNAV_CSS.read_text(encoding="utf-8")
        self.assertIn("body.lireate-theme-light-v4 .lh4-subnav", sub_css)
        self.assertNotIn("lireate-home-light-v4", sub_css)
        self.assertTrue(SUBNAV_CSS.name == "lireate-global-subnav-light-v4.css")

    def test_subnav_hides_at_1150(self):
        css = SUBNAV_CSS.read_text(encoding="utf-8")
        self.assertRegex(css, r"max-width:\s*1150px[\s\S]{0,200}display:\s*none")

    def test_subnav_link_contrast(self):
        self.assertGreaterEqual(contrast(_hex_to_rgb("1f212c"), _hex_to_rgb("ffffff")), 4.5)

    def test_pages_include_theme_and_subnav(self):
        for path in PAGES:
            text = path.read_text(encoding="utf-8")
            self.assertIn("lireate-theme-light-v4", text, path.name)
            self.assertIn("lireate_lh4_include_subnav", text, path.name)
            self.assertIn("lireate-cms-pages-light-v4.css", text, path.name)

    def test_uslugi_light_v4_and_fg_marker(self):
        text = USLUGI.read_text(encoding="utf-8")
        self.assertIn("lireate-theme-light-v4", text)
        self.assertIn("data-lireate-fg", text)
        self.assertIn("lireate_fg:uslugi-footer", (ROOT / "template/page/uslugi/lireate-uslugi-lib.php").read_text(encoding="utf-8"))

    def test_public_cms_chrome_assets(self):
        self.assertTrue(CHROME_CSS.is_file())
        sidebar = SIDEBAR.read_text(encoding="utf-8")
        self.assertNotIn("tochka.com", sidebar.lower())
        self.assertIn("leftSideBar", sidebar)
        css = CHROME_CSS.read_text(encoding="utf-8")
        self.assertRegex(css, r"min-width:\s*1151px")
        self.assertRegex(css, r"max-width:\s*1150px")

    def test_service_wired_with_chrome(self):
        text = SERVICE_SAMPLE.read_text(encoding="utf-8")
        self.assertIn("lireate-public-cms-chrome-wire:v1", text)
        self.assertIn("lireate_public_cms_include_chrome", text)
        self.assertIn("lx-nav-pill", text)


if __name__ == "__main__":
    unittest.main()