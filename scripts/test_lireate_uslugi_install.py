#!/usr/bin/env python3
import unittest
from pathlib import Path
import sys

sys.path.insert(0, str(Path(__file__).resolve().parent))
import lireate_uslugi_install_lib as lib


class UslugiInstallTests(unittest.TestCase):
    def test_patch_main_removes_uslugi_from_dark_array(self):
        text = (
            "<?php $_lx_cms_dark = array('home','uslugi','partners'); "
            + lib.USLUGI_BODY_OLD
            + "?>"
        )
        planned, changed = lib.patch_main_uslugi_light(text)
        self.assertTrue(changed)
        self.assertNotIn("array('home','uslugi'", planned.replace(" ", ""))
        self.assertIn("lireate-theme-light-v4", planned)


if __name__ == "__main__":
    unittest.main()