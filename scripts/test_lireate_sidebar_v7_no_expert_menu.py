#!/usr/bin/env python3
"""Sitewide sidebar V7 must never show expert portrait in the left menu."""
import pathlib
import unittest

ROOT = pathlib.Path(__file__).resolve().parents[1]
TEMPLATE = ROOT / "template/lireate"
JS = ROOT / "template/js/lireate-sitewide-sidebar-v7.js"
CSS = ROOT / "template/css/lireate-sitewide-sidebar-v7.css"


class SidebarV7NoExpertMenuTests(unittest.TestCase):
    def test_canonical_sidebar_has_no_expert_markup(self):
        for name in ("lireate-sidebar-v7-content.inc.php", "lireate-sidebar-v7.html"):
            text = (TEMPLATE / name).read_text(encoding="utf-8")
            self.assertNotIn('class="lsv7-sidebar-expert"', text)
            self.assertNotIn("/template/images/team/person/", text)

    def test_js_does_not_inject_expert(self):
        js = JS.read_text(encoding="utf-8")
        self.assertNotIn("ensureExpertPortrait", js)
        self.assertNotIn("person/11.webp", js)
        self.assertIn("removeChild(legacyExpert)", js)

    def test_expert_hidden_if_legacy_markup_present(self):
        css = CSS.read_text(encoding="utf-8")
        self.assertIn(".lsv7-sidebar-expert{display:none", css)


if __name__ == "__main__":
    unittest.main()