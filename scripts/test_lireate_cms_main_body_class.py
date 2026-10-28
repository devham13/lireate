#!/usr/bin/env python3
"""Tests for CMS main.php body class patch (docs + contacts)."""

import sys
import unittest
from pathlib import Path

REPO_ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(REPO_ROOT / "scripts"))

import lireate_cms_main_body_class as cms_body  # noqa: E402
import lireate_contacts_install_lib as contacts_lib  # noqa: E402
import lireate_docs_install_lib as docs_lib  # noqa: E402

MAIN_WITH_CONTACTS = REPO_ROOT / "scripts/fixtures/cms-main-with-contacts-body.php"
EXPECTED_BOTH = (
    'class="cms-default'
    + cms_body.DOCS_BODY_COND
    + cms_body.CONTACTS_BODY_COND
    + '"'
)


class CmsMainBodyClassTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        if not MAIN_WITH_CONTACTS.is_file():
            import lireate_fetch_main_contacts_fixture as fetch

            rc = fetch.main()
            if rc != 0:
                raise unittest.SkipTest("could not build cms-main-with-contacts-body.php fixture")

    def test_docs_patch_after_cms_default_with_contacts(self):
        text = MAIN_WITH_CONTACTS.read_text(encoding="utf-8")
        self.assertIn(cms_body.CONTACTS_BODY_COND, text)
        planned, changed = docs_lib.patch_body_class_conditional(text)
        self.assertTrue(changed)
        self.assertIn(EXPECTED_BOTH, planned)
        self.assertNotIn("if($temp[<?php", planned)
        err = docs_lib.lint_php_content(docs_lib.resolve_php_bin(), planned, "main.php")
        self.assertIsNone(err, err)

    def test_docs_patch_idempotent(self):
        text = MAIN_WITH_CONTACTS.read_text(encoding="utf-8")
        once, _ = docs_lib.patch_body_class_conditional(text)
        twice, changed = docs_lib.patch_body_class_conditional(once)
        self.assertFalse(changed)
        self.assertEqual(once, twice)

    def test_contacts_patch_idempotent_with_docs_present(self):
        text = MAIN_WITH_CONTACTS.read_text(encoding="utf-8")
        with_docs, _ = docs_lib.patch_body_class_conditional(text)
        again, changed = contacts_lib.patch_body_class_conditional(with_docs)
        self.assertFalse(changed)
        self.assertEqual(with_docs, again)


if __name__ == "__main__":
    unittest.main()
