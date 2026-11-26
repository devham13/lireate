#!/usr/bin/env python3
"""Offline regression checks for the five-page Light V5 installer."""
import pathlib
import sys
import tempfile
import unittest

sys.path.insert(0, str(pathlib.Path(__file__).parent))
import lireate_company_v5_install as inst


class CompanyV5InstallTests(unittest.TestCase):
    def setUp(self):
        self.tmp = tempfile.TemporaryDirectory()
        self.docroot = pathlib.Path(self.tmp.name) / "www"
        self.docroot.mkdir()
        self.backup = pathlib.Path(self.tmp.name) / "backup"
        self.previous = dict(inst.BASE_BLOB)
        for i, rel in enumerate(inst.PHP):
            dest = self.docroot / rel
            dest.parent.mkdir(parents=True, exist_ok=True)
            content = ('<?php echo "Original CMS page %d"; ?>' % i).encode("utf-8")
            dest.write_bytes(content)
            inst.BASE_BLOB[str(rel)] = inst.blob_id(content)

    def tearDown(self):
        inst.BASE_BLOB.clear()
        inst.BASE_BLOB.update(self.previous)
        self.tmp.cleanup()

    def test_dry_run_has_only_expected_file_paths_and_leaves_php_untouched(self):
        old = (self.docroot / inst.PHP[0]).read_bytes()
        changes = inst.plan(self.docroot)
        self.assertEqual(set(changes), set(inst.FILES))
        self.assertEqual((self.docroot / inst.PHP[0]).read_bytes(), old)
        self.assertEqual(set(inst.ASSETS).intersection(inst.PHP), set())

    def test_apply_recheck_and_rollback_restores_all(self):
        baseline = {p: (self.docroot / p).read_bytes() for p in inst.PHP}
        changes = inst.plan(self.docroot)
        inst.create_backup(self.docroot, changes, self.backup)
        for rel, data in changes.items():
            inst.atomic_write(self.docroot / rel, data)
        self.assertEqual(inst.plan(self.docroot), {})
        inst.rollback(self.docroot, self.backup)
        for rel, data in baseline.items():
            self.assertEqual((self.docroot / rel).read_bytes(), data)
        for rel in inst.ASSETS:
            self.assertFalse((self.docroot / rel).exists())

    def test_drift_in_existing_php_hard_blocks(self):
        existing = self.docroot / inst.PHP[0]
        existing.write_text("Grok changed this live file", encoding="utf-8")
        with self.assertRaisesRegex(ValueError, "live PHP differs"):
            inst.plan(self.docroot)

    def test_shared_asset_conflict_hard_blocks(self):
        asset = self.docroot / inst.ASSETS[0]
        asset.parent.mkdir(parents=True, exist_ok=True)
        asset.write_text("concurrent non-matching CSS", encoding="utf-8")
        with self.assertRaisesRegex(ValueError, "existing unexpected shared asset"):
            inst.plan(self.docroot)

    def test_source_contains_actual_logo_art_and_five_exact_page_components(self):
        svg = (inst.REPO / inst.ASSETS[2]).read_text(encoding="utf-8")
        self.assertIn('viewBox="0 0 759.7 136.1"', svg)
        self.assertIn(".st7{fill:#1F212C;}", svg)
        css = (inst.REPO / inst.ASSETS[0]).read_text(encoding="utf-8")
        chrome = (inst.REPO / inst.ASSETS[1]).read_text(encoding="utf-8")
        self.assertIn("mask-image:none!important", css)
        self.assertIn("logo-light-v5.svg", chrome)
        pairs = (
            (inst.PHP[0], inst.PHP[5]),
            (inst.PHP[1], inst.PHP[6]),
            (inst.PHP[2], inst.PHP[7]),
            (inst.PHP[3], inst.PHP[8]),
            (inst.PHP[4], inst.PHP[9]),
        )
        for tpl, shared in pairs:
            self.assertEqual(
                (inst.REPO / tpl).read_text(encoding="utf-8"),
                (inst.REPO / shared).read_text(encoding="utf-8"),
                str(tpl),
            )
        for rel in inst.PHP:
            page = (inst.REPO / rel).read_text(encoding="utf-8")
            self.assertIn("lireate-company-pages-v5.css", page)
            self.assertIn("lireate-company-v5", page)
            self.assertIn('class="lc5-hero-media', page)
        about = (inst.REPO / inst.PHP[5]).read_text(encoding="utf-8")
        self.assertIn("lireate_about_asset_url", about)

    def test_source_preserves_single_h1_in_each_page(self):
        import re
        for rel in inst.PHP:
            page = (inst.REPO / rel).read_text(encoding="utf-8")
            self.assertEqual(len(re.findall(r"<h1\b", page, re.I)), 1, str(rel))


if __name__ == "__main__":
    unittest.main()
