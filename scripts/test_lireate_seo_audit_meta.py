#!/usr/bin/env python3
"""Tests for SEO-audit CMS meta/content patcher."""

import tempfile
import unittest
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
import sys

sys.path.insert(0, str(REPO / "scripts"))

import lireate_seo_audit_meta_lib as lib  # noqa: E402


class SeoAuditMetaTests(unittest.TestCase):
    def test_patch_title_and_description(self):
        meta = {
            "title": "МКТУ — 45 классов товаров и услуг для товарных знаков",
            "description": "Классы МКТУ для регистрации товарного знака.",
            "h1": "Классы МКТУ для регистрации товарных знаков",
        }
        html = (
            "<title>Old</title>"
            '<meta name="description" content="old desc">'
            '<div class="title-ap"><h1>Старое</h1></div>'
        )
        patched, n = lib.patch_metadata_text(html, meta)
        self.assertGreaterEqual(n, 2)
        self.assertIn("МКТУ — 45 классов", patched)
        self.assertIn("Классы МКТУ для регистрации", patched)

    def test_content_1478(self):
        replacements = lib.load_content_replacements(REPO)
        old_snip = (
            "Подать заявку на регистрацию может только юридическое лицо, "
            "или физическое лицо предприниматель. Действующее законодательство "
            "прямо запрещает регистрацию физическим лицам."
        )
        text = "<p>{0}</p>".format(old_snip)
        patched, n = lib.patch_content_text(
            text,
            replacements,
            file_hint="kak_samostoyatelno_podgotovit_zayavku.php",
        )
        self.assertEqual(n, 1)
        self.assertIn("ст. 1478 ГК РФ", patched)
        self.assertNotIn("запрещает регистрацию физическим", patched)

    def test_meta_file_scoring(self):
        with tempfile.TemporaryDirectory() as tmp:
            root = Path(tmp)
            tpl = root / "template"
            tpl.mkdir(parents=True)
            main = tpl / "main.php"
            main.write_text(
                "<?php if($temp['page']=='mktu'){ ?>\n"
                "<title>МКТУ портфолио</title>\n"
                '<meta name="description" content="портфолио">\n'
                '<div class="title-ap"><h1>Портфолио</h1></div>\n'
                "<?php } ?>\n",
                encoding="utf-8",
            )
            hits = lib.scan_meta_tree(root, REPO)
            paths = [h[0].name for h in hits if h[1]["path"] == "/mktu/"]
            self.assertIn("main.php", paths)

    def test_skips_db_php(self):
        with tempfile.TemporaryDirectory() as tmp:
            root = Path(tmp)
            (root / "data").mkdir()
            db = root / "data" / "db.php"
            old = (
                "Подать заявку на регистрацию может только юридическое лицо, "
                "или физическое лицо предприниматель. Действующее законодательство "
                "прямо запрещает регистрацию физическим лицам."
            )
            db.write_text(old, encoding="utf-8")
            hits = lib.scan_content_tree(root, REPO)
            self.assertEqual(hits, [])


if __name__ == "__main__":
    unittest.main()