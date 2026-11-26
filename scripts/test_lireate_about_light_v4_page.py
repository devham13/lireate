#!/usr/bin/env python3
"""Guards: /o_kompanii Light V4 assets, no 404 CSS, CMS contrast overrides."""

import re
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SHARED_INC = ROOT / "pages" / "o_kompanii" / "_shared" / "lireate-about-page.inc.php"
TEMPLATE_INC = ROOT / "template" / "page" / "o_kompanii" / "lireate-about-page.inc.php"
PREVIEW = ROOT / "pages" / "o_kompanii" / "_preview" / "index.php"
CMS_CSS = ROOT / "template" / "css" / "lireate-cms-pages-light-v4.css"

HREF_RE = re.compile(
    r'<(?:link|script)[^>]+(?:href|src)="([^"?]+\.(?:css|js))',
    re.I,
)
FORBIDDEN = (
    "lireate-service-dark.css",
    "/pages/services/_shared/lireate-service-dark",
)


def repo_path_from_href(href):
    if not href.startswith("/"):
        return None
    return ROOT / href.lstrip("/")


def linked_assets(php_text):
    return [m.group(1) for m in HREF_RE.finditer(php_text)]


class AboutLightV4PageTests(unittest.TestCase):
    def test_shared_inc_has_footer_and_light_v4(self):
        text = SHARED_INC.read_text(encoding="utf-8")
        self.assertIn("lireate-blog-footer.css", text)
        self.assertIn("lireate_about_h($la_footer_css)", text)
        self.assertIn("lireate-light-v4.css", text)
        self.assertNotIn("lireate-service-dark", text)

    def test_shared_matches_template_asset_stack(self):
        for key in (
            "lireate-blog-footer.css",
            "lireate-light-v4.css",
            "lireate-cms-pages-light-v4.css",
            "lireate-light-v4.js",
        ):
            self.assertIn(key, SHARED_INC.read_text(encoding="utf-8"))
            self.assertIn(key, TEMPLATE_INC.read_text(encoding="utf-8"))

    def test_linked_css_js_exist_in_repo(self):
        for rel in (SHARED_INC, TEMPLATE_INC):
            for href in linked_assets(rel.read_text(encoding="utf-8")):
                for bad in FORBIDDEN:
                    self.assertNotIn(bad, href)
                path = repo_path_from_href(href)
                if path is None:
                    continue
                self.assertTrue(path.is_file(), "missing asset for {}: {}".format(rel.name, href))

    def test_preview_has_no_service_dark(self):
        text = PREVIEW.read_text(encoding="utf-8")
        self.assertNotIn("lireate-service-dark", text)
        self.assertIn("lireate-cms-page-dark", text)

    def test_cms_light_v4_about_wins_over_dark_shell(self):
        css = CMS_CSS.read_text(encoding="utf-8")
        marker = "body.lireate-cms-page-dark.lireate-about-body.lireate-theme-light-v4"
        self.assertIn(marker, css)
        idx = css.find(marker)
        block = css[idx : idx + 2400]
        self.assertIn("#2d4764", block)
        self.assertIn("body.lireate-about-body.lireate-cms-page-dark", css)


if __name__ == "__main__":
    unittest.main()