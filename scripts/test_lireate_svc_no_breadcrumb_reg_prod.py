#!/usr/bin/env python3
"""Visible breadcrumbs removed only on registraciya + prodlenie flagship slugs."""
import pathlib
import unittest

ROOT = pathlib.Path(__file__).resolve().parents[1]
PAIRS = (
    ("registraciya-tovarnogo-znaka", "Регистрация товарного знака"),
    ("prodlenie-tovarnogo-znaka", "Продление товарного знака"),
)
OTHER = "registraciya-tovarnogo-znaka-v-kitae"


class ServiceNoBreadcrumbFlagshipTests(unittest.TestCase):
    def test_flagship_pages_skip_breadcrumb_include(self):
        for slug, title in PAIRS:
            text = (ROOT / "pages/services" / slug / "index.php").read_text(encoding="utf-8")
            self.assertNotIn("blocks/lireate-breadcrumbs.inc.php", text, slug)
            self.assertIn("BreadcrumbList", text, slug)
            self.assertIn(title, text, slug)

    def test_other_services_keep_breadcrumb_include(self):
        text = (ROOT / "pages/services" / OTHER / "index.php").read_text(encoding="utf-8")
        self.assertIn("blocks/lireate-breadcrumbs.inc.php", text)


if __name__ == "__main__":
    unittest.main()