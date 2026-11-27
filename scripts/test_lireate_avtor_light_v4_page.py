#!/usr/bin/env python3
"""Guards: /avtor/ Light V4 assets and SEO helpers."""

import re
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SHARED_INC = ROOT / "pages" / "avtor" / "_shared" / "lireate-avtor-page.inc.php"
TEMPLATE_INC = ROOT / "template" / "page" / "avtor" / "lireate-avtor-page.inc.php"


class AvtorLightV4PageTests(unittest.TestCase):
    def test_shared_has_light_stack(self):
        text = SHARED_INC.read_text(encoding="utf-8")
        self.assertIn("lireate-cms-pages-light-v4.css", text)
        self.assertIn("lireate-light-v4.css", text)
        self.assertIn("lireate-avtor.css", text)
        self.assertIn('aria-current="page">Автор', text)
        self.assertIn("Горбачев Юрий Александрович", text)
        self.assertNotIn("lireate-service-dark", text)

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


if __name__ == "__main__":
    unittest.main()