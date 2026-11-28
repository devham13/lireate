#!/usr/bin/env python3
"""Docs CMS page: Light V4 includes and CSS overrides."""
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


class TestDocsLightV4(unittest.TestCase):
    def test_docs_inc_links_light_v4(self):
        inc = (ROOT / "pages/docs/_shared/lireate-docs-page.inc.php").read_text(encoding="utf-8")
        self.assertIn("lireate-cms-pages-light-v4.css", inc)
        self.assertIn("lireate-light-v4.css", inc)
        self.assertIn("lireate-theme-light-v4", inc)

    def test_main_body_cond_includes_theme(self):
        from lireate_cms_main_body_class import DOCS_BODY_COND

        self.assertIn("lireate-theme-light-v4", DOCS_BODY_COND)

    def test_cms_pages_css_has_docs_block(self):
        css = (ROOT / "template/css/lireate-cms-pages-light-v4.css").read_text(encoding="utf-8")
        self.assertIn("body.lireate-docs-body.lireate-theme-light-v4", css)
        self.assertIn("--ld-bg: #ffffff", css)


if __name__ == "__main__":
    unittest.main()