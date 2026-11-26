#!/usr/bin/env python3
"""Regression checks for portfolio V5 reversible CMS asset install (no network)."""
import pathlib
import sys
import tempfile
import unittest

SCRIPTS = pathlib.Path(__file__).resolve().parent
sys.path.insert(0, str(SCRIPTS))
import lireate_portfolio_v5_install as mod


class PortfolioV5Tests(unittest.TestCase):
    def setUp(self):
        self.tmp = tempfile.TemporaryDirectory()
        self.base = pathlib.Path(self.tmp.name)
        self.docroot = self.base / "public_html"
        (self.docroot / "template").mkdir(parents=True)
        (self.docroot / mod.MAIN).write_text(
            '<?php $temp=array("page"=>"portfolio"); ?><html><head><title>SEO title preserved</title>'
            '<meta name="description" content="Existing SEO"></head><body>'
            '<form action="/mail.php"><input name="tel"></form></body></html>',
            encoding="utf-8")
        self.backup = self.base / "backup"
    def tearDown(self):
        self.tmp.cleanup()

    def test_dry_run_has_only_expected_paths_and_preserves_source(self):
        main = (self.docroot / mod.MAIN).read_bytes()
        plan = mod.build_plan(self.docroot)
        self.assertEqual(set(plan), set(mod.FILES))
        self.assertEqual((self.docroot / mod.MAIN).read_bytes(), main)
        self.assertIn(b'Existing SEO', plan[mod.MAIN])
        self.assertIn(b'/mail.php', plan[mod.MAIN])
        self.assertEqual(plan[mod.MAIN].count(mod.VERSION.encode()), 1)

    def test_apply_idempotence_and_reversible_rollback(self):
        original = (self.docroot / mod.MAIN).read_bytes()
        plan = mod.build_plan(self.docroot)
        mod.backup_files(self.docroot, plan, self.backup)
        for rel, data in plan.items():
            mod.atomic_replace(self.docroot / rel, data)
        self.assertEqual(mod.build_plan(self.docroot), {})
        self.assertEqual((self.docroot / mod.MAIN).read_text().count(mod.VERSION), 1)
        mod.rollback(self.docroot, self.backup)
        self.assertEqual((self.docroot / mod.MAIN).read_bytes(), original)
        self.assertFalse((self.docroot / mod.CSS).exists())
        self.assertFalse((self.docroot / mod.JS).exists())

    def test_global_scope_is_path_guarded_and_covers_14_cases(self):
        script = (mod.REPO / mod.JS).read_text(encoding="utf-8")
        self.assertIn('if(path!==root && path.indexOf(root+"/")!==0)return;', script)
        for slug in (
            "svoya-kultura", "bentax", "avtopravo", "kingsname", "doza",
            "happy-mind", "elisa-hotels", "strigoon", "avenue-deluxe",
            "emiz", "nerud-grupp", "estetic", "berti", "vf"
        ):
            self.assertIn('["'+slug+'",', script)
        for messenger in ("t.me/lireate_patent", "max.ru/u/", "wa.me/79600484957"):
            self.assertIn(messenger, script)
        self.assertIn('.case-hero', script)
        self.assertIn('lxpf5-featured-grid', script)

    def test_missing_main_is_hard_blocker(self):
        (self.docroot / mod.MAIN).unlink()
        with self.assertRaises(ValueError):
            mod.build_plan(self.docroot)


if __name__ == "__main__":
    unittest.main()
