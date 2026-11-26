#!/usr/bin/env python3
"""Tests for mktu Light V4 install lib."""

import sys
import unittest
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(REPO / "scripts"))

import lireate_mktu_light_v4_install_lib as lib  # noqa: E402
import lireate_light_v4_asset_version as ver  # noqa: E402

MAIN_FIXTURE = REPO / "tests/fixtures/live-render/live-assets/template/main.php"
MKTU_CSS = REPO / "template/css/lireate-mktu-light-v4.css"


class MktuLightV4InstallTests(unittest.TestCase):
    def test_css_exists(self):
        self.assertTrue(MKTU_CSS.is_file())

    def test_plan_main_head_patch(self):
        text = MAIN_FIXTURE.read_text(encoding="utf-8", errors="replace")
        planned, changed, note = lib.plan_main_head_patch(text)
        self.assertIn(lib.HEAD_MARKER, planned)
        self.assertIn("lireate-mktu-light-v4.css", planned)
        self.assertIn(ver.LIGHT_V4_ASSET_VERSION, planned)
        lint = lib.lint_php_content(lib.resolve_php_bin(), planned, "main.php")
        self.assertIsNone(lint, lint)

    def test_idempotent(self):
        text = MAIN_FIXTURE.read_text(encoding="utf-8", errors="replace")
        once, _, _ = lib.plan_main_head_patch(text)
        twice, changed, _ = lib.plan_main_head_patch(once)
        self.assertFalse(changed)
        self.assertEqual(once, twice)


if __name__ == "__main__":
    unittest.main()