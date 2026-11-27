#!/usr/bin/env python3
"""Unit tests for sitemap crawl lib (no network)."""
import json
import sys
import unittest
from pathlib import Path
from unittest import mock

SCRIPTS = Path(__file__).resolve().parent
sys.path.insert(0, str(SCRIPTS))

import lireate_seo_sitemap_crawl_lib as lib  # noqa: E402
import lireate_seo_sitemap_crawl as crawl_cli  # noqa: E402

SAMPLE_HTML = """<!doctype html>
<html lang="ru"><head>
<meta charset="utf-8">
<title>Тест — Лирейт</title>
<meta name="description" content="Описание страницы">
<meta name="keywords" content="ключ1, ключ2">
<meta name="robots" content="index, follow">
<link rel="canonical" href="https://example.test/contacts/">
<meta property="og:title" content="OG title">
<meta property="og:description" content="OG desc">
<!-- lireate-sitewide-sidebar-v7-20261126 -->
<!-- seo-perf-20261127-v2 -->
<meta name="lireate-performance-patch" content="seo-perf-20261127-v2">
<script type="application/ld+json">
{"@context":"https://schema.org","@graph":[
  {"@type":"Organization","name":"LIREATE"},
  {"@type":"BreadcrumbList","itemListElement":[{"@type":"ListItem","position":1,"name":"Home"}]}
]}
</script>
</head>
<body>
<h1>Контакты</h1>
<h1>Второй h1</h1>
<a href="/news/">Блог</a>
<a href="https://other.test/x">ext</a>
</body></html>"""

SITEMAP_INDEX = b"""<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <sitemap><loc>https://example.test/sitemap-pages.xml</loc></sitemap>
</sitemapindex>"""

SITEMAP_URLSET = b"""<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url><loc>https://example.test/</loc></url>
  <url><loc>https://example.test/contacts/</loc></url>
</urlset>"""


class SitemapCrawlLibTests(unittest.TestCase):
    def test_parse_html_seo(self):
        row = lib.parse_html_seo(SAMPLE_HTML)
        self.assertEqual(row['title'], 'Тест — Лирейт')
        self.assertEqual(row['meta_description'], 'Описание страницы')
        self.assertEqual(row['meta_keywords'], 'ключ1, ключ2')
        self.assertEqual(row['robots_meta'], 'index, follow')
        self.assertEqual(row['canonical'], 'https://example.test/contacts/')
        self.assertEqual(row['h1_count'], 2)
        self.assertEqual(row['h1_first'], 'Контакты')
        self.assertEqual(row['og_title'], 'OG title')
        self.assertEqual(row['og_description'], 'OG desc')
        self.assertIn('Organization', row['jsonld_types'])
        self.assertIn('BreadcrumbList', row['jsonld_types'])
        self.assertEqual(row['breadcrumb_jsonld'], 'yes')
        self.assertEqual(row['v7_marker'], 'yes')
        self.assertEqual(row['perf_marker'], 'yes')
        self.assertEqual(row['fatal_error'], '')

    def test_fatal_rcpc(self):
        row = lib.parse_html_seo('<html><body>__rcpc challenge</body></html>')
        self.assertEqual(row['fatal_error'], 'hosting_rcpc')

    def test_parse_sitemap_index_recursive(self):
        store = {
            'https://example.test/sitemap-pages.xml': SITEMAP_URLSET,
        }

        def fetch_sm(url):
            return store.get(url)

        urls = lib.parse_sitemap_xml(
            SITEMAP_INDEX,
            'https://example.test/',
            fetch_sitemap=fetch_sm,
        )
        self.assertEqual(
            urls,
            [
                'https://example.test/',
                'https://example.test/contacts/',
            ],
        )

    def test_resolve_cluster_audit_and_path(self):
        audit = {
            'news': {'foo_slug': 'desc'},
            'pages': {'patent': 'desc'},
        }
        self.assertEqual(
            lib.resolve_cluster(
                'https://lireate.ru/news/foo_slug/',
                'https://lireate.ru/',
                audit_meta=audit,
                page_clusters={},
            ),
            'news',
        )
        self.assertEqual(
            lib.resolve_cluster(
                'https://lireate.ru/patent/',
                'https://lireate.ru/',
                audit_meta=audit,
                page_clusters={},
            ),
            'pages',
        )
        self.assertEqual(
            lib.resolve_cluster(
                'https://lireate.ru/portfolio/x/',
                'https://lireate.ru/',
                audit_meta=audit,
                page_clusters={},
            ),
            'portfolio',
        )

    def test_page_meta_cluster_override(self):
        with mock.patch.object(
            lib,
            'load_page_meta_clusters',
            return_value={'contacts': 'legal_hub'},
        ):
            c = lib.resolve_cluster(
                'https://lireate.ru/contacts/',
                'https://lireate.ru/',
                audit_meta={},
            )
        self.assertEqual(c, 'legal_hub')

    def test_extract_internal_links(self):
        links = lib.extract_internal_links(
            SAMPLE_HTML,
            'https://example.test/contacts/',
            'example.test',
        )
        self.assertEqual(links, ['https://example.test/news/'])

    def test_crawl_url_record_mocked_fetch(self):
        def fake_fetch(url):
            return {
                'status': 200,
                'final_url': url,
                'redirect_chain': [],
                'body': SAMPLE_HTML.encode('utf-8'),
                'error': '',
            }

        row, extras = lib.crawl_url_record(
            'https://example.test/contacts/',
            'before',
            'https://example.test/',
            fetch_fn=fake_fetch,
            discover_extra=True,
            page_clusters={'contacts': ''},
        )
        self.assertEqual(row['status'], 200)
        self.assertEqual(row['phase'], 'before')
        self.assertEqual(row['cluster'], 'static_page_meta')
        self.assertTrue(extras)

    def test_write_csv_bom(self):
        import tempfile

        with tempfile.TemporaryDirectory() as tmp:
            out = Path(tmp) / 'out.csv'
            crawl_cli.write_csv(
                [
                    {
                        'phase': 'after',
                        'requested_url': 'https://example.test/',
                        'final_url': 'https://example.test/',
                        'status': 200,
                        'redirect_chain': '',
                        'cluster': 'home',
                        'title': 'T',
                        'meta_description': '',
                        'meta_keywords': '',
                        'robots_meta': '',
                        'canonical': '',
                        'h1_count': 1,
                        'h1_first': 'H',
                        'og_title': '',
                        'og_description': '',
                        'jsonld_types': '',
                        'breadcrumb_jsonld': 'no',
                        'v7_marker': 'no',
                        'perf_marker': 'no',
                        'fatal_error': '',
                        'discover_extra': 'no',
                    }
                ],
                out,
            )
            raw = out.read_bytes()
            self.assertTrue(raw.startswith(b'\xef\xbb\xbf'))
            text = out.read_text(encoding='utf-8-sig')
            self.assertIn('phase', text)
            self.assertIn('home', text)

    def test_audit_meta_path_readable(self):
        path = REPO / 'data' / 'lireate-seo-audit-meta.json'
        self.assertTrue(path.is_file(), 'data/lireate-seo-audit-meta.json missing')
        data = json.loads(path.read_text(encoding='utf-8'))
        self.assertIn('pages', data)
        loaded = lib.load_audit_meta_json()
        self.assertIn('pages', loaded)


REPO = Path(__file__).resolve().parents[1]


if __name__ == '__main__':
    raise SystemExit(unittest.main())