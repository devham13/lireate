#!/usr/bin/env python3
"""Findtz hero uses six-services certificate specialist, not Yuri in static markup."""
import pathlib
import unittest

ROOT = pathlib.Path(__file__).resolve().parents[1]
FINDTZ = ROOT / "pages/services/proverka-tovarnogo-znaka/index.php"
JS = ROOT / "template/js/lireate-six-services-photo-v4.js"
IMG = ROOT / "template/images/services-v4/specialist-certificate.webp"


class FindtzExpertPhotoTests(unittest.TestCase):
    def test_certificate_asset_exists(self):
        self.assertTrue(IMG.is_file(), msg=str(IMG))

    def test_findtz_js_mapping(self):
        text = JS.read_text(encoding="utf-8")
        self.assertIn("'/findtz'", text)
        self.assertIn("specialist-certificate.webp", text)
        self.assertIn("Габдрахманова Эльвира", text)

    def test_findtz_index_no_yuri_static_hero(self):
        html = FINDTZ.read_text(encoding="utf-8")
        self.assertNotIn("/template/images/team/person/11.webp", html)
        self.assertNotIn("lx-findtz-expert-photo", html)
        self.assertIn("lireate-six-services-photo-v4", html)


if __name__ == "__main__":
    unittest.main()