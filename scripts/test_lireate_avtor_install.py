#!/usr/bin/env python3
"""Guards for isolated /avtor/ install helper."""

import sys
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
if str(ROOT / "scripts") not in sys.path:
    sys.path.insert(0, str(ROOT / "scripts"))

import lireate_avtor_install_lib as lib
import lireate_cms_main_body_class as cms_body


class AvtorInstallTests(unittest.TestCase):
    def test_deploy_list_excludes_sitewide(self):
        joined = " ".join(lib.deploy_file_list())
        self.assertNotIn("sitewide-sidebar", joined)
        self.assertNotIn("lireate-cms-pages-light-v4", joined)

    def test_body_patch_inserts_inside_long_php_line(self):
        text = "echo ' lireate-team-body'; if($temp['page']=='uslugi')"
        planned, changed = cms_body.patch_avtor_body_inline(text)
        self.assertTrue(changed)
        self.assertIn("lireate-avtor-body", planned)
        self.assertIn(cms_body.AVTOR_BODY_MARKER, planned)

    def test_avtor_page_php_marker(self):
        text = (ROOT / "template/page/avtor.php").read_text(encoding="utf-8")
        self.assertIn(INSTALL := "lireate-avtor-install:v1", text)
        self.assertIn("lireate-avtor-cms.inc.php", text)


if __name__ == "__main__":
    unittest.main()