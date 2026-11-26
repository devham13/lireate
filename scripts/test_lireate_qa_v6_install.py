#!/usr/bin/env python3
"""QA V6 offline smoke tests: guarded installer + sources + route scope."""
import base64
import gzip
import json
from pathlib import Path
import sys
import tempfile
import unittest

sys.path.insert(0, str(Path(__file__).resolve().parent))
import lireate_qa_v6_install as mod

FIXTURE_ROOT = mod.REPO / 'tests' / 'fixtures' / 'lireate_qa_v6_install'
PACKED = FIXTURE_ROOT / 'blobs.b85.json'

FIXTURE_KEYS = {
    'template/page/kontaktyi/lireate-contacts-page.inc.php': 'contacts-page.inc',
    'template/page/partners/lireate-partners-page.inc.php': 'partners-page.inc',
    'template/lireate/lireate-service-footer.inc.php': 'service-footer.inc',
    'template/page/news/lireate-blog-list.inc.php': 'lireate-blog-list.inc',
    'template/page/news/lireate-blog-lib.php': 'lireate-blog-lib.inc',
    'template/page/news/lireate-blog-site-footer.inc.php': 'lireate-blog-site-footer.inc',
    'pages/kontaktyi/_shared/lireate-contacts-page.inc.php': 'contacts-page.inc',
    'pages/partners/_shared/lireate-partners-page.inc.php': 'partners-page.inc',
    'pages/blog/_shared/lireate-blog-list.inc.php': 'shared-blog-list-prod.inc',
    'pages/blog/_shared/lireate-blog-lib.php': 'lireate-blog-lib.inc',
    'pages/blog/_shared/lireate-blog-site-footer.inc.php': 'lireate-blog-site-footer.inc',
}

V7_BEFORE_HEAD = (
    '\n<!-- lireate-sitewide-sidebar-v7-20261126 -->\n'
    '<link rel="stylesheet" href="/template/css/lireate-sitewide-sidebar-v7.css?v=20261126">\n'
    '<script defer src="/template/js/lireate-sitewide-sidebar-v7.js?v=20261126"></script>\n'
)


def packed_manifest():
    return json.loads(PACKED.read_text(encoding='utf-8'))


def packed_bytes(key):
    return gzip.decompress(base64.b85decode(packed_manifest()[key]))


def fixture_bytes(relative):
    pack_key = FIXTURE_KEYS.get(relative)
    if pack_key is None:
        raise FileNotFoundError(relative)
    return packed_bytes(pack_key)


def tool_baseline_bytes(blob_sha):
    return packed_bytes('tool-' + blob_sha)


PROD_PERF_BEFORE_HEAD = (
    '\n<!-- seo-perf-20261120-v1 -->\n'
    '<meta name="lireate-performance-patch" content="seo-perf-20261120-v1">\n'
    '<link rel="stylesheet" href="/template/css/lireate-performance.css?v=seo-perf-20261120-v1">\n'
    '<script defer src="/template/js/lireate-performance.js?v=seo-perf-20261120-v1"></script>\n'
)


def prod_perf_live_bytes(baseline):
    """Byte-exact prod: main + perf block inserted immediately before </head>."""
    text = baseline.decode('utf-8')
    if '</head>' not in text:
        raise ValueError('expected </head>')
    return text.replace('</head>', PROD_PERF_BEFORE_HEAD + '</head>', 1).encode('utf-8')


