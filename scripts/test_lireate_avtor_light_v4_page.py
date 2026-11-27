#!/usr/bin/env python3
"""Guards: /avtor/ Light V4 assets and SEO helpers."""

import re
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SHARED_INC = ROOT / "pages" / "avtor" / "_shared" / "lireate-avtor-page.inc.php"
TEMPLATE_INC = ROOT / "template" / "page" / "avtor" / "lireate-avtor-page.inc.php"


def _lum(channel):
    c = channel / 255.0
    return c / 12.92 if c <= 0.03928 else ((c + 0.055) / 1.055) ** 2.4


def contrast_ratio(fg, bg):
    l1 = 0.2126 * _lum(fg[0]) + 0.7152 * _lum(fg[1]) + 0.0722 * _lum(fg[2])
    l2 = 0.2126 * _lum(bg[0]) + 0.7152 * _lum(bg[1]) + 0.0722 * _lum(bg[2])
    lighter = max(l1, l2)
    darker = min(l1, l2)
    return (lighter + 0.05) / (darker + 0.05)


def _hex_to_rgb(h):
    h = h.lstrip("#")
    return tuple(int(h[i : i + 2], 16) for i in (0, 2, 4))


class AvtorLightV4PageTests(unittest.TestCase):
    def test_shared_has_light_stack(self):
        text = SHARED_INC.read_text(encoding="utf-8")
        self.assertIn("lireate-light-v4.css", text)
        self.assertIn("lireate-avtor.css", text)
        self.assertNotIn("lireate-cms-pages-light-v4.css", text)
        self.assertIn('aria-current="page">Автор', text)
        self.assertIn("Горбачев Юрий Александрович", text)
        self.assertNotIn("lireate-service-dark", text)
        self.assertIn("</div>\n<?php lireate_avtor_render_footer(); ?>", text)
        self.assertNotIn("lireate_avtor_render_footer(); ?>\n</div>", text)

    def test_template_synced(self):
        self.assertTrue(TEMPLATE_INC.is_file(), "run lireate_avtor_sync_template.py")
        self.assertEqual(
            SHARED_INC.read_text(encoding="utf-8"),
            TEMPLATE_INC.read_text(encoding="utf-8"),
        )

    def test_avtor_not_service_slug(self):
        runtime = (ROOT / "data" / "lireate-seo-runtime.php").read_text(encoding="utf-8")
        block = re.search(r"function lireate_seo_service_with_slash\(\)[\s\S]*?return array\(([\s\S]*?)\);", runtime)
        self.assertIsNotNone(block)
        self.assertNotIn("'avtor'", block.group(1))

    def test_avtor_schema_helper(self):
        schema = (ROOT / "data" / "lireate-seo-schema.inc.php").read_text(encoding="utf-8")
        self.assertIn("lireate_seo_render_avtor_schema_scripts", schema)
        self.assertIn("'ProfilePage'", schema)

    def test_body_and_footer_contrast_tokens_in_css(self):
        css = (ROOT / "pages" / "avtor" / "_shared" / "lireate-avtor.css").read_text(encoding="utf-8")
        self.assertIn("#52637b", css)
        self.assertIn("#0b0b1f", css)
        self.assertIn("lx-site-footer-title", css)
        bg_white = _hex_to_rgb("ffffff")
        body_text = _hex_to_rgb("52637b")
        title = _hex_to_rgb("1f212c")
        footer_bg = _hex_to_rgb("0b0b1f")
        footer_link = _hex_to_rgb("f8fafc")
        self.assertGreaterEqual(contrast_ratio(body_text, bg_white), 4.5)
        self.assertGreaterEqual(contrast_ratio(title, bg_white), 4.5)
        self.assertGreaterEqual(contrast_ratio(footer_link, footer_bg), 4.5)
        self.assertIn("lsv7-managed", css)
        self.assertIn("lsv7-sidebar-expert", css)

    def test_sidebar_v7_skips_expert_on_avtor(self):
        js = (ROOT / "template" / "js" / "lireate-sitewide-sidebar-v7.js").read_text(encoding="utf-8")
        self.assertNotIn("ensureExpertPortrait", js)
        self.assertNotIn("lsv7-sidebar-expert", js)


if __name__ == "__main__":
    unittest.main()