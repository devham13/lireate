#!/usr/bin/env python3
"""Sidebar V7 must not show an in-sidebar expert portrait (hero-only team photos)."""
import pathlib
import unittest

ROOT = pathlib.Path(__file__).resolve().parents[1]
TEMPLATE = ROOT / "template/lireate"
JS = ROOT / "template/js/lireate-sitewide-sidebar-v7.js"
PATENT_DATA = ROOT / "pages/patents/_shared/lireate-patent-data.php"


class SidebarV7NoExpertPhotoTests(unittest.TestCase):
    def test_v7_css_has_no_sidebar_expert_rules(self):
        css = (ROOT / "template/css/lireate-sitewide-sidebar-v7.css").read_text(encoding="utf-8")
        self.assertNotIn(".lsv7-sidebar-expert", css)

    def test_canonical_sidebar_markup_has_no_expert_photo(self):
        for name in ("lireate-sidebar-v7-content.inc.php", "lireate-sidebar-v7.html"):
            text = (TEMPLATE / name).read_text(encoding="utf-8")
            self.assertNotIn('class="lsv7-sidebar-expert"', text)
            self.assertNotIn("/template/images/team/person/11.webp", text)

    def test_js_does_not_inject_sidebar_expert(self):
        js = JS.read_text(encoding="utf-8")
        self.assertNotIn("ensureExpertPortrait", js)
        self.assertNotIn("lsv7-sidebar-expert", js)

    def test_patent_page_data_has_hero_portrait(self):
        text = PATENT_DATA.read_text(encoding="utf-8")
        block = text[text.find("if ($key === 'patent')") : text.find("if ($key === 'patent_prom_obr')")]
        self.assertIn("person/11.webp", block)
        self.assertIn("Горбачев Юрий Александрович", block)


if __name__ == "__main__":
    unittest.main()