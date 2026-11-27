#!/usr/bin/env python3
"""Mobile footer padding must target body.lireate-svc-body (not misplaced lireate-svc-page on body)."""
import pathlib
import re
import unittest

ROOT = pathlib.Path(__file__).resolve().parents[1]
LIGHT = ROOT / "pages/services/_shared/lireate-light-v4.css"
SIDEBAR = ROOT / "template/css/lireate-sitewide-sidebar-v7.css"


class FooterMobilePaddingTests(unittest.TestCase):
    def test_shared_light_v4_targets_svc_body_not_only_svc_page_on_body(self):
        css = LIGHT.read_text(encoding="utf-8")
        block = css[css.find("Мобильный подвал") : css.find("Legacy breadcrumb")]
        self.assertIn("body.lireate-svc-body footer.lx-site-footer", block)
        self.assertIn("calc(120px", block)
        self.assertNotIn("max-width: 768px", block)
        self.assertNotRegex(
            block,
            r"body\.lireate-theme-light-v4\.lireate-svc-page\s+footer",
        )

    def test_sidebar_v7_footer_padding_mobile(self):
        css = SIDEBAR.read_text(encoding="utf-8")
        self.assertIn("body.lsv7-managed footer.lx-site-footer", css)
        self.assertIn("calc(120px", css)


if __name__ == "__main__":
    unittest.main()