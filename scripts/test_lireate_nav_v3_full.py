#!/usr/bin/env python3
"""Nav V3 full: shared sidebar, bundle lib, hide oplata, PHP 5.6 syntax."""
import re
import subprocess
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SHARED = ROOT / "pages/_shared"
SIDEBAR = SHARED / "lireate-public-cms-sidebar.inc.php"
BUNDLE = SHARED / "lireate-navigation-v3-bundle-lib.php"


class NavV3FullTests(unittest.TestCase):
    def test_sidebar_five_socials_and_youtube_channel(self):
        text = SIDEBAR.read_text(encoding="utf-8")
        self.assertIn("lsn-soc--tg", text)
        self.assertIn("lsn-soc--wa", text)
        self.assertIn("lsn-soc--max", text)
        self.assertIn("lsn-soc--vk", text)
        self.assertIn("lsn-soc--yt", text)
        self.assertIn("UCpg1LWotCQWwzviPDb_9Rew", text)
        self.assertIn("vk.ru/lireate", text)
        self.assertNotIn("tochka.com", text)
        self.assertNotRegex(text, r"Оплата\s+услуг")

    def test_bundle_lib_lists_assets(self):
        text = BUNDLE.read_text(encoding="utf-8")
        self.assertIn("lireate-navigation-dual-v3.css", text)
        self.assertIn("lireate-public-cms-chrome.css", text)
        self.assertIn("lireate-global-subnav-light-v4.css", text)
        self.assertIn("20261126r4", text)

    def test_js_has_collapse_and_anchor_mobile(self):
        js = (ROOT / "template/js/lireate-navigation-dual-v3.js").read_text(encoding="utf-8")
        self.assertIn("lireateNavV3SideCollapsed", js)
        self.assertIn("side-collapsed", js)
        self.assertIn("anchorToggle", js)
        self.assertIn("anchorMobile", js)

    def test_preview_router_mentions_static_before_routes(self):
        router = ROOT / "pages/_preview/nav-v3-router.php"
        text = router.read_text(encoding="utf-8")
        self.assertIn("parse_url($uri, PHP_URL_PATH)", text)
        static_pos = text.find("is_file($static_file)")
        routes_pos = text.find("$routes = array(")
        self.assertGreater(static_pos, 0)
        self.assertGreater(routes_pos, 0)
        self.assertLess(static_pos, routes_pos)

    def test_mobile_anchors_partial_present(self):
        text = (SHARED / "lireate-lh4-mobile-anchors.inc.php").read_text(encoding="utf-8")
        self.assertIn("Разделы", text)
        self.assertIn("anchor-mobile", text)

    def test_shared_php_has_no_double_question_marks(self):
        for path in SHARED.glob("*.php"):
            body = path.read_text(encoding="utf-8")
            self.assertNotIn("??", body, msg=str(path))

    def test_php56_lint_shared_includes(self):
        php = ROOT / "pages/_shared"
        targets = [
            php / "lireate-navigation-v3-bundle-lib.php",
            php / "lireate-public-cms-chrome-lib.php",
            php / "lireate-lh4-subnav-lib.php",
            php / "lireate-lh4-subnav.inc.php",
        ]
        for path in targets:
            if not path.is_file():
                continue
            proc = subprocess.run(
                ["php", "-l", str(path)],
                capture_output=True,
                text=True,
            )
            self.assertEqual(proc.returncode, 0, proc.stdout + proc.stderr)


if __name__ == "__main__":
    unittest.main()