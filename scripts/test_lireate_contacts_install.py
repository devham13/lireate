#!/usr/bin/env python3
"""Tests for lireate_contacts_install (simulated DOCROOT, no network)."""

import shutil
import subprocess
import sys
import tempfile
import unittest
from pathlib import Path

REPO_ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(REPO_ROOT / "scripts"))

import lireate_contacts_install_lib as lib  # noqa: E402

FIXTURE_TEMPLATE = REPO_ROOT / "scripts/fixtures/cms-kontaktyi-template.php"
FIXTURE_MAIN = REPO_ROOT / "scripts/fixtures/cms-main-kontaktyi-fragment.php"


def php_lint(content):
    php_bin = lib.resolve_php_bin()
    err = lib.lint_php_content(php_bin, content, "test.php")
    return err


def php_render(page, source):
    php_bin = lib.resolve_php_bin()
    docroot = str(REPO_ROOT)
    body = (
        "<?php\n"
        "$_SERVER['DOCUMENT_ROOT'] = {0};\n"
        "$temp = array('page' => {1});\n?>\n"
        "{2}"
    ).format(repr(docroot), repr(page), source)
    tmp = tempfile.NamedTemporaryFile("w", suffix=".php", delete=False, encoding="utf-8")
    try:
        tmp.write(body)
        tmp.close()
        lint_err = lib.lint_php_content(php_bin, body, "render.php")
        if lint_err:
            return None, lint_err
        proc = lib.run_subprocess([php_bin, tmp.name])
        if proc.returncode != 0:
            return None, (proc.stderr or proc.stdout or "").strip()
        return proc.stdout, None
    finally:
        try:
            import os

            os.unlink(tmp.name)
        except OSError:
            pass


class ContactsInstallLibTests(unittest.TestCase):
    def test_patch_contacts_template(self):
        text = FIXTURE_TEMPLATE.read_text(encoding="utf-8")
        planned = lib.plan_contacts_template_content(text)
        self.assertIn(lib.INSTALL_MARKER, planned)
        self.assertIn(lib.CMS_INCLUDE, planned)
        self.assertIn(lib.BODY_CLASS_COND, planned)
        self.assertNotIn("order_form_content", planned)
        self.assertIsNone(php_lint(planned))

    def test_patch_idempotent(self):
        text = FIXTURE_TEMPLATE.read_text(encoding="utf-8")
        once = lib.plan_contacts_template_content(text)
        twice = lib.plan_contacts_template_content(once)
        self.assertEqual(once, twice)

    def test_main_fragment_preserves_php_and_divs(self):
        text = FIXTURE_MAIN.read_text(encoding="utf-8")
        planned = lib.plan_contacts_template_content(text)
        self.assertIn(lib.INSTALL_MARKER, planned)
        self.assertIn("<?php } ?>", planned)
        self.assertIn("<?php if($temp['page']=='news')", planned)
        self.assertIn('<div class="page_container">', planned)
        self.assertIsNone(php_lint(planned))
        self.assertEqual(
            planned.count("<?php if"),
            text.count("<?php if") + 1,
            "expected one extra if for conditional body class",
        )
        self.assertEqual(text.count("<?php }"), planned.count("<?php }"))

    def test_span_ends_before_wrapper_divs(self):
        text = FIXTURE_MAIN.read_text(encoding="utf-8")
        span = lib._span_contacts_block(text)
        self.assertIsNotNone(span)
        segment = text[span[0] : span[1]]
        self.assertIn("title-ap", segment)
        self.assertNotIn("page_container", segment)
        after = text[span[1] : span[1] + 120]
        self.assertRegex(after, r"^\s*</div>")

    def test_find_contacts_template_prefers_main(self):
        tmp = Path(tempfile.mkdtemp(prefix="lireate-contacts-doc-"))
        try:
            main = tmp / "template" / "main.php"
            view = tmp / "template" / "page" / "kontaktyi" / "view.php"
            main.parent.mkdir(parents=True)
            view.parent.mkdir(parents=True)
            shutil.copy(str(FIXTURE_MAIN), str(main))
            shutil.copy(str(FIXTURE_TEMPLATE), str(view))
            found = lib.find_contacts_template(tmp)
            self.assertEqual(found, main)
        finally:
            shutil.rmtree(tmp, ignore_errors=True)

    def test_render_kontaktyi_and_news_after_patch(self):
        text = FIXTURE_MAIN.read_text(encoding="utf-8")
        planned = lib.plan_contacts_template_content(text)
        out_k, err_k = php_render("kontaktyi", planned)
        self.assertIsNone(err_k, err_k)
        self.assertIn("lireate-contacts", out_k)
        self.assertIn("lireate-contacts-body", out_k)
        out_n, err_n = php_render("news", planned)
        self.assertIsNone(err_n, err_n)
        self.assertIn("News block ok", out_n)
        self.assertNotIn("lireate-contacts-body", out_n)

    def test_copy_plan_includes_docroot_shared(self):
        pairs = lib.copy_plan(REPO_ROOT)
        rels = {str(rel).replace("\\", "/") for _src, rel in pairs}
        self.assertIn("pages/kontaktyi/_shared/lireate-contacts.css", rels)
        self.assertIn("template/page/kontaktyi/lireate-contacts.css", rels)

    def test_rollback_removes_created_files(self):
        tmp = Path(tempfile.mkdtemp(prefix="lireate-contacts-rollback-"))
        try:
            backup_dir = tmp / "backup"
            docroot = tmp / "docroot"
            docroot.mkdir()
            created_rel = lib.SHARED_DOCROOT_REL / "lireate-contacts.css"
            target = docroot / created_rel
            target.parent.mkdir(parents=True)
            target.write_text("new", encoding="utf-8")
            lib.write_created_manifest(backup_dir, [created_rel])
            notes = lib.remove_created_files(docroot, backup_dir)
            self.assertFalse(target.is_file())
            self.assertTrue(any("removed created" in n for n in notes))
        finally:
            shutil.rmtree(tmp, ignore_errors=True)

    def test_load_env_file_alias(self):
        import lireate_dotenv as dotenv

        self.assertTrue(callable(dotenv.load_env_file))


if __name__ == "__main__":
    unittest.main()
