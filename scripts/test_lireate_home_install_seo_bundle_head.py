#!/usr/bin/env python3
"""HEAD marker detection when home assets are inside SEO performance CSS bundle."""

import base64
import importlib
import sys
import unittest
from pathlib import Path

REPO_ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(REPO_ROOT / "scripts"))

home_lib = importlib.import_module("lireate_home_light_v4_install_lib")


class HomeInstallSeoBundleHeadTests(unittest.TestCase):
    def test_head_marker_detected_in_performance_bundle_comment(self):
        inner = (
            '<?php if($temp[\'page\']==\'home\'){ ?>\n'
            '<link rel="stylesheet" href="/template/css/lireate-home-light-v4.css?v=x">'
            '<!-- lireate-home-light-v4-install-head:v1 -->\n'
            '<?php } ?>\n'
        )
        encoded = base64.b64encode(inner.encode("utf-8")).decode("ascii")
        main = (
            "<html><head>\n"
            "<!--lireate-performance-css-v1:" + encoded + "-->"
            '<link rel="stylesheet" href="/template/lireate-bundle.css" data-lireate-css-bundle="1">\n'
            "</head><body></body></html>"
        )
        self.assertNotIn(home_lib.HEAD_MARKER, main)
        self.assertTrue(home_lib.head_marker_present(main))
        planned, changed = home_lib.patch_head_assets(main)
        self.assertFalse(changed)
        self.assertEqual(planned, main)


if __name__ == "__main__":
    unittest.main()