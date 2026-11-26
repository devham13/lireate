#!/usr/bin/env python3
import sys
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(ROOT / "scripts"))

import lireate_cms_main_body_class as cms  # noqa: E402


class CmsMainBodyClassTests(unittest.TestCase):
    def test_ternary_in_array_dark_guarded_on_home(self):
        src = (
            "<body class=\"cms-default"
            "<?php echo in_array($temp['page'], array('home','news')) ? ' lireate-cms-page-dark' : ''; ?>"
            "<?php echo ($temp['page']=='home') ? ' lireate-home-dark' : ''; ?>\">"
        )
        out, changed = cms.patch_home_light_v4_suppress_dark_body(src)
        self.assertTrue(changed)
        self.assertIn("$temp['page']!='home'", out)
        self.assertIn("lireate-home-dark", out)

    def test_ternary_home_dark_still_present_for_cron(self):
        src = "<?php echo ($temp['page']=='home') ? ' lireate-home-dark' : ''; ?>"
        out, changed = cms.patch_home_light_v4_suppress_dark_body(src)
        self.assertTrue(changed)
        self.assertIn("lireate-home-dark", out)


if __name__ == "__main__":
    unittest.main()