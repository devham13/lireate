#!/usr/bin/env python3
"""Home hero: portrait assets, picture markup; hero uses scoped cover+zoom (r24)."""

import re
import sys
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(ROOT / "scripts"))
HOME_INC = ROOT / "pages" / "home" / "_shared" / "lireate-home-page.inc.php"
HOME_CSS = ROOT / "template" / "css" / "lireate-home-light-v4.css"
JPG = ROOT / "template/images/home/gabdrakhmanova-elvira-2026-11.jpg"
WEBP = ROOT / "template/images/home/gabdrakhmanova-elvira-2026-11.webp"
INCOMING = ROOT / "design-proposals/incoming/gabdrakhmanova-elvira-2026-11.jpg"


class HomeHeroPhotoTests(unittest.TestCase):
    def test_portrait_files_present_and_jpg_matches_incoming(self):
        self.assertTrue(JPG.is_file(), "hero JPG must ship in template/images/home")
        self.assertTrue(INCOMING.is_file(), "design incoming JPG must stay in repo")
        self.assertGreater(JPG.stat().st_size, 100000)
        self.assertEqual(JPG.stat().st_size, INCOMING.stat().st_size)
        if WEBP.is_file():
            self.assertGreater(WEBP.stat().st_size, 5000)

    def test_markup_uses_picture_and_lcp_attrs(self):
        text = HOME_INC.read_text(encoding="utf-8")
        self.assertIn("<picture>", text)
        self.assertIn("lireate_home_hero_expert_portrait()", text)
        self.assertIn("$lh_hero_portrait['jpg']", text)
        data = (ROOT / "pages/home/_shared/lireate-home-data.php").read_text(encoding="utf-8")
        self.assertIn("Габдрахманова Эльвира — Лирейт", data)
        self.assertIn("fetchpriority=\"high\"", text)
        hero_block = text[text.find("expert-photo") : text.find("expert-info")]
        self.assertNotIn("team/person/1.jpg", hero_block)

    def test_css_hero_portrait_uses_scoped_cover_zoom(self):
        css = HOME_CSS.read_text(encoding="utf-8")
        self.assertIn("aspect-ratio:683/1024", css.replace(" ", ""))
        self.assertRegex(css, r"\.expert-photo img\{[^}]*object-fit:contain")
        self.assertRegex(
            css,
            r"\.hero-right \.expert-photo img\{[^}]*object-fit:cover",
        )
        self.assertRegex(css, r"object-position:\s*50%\s*2%")
        self.assertIn("transform:scale(1.78)", css.replace(" ", ""))

    def test_head_snippet_preloads_hero_image(self):
        import lireate_home_light_v4_install_lib as home_lib

        snippet = home_lib._head_snippet()
        self.assertIn("rel=\"preload\" as=\"image\"", snippet)
        self.assertIn("gabdrakhmanova-elvira-2026-11.jpg", snippet)


if __name__ == "__main__":
    unittest.main()