#!/usr/bin/env python3
"""Sitewide sidebar V7 installer regression (offline; no live writes)."""
import pathlib
import sys
import tempfile
import unittest

sys.path.insert(0,str(pathlib.Path(__file__).resolve().parent))
import lireate_sitewide_sidebar_v7_install as m


class SidebarV7InstallTests(unittest.TestCase):
    def setUp(self):
        self.tmp=tempfile.TemporaryDirectory()
        self.docroot=pathlib.Path(self.tmp.name)/"public_html"
        self.docroot.mkdir()
        self.backup=pathlib.Path(self.tmp.name)/"backup"
        self.main=self.docroot/m.MAIN
        self.main.parent.mkdir(parents=True)
        self.original_main=b'<?php /* main */ ?><!doctype html><html><head><title>SEO preserved</title></head><body><form action="/mail.php"></form></body></html>'
        self.main.write_bytes(self.original_main)
        self.sample=self.docroot/"findtz/index.php"
        self.sample.parent.mkdir(parents=True)
        self.sample_content=b'<!DOCTYPE html><html><head><title>Find trademark</title><meta name="description" content="Original SEO"></head><body><form id="original"><input name="email"></form></body></html>'
        self.sample.write_bytes(self.sample_content)
        self.secret=self.docroot/"admin/index.php"
        self.secret.parent.mkdir(parents=True)
        self.secret.write_bytes(self.sample_content)

    def tearDown(self):
        self.tmp.cleanup()

    def test_public_manifest_routes_cover_known_standalones(self):
        routes=m.service_route_paths()
        self.assertIn(pathlib.Path("findtz/index.php"),routes)
        self.assertIn(pathlib.Path("patentnye-zadachi/index.php"),routes)
        self.assertIn(pathlib.Path("registraciya_tovarnogo_znaka/index.php"),routes)
        self.assertGreaterEqual(len(routes),20)

    def test_dry_run_no_data_changes_and_no_admin(self):
        changes,before,notes=m.plan(self.docroot)
        self.assertIn(m.MAIN,changes)
        self.assertIn(pathlib.Path("findtz/index.php"),changes)
        self.assertTrue(all(asset in changes for asset in m.ASSETS))
        self.assertNotIn(pathlib.Path("admin/index.php"),changes)
        self.assertEqual(self.main.read_bytes(),self.original_main)
        self.assertEqual(self.sample.read_bytes(),self.sample_content)
        self.assertIn(b'<title>SEO preserved</title>',changes[m.MAIN])
        self.assertIn(b'<form id="original">',changes[pathlib.Path("findtz/index.php")])
        self.assertEqual(changes[pathlib.Path("findtz/index.php")].count(m.VERSION.encode()),1)

    def test_apply_idempotent_and_rollback(self):
        changes,before,_=m.plan(self.docroot)
        self.backup.mkdir()
        entries=[]
        for rel,data in changes.items():
            dest=self.docroot/rel
            present=dest.is_file()
            entries.append({"path":str(rel),"exists":present})
            if present:
                archived=self.backup/rel
                archived.parent.mkdir(parents=True,exist_ok=True)
                archived.write_bytes(dest.read_bytes())
            m.atomic(dest,data)
        (self.backup/"manifest.json").write_text(
            __import__("json").dumps({"version":m.VERSION,"files":entries}),encoding="utf-8")
        new_changes,_,_=m.plan(self.docroot)
        self.assertEqual(new_changes,{})
        m.rollback(self.docroot,self.backup)
        self.assertEqual(self.main.read_bytes(),self.original_main)
        self.assertEqual(self.sample.read_bytes(),self.sample_content)
        for rel in m.ASSETS:self.assertFalse((self.docroot/rel).exists())

    def test_skip_admin_and_other_private_paths(self):
        unsafe=["admin/index.php","bitrix/index.php","api/v1/index.php","upload/index.php",
                "template/random.php","../../etc/passwd"]
        for item in unsafe:self.assertFalse(m.is_safe_rel(pathlib.Path(item)))
        observed=m.discover_public_frontends(self.docroot)
        self.assertNotIn(pathlib.Path("admin/index.php"),observed)
        self.assertIn(pathlib.Path("findtz/index.php"),observed)

    def test_no_duplicate_head_injection(self):
        first=m.patch_head(self.sample_content,pathlib.Path("findtz/index.php"))
        again=m.patch_head(first,pathlib.Path("findtz/index.php"))
        self.assertEqual(first,again)
        self.assertEqual(again.count(b"lireate-sitewide-sidebar-v7.js"),1)

    def test_sidebar_fragment_has_socials_and_full_navigation(self):
        html=(m.ROOT/"template/lireate/lireate-sidebar-v7-content.inc.php").read_text(encoding="utf-8")
        self.assertIn('id="leftSideBar"',html)
        self.assertIn('href="/patentnye-zadachi/"',html)
        self.assertIn('href="/news"',html)
        for name in ("Telegram","WhatsApp","Max","ВКонтакте","YouTube"):
            self.assertIn('aria-label="'+name+'"',html)
        js=(m.ROOT/"template/js/lireate-sitewide-sidebar-v7.js").read_text(encoding="utf-8")
        self.assertIn("document.getElementById",js)
        self.assertIn("FRAGMENT",js)
        self.assertIn("lsv7-injected",js)


if __name__=="__main__":
    unittest.main()
