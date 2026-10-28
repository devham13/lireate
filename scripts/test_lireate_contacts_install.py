#!/usr/bin/env python3
"""Tests for lireate_contacts_install (simulated DOCROOT)."""

import shutil
import sys
import tempfile
import unittest
from pathlib import Path

REPO_ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(REPO_ROOT / "scripts"))

import lireate_contacts_install_lib as lib  # noqa: E402

FIXTURE = REPO_ROOT / "scripts/fixtures/cms-kontaktyi-template.php"


class ContactsInstallLibTests(unittest.TestCase):
    def test_patch_contacts_template(self):
        text = FIXTURE.read_text(encoding="utf-8")
        planned = lib.plan_contacts_template_content(text)
        self.assertIn(lib.INSTALL_MARKER, planned)
        self.assertIn(lib.CMS_INCLUDE, planned)
        self.assertIn(lib.BODY_CLASS, planned)
        self.assertNotIn("order_form_content", planned)

    def test_patch_idempotent(self):
        text = FIXTURE.read_text(encoding="utf-8")
        once = lib.plan_contacts_template_content(text)
        twice = lib.plan_contacts_template_content(once)
        self.assertEqual(once, twice)

    def test_find_contacts_template(self):
        tmp = Path(tempfile.mkdtemp(prefix="lireate-contacts-doc-"))
        try:
            dest = tmp / "template" / "page" / "kontaktyi" / "view.php"
            dest.parent.mkdir(parents=True)
            shutil.copy(str(FIXTURE), str(dest))
            found = lib.find_contacts_template(tmp)
            self.assertEqual(found, dest)
        finally:
            shutil.rmtree(tmp, ignore_errors=True)


if __name__ == "__main__":
    unittest.main()
