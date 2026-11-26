#!/usr/bin/env python3
"""QA V6 offline smoke tests: guarded installer + sources + route scope."""
import json
from pathlib import Path
import subprocess
import sys
import tempfile
import unittest

sys.path.insert(0,str(Path(__file__).resolve().parent))
import lireate_qa_v6_install as mod

COMPANY_V5_COMMIT = '15bf93c9f32c3813172567806755cfd2d63bcc1a'


def git_bytes(commit, relative):
    return subprocess.check_output(
        ['git', 'show', '{0}:{1}'.format(commit, relative)],
        cwd=str(mod.REPO),
    )


def fixture_live_bytes(live):
    if live in mod._CMS_PROD_BLOB and live.startswith('template/page/'):
        return git_bytes(COMPANY_V5_COMMIT, live)
    return git_bytes('origin/main', live)


class QaV6Tests(unittest.TestCase):
    def setUp(self):
        self.tmp=tempfile.TemporaryDirectory()
        self.docroot=Path(self.tmp.name)/'public_html'
        self.docroot.mkdir()
        self.backup=Path(self.tmp.name)/'backup'
        (self.docroot/mod.MAIN).parent.mkdir(parents=True)
        (self.docroot/mod.MAIN).write_text('<!doctype html><head><title>SEO intact</title></head><body><form action="/mail.php"></form></body>',encoding='utf-8')
        for live in mod.CMS:
            path=self.docroot/live
            path.parent.mkdir(parents=True,exist_ok=True)
            data=fixture_live_bytes(live)
            path.write_bytes(data)
            allowed=mod.BASE_ALLOWED[live]
            self.assertIn(mod.blob_sha(data), allowed, live)

    def tearDown(self):
        self.tmp.cleanup()

    def test_dry_run_only_expected_paths(self):
        main=(self.docroot/mod.MAIN).read_bytes()
        plan=mod.build_plan(self.docroot)
        self.assertEqual(len(plan),1+len(mod.ASSETS)+len(mod.CMS))
        self.assertIn(mod.MAIN,plan)
        self.assertIn(b'<title>SEO intact</title>',plan[mod.MAIN])
        self.assertIn(b'<form action="/mail.php">',plan[mod.MAIN])
        self.assertEqual((self.docroot/mod.MAIN).read_bytes(),main)

    def test_apply_idempotence_and_rollback(self):
        plan=mod.build_plan(self.docroot)
        baseline={p:(self.docroot/p).read_bytes() for p in [mod.MAIN]+[Path(x) for x in mod.CMS]}
        mod.save_backup(self.docroot,plan,self.backup)
        for rel,data in plan.items():mod.write_atomic(self.docroot/rel,data)
        self.assertEqual(mod.build_plan(self.docroot),{})
        mod.rollback(self.docroot,self.backup)
        for rel,data in baseline.items():self.assertEqual((self.docroot/rel).read_bytes(),data)
        for rel in mod.ASSETS:self.assertFalse((self.docroot/rel).exists())

    def test_live_file_drift_blocker(self):
        live=next(iter(mod.CMS))
        (self.docroot/live).write_text('unexpected Grok edit',encoding='utf-8')
        with self.assertRaisesRegex(ValueError,'BLOCKER live PHP'):mod.build_plan(self.docroot)

    def test_prod_company_v5_blobs_accepted(self):
        for live, prod in mod._CMS_PROD_BLOB.items():
            if live not in mod.CMS:
                continue
            if live.startswith('template/page/'):
                data=git_bytes(COMPANY_V5_COMMIT, live)
                self.assertEqual(mod.blob_sha(data), prod, live)
            else:
                self.assertIn(prod, mod.BASE_ALLOWED[live])
                continue
            self.assertTrue(mod.live_matches_allowed(data, Path(live), mod.CMS[live]))

    def test_tool_perf_block_preserved(self):
        source, (live, before) = next(iter(mod.TOOLS.items()))
        baseline = subprocess.check_output(['git', 'cat-file', 'blob', before])
        for src_path, (live_rel, before_sha) in mod.TOOLS.items():
            tool_base = subprocess.check_output(['git', 'cat-file', 'blob', before_sha])
            live_path = self.docroot / live_rel
            live_path.parent.mkdir(parents=True, exist_ok=True)
            if src_path == source:
                live_path.write_bytes(mod.merge_perf_into_head(tool_base, mod.PERF_BLOCK))
            else:
                live_path.write_bytes(tool_base)
        plan = mod.build_plan(self.docroot, with_tools=True)
        self.assertIn(Path(live), plan)
        self.assertIn(mod.PERF_MARK.encode('utf-8'), plan[Path(live)])

    def test_tools_require_existing_published_files(self):
        with self.assertRaisesRegex(ValueError,'expected published PHP'):
            mod.build_plan(self.docroot,with_tools=True)

    def test_php_templates_keep_forms_and_real_social_links(self):
        for path in ('template/page/kontaktyi/lireate-contacts-page.inc.php','pages/kontaktyi/_shared/lireate-contacts-page.inc.php'):
            src=(mod.REPO/path).read_text(encoding='utf-8')
            self.assertIn('lireate-company-pages-v5.css?v=20261126c',src)
            self.assertIn('lireate-qa-v6.css?v=20261126',src)
            self.assertNotIn('lireate-light-v4.css',src)
            self.assertNotIn('lireate-contacts-map',src)
            self.assertEqual(src.count('class="lireate-contacts-social-item '),5)
            for required in ('t.me/lireate_patent','wa.me/79600484957','max.ru/channel_lireate','vk.ru/lireate','youtube.com/channel/'):
                self.assertIn(required,src.lower())
            self.assertIn('id="lireate-lead-form"',src)
        for path in ('template/page/partners/lireate-partners-page.inc.php','pages/partners/_shared/lireate-partners-page.inc.php'):
            src=(mod.REPO/path).read_text(encoding='utf-8')
            self.assertIn('lireate-company-pages-v5.css?v=20261126c',src)
            self.assertIn('lireate-qa-v6.css?v=20261126',src)
            self.assertNotIn('lireate-light-v4.css',src)
            self.assertIn('<details class="lireate-partners-disclosure">',src)
            self.assertIn('<summary class="lireate-partners-summary">',src)
            self.assertIn('lireate-partners-expanded',src)
        for path in ('template/page/news/lireate-blog-list.inc.php','pages/blog/_shared/lireate-blog-list.inc.php'):
            src=(mod.REPO/path).read_text(encoding='utf-8')
            self.assertEqual(src.count('class="lireate-blog-list-cover-img"'),2)

    def test_footer_and_patent_routes(self):
        for path in ('template/page/news/lireate-blog-site-footer.inc.php','template/lireate/lireate-service-footer.inc.php'):
            src=(mod.REPO/path).read_text(encoding='utf-8')
            self.assertIn('href="/docs">Реквизиты</a>',src)
            self.assertNotIn('<summary>Реквизиты</summary>',src)
        footer=(mod.REPO/'template/lireate/lireate-service-footer.inc.php').read_text(encoding='utf-8')
        self.assertIn('lireate-messenger-urls.php',footer)
        self.assertIn('max.ru/u/',footer)
        self.assertEqual(len(mod.TOOLS),13)
        for source, (live, sha) in mod.TOOLS.items():
            src=(mod.REPO/source).read_text(encoding='utf-8')
            self.assertIn('lireate-qa-v6.css',src)
            self.assertIn('lireate-patent-tasks-sidebar-v6.inc.php',src)
            self.assertIn('lxq-patent-tasks',src)
            self.assertTrue(live.startswith('patentnye-zadachi/'))
        script=(mod.REPO/'template/js/lireate-qa-v6.js').read_text(encoding='utf-8')
        self.assertIn('new URL',script)
        self.assertIn('"/mktu"',script)
        self.assertIn('"/patent_prom_obr"',script)


if __name__=='__main__':
    unittest.main()
