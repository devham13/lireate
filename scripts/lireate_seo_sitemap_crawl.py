#!/usr/bin/env python3
"""
Crawl sitemap.xml (recursive index), extract on-page SEO signals, write UTF-8 BOM CSV.

Requires LIREATE_PUBLIC_SITE_URL (or --base-url). Cluster column uses
shared/lireate-seo-audit-meta.json (symlink to data/) and optional cluster keys in
data/lireate-seo-page-meta.inc.php — see lireate_seo_sitemap_crawl_lib.AUDIT_META_CANDIDATES.
"""
from __future__ import print_function

import argparse
import csv
import os
import sys
from pathlib import Path

SCRIPTS = Path(__file__).resolve().parent
if str(SCRIPTS) not in sys.path:
    sys.path.insert(0, str(SCRIPTS))

from lireate_seo_sitemap_crawl_lib import (  # noqa: E402
    CSV_FIELDNAMES,
    RateLimiter,
    crawl_url_record,
    fetch_url,
    load_audit_meta_json,
    load_page_meta_clusters,
    normalize_url,
    parse_sitemap_xml,
)

REPO = SCRIPTS.parent


def default_base_url():
    for key in ('LIREATE_PUBLIC_SITE_URL', 'PUBLIC_SITE_URL'):
        val = (os.environ.get(key) or '').strip().rstrip('/')
        if val:
            return val
    return ''


def sitemap_url(base_url):
    return normalize_url('/sitemap.xml', base_url + '/')


def write_csv(rows, output_path):
    out = Path(output_path)
    out.parent.mkdir(parents=True, exist_ok=True)
    with open(str(out), 'w', encoding='utf-8-sig', newline='') as fh:
        writer = csv.DictWriter(fh, fieldnames=CSV_FIELDNAMES, extrasaction='ignore')
        writer.writeheader()
        for row in rows:
            writer.writerow({k: row.get(k, '') for k in CSV_FIELDNAMES})


def run_crawl(base_url, phase, output, max_urls=None, discover_extra=False):
    base_url = base_url.rstrip('/')
    rate = RateLimiter(2.0)
    audit_meta = load_audit_meta_json()
    page_clusters = load_page_meta_clusters()

    def fetch_sitemap(url):
        res = fetch_url(url, rate_limiter=rate)
        if res.get('status') == 200:
            return res.get('body')
        return None

    sm_url = sitemap_url(base_url)
    sm_res = fetch_url(sm_url, rate_limiter=rate)
    if sm_res.get('status') != 200 or not sm_res.get('body'):
        raise SystemExit(
            'BLOCKER: sitemap {0} HTTP {1} ({2})'.format(
                sm_url, sm_res.get('status'), sm_res.get('error')
            )
        )
    urls = parse_sitemap_xml(
        sm_res['body'], base_url + '/', fetch_sitemap=fetch_sitemap
    )
    if not urls:
        urls = [normalize_url('/', base_url + '/')]
    if max_urls is not None:
        urls = urls[: int(max_urls)]

    rows = []
    queued_extra = []
    seen = set(urls)

    for url in urls:
        row, extras = crawl_url_record(
            url,
            phase,
            base_url + '/',
            rate_limiter=rate,
            discover_extra=discover_extra,
            audit_meta=audit_meta,
            page_clusters=page_clusters,
        )
        rows.append(row)
        if discover_extra:
            for link in extras:
                if link not in seen:
                    queued_extra.append(link)

    if discover_extra and queued_extra:
        cap = 50
        for link in queued_extra[:cap]:
            if link in seen:
                continue
            seen.add(link)
            row, _ = crawl_url_record(
                link,
                phase,
                base_url + '/',
                rate_limiter=rate,
                discover_extra=False,
                audit_meta=audit_meta,
                page_clusters=page_clusters,
            )
            row['discover_extra'] = 'yes'
            rows.append(row)

    write_csv(rows, output)
    return len(rows)


def main(argv=None):
    parser = argparse.ArgumentParser(description='LIREATE sitemap SEO crawl (stdlib).')
    parser.add_argument(
        '--base-url',
        default='',
        help='Site origin (default: LIREATE_PUBLIC_SITE_URL env)',
    )
    parser.add_argument('--output', required=True, help='Output CSV path (UTF-8 BOM)')
    parser.add_argument(
        '--phase',
        required=True,
        choices=['before', 'after'],
        help='Audit phase label stored in CSV',
    )
    parser.add_argument(
        '--max-urls',
        type=int,
        default=None,
        help='Limit URLs (tests / sampling)',
    )
    parser.add_argument(
        '--discover-extra',
        action='store_true',
        help='Crawl up to 50 extra same-host links found on pages',
    )
    args = parser.parse_args(argv)
    base = (args.base_url or default_base_url()).strip().rstrip('/')
    if not base:
        raise SystemExit('BLOCKER: set --base-url or LIREATE_PUBLIC_SITE_URL')
    count = run_crawl(
        base,
        args.phase,
        args.output,
        max_urls=args.max_urls,
        discover_extra=args.discover_extra,
    )
    print('Wrote {0} rows -> {1}'.format(count, args.output))
    return 0


if __name__ == '__main__':
    raise SystemExit(main())