class QaV6Tests(unittest.TestCase):
    def setUp(self):
        self.tmp = tempfile.TemporaryDirectory()
        self.docroot = Path(self.tmp.name) / 'public_html'
        self.docroot.mkdir()
        self.backup = Path(self.tmp.name) / 'backup'
        (self.docroot / mod.MAIN).parent.mkdir(parents=True)
        (self.docroot / mod.MAIN).write_text(
            '<!doctype html><head><title>SEO intact</title></head><body><form action="/mail.php"></form></body>',
            encoding='utf-8',
        )
        for live in mod.CMS:
            path = self.docroot / live
            path.parent.mkdir(parents=True, exist_ok=True)
            data = fixture_bytes(live)
            path.write_bytes(data)
            allowed = mod.BASE_ALLOWED[live]
            self.assertIn(mod.blob_sha(data), allowed, live)

    def tearDown(self):
        self.tmp.cleanup()

    def test_dry_run_only_expected_paths(self):
        main = (self.docroot / mod.MAIN).read_bytes()
        plan = mod.build_plan(self.docroot)
        self.assertEqual(len(plan), 1 + len(mod.ASSETS) + len(mod.CMS))
        self.assertIn(mod.MAIN, plan)
        self.assertIn(b'<title>SEO intact</title>', plan[mod.MAIN])
        self.assertIn(b'<form action="/mail.php">', plan[mod.MAIN])
        self.assertEqual((self.docroot / mod.MAIN).read_bytes(), main)

    def test_apply_idempotence_and_rollback(self):
        plan = mod.build_plan(self.docroot)
        baseline = {p: (self.docroot / p).read_bytes() for p in [mod.MAIN] + [Path(x) for x in mod.CMS]}
        mod.save_backup(self.docroot, plan, self.backup)
        for rel, data in plan.items():
            mod.write_atomic(self.docroot / rel, data)
        self.assertEqual(mod.build_plan(self.docroot), {})
        mod.rollback(self.docroot, self.backup)
        for rel, data in baseline.items():
            self.assertEqual((self.docroot / rel).read_bytes(), data)
        for rel in mod.ASSETS:
            self.assertFalse((self.docroot / rel).exists())

    def test_live_file_drift_blocker(self):
        live = next(iter(mod.CMS))
        (self.docroot / live).write_text('unexpected Grok edit', encoding='utf-8')
        with self.assertRaisesRegex(ValueError, 'BLOCKER live PHP'):
            mod.build_plan(self.docroot)

    def test_prod_company_v5_blobs_accepted(self):
        for live, prod in mod._CMS_PROD_BLOB.items():
            if live not in mod.CMS:
                continue
            data = fixture_bytes(live)
            self.assertEqual(mod.blob_sha(data), prod, live)
            self.assertTrue(mod.live_matches_allowed(data, Path(live), mod.CMS[live]))

    def test_strip_perf_matches_all_tool_baselines(self):
        for _src, (_live, before_sha) in mod.TOOLS.items():
            baseline = tool_baseline_bytes(before_sha)
            live = prod_perf_live_bytes(baseline)
            stripped = mod.strip_perf(live)
            self.assertEqual(stripped, baseline)
            self.assertEqual(mod.blob_sha(stripped), before_sha)

    def test_strip_v7_for_hash_guard(self):
        source, (live, before) = next(iter(mod.TOOLS.items()))
        baseline = tool_baseline_bytes(before)
        text = baseline.decode('utf-8').replace('</head>', V7_BEFORE_HEAD + '</head>', 1)
        live = text.encode('utf-8')
        stripped = mod.strip_ephemeral(live)
        self.assertEqual(mod.blob_sha(stripped), before)

    def test_main_qa_marker_idempotent(self):
        main = self.docroot / mod.MAIN
        main.write_text(
            '<!doctype html><head><title>x</title>'
            + mod.HEAD
            + '</head><body></body>',
            encoding='utf-8',
        )
        plan = mod.build_plan(self.docroot)
        self.assertNotIn(mod.MAIN, plan)

    def test_with_shared_prod_blobs(self):
        for live in mod.SHARED_CMS:
            path = self.docroot / live
            path.parent.mkdir(parents=True, exist_ok=True)
            data = fixture_bytes(live)
            path.write_bytes(data)
            self.assertTrue(mod.live_matches_allowed(data, Path(live)), live)
        plan = mod.build_plan(self.docroot, with_shared=True)
        self.assertIn(Path('pages/kontaktyi/_shared/lireate-contacts-page.inc.php'), plan)
        footer = Path('registraciya_tovarnogo_znaka/lireate-service-footer.inc.php')
        live_footer = self.docroot / footer
        live_footer.parent.mkdir(parents=True, exist_ok=True)
        live_footer.write_bytes(
            (mod.REPO / 'pages/services/registraciya-tovarnogo-znaka/lireate-service-footer.inc.php').read_bytes()
        )
        plan2 = mod.build_plan(self.docroot, with_shared=True)
        patched = plan2.get(footer, b'')
        self.assertIn(b'href="/docs">', patched)
        self.assertIn('Реквизиты</a>'.encode('utf-8'), patched)
        self.assertNotIn(b'<details class="lx-site-footer-requisites">', patched)

    def test_service_footer_patch_only(self):
        raw = (
            mod.REPO / 'pages/services/registraciya-tovarnogo-znaka/lireate-service-footer.inc.php'
        ).read_bytes()
        out = mod.patch_service_footer_requisites(raw)
        self.assertNotEqual(raw, out)
        again = mod.patch_service_footer_requisites(out)
        self.assertEqual(out, again)

    def test_tool_perf_block_preserved(self):
        source, (live, before) = next(iter(mod.TOOLS.items()))
        for src_path, (live_rel, before_sha) in mod.TOOLS.items():
            tool_base = tool_baseline_bytes(before_sha)
            live_path = self.docroot / live_rel
            live_path.parent.mkdir(parents=True, exist_ok=True)
            if src_path == source:
                live_path.write_bytes(prod_perf_live_bytes(tool_base))
            else:
                live_path.write_bytes(tool_base)
        plan = mod.build_plan(self.docroot, with_tools=True)
        self.assertIn(Path(live), plan)
        out = plan[Path(live)]
        self.assertIn(mod.PERF_MARK.encode('utf-8'), out)
        self.assertIn(b'lireate-performance.css?v=seo-perf-20261120-v1', out)
        self.assertIn(b'lireate-performance.js?v=seo-perf-20261120-v1', out)

    def test_tools_require_existing_published_files(self):
        with self.assertRaisesRegex(ValueError, 'expected published PHP'):
            mod.build_plan(self.docroot, with_tools=True)

    def test_php_templates_keep_forms_and_real_social_links(self):
        for path in (
            'template/page/kontaktyi/lireate-contacts-page.inc.php',
            'pages/kontaktyi/_shared/lireate-contacts-page.inc.php',
        ):
            src = (mod.REPO / path).read_text(encoding='utf-8')
            self.assertIn('lireate-company-pages-v5.css?v=20261126c', src)
            self.assertIn('lireate-qa-v6.css?v=20261126', src)
            self.assertNotIn('lireate-light-v4.css', src)
            self.assertNotIn('lireate-contacts-map', src)
            self.assertIn('lc5-hero-media lc5-hero-media--compact', src)
            self.assertIn('Оставить обращение', src)
            self.assertEqual(src.count('class="lireate-contacts-social-item '), 5)
            for required in (
                't.me/lireate_patent',
                'wa.me/79600484957',
                'max.ru/channel_lireate',
                'vk.ru/lireate',
                'youtube.com/channel/',
            ):
                self.assertIn(required, src.lower())
            self.assertIn('id="lireate-lead-form"', src)
        for path in (
            'template/page/partners/lireate-partners-page.inc.php',
            'pages/partners/_shared/lireate-partners-page.inc.php',
        ):
            src = (mod.REPO / path).read_text(encoding='utf-8')
            self.assertIn('lireate-company-pages-v5.css?v=20261126c', src)
            self.assertIn('lireate-qa-v6.css?v=20261126', src)
            self.assertNotIn('lireate-light-v4.css', src)
            self.assertIn('<details class="lireate-partners-disclosure">', src)
            self.assertIn('<summary class="lireate-partners-summary">', src)
            self.assertIn('lireate-partners-expanded', src)
        for path in (
            'template/page/news/lireate-blog-list.inc.php',
            'pages/blog/_shared/lireate-blog-list.inc.php',
        ):
            src = (mod.REPO / path).read_text(encoding='utf-8')
            self.assertEqual(src.count('class="lireate-blog-list-cover-img"'), 2)

    def test_footer_and_patent_routes(self):
        for path in (
            'template/page/news/lireate-blog-site-footer.inc.php',
            'template/lireate/lireate-service-footer.inc.php',
        ):
            src = (mod.REPO / path).read_text(encoding='utf-8')
            self.assertIn('href="/docs">Реквизиты</a>', src)
            self.assertNotIn('<summary>Реквизиты</summary>', src)
        footer = (mod.REPO / 'template/lireate/lireate-service-footer.inc.php').read_text(
            encoding='utf-8'
        )
        self.assertIn('lireate-messenger-urls.php', footer)
        self.assertIn('max.ru/u/', footer)
        self.assertTrue(footer.endswith('\n'))
        prod_fixture = fixture_bytes('template/lireate/lireate-service-footer.inc.php')
        self.assertEqual(
            mod.blob_sha(prod_fixture),
            'd8b9d8f4d5a698ac10d1a3ef7fc3b58cadb2561d',
        )
        self.assertEqual(len(mod.TOOLS), 13)
        for source, (live, sha) in mod.TOOLS.items():
            src = (mod.REPO / source).read_text(encoding='utf-8')
            self.assertIn('lireate-qa-v6.css', src)
            self.assertIn('lireate-patent-tasks-sidebar-v6.inc.php', src)
            self.assertIn('lxq-patent-tasks', src)
            self.assertTrue(live.startswith('patentnye-zadachi/'))
        script = (mod.REPO / 'template/js/lireate-qa-v6.js').read_text(encoding='utf-8')
        self.assertIn('new URL', script)
        self.assertIn('"/mktu"', script)
        self.assertIn('"/patent_prom_obr"', script)


if __name__ == '__main__':
    unittest.main()