#!/usr/bin/env python3
"""Sitewide sidebar V7 installer regression (offline; no live writes)."""
import io
import pathlib
import re
import sys
import tempfile
import unittest
from contextlib import redirect_stdout

sys.path.insert(0, str(pathlib.Path(__file__).resolve().parent))
import lireate_sitewide_sidebar_v7_install as m


class SidebarV7InstallTests(unittest.TestCase):
    def setUp(self):
        self.tmp = tempfile.TemporaryDirectory()
        self.docroot = pathlib.Path(self.tmp.name) / "public_html"
        self.docroot.mkdir()
        self.backup = pathlib.Path(self.tmp.name) / "backup"
        self.main = self.docroot / m.MAIN
        self.main.parent.mkdir(parents=True)
        self.original_main = (
            b'<?php /* main */ ?><!doctype html><html><head>'
            b'<title>SEO preserved</title></head><body>'
            b'<form action="/mail.php"></form></body></html>'
        )
        self.main.write_bytes(self.original_main)
        self.sample = self.docroot / "findtz/index.php"
        self.sample.parent.mkdir(parents=True)
        self.sample_content = (
            b'<!DOCTYPE html><html><head><title>Find trademark</title>'
            b'<meta name="description" content="Original SEO"></head><body>'
            b'<form id="original"><input name="email"></form></body></html>'
        )
        self.sample.write_bytes(self.sample_content)
        self.secret = self.docroot / "admin/index.php"
        self.secret.parent.mkdir(parents=True)
        self.secret.write_bytes(self.sample_content)
        self._seed_forbidden_landings()

    def _seed_forbidden_landings(self):
        for landing in (
            "everest/pages/offer/index.php",
            "reg-tov-znak/pages/landing/index.php",
            "reg-tov-znak1(test)/pages/x/index.php",
            "lireate-analytics/dashboard/index.php",
        ):
            path = self.docroot / landing
            path.parent.mkdir(parents=True, exist_ok=True)
            path.write_bytes(self.sample_content)
        stub = self.docroot / "uslugi/index.php"
        stub.parent.mkdir(parents=True, exist_ok=True)
        stub.write_bytes(b"<?php /* stub */ ?><html><head></head><body>x</body></html>")

    def tearDown(self):
        self.tmp.cleanup()

    def test_public_manifest_routes_cover_known_standalones(self):
        routes = m.whitelist_standalone_paths()
        self.assertIn(pathlib.Path("findtz/index.php"), routes)
        self.assertIn(pathlib.Path("patentnye-zadachi/index.php"), routes)
        self.assertIn(pathlib.Path("patentnye-zadachi/poshliny-prodlenie/index.php"), routes)
        self.assertIn(pathlib.Path("registraciya_tovarnogo_znaka/index.php"), routes)
        self.assertIn(pathlib.Path("dogovory/index.php"), routes)
        self.assertIn(pathlib.Path("registraciya_programm_evm/index.php"), routes)
        self.assertNotIn(pathlib.Path("uslugi/SLUG-ЗАМЕНИТЬ/index.php"), routes)
        self.assertGreaterEqual(len(routes), 20)

    def test_dry_run_no_data_changes_and_no_admin(self):
        changes, before, notes = m.plan(self.docroot)
        self.assertIn(m.MAIN, changes)
        self.assertIn(pathlib.Path("findtz/index.php"), changes)
        self.assertTrue(all(asset in changes for asset in m.ASSETS))
        self.assertNotIn(pathlib.Path("admin/index.php"), changes)
        self.assertNotIn(pathlib.Path("everest/pages/offer/index.php"), changes)
        self.assertNotIn(pathlib.Path("reg-tov-znak/pages/landing/index.php"), changes)
        self.assertNotIn(pathlib.Path("uslugi/index.php"), changes)
        self.assertEqual(self.main.read_bytes(), self.original_main)
        self.assertEqual(self.sample.read_bytes(), self.sample_content)
        self.assertIn(b"<title>SEO preserved</title>", changes[m.MAIN])
        self.assertIn(b"<form id=\"original\">", changes[pathlib.Path("findtz/index.php")])
        self.assertEqual(changes[pathlib.Path("findtz/index.php")].count(m.VERSION.encode()), 1)

    def _php_stub_bin(self):
        stub = pathlib.Path(self.tmp.name) / "php-stub.sh"
        stub.write_text("#!/bin/sh\nif [ \"$1\" = \"-l\" ]; then\n  exit 0\nfi\nexit 0\n", encoding="utf-8")
        stub.chmod(0o755)
        return str(stub)

    def test_run_apply_idempotent_rollback_cli(self):
        main_with_inserts = (
            b"<?php /* cms */ ?><!doctype html><html><head><title>CMS</title>\n"
            b"<!-- lireate-qa-v6-20261126 -->\n"
            b'<link rel="stylesheet" href="/template/css/lireate-qa-v6.css">\n'
            b"<!-- lireate-perf-cron-20261126 -->\n"
            b'<link rel="stylesheet" href="/template/css/lireate-perf-cron.css">\n'
            b"</head><body><form action=\"/mail.php\"></form></body></html>"
        )
        self.main.write_bytes(main_with_inserts)
        service = self.docroot / "registraciya_tovarnogo_znaka/index.php"
        service.parent.mkdir(parents=True, exist_ok=True)
        service_bytes = (
            b"<!DOCTYPE html><html><head><title>Reg TZ</title>"
            b"<!-- lireate-qa-v6-20261126 -->\n"
            b'<link rel="stylesheet" href="/template/css/lireate-qa-v6.css">\n'
            b"</head><body><form id=\"svc-form\"></form></body></html>"
        )
        service.write_bytes(service_bytes)
        patent = self.docroot / "patentnye-zadachi/poshliny-prodlenie/index.php"
        patent.parent.mkdir(parents=True, exist_ok=True)
        patent_bytes = (
            b"<!DOCTYPE html><html><head><title>Patent tool</title>"
            b"<!-- lireate-qa-v6-20261126 -->\n"
            b'<link rel="stylesheet" href="/template/css/lireate-qa-v6.css">\n'
            b"</head><body>"
            b'<div id="leftSideBar" class="lxq-sidebar"><button class="lxq-mobile-nav-toggle"></button></div>'
            b"<form id=\"patent-form\"></form></body></html>"
        )
        patent.write_bytes(patent_bytes)
        originals = {
            m.MAIN: main_with_inserts,
            pathlib.Path("registraciya_tovarnogo_znaka/index.php"): service_bytes,
            pathlib.Path("patentnye-zadachi/poshliny-prodlenie/index.php"): patent_bytes,
            pathlib.Path("findtz/index.php"): self.sample_content,
        }
        php_bin = self._php_stub_bin()
        backup_root = pathlib.Path(self.tmp.name) / "v7-backups"
        base_args = [
            "--docroot", str(self.docroot),
            "--backup-root", str(backup_root),
            "--php-bin", php_bin,
        ]

        buf = io.StringIO()
        with redirect_stdout(buf):
            code = m.run(base_args + ["--apply"])
        self.assertEqual(code, 0)
        applied = buf.getvalue()
        backup_match = re.search(r"APPLIED backup=(.+)", applied)
        self.assertIsNotNone(backup_match)
        backup_dir = pathlib.Path(backup_match.group(1).strip())
        self.assertIn(m.VERSION.encode(), self.main.read_bytes())
        self.assertIn(b"lireate-qa-v6-20261126", self.main.read_bytes())
        self.assertIn(b"lireate-perf-cron-20261126", self.main.read_bytes())
        for rel in m.ASSETS:
            self.assertTrue((self.docroot / rel).is_file(), msg=str(rel))

        buf = io.StringIO()
        with redirect_stdout(buf):
            code = m.run(base_args + ["--apply"])
        self.assertEqual(code, 0)
        self.assertIn("NO_CHANGES", buf.getvalue())

        buf = io.StringIO()
        with redirect_stdout(buf):
            code = m.run(["--docroot", str(self.docroot), "--rollback", str(backup_dir)])
        self.assertEqual(code, 0)
        for rel, original in originals.items():
            self.assertEqual((self.docroot / rel).read_bytes(), original, msg=str(rel))
        for rel in m.ASSETS:
            self.assertFalse((self.docroot / rel).exists(), msg=str(rel))

    def test_apply_idempotent_and_rollback_strips_block(self):
        changes, before, _ = m.plan(self.docroot)
        self.backup.mkdir()
        entries = []
        for rel, data in changes.items():
            dest = self.docroot / rel
            present = dest.is_file()
            entries.append({
                "path": str(rel),
                "exists": present,
                "mode": m.entry_mode_for(rel),
            })
            m.atomic(dest, data)
        later_qa = (
            b"\n<!-- lireate-qa-v6-20261126 -->\n"
            b"<link rel=\"stylesheet\" href=\"/template/css/qa-v6.css\">\n"
        )
        self.main.write_bytes(self.main.read_bytes() + later_qa)
        (self.backup / "manifest.json").write_text(
            __import__("json").dumps({"version": m.VERSION, "files": entries}), encoding="utf-8"
        )
        new_changes, _, _ = m.plan(self.docroot)
        self.assertEqual(new_changes, {})
        m.rollback(self.docroot, self.backup)
        rolled = self.main.read_bytes()
        self.assertIn(b"lireate-qa-v6-20261126", rolled)
        self.assertNotIn(m.VERSION.encode(), rolled)
        self.assertEqual(self.sample.read_bytes(), self.sample_content)
        for rel in m.ASSETS:
            self.assertFalse((self.docroot / rel).exists())

    def test_exclusions_and_never_patch_paths(self):
        for item in (
            "everest/pages/a/index.php",
            "reg-tov-znak/pages/a/index.php",
            "reg-tov-znak1(test)/pages/a/index.php",
            "mail_parse/index.php",
            "uslugi/index.php",
            "index.php",
            "mail.php",
        ):
            rel = pathlib.Path(item)
            self.assertFalse(m.is_allowed_patch_target(rel), msg=str(rel))
        routes = m.whitelist_standalone_paths()
        for bad in (
            pathlib.Path("everest/pages/a/index.php"),
            pathlib.Path("reg-tov-znak/pages/a/index.php"),
        ):
            self.assertNotIn(bad, routes)

    def test_no_duplicate_head_injection(self):
        first = m.patch_head(self.sample_content, pathlib.Path("findtz/index.php"))
        again = m.patch_head(first, pathlib.Path("findtz/index.php"))
        self.assertEqual(first, again)
        self.assertEqual(again.count(b"lireate-sitewide-sidebar-v7.js"), 1)

    def test_patch_head_after_qa_v6_block(self):
        with_qa = (
            b"<!DOCTYPE html><html><head><title>x</title>"
            b"\n<!-- lireate-qa-v6-20261126 -->\n"
            b'<link rel="stylesheet" href="/template/css/qa-v6.css">\n'
            b"</head><body></body></html>"
        )
        patched = m.patch_head(with_qa, m.MAIN)
        self.assertIn(b"lireate-qa-v6-20261126", patched)
        self.assertIn(m.VERSION.encode(), patched)
        self.assertLess(patched.index(b"lireate-qa-v6"), patched.index(m.VERSION.encode()))

    def test_strip_v7_preserves_other_head_inserts(self):
        patched = m.patch_head(with_qa := (
            b"<!DOCTYPE html><html><head><title>x</title>"
            b"\n<!-- lireate-qa-v6-20261126 -->\n"
            b'<link rel="stylesheet" href="/template/css/qa-v6.css">\n'
            b"</head><body></body></html>"
        ), m.MAIN)
        stripped = m.strip_v7_head_block(patched)
        self.assertIn(b"lireate-qa-v6-20261126", stripped)
        self.assertNotIn(m.VERSION.encode(), stripped)

    def test_sidebar_fragment_has_socials_and_full_navigation(self):
        html = (m.ROOT / "template/lireate/lireate-sidebar-v7-content.inc.php").read_text(encoding="utf-8")
        self.assertIn('id="leftSideBar"', html)
        self.assertIn('href="/patentnye-zadachi/"', html)
        self.assertIn('href="/news"', html)
        self.assertIn('href="https://vk.com/lireate"', html)
        for name in ("Telegram", "WhatsApp", "Max", "ВКонтакте", "YouTube"):
            self.assertIn('aria-label="' + name + '"', html)
        js = (m.ROOT / "template/js/lireate-sitewide-sidebar-v7.js").read_text(encoding="utf-8")
        self.assertIn("document.getElementById", js)
        self.assertIn("FRAGMENT", js)
        self.assertIn("lsv7-injected", js)


if __name__ == "__main__":
    unittest.main()