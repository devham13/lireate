#!/usr/bin/env python3
"""Regression: service publish strips overlays; post_publish restores markers."""
import pathlib
import sys
import tempfile
import unittest

sys.path.insert(0, str(pathlib.Path(__file__).resolve().parent))
import lireate_hosting_service_post_publish as post
import lireate_sitewide_sidebar_v7_install as sidebar_v7
import lireate_hosting_performance_patch as perf_patch

V7 = sidebar_v7.VERSION
PERF = perf_patch.VERSION


class PostPublishOverlayTests(unittest.TestCase):
    def setUp(self):
        self.tmp = tempfile.TemporaryDirectory()
        self.docroot = pathlib.Path(self.tmp.name) / "public_html"
        self.docroot.mkdir()
        self.backup = pathlib.Path(self.tmp.name) / "backups"
        self.office = pathlib.Path(__file__).resolve().parents[1]
        findtz = self.docroot / "findtz" / "index.php"
        findtz.parent.mkdir(parents=True)
        findtz.write_bytes(
            b'<!DOCTYPE html><html><head><title>T</title>'
            b'<link rel="stylesheet" href="/assets/lireate-shared/lireate-service-dark.css">'
            b'</head><body class="lsv7-managed"></body></html>'
        )
        main = self.docroot / "template" / "main.php"
        main.parent.mkdir(parents=True)
        main.write_bytes(b'<?php ?><html><head></head><body></body></html>')

    def tearDown(self):
        self.tmp.cleanup()

    def test_dry_run_adds_markers_in_plan(self):
        code = post.apply_overlays(
            self.docroot, self.backup, apply=False, office_repo=self.office
        )
        self.assertEqual(code, 0)
        text = (self.docroot / "findtz" / "index.php").read_text(encoding="utf-8")
        self.assertNotIn(V7, text)
        plan, _ = perf_patch.build_plan(self.docroot)
        patched = plan[pathlib.Path("findtz/index.php")].decode("utf-8")
        self.assertIn(PERF, patched)
        self.assertIn(V7, sidebar_v7.patch_head(
            (self.docroot / "findtz" / "index.php").read_bytes(),
            pathlib.Path("findtz/index.php"),
        ).decode("utf-8"))


if __name__ == "__main__":
    unittest.main()