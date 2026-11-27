#!/usr/bin/env python3
"""Sidebar V7 logo must use full wordmark (no light-v4 mask crop)."""
import pathlib
import unittest

CSS = pathlib.Path(__file__).resolve().parents[1] / "template/css/lireate-sitewide-sidebar-v7.css"


class SidebarV7LogoTests(unittest.TestCase):
    def test_v7_css_disables_mask_and_uses_light_v7_wordmark(self):
        text = CSS.read_text(encoding="utf-8")
        self.assertIn("logo-light-v7.svg", text)
        self.assertIn("mask-image:none", text)
        self.assertNotIn("background-size:190px auto", text)

    def test_collapsed_logo_uses_contain_not_horizontal_crop(self):
        text = CSS.read_text(encoding="utf-8")
        collapsed = text[text.find(".lsv7-collapsed") :]
        self.assertIn("background-size:contain", collapsed)
        self.assertNotIn("background-size:190px", collapsed)


if __name__ == "__main__":
    unittest.main()