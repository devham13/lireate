#!/usr/bin/env python3
import sys
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(ROOT / "scripts"))

import lireate_cms_main_body_class as cms  # noqa: E402

LIVE_FIXTURE = ROOT / "scripts" / "fixtures" / "cms-main-live-body-classes-round18.php"


class CmsMainBodyClassTests(unittest.TestCase):
    def test_live_fixture_suppresses_dark_on_home(self):
        src = LIVE_FIXTURE.read_text(encoding="utf-8")
        out, changed = cms.patch_home_light_v4_suppress_dark_body(src)
        self.assertTrue(changed)
        self.assertIn("lireate-home-light-v4-install:suppress-home-dark", out)
        self.assertIn("/* lireate-home-dark */", out)
        self.assertNotIn("? ' lireate-home-dark'", out)
        self.assertIn("$temp['page']!='home'", out)
        self.assertNotIn("'home'", out.split("array(")[1].split(")")[0])

    def test_live_fixture_idempotent(self):
        src = LIVE_FIXTURE.read_text(encoding="utf-8")
        out, _ = cms.patch_home_light_v4_suppress_dark_body(src)
        out2, changed2 = cms.patch_home_light_v4_suppress_dark_body(out)
        self.assertFalse(changed2)
        self.assertEqual(out, out2)


if __name__ == "__main__":
    unittest.main()