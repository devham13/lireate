#!/usr/bin/env python3
"""Nav V3: hide «Оплата услуг» Tochka link in left sidebar styling/JS."""

import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
CSS = ROOT / "template/css/lireate-navigation-dual-v3.css"
JS = ROOT / "template/js/lireate-navigation-dual-v3.js"


class NavV3HideOplataTests(unittest.TestCase):
    def test_css_hides_tochka_payment_sidebar_link(self):
        css = CSS.read_text(encoding="utf-8")
        self.assertIn("tochka.com/myprofile/lireat", css)
        compact = css.replace(" ", "")
        self.assertIn("display:none!important", compact)

    def test_js_hides_payment_label(self):
        js = JS.read_text(encoding="utf-8")
        self.assertIn("Оплата услуг", js)
        self.assertIn("hidePaymentLink", js)


if __name__ == "__main__":
    unittest.main()