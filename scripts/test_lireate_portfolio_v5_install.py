#!/usr/bin/env python3
"""Regression checks for portfolio V5 reversible CMS asset install (no network)."""
import pathlib
import shutil
import subprocess
import sys
import tempfile
import unittest

SCRIPTS = pathlib.Path(__file__).resolve().parent
REPO = SCRIPTS.parent
sys.path.insert(0, str(SCRIPTS))
import lireate_portfolio_v5_install as mod

LIVE_MAIN = REPO / "tests/fixtures/live-render/live-assets/template/main.php"


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
        self.assertEqual(plan[mod.MAIN].count(b"<!-- " + mod.VERSION.encode()), 1)
        head = plan[mod.MAIN].decode("utf-8")
        self.assertIn("portfolio/') === 0", head)
        self.assertIn("preg_match('#^/portfolio(?:[/?]|$)#", head)
        self.assertIn("lireate-portfolio-v5.css", head)
        self.assertIn("endif", head)

    def test_apply_idempotence_and_reversible_rollback(self):
        original = (self.docroot / mod.MAIN).read_bytes()
        plan = mod.build_plan(self.docroot)
        mod.backup_files(self.docroot, plan, self.backup)
        for rel, data in plan.items():
            mod.atomic_replace(self.docroot / rel, data)
        self.assertEqual(mod.build_plan(self.docroot), {})
        self.assertEqual((self.docroot / mod.MAIN).read_text().count("<!-- " + mod.VERSION), 1)
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
        for messenger in ("t.me/lireate_patent", "max.ru/channel_lireate", "wa.me/79600484957"):
            self.assertIn(messenger, script)
        self.assertIn('.case-hero', script)
        self.assertIn('.lx-case-hero', script)
        self.assertIn('lxpf5-featured-grid', script)

    def test_missing_main_is_hard_blocker(self):
        (self.docroot / mod.MAIN).unlink()
        with self.assertRaises(ValueError):
            mod.build_plan(self.docroot)

    def test_live_main_fixture_php_lint_after_patch(self):
        if not LIVE_MAIN.is_file():
            self.skipTest("live main.php fixture missing")
        php = shutil.which("php") or shutil.which("php5.6")
        if not php:
            self.skipTest("php CLI missing")
        docroot = self.base / "live_fixture_docroot"
        (docroot / "template").mkdir(parents=True)
        shutil.copy2(LIVE_MAIN, docroot / mod.MAIN)
        plan = mod.build_plan(docroot)
        patched = plan[mod.MAIN].decode("utf-8")
        lint = subprocess.run(
            [php, "-l"],
            input=patched,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            universal_newlines=True,
            timeout=30,
        )
        self.assertEqual(lint.returncode, 0, lint.stdout + lint.stderr)


if __name__ == "__main__":
    unittest.main()