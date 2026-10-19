#!/usr/bin/env python3
"""Tests for lireate_menu_remove_item_lib."""

import tempfile
import unittest
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
FIXTURE = REPO / "scripts/fixtures/cms-sidebar-with-oplata.php"
FIXTURE_HOME = REPO / "scripts/fixtures/cms-sidebar-home-duplicate.php"

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
            tpl = docroot / "template" / "parts" / "leftSideNav.php"
            tpl.parent.mkdir(parents=True)
            tpl.write_text(FIXTURE.read_text(encoding="utf-8"), encoding="utf-8")
            slug = "registraciya-tovarnogo-znaka-s-oplatoy-za-rezultat"
            href = "/registraciya_tovarnogo_znaka_s_oplatoy_za_rezultat/"
            title = "Регистрация товарного знака с оплатой за результат"
            found = lib.find_sidebar_templates(docroot, slug, href, title)
            self.assertEqual(len(found), 1)
            self.assertEqual(found[0], tpl)

    def test_find_multiple_nav_copies(self):
        with tempfile.TemporaryDirectory() as tmp:
            docroot = Path(tmp)
            slug = "registraciya-tovarnogo-znaka-s-oplatoy-za-rezultat"
            href = "/registraciya_tovarnogo_znaka_s_oplatoy_za_rezultat/"
            title = "Регистрация товарного знака с оплатой за результат"
            a = docroot / "template" / "parts" / "leftSideNav.php"
            b = docroot / "template" / "parts" / "leftSideNavMain.php"
            a.parent.mkdir(parents=True)
            a.write_text(FIXTURE.read_text(encoding="utf-8"), encoding="utf-8")
            b.write_text(FIXTURE_HOME.read_text(encoding="utf-8"), encoding="utf-8")
            found = lib.find_sidebar_templates(docroot, slug, href, title)
            self.assertEqual(len(found), 2)

    def test_remove_duplicate_without_marker(self):
        text = FIXTURE_HOME.read_text(encoding="utf-8")
        slug = "registraciya-tovarnogo-znaka-s-oplatoy-za-rezultat"
        href = "/registraciya_tovarnogo_znaka_s_oplatoy_za_rezultat/"
        title = "Регистрация товарного знака с оплатой за результат"
        patched, changed = lib.remove_menu_item(text, slug, href, title)
        self.assertTrue(changed)
        self.assertNotIn("s_oplatoy_za_rezultat", patched)

    def test_http_probe_unpack(self):
        from unittest.mock import patch

        with patch("lireate_blog_install_lib.http_probe", return_value=(200, "<html/>", "https://x/", {})):
            status, body = lib.http_probe_status_body("https://x", "/")
        self.assertEqual(status, 200)
        self.assertEqual(body, "<html/>")


if __name__ == "__main__":
    unittest.main()
