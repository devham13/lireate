#!/usr/bin/env python3
"""Tests for lireate_menu_remove_oplata_lib."""

import sys
import tempfile
import unittest
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(REPO / "scripts"))

import lireate_menu_remove_oplata_lib as lib  # noqa: E402

FIXTURE = REPO / "scripts/fixtures/cms-sidebar-oplata-tochka.php"


class MenuRemoveOplataTests(unittest.TestCase):
    def test_remove_sidebar_and_burger(self):
        text = FIXTURE.read_text(encoding="utf-8")
        patched, changed = lib.remove_oplata_links(text)
        self.assertTrue(changed)
        self.assertNotIn(lib.OPLATA_HREF, patched)
        self.assertIn("leftSideNav", patched)

    def test_idempotent(self):
        text = FIXTURE.read_text(encoding="utf-8")
        once, _ = lib.remove_oplata_links(text)
        twice, changed = lib.remove_oplata_links(once)
        self.assertFalse(changed)
        self.assertEqual(once, twice)

    def test_find_in_docroot(self):
        with tempfile.TemporaryDirectory() as tmp:
            docroot = Path(tmp)
            tpl = docroot / "template" / "parts" / "side.php"
            tpl.parent.mkdir(parents=True)
            tpl.write_text(FIXTURE.read_text(encoding="utf-8"), encoding="utf-8")
            found = lib.find_templates(docroot)
            self.assertEqual(len(found), 1)


if __name__ == "__main__":
    unittest.main()