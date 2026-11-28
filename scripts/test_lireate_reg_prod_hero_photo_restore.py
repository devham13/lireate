#!/usr/bin/env python3
"""Reg/prod hero photo assets in index; visible breadcrumbs stay removed (#189)."""
import pathlib
import unittest

ROOT = pathlib.Path(__file__).resolve().parents[1]
REG = ROOT / "pages/services/registraciya-tovarnogo-znaka/index.php"
PROD = ROOT / "pages/services/prodlenie-tovarnogo-znaka/index.php"
MARKER = "lireate-six-services-photo-v4-20261126"


class RegProdHeroPhotoRestoreTests(unittest.TestCase):
    def _assert_page(self, html, slug):
        self.assertIn(MARKER, html)
        self.assertIn("lireate-six-services-photo-v4.css", html)
        self.assertIn("lireate-six-services-photo-v4.js", html)
        self.assertNotIn("blocks/lireate-breadcrumbs.inc.php", html)
        self.assertIn("lx-glass", html)
        self.assertIn("BreadcrumbList", html)

    def test_registraciya_hero_photo_assets_no_ui_breadcrumbs(self):
        self._assert_page(REG.read_text(encoding="utf-8"), "reg")

    def test_prodlenie_hero_photo_assets_no_ui_breadcrumbs(self):
        self._assert_page(PROD.read_text(encoding="utf-8"), "prod")


if __name__ == "__main__":
    unittest.main()