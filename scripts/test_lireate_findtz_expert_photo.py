#!/usr/bin/env python3
"""Findtz hero uses six-services certificate specialist, not Yuri in static markup."""
import pathlib
import unittest

ROOT = pathlib.Path(__file__).resolve().parents[1]
FINDTZ = ROOT / "pages/services/proverka-tovarnogo-znaka/index.php"
JS = ROOT / "template/js/lireate-six-services-photo-v4.js"
IMG_WEBP = ROOT / "template/images/findtz/findtz-hero.webp"
IMG_JPG = ROOT / "template/images/findtz/findtz-hero.jpg"


class FindtzExpertPhotoTests(unittest.TestCase):
    def test_findtz_hero_assets_exist(self):
        self.assertTrue(IMG_WEBP.is_file(), msg=str(IMG_WEBP))
        self.assertTrue(IMG_JPG.is_file(), msg=str(IMG_JPG))

    def test_findtz_js_mapping(self):
        text = JS.read_text(encoding="utf-8")
        self.assertIn("'/findtz'", text)
        self.assertIn("findtz-hero.webp", text)
        html = FINDTZ.read_text(encoding="utf-8")
        self.assertNotIn("Габдрахманова", html)
        self.assertNotIn("lx-photo-v4-expert-name", html)
        self.assertNotIn("lx-photo-v4-expert-next", html)

    def test_findtz_index_no_yuri_static_hero(self):
        html = FINDTZ.read_text(encoding="utf-8")
        self.assertNotIn("/template/images/team/person/11.webp", html)
        self.assertNotIn("lx-findtz-expert-photo", html)
        self.assertIn("lireate-six-services-photo-v4", html)
        self.assertIn("lx-photo-v4-expert-card", html)
        self.assertIn("ЭКСПЕРТНОЕ СОПРОВОЖДЕНИЕ", html)
        self.assertIn("lx-photo-v4", html)
        self.assertIn("findtz/findtz-hero.webp", html)
        self.assertIn("20261128-findtz6", html)
        self.assertIn("lx-photo-v4-expert-caption-kicker", html)
        self.assertIn("LIREATE / ЭКСПЕРТНОЕ СОПРОВОЖДЕНИЕ", html)
        self.assertNotIn("<strong>ЭКСПЕРТНОЕ СОПРОВОЖДЕНИЕ</strong>", html)
        self.assertIn("lx-hero-mini--after-photo", html)
        self.assertIn("lx-photo-v4-expert-card--photo-only", html)


if __name__ == "__main__":
    unittest.main()