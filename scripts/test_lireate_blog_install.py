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
FIXTURE_NEWSTYLE_LEGACY_CAT = (
    REPO_ROOT / "scripts/fixtures/cms-newstyle-legacy-category-top.php"
)
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

    def test_verify_http_aborts_on_5xx_without_second_round(self):
        article_html = (
            '<div class="lireate-blog-page lireate-blog-body">'
            '<nav class="lireate-blog-toc-card" data-lx-blog-toc></nav>'
            '<footer class="lx-site-footer"></footer></div>'
        )
        calls = []

        def fake_probe(base, path, timeout=25, cache_bust=None):
            calls.append((path, cache_bust))
            if path == "/news/":
                return 200, article_html, "https://x/news/", {}
            return 500, "error", "https://x/article/", {}

        with mock.patch.object(lib, "http_probe", side_effect=fake_probe):
            with mock.patch.object(lib, "time") as t:
                t.sleep = mock.MagicMock()
                errors = lib.verify_http_deploy(
                    "https://x",
                    ["/article/"],
                    rounds=2,
                    wait_seconds=90,
                )
        self.assertTrue(errors)
        self.assertEqual(len(calls), 2)
        self.assertTrue(all(cb.endswith("-r1") for _p, cb in calls))
        t.sleep.assert_not_called()

    def test_template_lireate_footer_uses_docroot_shared(self):
        tmp = Path(tempfile.mkdtemp(prefix="lireate-tpl-footer-"))
        try:
            doc = tmp / "docroot"
            shared = doc / "_shared"
            tpl = doc / "template/lireate"
            shared.mkdir(parents=True)
            tpl.mkdir(parents=True)
            for name in lib.DOCROOT_MESSENGER_BUNDLE_NAMES:
                shutil.copy2(str(REPO_ROOT / "pages/services/_shared" / name), str(shared / name))
            shutil.copy2(
                str(REPO_ROOT / "template/lireate/lireate-service-footer.inc.php"),
                str(tpl / "lireate-service-footer.inc.php"),
            )
            shutil.copy2(
                str(REPO_ROOT / "pages/services/_shared/lireate-service-messenger-include.inc.php"),
                str(tpl / "lireate-service-messenger-include.inc.php"),
            )
            runner = tmp / "run.php"
            runner.write_text(
                (
                    "<?php\n"
                    "ini_set('display_errors', '1');\n"
                    "error_reporting(E_ALL);\n"
                    "$_SERVER['DOCUMENT_ROOT'] = '{doc}';\n"
                    "include '{footer}';\n"
                ).format(doc=doc, footer=tpl / "lireate-service-footer.inc.php"),
                encoding="utf-8",
            )
            out = subprocess.run(["php", str(runner)], capture_output=True, text=True)
            combined = out.stdout + out.stderr
            self.assertEqual(out.returncode, 0, msg=combined)
            self.assertNotIn("template/_shared", combined)
            self.assertNotIn("Failed opening required", combined)
            self.assertIn("lx-site-footer", out.stdout)
        finally:
            shutil.rmtree(tmp, ignore_errors=True)

    def test_service_footer_without_slug_messenger_file(self):
        tmp = Path(tempfile.mkdtemp(prefix="lireate-svc-messenger-"))
        try:
            doc = tmp / "docroot"
            shared = doc / "_shared"
            shared.mkdir(parents=True)
            svc_dir = doc / "registraciya_tovarnogo_znaka_v_kitae"
            svc_dir.mkdir(parents=True)
            for name in lib.DOCROOT_MESSENGER_BUNDLE_NAMES:
                shutil.copy2(
                    str(REPO_ROOT / "pages/services/_shared" / name),
                    str(shared / name),
                )
            shutil.copy2(
                str(REPO_ROOT / "pages/services/_shared/lireate-service-footer.inc.php"),
                str(svc_dir / "lireate-service-footer.inc.php"),
            )
            shutil.copy2(
                str(REPO_ROOT / "pages/services/_shared/lireate-service-messenger-include.inc.php"),
                str(svc_dir / "lireate-service-messenger-include.inc.php"),
            )
            runner = tmp / "run.php"
            runner.write_text(
                (
                    "<?php\n"
                    "ini_set('display_errors', '1');\n"
                    "error_reporting(E_ALL);\n"
                    "$_SERVER['DOCUMENT_ROOT'] = '{doc}';\n"
                    "include '{footer}';\n"
                ).format(doc=doc, footer=svc_dir / "lireate-service-footer.inc.php"),
                encoding="utf-8",
            )
            out = subprocess.run(["php", str(runner)], capture_output=True, text=True)
            combined = out.stdout + out.stderr
            self.assertEqual(out.returncode, 0, msg=combined)
            self.assertNotIn("Failed opening required", combined)
            self.assertIn("lx-site-footer", out.stdout)
        finally:
            shutil.rmtree(tmp, ignore_errors=True)

    def test_php_timezone_warning_ignored_in_diag_sandbox(self):
        html = (
            '<b>Warning</b>: date(): It is not safe to rely on the system\'s timezone settings '
            "in <b>/tmp/x.php</b> on line <b>12</b><br />\n"
            '<div class="lireate-blog-page">'
            '<nav class="lireate-blog-toc-card" data-lx-blog-toc></nav>'
            '<footer class="lx-site-footer"></footer></div>'
        )
        errs, _diag = lib.verify_render_html_errors(html, {}, "article", diag_sandbox=True)
        self.assertEqual(errs, [])

    def test_diag_sandbox_ignores_notice_in_html(self):
        html = (
            "<b>Notice</b>: Undefined index: category in <b>/tmp/newStyle.php</b> on line <b>26</b>"
            '<div class="lireate-blog-page">'
            '<nav class="lireate-blog-toc-card" data-lx-blog-toc></nav>'
            '<footer class="lx-site-footer"></footer></div>'
        )
        errs, _diag = lib.verify_render_html_errors(html, {}, "article", diag_sandbox=True)
        self.assertEqual(errs, [])

    def test_diag_log_fatal_blocks_sandbox_verify(self):
        log_parsed = lib.parse_php_error_log(
            "PHP Parse error:  syntax error, unexpected '}' in /tmp/newStyle.php on line 9\n"
        )
        html = (
            '<div class="lireate-blog-page">'
            '<nav class="lireate-blog-toc-card" data-lx-blog-toc></nav>'
            '<footer class="lx-site-footer"></footer></div>'
        )
        errs, _diag = lib.verify_render_html_errors(
            html, {}, "article", diag_sandbox=True, diag_log_parsed=log_parsed
        )
        self.assertTrue(any("PHP log fatal" in e for e in errs))

    def test_newstyle_patch_guards_news_article_category(self):
        snippet = FIXTURE_NEWSTYLE.read_text(encoding="utf-8")
        planned = lib.plan_template_content(snippet)
        self.assertIn(lib.NEWSTYLE_CATEGORY_GUARD_MARKER, planned)
        self.assertIn("array_key_exists('category'", planned)

    def test_patched_newstyle_legacy_category_php_lint_and_render(self):
        if not shutil.which("php"):
            self.skipTest("php-cli not available")
        snippet = FIXTURE_NEWSTYLE_LEGACY_CAT.read_text(encoding="utf-8")
        planned = lib.plan_template_content(snippet)
        self.assertNotIn("isset((", planned)
        tmp = Path(tempfile.mkdtemp(prefix="lireate-newstyle-patch-"))
        try:
            doc = tmp / "docroot"
            for src, rel in lib.copy_plan(REPO_ROOT):
                dst = doc / rel
                dst.parent.mkdir(parents=True, exist_ok=True)
                shutil.copy2(str(src), str(dst))
            article = doc / "template/page/news/newStyle.php"
            article.parent.mkdir(parents=True, exist_ok=True)
            article.write_text(planned, encoding="utf-8")
            lint = subprocess.run(
                ["php", "-l", str(article)], capture_output=True, text=True
            )
            self.assertEqual(lint.returncode, 0, msg=lint.stdout + lint.stderr)
            slug = "diag-slug"
            runner = tmp / "run.php"
            runner.write_text(
                lib.build_php_newstyle_diag_runner(str(doc), str(article), slug),
                encoding="utf-8",
            )
            render = subprocess.run(
                [
                    "php",
                    "-d",
                    "display_errors=0",
                    "-d",
                    "log_errors=1",
                    "-d",
                    "date.timezone=Europe/Moscow",
                    str(runner),
                ],
                capture_output=True,
                text=True,
                cwd=str(REPO_ROOT),
            )
            combined = render.stdout + render.stderr
            self.assertEqual(render.returncode, 0, msg=combined)
            self.assertNotIn("Fatal error", combined)
            self.assertNotIn("Parse error", combined)
            self.assertIn("lireate-blog-page", render.stdout)
        finally:
            shutil.rmtree(tmp, ignore_errors=True)

    def test_diff_new_log_signatures_ignores_line_number_shift(self):
        baseline = {
            lib.php_error_signature(
                "Notice", "Undefined index: category", "/doc/newStyle.php"
            )
        }
        log_260 = (
            "PHP Notice:  Undefined index: category in /doc/newStyle.php on line 260\n"
        )
        parsed = lib.parse_php_error_log(log_260)
        new_sigs = lib.diff_new_log_signatures(baseline, parsed)
        self.assertEqual(new_sigs, [])

    def test_diff_new_log_signatures_only_new_notices_fail(self):
        baseline = {
            lib.php_error_signature(
                "Notice", "Undefined index: category", "/doc/newStyle.php"
            )
        }
        log = (
            "PHP Notice:  Undefined index: category in /doc/newStyle.php on line 26\n"
            "PHP Notice:  Undefined index: brand_new in /doc/newStyle.php on line 30\n"
        )
        parsed = lib.parse_php_error_log(log)
        new_sigs = lib.diff_new_log_signatures(baseline, parsed)
        self.assertEqual(len(new_sigs), 1)
        self.assertIn("brand_new", new_sigs[0])

    def test_collage_diag_runner_renders_service_footer(self):
        php_src = lib.build_php_article_collage_diag_runner("/tmp/s", "/tmp/l.php", "/tmp/layout.php")
        self.assertIn("lireate_blog_render_service_footer", php_src)

    def test_verify_article_markers_require_toc(self):
        html = (
            '<div class="lireate-blog-page">'
            '<footer class="lx-site-footer"></footer></div>'
        )
        errs, diag = lib.verify_render_html_errors(html, {}, "article")
        self.assertTrue(any("TOC" in e for e in errs))
        self.assertFalse(diag["markers"]["toc"])

    def test_verify_article_markers_h3_toc_markup(self):
        html = (
            '<div class="lireate-blog-page">'
            '<nav class="lireate-blog-toc-card" data-lx-blog-toc></nav>'
            '<footer class="lx-site-footer"></footer></div>'
        )
        errs, _diag = lib.verify_render_html_errors(html, {}, "article")
        self.assertEqual(errs, [])

    def test_h3_heading_ids_and_toc_extract(self):
        if not shutil.which("php"):
            self.skipTest("php-cli not available")
        php = (
            "<?php\n"
            "require '{lib}';\n"
            "$html = '<h3>Раздел A</h3><p>x</p><h3>Раздел B</h3>';\n"
            "$html = lireate_blog_ensure_h2_ids($html);\n"
            "$toc = lireate_blog_extract_toc($html);\n"
            "echo json_encode($toc, JSON_UNESCAPED_UNICODE);\n"
        ).format(lib=REPO_ROOT / "pages/blog/_shared/lireate-blog-lib.php")
        tmp = Path(tempfile.mkdtemp(prefix="lireate-h3-toc-"))
        try:
            script = tmp / "run.php"
            script.write_text(php, encoding="utf-8")
            out = subprocess.run(["php", str(script)], capture_output=True, text=True)
            self.assertEqual(out.returncode, 0, msg=out.stderr + out.stdout)
            data = json.loads(out.stdout)
            self.assertEqual(len(data), 2)
            self.assertIn("razdel", data[0]["id"].lower())
        finally:
            shutil.rmtree(tmp, ignore_errors=True)

    def test_dual_lib_tree_article_render_no_redeclare(self):
        tmp = Path(tempfile.mkdtemp(prefix="lireate-dual-lib-"))
        try:
            doc = tmp / "docroot"
            for src, rel in lib.copy_plan(REPO_ROOT):
                dst = doc / rel
                dst.parent.mkdir(parents=True, exist_ok=True)
                shutil.copy2(str(src), str(dst))
            snippet = FIXTURE_NEWSTYLE.read_text(encoding="utf-8")
            planned, changed, _ = lib.patch_news_template_content(snippet)
            self.assertTrue(changed)
            article = doc / "template/page/news/newStyle.php"
            article.parent.mkdir(parents=True, exist_ok=True)
            article.write_text(planned, encoding="utf-8")
            slug = lib.probe_article_path(REPO_ROOT).strip("/")
            runner = tmp / "run.php"
            runner.write_text(
                lib.build_php_newstyle_diag_runner(str(doc), str(article), slug),
                encoding="utf-8",
            )
            out = subprocess.run(
                ["php", str(runner)],
                capture_output=True,
                text=True,
                cwd=str(REPO_ROOT),
            )
            self.assertEqual(out.returncode, 0, msg=out.stderr + out.stdout)
            self.assertNotIn("Cannot redeclare", out.stdout + out.stderr)
            self.assertIn("lireate-blog-page", out.stdout)
            errs, _diag = lib.verify_render_html_errors(out.stdout, {}, "article")
            self.assertEqual(errs, [])
        finally:
            shutil.rmtree(tmp, ignore_errors=True)

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

    def test_list_cover_prefers_existing_small_image(self):
        tmp = Path(tempfile.mkdtemp(prefix="lireate-blog-cover-"))
        try:
            img_dir = tmp / "template" / "images" / "blog" / "small"
            img_dir.mkdir(parents=True)
            img = img_dir / "cover-test.jpg"
            img.write_bytes(b"fake")
            php = (
                "<?php\n"
                "$_SERVER['DOCUMENT_ROOT'] = '{doc}';\n"
                "require '{lib}';\n"
                "$row = array(\n"
                "  'name' => 'T',\n"
                "  'seo_url' => 'wrong_slug',\n"
                "  'full_story' => '<p><img src=\"/template/images/blog/small/cover-test.jpg\" alt=\"\"></p>',\n"
                ");\n"
                "echo lireate_blog_list_cover_from_row($row);\n"
            ).format(doc=tmp, lib=REPO_ROOT / "pages/blog/_shared/lireate-blog-lib.php")
            script = tmp / "run.php"
            script.write_text(php, encoding="utf-8")
            out = subprocess.run(["php", str(script)], capture_output=True, text=True)
            self.assertEqual(out.returncode, 0, msg=out.stderr)
            self.assertIn("cover-test.jpg", out.stdout)
        finally:
            shutil.rmtree(tmp, ignore_errors=True)

    def test_list_pagination_build_php(self):
        php = (
            "<?php\n"
            "require '{lib}';\n"
            "$p = lireate_blog_list_build_pagination(2, 21);\n"
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
            self.assertEqual(data["total"], 21)
            nums = [i["num"] for i in data["items"] if i.get("type") == "page"]
            self.assertEqual(nums, [1, 2, 3, 21])
            self.assertTrue(any(i.get("type") == "ellipsis" for i in data["items"]))
            self.assertLessEqual(len(data["items"]), 6)
        finally:
            shutil.rmtree(tmp, ignore_errors=True)

    def test_deploy_php_sources_no_php7_dirname_levels(self):
        violations = lib.scan_php56_forbidden_sources(REPO_ROOT)
        self.assertEqual(violations, [], msg="\n".join(violations))

    def test_build_article_collage_diag_runner_has_php_braces(self):
        """Runner must not use str.format on PHP (braces and !empty break format)."""
        sandbox = Path("/tmp/lireate-blog-diag-sandbox")
        lib_path = REPO_ROOT / "pages/blog/_shared/lireate-blog-lib.php"
        layout_path = REPO_ROOT / "pages/blog/_shared/lireate-blog-layout.inc.php"
        php_src = lib.build_php_article_collage_diag_runner(sandbox, lib_path, layout_path)
        self.assertIn("function lireate_blog_diag_render_article", php_src)
        self.assertIn("!empty($prep['mode'])", php_src)
        self.assertIn("lireate_blog_collage_mode", php_src)
        self.assertNotIn("__DOC__", php_src)
        self.assertIn("DOCUMENT_ROOT", php_src)

    def test_collage_v1_prepare_strips_cover_figure(self):
        php = (
            "<?php\n"
            "require '{lib}';\n"
            "$story = '<figure class=\"lx-article-cover\" data-cover-style=\"collage-v1\">'"
            "<img src=\"/template/images/blog/x.jpg\" alt=\"\" width=\"860\" height=\"400\"></figure><p>Hi</p>';\n"
            "$r = lireate_blog_article_collage_prepare(array('full_story' => $story, 'cover_style' => 'collage-v1', 'seo_url' => 'x', 'name' => 'T'));\n"
            "echo json_encode($r);\n"
        ).format(lib=REPO_ROOT / "pages/blog/_shared/lireate-blog-lib.php")
        tmp = Path(tempfile.mkdtemp(prefix="lireate-blog-collage-"))
        try:
            script = tmp / "run.php"
            script.write_text(php, encoding="utf-8")
            out = subprocess.run(["php", str(script)], capture_output=True, text=True)
            self.assertEqual(out.returncode, 0, msg=out.stderr)
            data = json.loads(out.stdout)
            self.assertTrue(data["mode"])
            self.assertNotIn("lx-article-cover", data["body"])
            self.assertIn("lireate-blog-collage-hero", data["hero_html"])
        finally:
            shutil.rmtree(tmp, ignore_errors=True)

    def test_footer_fix_css_marker_in_shared(self):
        css = (REPO_ROOT / "pages/blog/_shared/lireate-blog-footer.css").read_text(encoding="utf-8")
        self.assertIn(lib.FOOTER_FIX_CSS_MARKER, css)
        self.assertIn(lib.FOOTER_FIX_CSS_RULE, css)
        self.assertIn(".lx-site-footer p.lx-site-footer-title{font-size:16px", css)

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

    @mock.patch.object(lib, "run_php_list_diag", return_value=0)
    @mock.patch.object(lib, "verify_http_deploy", return_value=[])
    @mock.patch.object(lib, "sync_office_blog_template")
    def test_apply_deploys_and_patches(self, _sync, _verify, _gate):
        code = install.main(["--env-file", str(self.env_file), "--apply"])
        self.assertEqual(code, 0)
        cms = self.docroot / "template/page/news/lireate-blog-cms.inc.php"
        self.assertTrue(cms.is_file())
        patched = (self.docroot / "template/page/news/article.php").read_text(encoding="utf-8")
        self.assertIn(lib.INSTALL_MARKER, patched)
        self.assertIn(lib.BODY_CLASS, patched)

    @mock.patch.object(lib, "run_php_list_diag", return_value=0)
    @mock.patch.object(lib, "verify_http_deploy", return_value=["https://example.test/x: missing lx-site-footer"])
    @mock.patch.object(lib, "sync_office_blog_template")
    def test_apply_rolls_back_on_http_failure(self, _sync, _verify, _gate):
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
