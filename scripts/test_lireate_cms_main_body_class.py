#!/usr/bin/env python3
import re
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
LIVE_MAIN = ROOT / "scripts" / "fixtures" / "cms-main-live-main-php-round19.php"


class CmsMainBodyClassTests(unittest.TestCase):
    def test_live_snippet_suppresses_dark_on_home(self):
        src = LIVE_SNIPPET.read_text(encoding="utf-8")
        out, changed = cms.patch_home_light_v4_suppress_dark_body(src)
        self.assertTrue(changed)
        self.assertIn("lireate-home-light-v4-install:suppress-home-dark", out)
        self.assertIn("/* lireate-home-dark */", out)
        self.assertNotIn("? ' lireate-home-dark'", out)
        self.assertIn("$temp['page']!='home'", out)

    def test_live_main_round19_render_no_dark_classes(self):
        if not LIVE_MAIN.is_file():
            self.skipTest("fixture missing")
        original = LIVE_MAIN.read_text(encoding="utf-8")
        planned = home_lib.plan_home_main_content(original)
        self.assertIn("suppress-home-dark", planned)
        with tempfile.NamedTemporaryFile("w", suffix=".php", delete=False, encoding="utf-8") as fh:
            fh.write(planned)
            tmp = Path(fh.name)
        try:
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
            if tmp.is_file():
                tmp.unlink()

    def test_live_snippet_idempotent(self):
        src = LIVE_SNIPPET.read_text(encoding="utf-8")
        out, _ = cms.patch_home_light_v4_suppress_dark_body(src)
        out2, changed2 = cms.patch_home_light_v4_suppress_dark_body(out)
        self.assertFalse(changed2)
        self.assertEqual(out, out2)


if __name__ == "__main__":
    unittest.main()