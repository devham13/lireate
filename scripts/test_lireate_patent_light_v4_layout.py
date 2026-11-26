#!/usr/bin/env python3
"""Patent Light V4 markup/CSS guards (PHP 5.6 sources, CMS shell hooks)."""

import subprocess
import unittest
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
INC = ROOT / "pages/patents/_shared/lireate-patent-page.inc.php"
DATA = ROOT / "pages/patents/_shared/lireate-patent-data.php"
CSS = ROOT / "template/css/lireate-patent-light-v4.css"


class PatentLightV4LayoutTests(unittest.TestCase):
    def test_single_h1_and_modal_form_attrs(self):
        text = INC.read_text(encoding="utf-8")
        self.assertEqual(text.count("<h1>"), 1)
        self.assertIn('class="lp4-btn lp4-btn-main js-modal-form"', text)
        self.assertIn("data-frm=\"name,tel\"", text)
        self.assertIn("data-tomail=", text)

    def test_patent_pages_use_distinct_portraits(self):
        php = DATA.read_text(encoding="utf-8")
        self.assertIn("patent-inventions.webp", php)
        self.assertIn("patent-industrial-design.webp", php)
        self.assertNotIn("scaleX(-1", CSS.read_text(encoding="utf-8"))

    def test_sidebar_shell_css_present(self):
        css = CSS.read_text(encoding="utf-8")
        self.assertIn("#leftSideBar", css)
        self.assertIn("lireate-patent-light-v4", css)

    def test_php56_syntax_sources(self):
        for path in (INC, DATA):
            proc = subprocess.run(
                ["php", "-l", str(path)], capture_output=True, text=True
            )
            self.assertEqual(proc.returncode, 0, proc.stderr or proc.stdout)


if __name__ == "__main__":
    unittest.main()