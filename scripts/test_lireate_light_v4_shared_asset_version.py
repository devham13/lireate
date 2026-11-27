#!/usr/bin/env python3
"""Shared Light V4 CSS/JS must use LIGHT_V4_ASSET_VERSION in CMS page configs."""

import re
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
sys_path = ROOT / "scripts"
import sys

sys.path.insert(0, str(sys_path))
import lireate_light_v4_asset_version as ver  # noqa: E402

LIGHT_V4_CSS = (
    "lireate-cms-pages-light-v4.css",
    "lireate-light-v4.css",
    "lireate-home-light-v4.css",
    "lireate-blog-footer-light-v4.css",
)

CONFIG_MARKERS = (
    "LIREATE_HOME_TEMPLATE_VERSION",
    "LIREATE_BLOG_TEMPLATE_VERSION",
    "LIREATE_TEAM_TEMPLATE_VERSION",
    "LIREATE_ABOUT_TEMPLATE_VERSION",
    "LIREATE_CONTACTS_TEMPLATE_VERSION",
    "LIREATE_PARTNERS_TEMPLATE_VERSION",
    "LIREATE_DOCS_TEMPLATE_VERSION",
)

CMS_HEAD = ROOT / "pages" / "_shared" / "lireate-cms-light-v4-head.inc.php"


def config_files():
    out = []
    for pattern in ("pages/**/lireate-*-config.php", "template/page/**/lireate-*-config.php"):
        out.extend(ROOT.glob(pattern))
    return sorted(set(out))


class LightV4SharedAssetVersionTests(unittest.TestCase):
    def test_cms_configs_use_light_v4_asset_version(self):
        expected = ver.LIGHT_V4_ASSET_VERSION
        pattern = re.compile(
            r"define\s*\(\s*'(LIREATE_[A-Z_]+_TEMPLATE_VERSION)'\s*,\s*'([^']+)'"
        )
        for path in config_files():
            text = path.read_text(encoding="utf-8")
            if not any(m in text for m in CONFIG_MARKERS):
                continue
            for name, value in pattern.findall(text):
                if name not in CONFIG_MARKERS:
                    continue
                if name == "LIREATE_HOME_TEMPLATE_VERSION":
                    want = ver.HOME_TEMPLATE_VERSION
                else:
                    want = expected
                self.assertEqual(
                    want,
                    value,
                    "{0}: {1} is {2}, expected {3}".format(path, name, value, want),
                )

    def test_cms_head_helper_version(self):
        text = CMS_HEAD.read_text(encoding="utf-8")
        self.assertIn(ver.LIGHT_V4_ASSET_VERSION, text)
        self.assertIn("lireate-cms-pages-light-v4.css", text)

    def test_no_stale_light_v4_query_on_shared_css_in_repo_php(self):
        """Page inc files must not hardcode old ?v= on shared Light V4 CSS."""
        expected = ver.LIGHT_V4_ASSET_VERSION
        stale = re.compile(
            r"lireate-(?:cms-pages-|home-|light-v4)[^\"']+\.css\?v=([0-9a-zA-Z_-]+)"
        )
        for path in ROOT.glob("pages/**/*.inc.php"):
            if "_preview" in path.parts:
                continue
            text = path.read_text(encoding="utf-8", errors="replace")
            if "<?php" in text and "echo" in text:
                continue
            for found_v in stale.findall(text):
                self.assertEqual(
                    expected,
                    found_v,
                    "{0}: hardcoded shared css v={1}".format(path, found_v),
                )

    def test_home_css_version_in_head_snippet(self):
        """Home hero CSS has its own cache-bust token (r21+) in the main.php head snippet."""
        import lireate_home_light_v4_install_lib as home_lib

        snippet = home_lib._head_snippet()
        self.assertIn(
            "/template/css/lireate-home-light-v4.css?v={0}".format(ver.HOME_CSS_VERSION), snippet
        )
        self.assertNotIn(
            "/template/css/lireate-home-light-v4.css?v={0}\"".format(ver.LIGHT_V4_ASSET_VERSION), snippet
        )
        # other shared assets keep the shared token
        self.assertIn("/template/css/lireate-light-v4.css?v={0}".format(ver.LIGHT_V4_ASSET_VERSION), snippet)

    def test_home_hero_grid_aligns_top(self):
        """Hero: left column (eyebrow/H1) starts at the photo top, not vertically centred."""
        css = (ROOT / "template" / "css" / "lireate-home-light-v4.css").read_text(encoding="utf-8")
        rule = re.search(r"\.lh4-root \.hero-grid\{([^}]*)\}", css)
        self.assertIsNotNone(rule)
        self.assertIn("align-items:start", rule.group(1))
        self.assertNotIn("align-items:center", rule.group(1))
        self.assertIn(".hero-grid > .reveal:first-child{align-self:start;margin-top:0;padding-top:0}", css)


if __name__ == "__main__":
    unittest.main()