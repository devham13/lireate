#!/usr/bin/env python3
"""CI: blog list pagination URLs and invalid page noindex (PHP, no DB)."""
import os
import shutil
import subprocess
import tempfile
import time
import unittest
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
LIST_DATA = REPO / "pages/blog/_shared/lireate-blog-list-data.php"
LOAD = REPO / "pages/blog/_shared/lireate-blog-load.php"


def _run_php(php_source):
    tmp = Path(tempfile.mkdtemp(prefix="lireate-blog-pag-"))
    try:
        script = tmp / "run.php"
        script.write_text(php_source, encoding="utf-8")
        out = subprocess.run(
            ["php", str(script)],
            capture_output=True,
            text=True,
            cwd=str(REPO),
        )
        return out
    finally:
        import shutil

        shutil.rmtree(tmp, ignore_errors=True)


class BlogListPaginationUrlTests(unittest.TestCase):
    def test_pagination_urls_strip_view_and_trailing_slash(self):
        php = (
            "<?php\n"
            "$_GET = array('view' => 'news', 'page' => '2');\n"
            "require '{data}';\n"
            "echo lireate_blog_list_pagination_url(1), \"\\n\";\n"
            "echo lireate_blog_list_pagination_url(2), \"\\n\";\n"
            "echo lireate_blog_list_pagination_url(3), \"\\n\";\n"
        ).format(data=LIST_DATA)
        out = _run_php(php)
        self.assertEqual(out.returncode, 0, msg=out.stderr)
        lines = out.stdout.strip().splitlines()
        self.assertEqual(lines[0], "/news")
        self.assertEqual(lines[1], "/news?page=2")
        self.assertEqual(lines[2], "/news?page=3")
        self.assertNotIn("view=", out.stdout)

    def test_pagination_preserves_category_filters(self):
        php = (
            "<?php\n"
            "$_GET = array('num' => '2', 'category' => '9', 'view' => 'news', 'page' => '2');\n"
            "require '{data}';\n"
            "echo lireate_blog_list_pagination_url(1), \"\\n\";\n"
            "echo lireate_blog_list_pagination_url(2), \"\\n\";\n"
        ).format(data=LIST_DATA)
        out = _run_php(php)
        self.assertEqual(out.returncode, 0, msg=out.stderr)
        lines = out.stdout.strip().splitlines()
        self.assertEqual(lines[0], "/news?num=2&category=9")
        self.assertEqual(lines[1], "/news?num=2&category=9&page=2")

    def test_build_pagination_links_use_news_without_view(self):
        php = (
            "<?php\n"
            "$_GET = array('view' => 'news');\n"
            "require '{load}';\n"
            "lireate_blog_require_lib();\n"
            "$p = lireate_blog_list_build_pagination(2, 5);\n"
            "echo $p['prev_url'], \"\\n\";\n"
            "echo $p['next_url'], \"\\n\";\n"
            "echo $p['items'][0]['url'], \"\\n\";\n"
        ).format(load=LOAD)
        out = _run_php(php)
        self.assertEqual(out.returncode, 0, msg=out.stderr)
        lines = out.stdout.strip().splitlines()
        self.assertEqual(lines[0], "/news")
        self.assertEqual(lines[1], "/news?page=3")
        self.assertNotIn("view=", out.stdout)
        self.assertNotIn("/news/?", out.stdout)


