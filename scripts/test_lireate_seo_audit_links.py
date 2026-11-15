#!/usr/bin/env python3
"""Tests for SEO-audit link patcher."""

import tempfile
import unittest
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
import sys

sys.path.insert(0, str(REPO / "scripts"))

import lireate_seo_audit_links_lib as lib  # noqa: E402


class SeoAuditLinksTests(unittest.TestCase):
    def test_patch_contacts_and_blog_404(self):
        replacements = lib.load_replacements(REPO)
        html = (
            '<a href="/contacts/">Контакты</a>'
            '<a href="/blog/kak-opredelit-klass-mktu/">МКТУ</a>'
        )
        patched, n = lib.patch_text_content(html, replacements)
        self.assertGreaterEqual(n, 2)
        self.assertIn('href="/kontaktyi"', patched)
        self.assertIn('href="/struktura_mktu_kak_opredelit_klass_dlya_tovara_i_uslugi/"', patched)

    def test_reg_tz_main_to_service(self):
        replacements = lib.load_replacements(REPO)
        html = '<a class="geo-v4__service" href="/reg-tz-main">Регистрация</a>'
        patched, n = lib.patch_text_content(html, replacements)
        self.assertEqual(n, 1)
        self.assertIn("/registraciya_tovarnogo_znaka/", patched)

    def test_idempotent(self):
        replacements = lib.load_replacements(REPO)
        html = '<a href="/findtz">Проверка</a>'
        once, _ = lib.patch_text_content(html, replacements)
        twice, changes = lib.patch_text_content(once, replacements)
        self.assertEqual(changes, 0)
        self.assertEqual(once, twice)

    def test_skips_db_php_in_iterator(self):
        with tempfile.TemporaryDirectory() as tmp:
            root = Path(tmp)
            (root / "data").mkdir()
            db = root / "data" / "db.php"
            db.write_text('<a href="/contacts/">x</a>', encoding="utf-8")
            page = root / "index.php"
            page.write_text('<a href="/contacts/">x</a>', encoding="utf-8")
            replacements = lib.load_replacements(REPO)
            hits = lib.scan_tree(root, replacements)
            rels = [p.name for p, _ in hits]
            self.assertIn("index.php", rels)
            self.assertNotIn("db.php", rels)

    def test_absolute_host_preserved(self):
        replacements = lib.load_replacements(REPO)
        host = "https://example.test"  # pragma: allowlist secret
        html = '<a href="{0}/reg-tz-main">x</a>'.format(host)
        patched, n = lib.patch_text_content(html, replacements)
        self.assertEqual(n, 1)
        self.assertIn("{0}/registraciya_tovarnogo_znaka/".format(host), patched)


if __name__ == "__main__":
    unittest.main()