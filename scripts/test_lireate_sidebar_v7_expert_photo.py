#!/usr/bin/env python3
"""Sidebar V7 must show Yuri Gorbachev portrait (person/11) like the home expert block."""
import pathlib
import unittest

ROOT = pathlib.Path(__file__).resolve().parents[1]
TEMPLATE = ROOT / "template/lireate"
JS = ROOT / "template/js/lireate-sitewide-sidebar-v7.js"
PATENT_DATA = ROOT / "pages/patents/_shared/lireate-patent-data.php"


class SidebarV7ExpertPhotoTests(unittest.TestCase):
    def test_v7_css_expert_is_compact_round_not_card(self):
        css = (ROOT / "template/css/lireate-sitewide-sidebar-v7.css").read_text(encoding="utf-8")
        self.assertIn("border-radius:50%", css)
        self.assertIn("width:64px", css)
        self.assertNotIn("border:1px solid var(--lsv7-line)!important", css.split(".lsv7-sidebar-expert")[1][:400])
        self.assertIn("lx-photo-v4", css)

    def test_js_skips_expert_on_lx_photo_v4_pages(self):
        js = JS.read_text(encoding="utf-8")
        self.assertIn('classList.contains("lx-photo-v4")', js)
    def test_canonical_sidebar_markup_has_yuri_photo(self):
        for name in ("lireate-sidebar-v7-content.inc.php", "lireate-sidebar-v7.html"):
            text = (TEMPLATE / name).read_text(encoding="utf-8")
            self.assertIn('class="lsv7-sidebar-expert"', text)
            self.assertIn("/template/images/team/person/11.webp", text)
            self.assertIn("Горбачев Юрий Александрович", text)

    def test_js_injects_expert_when_legacy_sidebar_lacks_photo(self):
        js = JS.read_text(encoding="utf-8")
        self.assertIn("ensureExpertPortrait", js)
        self.assertIn("lsv7-sidebar-expert", js)
        self.assertIn("person/11.webp", js)

    def test_patent_page_data_has_hero_portrait(self):
        text = PATENT_DATA.read_text(encoding="utf-8")
        block = text[text.find("if ($key === 'patent')") : text.find("if ($key === 'patent_prom_obr')")]
        self.assertIn("person/11.webp", block)
        self.assertIn("Горбачев Юрий Александрович", block)


if __name__ == "__main__":
    unittest.main()