#!/usr/bin/env python3
"""Tests for lireate_blog_install (simulated DOCROOT, no network)."""

import json
import os
import shutil
import subprocess
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
FIXTURE_LIST = REPO_ROOT / "scripts/fixtures/cms-news-list-template.php"
FIXTURE_NEWSTYLE = REPO_ROOT / "scripts/fixtures/cms-newstyle-article-snippet.php"
BODY_MARKER = "LIREATE_UNIQUE_BODY_MARKER_42"


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

    def test_patch_newstyle_maps_news_article(self):
        text = FIXTURE_NEWSTYLE.read_text(encoding="utf-8")
        planned, changed, desc = lib.patch_news_template_content(text)
        self.assertTrue(changed)
        self.assertIn("$_lb_art", planned)
        self.assertIn("news_article[0]", planned)
        self.assertIn("$category", planned)
        self.assertIn("$catUrl", planned)
        self.assertNotIn('id="textBox"', planned)
        self.assertIn("datetime", planned)
        self.assertNotIn("isset($name)", planned)

    def test_newstyle_render_single_body_and_balanced_html(self):
        snippet = FIXTURE_NEWSTYLE.read_text(encoding="utf-8")
        planned, changed, _ = lib.patch_news_template_content(snippet)
        self.assertTrue(changed)
        php = (
            "<?php\n"
            "$_SERVER['DOCUMENT_ROOT'] = '{docroot}';\n"
            "define('LIREATE_BLOG_ASSETS_BASE', '/template/page/news');\n"
            "$news_article = array(array(\n"
            "  'name' => 'Test title',\n"
            "  'full_story' => '<p>{marker}</p><img src=\"/template/images/blog/cover-test.jpg\" alt=\"\"><p>tail</p>',\n"
            "  'datetime' => 1760832000,\n"
            "  'seo_url' => 'slug-one',\n"
            "));\n"
            "$category = 'Товарный знак';\n"
            "$catUrl = '/news/';\n"
            "ob_start();\n"
            "?>\n"
            "{planned}\n"
            "<?php echo ob_get_clean();\n"
        ).format(docroot=REPO_ROOT, marker=BODY_MARKER, planned=planned)
        tmp = Path(tempfile.mkdtemp(prefix="lireate-newstyle-render-"))
        try:
            script = tmp / "run.php"
            script.write_text(php, encoding="utf-8")
            out = subprocess.run(
                ["php", str(script)],
                capture_output=True,
                text=True,
                cwd=str(REPO_ROOT),
            )
            self.assertEqual(out.returncode, 0, msg=out.stderr)
            html = out.stdout
            self.assertEqual(html.count(BODY_MARKER), 1)
            self.assertIn("lireate-blog-cover", html)
            self.assertIn("cover-test.jpg", html)
            self.assertTrue(lib.html_tags_balanced(html))
        finally:
            shutil.rmtree(tmp, ignore_errors=True)

    def test_http_probe_cache_bust(self):
        fake = mock.MagicMock()
        fake.read.return_value = b"ok"
        fake.status = 200
        fake.headers = {}
        fake.__enter__.return_value = fake
        with mock.patch("urllib.request.urlopen", return_value=fake):
            url = lib.http_probe("https://example.test", "/news/", cache_bust="12345")[2]
        self.assertIn("_lireate_blog_install=12345", url)

    def test_verify_news_list_requires_list_markup(self):
        article_html = (
            '<div class="lireate-blog-page lireate-blog-body">'
            '<nav class="lireate-blog-toc-card" data-lx-blog-toc></nav>'
            '<footer class="lx-site-footer"></footer></div>'
        )
        list_html = (
            '<div class="lireate-blog-list-page">'
            '<footer class="lx-site-footer"></footer></div>'
        )

        def fake_probe(base, path, timeout=25, cache_bust=None):
            if path == "/news/":
                return 200, list_html, "https://x/news/", {}
            return 200, article_html, "https://x/article/", {}

        with mock.patch.object(lib, "http_probe", side_effect=fake_probe):
            errors = lib.verify_http("https://x", "/article/")
        self.assertEqual(errors, [])

    def test_verify_article_requires_footer(self):
        with mock.patch.object(
            lib,
            "http_probe",
            return_value=(200, "<html>no blog</html>", "https://x/a/", {}),
        ):
            errors = lib.verify_http("https://x", "/article/")
        self.assertTrue(any("lx-site-footer" in e for e in errors))

    def test_copy_plan_has_cms_include(self):
        pairs = lib.copy_plan(REPO_ROOT)
        dests = {str(rel).replace("\\", "/") for _, rel in pairs}
        self.assertIn("template/page/news/lireate-blog-cms.inc.php", dests)
        self.assertIn("template/page/news/lireate-blog-list-cms.inc.php", dests)
        self.assertIn("template/page/news/lireate-blog-list-data.php", dests)
        self.assertIn("template/page/news/lireate-blog-list-legacy.inc.php", dests)
        self.assertIn("template/images/blog/author/yuriy-gorbachev.jpg", dests)

    def test_patch_news_list_template(self):
        text = FIXTURE_LIST.read_text(encoding="utf-8")
        planned, changed, desc = lib.patch_news_list_template_content(text)
        self.assertTrue(changed)
        self.assertIn("lireate-blog-list-cms.inc.php", planned)
        self.assertIn(lib.LIST_INSTALL_MARKER, planned)
        self.assertIn("lireate-blog-list-cms.inc.php", planned)
        self.assertNotIn("Самое свежее и интересное", planned)

    def test_list_check_apply_status_agree(self):
        text = FIXTURE_LIST.read_text(encoding="utf-8")
        planned = lib.plan_list_template_content(text)
        self.assertEqual(lib.list_template_patch_status(text, planned), "would patch")
        self.assertEqual(lib.list_template_patch_status(planned, planned), "no changes needed (list include already present)")

    def test_extract_list_legacy_inner(self):
        text = FIXTURE_LIST.read_text(encoding="utf-8")
        inner = lib.extract_blog_section_legacy_inner(text)
        self.assertIsNotNone(inner)
        self.assertIn("Самое свежее и интересное", inner)

    def test_verify_news_list_rejects_fallback(self):
        list_html = (
            '<!-- lireate-blog-list-fallback:v1 -->'
            '<div class="lireate-blog-list-page">'
            '<footer class="lx-site-footer"></footer></div>'
        )
        article_html = (
            '<div class="lireate-blog-page lireate-blog-body">'
            '<nav class="lireate-blog-toc-card" data-lx-blog-toc></nav>'
            '<footer class="lx-site-footer"></footer></div>'
        )

        def fake_probe(base, path, timeout=25, cache_bust=None):
            if path == "/news/":
                return 200, list_html, "https://x/news/", {}
            return 200, article_html, "https://x/article/", {}

        with mock.patch.object(lib, "http_probe", side_effect=fake_probe):
            errors = lib.verify_http("https://x", "/article/")
        self.assertTrue(any("fallback" in e for e in errors))

    def test_find_news_list_template(self):
        tmp = Path(tempfile.mkdtemp(prefix="lireate-blog-list-doc-"))
        try:
            dest = tmp / "template" / "page" / "news" / "list.php"
            dest.parent.mkdir(parents=True)
            shutil.copy(str(FIXTURE_LIST), str(dest))
            found = lib.find_news_list_template(tmp)
            self.assertEqual(found, dest)
        finally:
            shutil.rmtree(tmp, ignore_errors=True)

    def test_list_pagination_build_php(self):
        php = (
            "<?php\n"
            "require '{lib}';\n"
            "$p = lireate_blog_list_build_pagination(2, 5);\n"
            "echo json_encode($p);\n"
        ).format(lib=REPO_ROOT / "pages/blog/_shared/lireate-blog-lib.php")
        tmp = Path(tempfile.mkdtemp(prefix="lireate-blog-pager-"))
        try:
            script = tmp / "run.php"
            script.write_text(php, encoding="utf-8")
            out = subprocess.run(["php", str(script)], capture_output=True, text=True)
            self.assertEqual(out.returncode, 0, msg=out.stderr)
            data = json.loads(out.stdout)
            self.assertEqual(data["current"], 2)
            self.assertEqual(data["total"], 5)
            self.assertEqual(len(data["pages"]), 5)
        finally:
            shutil.rmtree(tmp, ignore_errors=True)

    def test_list_diag_without_live_list_cms(self):
        """--diag must render from office-repo when list-cms is not deployed yet."""
        tmp = Path(tempfile.mkdtemp(prefix="lireate-blog-diag-"))
        try:
            live = tmp / "docroot"
            tpl_dir = live / "template" / "page" / "news"
            tpl_dir.mkdir(parents=True)
            (live / "data").mkdir(parents=True)
            (live / "data" / "db.php").write_text("<?php\n", encoding="utf-8")
            shutil.copy(str(FIXTURE_LIST), str(tpl_dir / "blog.php"))
            list_cms_live = live / "template/page/news/lireate-blog-list-cms.inc.php"
            self.assertFalse(list_cms_live.is_file())
            code = lib.run_php_list_diag(live, REPO_ROOT)
            self.assertEqual(code, 0)
        finally:
            shutil.rmtree(tmp, ignore_errors=True)

    def test_list_patch_strips_v2_footer_tail(self):
        text = (
            "<?php ?>\n"
            "<section id=\"blog\">\n"
            "<?php /* %s */ ?>\n"
            "</section>\n"
            "<?php\nif (function_exists('lireate_blog_render_service_footer')) {\n"
            "    lireate_blog_render_service_footer();\n}\n?>\n"
        ) % lib.LIST_INSTALL_MARKER_LEGACY_V2
        planned, changed, _desc = lib.patch_news_list_template_content(text)
        self.assertTrue(changed)
        self.assertIn(lib.LIST_INSTALL_MARKER, planned)
        self.assertNotIn("lireate_blog_render_service_footer", planned)


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
    def test_explicit_check_flag(self, _sync):
        code = install.main(["--check", "--env-file", str(self.env_file)])
        self.assertEqual(code, 0)

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
