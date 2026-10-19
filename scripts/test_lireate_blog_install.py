#!/usr/bin/env python3
"""Tests for lireate_blog_install (simulated DOCROOT, no network)."""

import json
import os
import shutil
import sys
import tempfile
import unittest
from pathlib import Path
from unittest import mock

REPO_ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(REPO_ROOT / "scripts"))

import lireate_blog_install as install  # noqa: E402
import lireate_blog_install_lib as lib  # noqa: E402

FIXTURE_TEMPLATE = REPO_ROOT / "scripts/fixtures/cms-news-article-template.php"


class BlogInstallLibTests(unittest.TestCase):
    def test_patch_legacy_template(self):
        text = FIXTURE_TEMPLATE.read_text(encoding="utf-8")
        planned = lib.plan_template_content(text)
        self.assertIn(lib.INSTALL_MARKER, planned)
        self.assertIn(lib.CMS_INCLUDE, planned)
        self.assertIn(lib.BODY_CLASS, planned)
        self.assertNotIn('id="textBox"', planned)

    def test_patch_idempotent(self):
        text = FIXTURE_TEMPLATE.read_text(encoding="utf-8")
        once = lib.plan_template_content(text)
        twice = lib.plan_template_content(once)
        self.assertEqual(once, twice)

    def test_find_news_template(self):
        tmp = Path(tempfile.mkdtemp(prefix="lireate-blog-doc-"))
        try:
            dest = tmp / "template" / "page" / "news" / "view.php"
            dest.parent.mkdir(parents=True)
            shutil.copy(str(FIXTURE_TEMPLATE), str(dest))
            found = lib.find_news_template(tmp)
            self.assertEqual(found, dest)
        finally:
            shutil.rmtree(tmp, ignore_errors=True)

    def test_copy_plan_has_cms_include(self):
        pairs = lib.copy_plan(REPO_ROOT)
        dests = {str(rel).replace("\\", "/") for _, rel in pairs}
        self.assertIn("template/page/news/lireate-blog-cms.inc.php", dests)
        self.assertIn("template/images/blog/author/yuriy-gorbachev.jpg", dests)


class BlogInstallApplyTests(unittest.TestCase):
    def setUp(self):
        self.tmp = Path(tempfile.mkdtemp(prefix="lireate-blog-install-"))
        self.docroot = self.tmp / "docroot"
        self.home = self.tmp / "autopub"
        self.env_file = self.home / ".env"
        self.docroot.mkdir(parents=True)
        self.home.mkdir(parents=True)
        tpl_dir = self.docroot / "template" / "page" / "news"
        tpl_dir.mkdir(parents=True)
        shutil.copy(str(FIXTURE_TEMPLATE), str(tpl_dir / "article.php"))
        self.env_file.write_text(
            "LIREATE_DOCROOT={0}\n".format(self.docroot),
            encoding="utf-8",
        )
        os.environ["LIREATE_AUTOPUB_HOME"] = str(self.home)
        os.environ["LIREATE_ENV_FILE"] = str(self.env_file)
        os.environ["LIREATE_DOCROOT"] = str(self.docroot)
        os.environ["LIREATE_PUBLIC_SITE_URL"] = "https://example.test"

    def tearDown(self):
        shutil.rmtree(self.tmp, ignore_errors=True)

    @mock.patch.object(lib, "verify_http", return_value=[])
    @mock.patch.object(lib, "sync_office_blog_template")
    def test_apply_deploys_and_patches(self, _sync, _verify):
        code = install.main(["--env-file", str(self.env_file), "--apply"])
        self.assertEqual(code, 0)
        cms = self.docroot / "template/page/news/lireate-blog-cms.inc.php"
        self.assertTrue(cms.is_file())
        patched = (self.docroot / "template/page/news/article.php").read_text(encoding="utf-8")
        self.assertIn(lib.INSTALL_MARKER, patched)
        self.assertIn(lib.BODY_CLASS, patched)

    @mock.patch.object(lib, "verify_http", return_value=["https://example.test/x: missing lx-site-footer"])
    @mock.patch.object(lib, "sync_office_blog_template")
    def test_apply_rolls_back_on_http_failure(self, _sync, _verify):
        code = install.main(["--env-file", str(self.env_file), "--apply"])
        self.assertEqual(code, 1)
        patched = (self.docroot / "template/page/news/article.php").read_text(encoding="utf-8")
        self.assertIn('id="textBox"', patched)
        self.assertNotIn(lib.INSTALL_MARKER, patched)

    @mock.patch.object(lib, "sync_office_blog_template")
    def test_check_prints_diff(self, _sync):
        import io
        from contextlib import redirect_stdout

        buf = io.StringIO()
        with redirect_stdout(buf):
            code = install.main(["--env-file", str(self.env_file)])
        self.assertEqual(code, 0)
        out = buf.getvalue()
        self.assertIn("COPY", out)
        self.assertIn("lireate-blog-cms.inc.php", out)
        self.assertIn(lib.BODY_CLASS, out)


if __name__ == "__main__":
    unittest.main()
