#!/usr/bin/env python3
"""Tests for lireate_menu_remove_item_lib."""

import tempfile
import unittest
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
FIXTURE = REPO / "scripts/fixtures/cms-sidebar-with-oplata.php"

import sys

sys.path.insert(0, str(REPO / "scripts"))

import lireate_menu_remove_item_lib as lib  # noqa: E402


class MenuRemoveLibTests(unittest.TestCase):
    def test_remove_by_marker(self):
        text = FIXTURE.read_text(encoding="utf-8")
        slug = "registraciya-tovarnogo-znaka-s-oplatoy-za-rezultat"
        href = "/registraciya_tovarnogo_znaka_s_oplatoy_za_rezultat/"
        title = "Регистрация товарного знака с оплатой за результат"
        patched, changed = lib.remove_menu_item(text, slug, href, title)
        self.assertTrue(changed)
        self.assertNotIn("lireate-menu:" + slug, patched)
        self.assertNotIn(href, patched)
        self.assertIn("Продление товарного знака", patched)
        self.assertIn("Регистрация товарного знака</a>", patched)

    def test_idempotent(self):
        text = FIXTURE.read_text(encoding="utf-8")
        slug = "registraciya-tovarnogo-znaka-s-oplatoy-za-rezultat"
        href = "/registraciya_tovarnogo_znaka_s_oplatoy_za_rezultat/"
        title = "Регистрация товарного знака с оплатой за результат"
        once, _ = lib.remove_menu_item(text, slug, href, title)
        twice, changed = lib.remove_menu_item(once, slug, href, title)
        self.assertFalse(changed)
        self.assertEqual(once, twice)

    def test_find_in_docroot(self):
        with tempfile.TemporaryDirectory() as tmp:
            docroot = Path(tmp)
            tpl = docroot / "template" / "page" / "left.php"
            tpl.parent.mkdir(parents=True)
            tpl.write_text(FIXTURE.read_text(encoding="utf-8"), encoding="utf-8")
            slug = "registraciya-tovarnogo-znaka-s-oplatoy-za-rezultat"
            href = "/registraciya_tovarnogo_znaka_s_oplatoy_za_rezultat/"
            title = "Регистрация товарного знака с оплатой за результат"
            found = lib.find_sidebar_templates(docroot, slug, href, title)
            self.assertEqual(len(found), 1)
            self.assertEqual(found[0], tpl)


if __name__ == "__main__":
    unittest.main()
