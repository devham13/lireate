#!/usr/bin/env python3
"""CSS contract tests for CMS-embedded blog articles."""

import unittest
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
CSS = REPO / "pages/blog/_shared/lireate-blog-article.css"


class BlogCmsCssTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.text = CSS.read_text(encoding="utf-8")

    def test_articlebox_shell_override(self):
        self.assertIn("#articleBox:has(.lireate-blog-page)", self.text)
        self.assertIn(".content:has(#articleBox .lireate-blog-page)", self.text)

    def test_h1_beats_legacy_articlebox(self):
        self.assertIn("#articleBox h1.lireate-blog-h1", self.text)
        self.assertIn("color: #fff !important", self.text)

    def test_prose_h2_display(self):
        self.assertIn("#articleBox .lireate-blog-prose h2", self.text)
        self.assertIn("Unbounded", self.text)

    def test_tables_and_blockquote(self):
        self.assertIn("#articleBox .lireate-blog-prose blockquote", self.text)
        self.assertIn("#articleBox .lireate-blog-prose table", self.text)


if __name__ == "__main__":
    unittest.main()
