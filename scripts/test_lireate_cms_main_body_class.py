#!/usr/bin/env python3
import re
import shutil
import subprocess
import sys
import tempfile
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(ROOT / "scripts"))

import lireate_cms_main_body_class as cms  # noqa: E402
import lireate_home_light_v4_install_lib as home_lib  # noqa: E402

LIVE_SNIPPET = ROOT / "scripts" / "fixtures" / "cms-main-live-body-line392-snippet.php"
LIVE_MAIN_FIXTURE = ROOT / "tests/fixtures/live-render/live-assets/template/main.php"


class CmsMainBodyClassTests(unittest.TestCase):
    def test_live_snippet_suppresses_dark_on_home(self):
        src = LIVE_SNIPPET.read_text(encoding="utf-8")
        out, changed = cms.patch_home_light_v4_suppress_dark_body(src)
        self.assertTrue(changed)
        self.assertIn("lireate-home-light-v4-install:suppress-home-dark", out)
        self.assertIn("/* lireate-home-dark */", out)
        self.assertNotIn("? ' lireate-home-dark'", out)
        self.assertIn("$temp['page']!='home'", out)

    def test_live_snippet_idempotent(self):
        src = LIVE_SNIPPET.read_text(encoding="utf-8")
        out, _ = cms.patch_home_light_v4_suppress_dark_body(src)
        out2, changed2 = cms.patch_home_light_v4_suppress_dark_body(out)
        self.assertFalse(changed2)
        self.assertEqual(out, out2)

    def test_live_main_fixture_home_install_php_lint(self):
        if not LIVE_MAIN_FIXTURE.is_file():
            self.skipTest("live main.php fixture missing")
        original = LIVE_MAIN_FIXTURE.read_text(encoding="utf-8")
        self.assertIn("lireate_design_cms_lu_assets_v1", original)
        self.assertIn("lireate_design_cms_dark_pages_v2", original)
        self.assertIn("$temp['page'] === 'home'", original)
        planned = home_lib.plan_home_main_content(original)
        self.assertIn("suppress-home-dark", planned)
        self.assertNotRegex(planned, r"===\s*\)\s*:")
        self.assertIn("$temp['page'] === 'home'", planned)
        php = home_lib.resolve_php_bin()
        tmp = Path(tempfile.mkdtemp()) / "main.php"
        try:
            tmp.write_text(planned, encoding="utf-8")
            lint = home_lib.run_subprocess([php, "-l", str(tmp)])
            self.assertEqual(0, lint.returncode, lint.stdout + lint.stderr)
        finally:
            shutil.rmtree(tmp.parent, ignore_errors=True)

    def test_live_main_fixture_render_no_dark_on_home(self):
        if not LIVE_MAIN_FIXTURE.is_file():
            self.skipTest("live main.php fixture missing")
        body_start = LIVE_MAIN_FIXTURE.read_text(encoding="utf-8").find("<body")
        self.assertGreater(body_start, 0)
        original = (
            "<?php $temp=array('page'=>'home'); $_lx_home_v2=false; ?>\n"
            + LIVE_MAIN_FIXTURE.read_text(encoding="utf-8")[body_start:]
        )
        planned = cms.patch_home_light_v4_suppress_dark_body(original)[0]
        planned, _ = cms.patch_home_light_v4_body_conditional(planned)
        tmp = Path(tempfile.mkdtemp()) / "main.php"
        try:
            tmp.write_text(planned, encoding="utf-8")
            proc = subprocess.run(
                ["php", "-f", str(tmp)],
                stdout=subprocess.PIPE,
                stderr=subprocess.PIPE,
                universal_newlines=True,
                check=False,
            )
            self.assertEqual(0, proc.returncode, proc.stderr)
            m = re.search(r'<body class="([^"]*)"', proc.stdout)
            self.assertIsNotNone(m, proc.stdout[:500])
            cls = m.group(1)
            self.assertNotIn("lireate-cms-page-dark", cls)
            self.assertNotIn("lireate-home-dark", cls)
            self.assertIn("lireate-home-light-v4", cls)
        finally:
            shutil.rmtree(tmp.parent, ignore_errors=True)

    def test_live_main_fixture_lineage_comment(self):
        if not LIVE_MAIN_FIXTURE.is_file():
            self.skipTest("live main.php fixture missing")
        head = LIVE_MAIN_FIXTURE.read_text(encoding="utf-8", errors="replace")[:400]
        self.assertIn("9e2479f6", head)


if __name__ == "__main__":
    unittest.main()