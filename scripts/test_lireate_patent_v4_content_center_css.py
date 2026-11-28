#!/usr/bin/env python3
"""CSS guards: patent-v4 content centering with sitewide sidebar V7."""
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
CSS = ROOT / "template" / "css" / "lireate-patent-light-v4.css"


class TestPatentV4ContentCenterCss(unittest.TestCase):
    def test_patent_shell_reset_for_lsv7(self):
        css = CSS.read_text(encoding="utf-8")
        self.assertIn("body.lireate-patent-light-v4.lsv7-managed", css)
        self.assertIn("body.lireate-patent-light-v4.lsv7-managed .container.content", css)
        self.assertIn("padding-left:0!important", css)
        self.assertIn("body.lireate-patent-light-v4 .container.content", css)

    def test_lp4_version_bump(self):
        inc = (ROOT / "pages" / "patents" / "_shared" / "lireate-patent-page.inc.php").read_text(
            encoding="utf-8"
        )
        self.assertIn("20261128-center-v1", inc)


if __name__ == "__main__":
    unittest.main()