class BlogListInvalidPageNoindexTests(unittest.TestCase):
    def test_noindex_signals_include_x_robots_header_in_source(self):
        text = LIST_DATA.read_text(encoding="utf-8")
        self.assertIn("X-Robots-Tag: noindex, follow", text)
        self.assertIn("headers_sent()", text)
        self.assertIn("lireate_blog_list_apply_noindex_signals", text)

    def test_noindex_x_robots_header_via_php_builtin_server(self):
        if not shutil.which("php"):
            self.skipTest("php-cli not available")
        import socket
        import urllib.request

        tmp = Path(tempfile.mkdtemp(prefix="lireate-blog-xrobots-"))
        try:
            data = str(LIST_DATA).replace("\\", "\\\\").replace("'", "\\'")
            (tmp / "index.php").write_text(
                (
                    "<?php\n"
                    "$_GET = array('page' => '0');\n"
                    "require '{data}';\n"
                    "lireate_blog_list_mark_invalid_page_noindex(5);\n"
                    "echo 'ok';\n"
                ).format(data=data),
                encoding="utf-8",
            )
            sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
            sock.bind(("127.0.0.1", 0))
            port = sock.getsockname()[1]
            sock.close()
            proc = subprocess.Popen(
                ["php", "-S", "127.0.0.1:{0}".format(port), "-t", str(tmp)],
                stdout=subprocess.DEVNULL,
                stderr=subprocess.DEVNULL,
            )
            try:
                time.sleep(0.3)
                req = urllib.request.Request(
                    "http://127.0.0.1:{0}/index.php".format(port),
                    method="GET",
                )
                with urllib.request.urlopen(req, timeout=10) as resp:
                    header = resp.headers.get("X-Robots-Tag", "")
                self.assertIn("noindex", header.lower())
            finally:
                proc.terminate()
                proc.wait(timeout=5)
        finally:
            import shutil as _sh

            _sh.rmtree(tmp, ignore_errors=True)

    def test_noindex_page_zero_abc_overflow(self):
        php = (
            "<?php\n"
            "require '{data}';\n"
            "function reset_noindex() {{ unset($GLOBALS['lireate_robots_noindex']); }}\n"
            "function check($get, $total) {{\n"
            "  $_GET = $get;\n"
            "  reset_noindex();\n"
            "  lireate_blog_list_mark_invalid_page_noindex($total);\n"
            "  return !empty($GLOBALS['lireate_robots_noindex']);\n"
            "}}\n"
            "echo check(array('page' => '0'), 5) ? '1' : '0', \"\\n\";\n"
            "echo check(array('page' => 'abc'), 5) ? '1' : '0', \"\\n\";\n"
            "echo check(array('page' => '999'), 3) ? '1' : '0', \"\\n\";\n"
            "echo check(array('page' => '2'), 5) ? '1' : '0', \"\\n\";\n"
            "echo check(array(), 5) ? '1' : '0', \"\\n\";\n"
        ).format(data=LIST_DATA)
        out = _run_php(php)
        self.assertEqual(out.returncode, 0, msg=out.stderr)
        lines = out.stdout.strip().splitlines()
        self.assertEqual(lines, ["1", "1", "1", "0", "0"])

    def test_list_inc_h1_page_two(self):
        php = (
            "<?php\n"
            "$_GET = array('page' => '2');\n"
            "$lireate_blog_list_items = array();\n"
            "$lireate_blog_list_featured = null;\n"
            "$lireate_blog_list_filters = array();\n"
            "$lireate_blog_list_pagination = array('current' => 2, 'total' => 5);\n"
            "$GLOBALS['lireate_blog_list_pagination'] = $lireate_blog_list_pagination;\n"
            "$GLOBALS['lireate_blog_list_total_pages'] = 5;\n"
            "ob_start();\n"
            "include '{inc}';\n"
            "$html = ob_get_clean();\n"
            "echo (strpos($html, 'Блог Лирейта — страница 2') !== false ? '1' : '0'), \"\\n\";\n"
            "echo (strpos($html, 'lireate-blog-list-h1-sub') !== false ? '1' : '0'), \"\\n\";\n"
        ).format(inc=REPO / "pages/blog/_shared/lireate-blog-list.inc.php")
        out = _run_php(php)
        self.assertEqual(out.returncode, 0, msg=out.stderr)
        lines = out.stdout.strip().splitlines()
        self.assertEqual(lines[0], "1")
        self.assertEqual(lines[1], "0")


if __name__ == "__main__":
    raise SystemExit(unittest.main())