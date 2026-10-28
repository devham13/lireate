#!/usr/bin/env python3
"""Tests for lireate_docs_install."""

import shutil
import sys
import tempfile
import unittest
from pathlib import Path

REPO_ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(REPO_ROOT / "scripts"))

import lireate_docs_install_lib as lib  # noqa: E402

FIXTURE_INDEX = REPO_ROOT / "scripts/fixtures/cms-docs-index-fragment.php"
FIXTURE_MAIN = REPO_ROOT / "scripts/fixtures/cms-main-live.snapshot.php"


class DocsInstallTests(unittest.TestCase):
    def test_patch_docs_index(self):
        text = FIXTURE_INDEX.read_text(encoding="utf-8")
        planned = lib.plan_docs_index_content(text)
        self.assertIn(lib.INSTALL_MARKER, planned)
        self.assertIn(lib.CMS_INCLUDE, planned)
        self.assertNotIn("paddingSection section_1", planned)
        self.assertIsNone(lib.lint_php_content(lib.resolve_php_bin(), planned, "index.php"))

    def test_patch_main_live_snapshot_php_lint(self):
        text = FIXTURE_MAIN.read_text(encoding="utf-8")
        planned = lib.plan_main_body_content(text)
        self.assertIn(lib.BODY_CLASS_COND, planned)
        err = lib.lint_php_content(lib.resolve_php_bin(), planned, "main.php")
        self.assertIsNone(err, err)

    def test_span_preserves_structure(self):
        text = FIXTURE_INDEX.read_text(encoding="utf-8")
        span = lib._span_docs_index_block(text)
        self.assertIsNotNone(span)
        segment = text[span[0] : span[1]]
        self.assertIn("section_1", segment)
        self.assertIn("section_2", segment)

    def test_copy_plan_shared_paths(self):
        pairs = lib.copy_plan(REPO_ROOT)
        rels = {str(r).replace("\\", "/") for _s, r in pairs}
        self.assertIn("pages/docs/_shared/lireate-docs.css", rels)
        self.assertIn("template/page/docs/lireate-docs.css", rels)

    def test_documents_data_count(self):
        data = (REPO_ROOT / "pages/docs/_shared/lireate-docs-data.php").read_text(encoding="utf-8")
        self.assertEqual(data.count("'title' =>"), 6)


if __name__ == "__main__":
    unittest.main()
