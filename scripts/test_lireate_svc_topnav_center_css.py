#!/usr/bin/env python3
"""Shared topnav center CSS wired on reg / findtz / prodlenie only."""
import pathlib
import unittest

ROOT = pathlib.Path(__file__).resolve().parents[1]
CSS = ROOT / "pages/services/_shared/lireate-svc-topnav-center.css"
VER = "20261128-hcv1"


class SvcTopnavCenterTests(unittest.TestCase):
    def test_shared_css_has_rules(self):
        text = CSS.read_text(encoding="utf-8")
        self.assertIn("lireate-svc-topnav-center", text)
        self.assertIn(":has(#leftSideBar)", text)
        self.assertIn("--lx-svc-nav-gap-below", text)

    def test_pages_link_css(self):
        for rel in (
            "pages/services/registraciya-tovarnogo-znaka/index.php",
            "pages/services/prodlenie-tovarnogo-znaka/index.php",
            "pages/services/proverka-tovarnogo-znaka/index.php",
        ):
            html = (ROOT / rel).read_text(encoding="utf-8")
            self.assertIn("lireate-svc-topnav-center.css", html, msg=rel)
            self.assertIn(VER, html, msg=rel)
            self.assertIn("lireate-svc-topnav-center", html, msg=rel)

    def test_findtz_without_old_translate(self):
        css = (
            ROOT / "pages/services/proverka-tovarnogo-znaka/assets/findtz-topnav-center.css"
        ).read_text(encoding="utf-8")
        self.assertNotIn("translateX(calc(var(--lpc-side-width", css)


if __name__ == "__main__":
    unittest.main()