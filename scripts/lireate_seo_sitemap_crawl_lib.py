#!/usr/bin/env python3
"""Sitemap crawl + HTML SEO extraction (stdlib only). Used by lireate_seo_sitemap_crawl.py."""
from __future__ import print_function

import json
import re
import time
import xml.etree.ElementTree as ET
from html.parser import HTMLParser
from urllib.error import HTTPError, URLError
from urllib.parse import urljoin, urlparse, urlunparse
from urllib.request import HTTPRedirectHandler, Request, build_opener

REPO_ROOT = __import__('pathlib').Path(__file__).resolve().parents[1]

V7_MARKER_SUBSTR = 'lireate-sitewide-sidebar-v7'
PERF_MARKER = 'seo-perf-20261127-v2'
USER_AGENT = 'LIREATE-SEO-Sitemap-Crawl/1.0 (+https://lireate.ru)'

# Cluster lookup: shared symlink or data copy (see shared/lireate-seo-audit-meta.json).
AUDIT_META_CANDIDATES = (
    REPO_ROOT / 'shared' / 'lireate-seo-audit-meta.json',
    REPO_ROOT / 'data' / 'lireate-seo-audit-meta.json',
)
PAGE_META_PHP = REPO_ROOT / 'data' / 'lireate-seo-page-meta.inc.php'

SITEMAP_NS = {'sm': 'http://www.sitemaps.org/schemas/sitemap/0.9'}

FATAL_ERROR_PATTERNS = (
    re.compile(r'\bPHP\s+Fatal\s+error\b', re.I),
    re.compile(r'\bParse\s+error\b.*\bin\b', re.I),
    re.compile(r'__rcpc', re.I),
    re.compile(r'\bInternal\s+Server\s+Error\b', re.I),
)

CSV_FIELDNAMES = (
    'phase',
    'requested_url',
    'final_url',
    'status',
    'redirect_chain',
    'cluster',
    'title',
    'meta_description',
    'meta_keywords',
    'robots_meta',
    'canonical',
    'h1_count',
    'h1_first',
    'og_title',
    'og_description',
    'jsonld_types',
    'breadcrumb_jsonld',
    'v7_marker',
    'perf_marker',
    'fatal_error',
    'discover_extra',
    'problems',
)

_RETRY_STATUSES = frozenset(range(500, 600)) | frozenset((429,))


class _RecordingRedirectHandler(HTTPRedirectHandler):
    def __init__(self):
        HTTPRedirectHandler.__init__(self)
        self.chain = []

    def redirect_request(self, req, fp, code, msg, headers, newurl):
        self.chain.append(
            {'from': req.full_url, 'to': newurl, 'status': int(code)}
        )
        return HTTPRedirectHandler.redirect_request(
            self, req, fp, code, msg, headers, newurl
        )


class _SeoHTMLParser(HTMLParser):
    def __init__(self):
        HTMLParser.__init__(self)
        self._in_title = False
        self._in_h1 = False
        self._h1_buf = []
        self.title = ''
        self.h1_texts = []
        self.meta = {}
        self.link_canonical = ''
        self.ld_json_chunks = []

    def handle_starttag(self, tag, attrs):
        attr = {k.lower(): (v or '') for k, v in attrs}
        tag = tag.lower()
        if tag == 'title':
            self._in_title = True
        elif tag == 'h1':
            self._in_h1 = True
            self._h1_buf = []
        elif tag == 'meta':
            name = attr.get('name', '').lower()
            prop = attr.get('property', '').lower()
            content = attr.get('content', '')
            if name == 'description':
                self.meta['description'] = content
            elif name == 'keywords':
                self.meta['keywords'] = content
            elif name == 'robots':
                self.meta['robots'] = content
            elif prop == 'og:title':
                self.meta['og:title'] = content
            elif prop == 'og:description':
                self.meta['og:description'] = content
        elif tag == 'link' and attr.get('rel', '').lower() == 'canonical':
            href = attr.get('href', '')
            if href:
                self.link_canonical = href
        elif tag == 'script':
            typ = attr.get('type', '').lower()
            if typ == 'application/ld+json':
                self.ld_json_chunks.append('')

    def handle_endtag(self, tag):
        tag = tag.lower()
        if tag == 'title':
            self._in_title = False
        elif tag == 'h1':
            self._in_h1 = False
            text = re.sub(r'\s+', ' ', ''.join(self._h1_buf)).strip()
            if text:
                self.h1_texts.append(text)
        elif tag == 'script' and self.ld_json_chunks and self.ld_json_chunks[-1] == '':
            pass

    def handle_data(self, data):
        if self._in_title:
            self.title += data
        if self._in_h1:
            self._h1_buf.append(data)
        if self.ld_json_chunks and self.ld_json_chunks[-1] == '':
            self.ld_json_chunks[-1] += data

    def handle_entityref(self, name):
        self.handle_data('&{0};'.format(name))

    def handle_charref(self, name):
        self.handle_data('&#{0};'.format(name))


class RateLimiter(object):
    def __init__(self, requests_per_second=2.0):
        self._min_interval = 1.0 / float(requests_per_second)
        self._last = 0.0

    def wait(self):
        now = time.time()
        delta = now - self._last
        if delta < self._min_interval:
            time.sleep(self._min_interval - delta)
        self._last = time.time()


def normalize_url(url, base=None):
    url = (url or '').strip()
    if not url:
        return ''
    if base:
        url = urljoin(base, url)
    parsed = urlparse(url)
    path = parsed.path or '/'
    if not path.endswith('/') and '.' not in path.rsplit('/', 1)[-1]:
        path = path + '/'
    return urlunparse((parsed.scheme, parsed.netloc, path, '', parsed.query, ''))


def _local_tag(elem):
    if elem.tag.startswith('{'):
        return elem.tag.split('}', 1)[1]
    return elem.tag


def parse_sitemap_xml(xml_bytes, base_url, fetch_sitemap=None, _seen=None):
    """Return unique absolute URLs from sitemap or recursive sitemap index."""
    if _seen is None:
        _seen = set()
    urls = []
    try:
        root = ET.fromstring(xml_bytes)
    except ET.ParseError:
        return urls
    root_tag = _local_tag(root)
    if root_tag == 'sitemapindex':
        for sm in root.findall('.//{http://www.sitemaps.org/schemas/sitemap/0.9}sitemap'):
            loc = sm.find('{http://www.sitemaps.org/schemas/sitemap/0.9}loc')
            if loc is None or not (loc.text or '').strip():
                continue
            child_url = normalize_url(loc.text.strip(), base_url)
            if child_url in _seen:
                continue
            _seen.add(child_url)
            if fetch_sitemap is None:
                continue
            child_xml = fetch_sitemap(child_url)
            if child_xml:
                urls.extend(parse_sitemap_xml(child_xml, base_url, fetch_sitemap, _seen))
    elif root_tag == 'urlset':
        for u in root.findall('.//{http://www.sitemaps.org/schemas/sitemap/0.9}url'):
            loc = u.find('{http://www.sitemaps.org/schemas/sitemap/0.9}loc')
            if loc is not None and (loc.text or '').strip():
                urls.append(normalize_url(loc.text.strip(), base_url))
    else:
        for loc in root.iter():
            if _local_tag(loc) == 'loc' and (loc.text or '').strip():
                urls.append(normalize_url(loc.text.strip(), base_url))
    deduped = []
    seen = set()
    for u in urls:
        if u and u not in seen:
            seen.add(u)
            deduped.append(u)
    return deduped


def load_audit_meta_json():
    for path in AUDIT_META_CANDIDATES:
        if path.is_file():
            try:
                return json.loads(path.read_text(encoding='utf-8'))
            except (ValueError, OSError):
                return {}
    return {}


def load_page_meta_clusters():
    """Parse optional 'cluster' => '…' per slug from data/lireate-seo-page-meta.inc.php."""
    clusters = {}
    if not PAGE_META_PHP.is_file():
        return clusters
    text = PAGE_META_PHP.read_text(encoding='utf-8', errors='replace')
    for m in re.finditer(r"'([^']+)'\s*=>\s*array\s*\((.*?)\)\s*,", text, re.DOTALL):
        slug = m.group(1)
        block = m.group(2)
        cm = re.search(r"'cluster'\s*=>\s*'([^']*)'", block)
        if cm:
            clusters[slug] = cm.group(1)
        clusters.setdefault(slug, '')
    return clusters


def slug_from_url(url, base_netloc):
    parsed = urlparse(url)
    if parsed.netloc and parsed.netloc != base_netloc:
        return ''
    path = (parsed.path or '/').strip('/')
    return path


def resolve_cluster(url, base_url, audit_meta=None, page_clusters=None):
    audit_meta = audit_meta if audit_meta is not None else load_audit_meta_json()
    page_clusters = page_clusters if page_clusters is not None else load_page_meta_clusters()
    base_netloc = urlparse(base_url).netloc
    path_slug = slug_from_url(url, base_netloc)
    if not path_slug:
        if urlparse(url).path in ('', '/'):
            return 'home'
        return 'external'
    if path_slug in page_clusters and page_clusters[path_slug]:
        return page_clusters[path_slug]
    leaf = path_slug.split('/')[-1]
    if path_slug in page_clusters:
        return 'static_page_meta'
    news = audit_meta.get('news') or {}
    pages = audit_meta.get('pages') or {}
    if leaf in news or path_slug in news:
        return 'news'
    if leaf in pages or path_slug in pages:
        return 'pages'
    first = path_slug.split('/')[0]
    if first == 'news':
        return 'blog_article' if '/' in path_slug else 'blog'
    if first == 'portfolio':
        return 'portfolio'
    if first in ('findtz', 'uslugi'):
        return 'services_hub'
    if first == 'contacts' or path_slug == 'contacts':
        return 'contacts'
    if first == 'team':
        return 'team'
    if first == 'patent' or path_slug.startswith('patent'):
        return 'patent'
    return 'other'


def _collect_jsonld_types(node, out):
    if isinstance(node, dict):
        t = node.get('@type')
        if t:
            if isinstance(t, list):
                out.update(str(x) for x in t)
            else:
                out.add(str(t))
        graph = node.get('@graph')
        if isinstance(graph, list):
            for item in graph:
                _collect_jsonld_types(item, out)
        for v in node.values():
            _collect_jsonld_types(v, out)
    elif isinstance(node, list):
        for item in node:
            _collect_jsonld_types(item, out)


def extract_jsonld_types(html_text):
    types = set()
    for chunk in re.findall(
        r'<script[^>]+type=["\']application/ld\+json["\'][^>]*>(.*?)</script>',
        html_text,
        re.I | re.DOTALL,
    ):
        chunk = chunk.strip()
        if not chunk:
            continue
        try:
            data = json.loads(chunk)
        except ValueError:
            continue
        _collect_jsonld_types(data, types)
    return sorted(types)


def has_breadcrumb_jsonld(html_text, types=None):
    if types is None:
        types = extract_jsonld_types(html_text)
    for t in types:
        if t.lower() == 'breadcrumblist':
            return True
    if re.search(r'"@type"\s*:\s*"BreadcrumbList"', html_text, re.I):
        return True
    return False


def detect_fatal_errors(html_text, status):
    if status >= 500:
        return 'http_{0}'.format(status)
    if status == 403:
        return 'http_403'
    low = (html_text or '')[:8000].lower()
    if '__rcpc' in low:
        return 'hosting_rcpc'
    for pat in FATAL_ERROR_PATTERNS:
        if pat.search(html_text or ''):
            return pat.pattern[:40]
    if status == 200 and '<h1' not in low and '<body' in low:
        if 'fatal' in low or 'error' in low[:2000]:
            return 'suspect_error_body'
    return ''


def parse_html_seo(html_text):
    parser = _SeoHTMLParser()
    try:
        parser.feed(html_text or '')
        parser.close()
    except Exception:
        pass
    title = re.sub(r'\s+', ' ', parser.title).strip()
    if not title:
        m = re.search(r'<title[^>]*>(.*?)</title>', html_text or '', re.I | re.DOTALL)
        if m:
            title = re.sub(r'\s+', ' ', re.sub(r'<[^>]+>', '', m.group(1))).strip()
    h1_first = parser.h1_texts[0] if parser.h1_texts else ''
    jsonld_types = extract_jsonld_types(html_text or '')
    return {
        'title': title,
        'meta_description': parser.meta.get('description', ''),
        'meta_keywords': parser.meta.get('keywords', ''),
        'robots_meta': parser.meta.get('robots', ''),
        'canonical': parser.link_canonical,
        'h1_count': len(parser.h1_texts),
        'h1_first': h1_first,
        'og_title': parser.meta.get('og:title', ''),
        'og_description': parser.meta.get('og:description', ''),
        'jsonld_types': '|'.join(jsonld_types),
        'breadcrumb_jsonld': 'yes' if has_breadcrumb_jsonld(html_text or '', jsonld_types) else 'no',
        'v7_marker': 'yes' if V7_MARKER_SUBSTR in (html_text or '') else 'no',
        'perf_marker': 'yes' if PERF_MARKER in (html_text or '') else 'no',
        'fatal_error': detect_fatal_errors(html_text or '', 200),
    }


def extract_internal_links(html_text, page_url, base_netloc, limit=200):
    found = []
    seen = set()
    for href in re.findall(r'<a\b[^>]*\bhref=["\']([^"\']+)["\']', html_text or '', re.I):
        href = href.strip()
        if not href or href.startswith(('#', 'mailto:', 'tel:', 'javascript:')):
            continue
        abs_url = normalize_url(urljoin(page_url, href))
        p = urlparse(abs_url)
        if p.scheme not in ('http', 'https'):
            continue
        if p.netloc != base_netloc:
            continue
        if abs_url not in seen:
            seen.add(abs_url)
            found.append(abs_url)
        if len(found) >= limit:
            break
    return found


def _idn_request_url(url):
    try:
        url.encode('ascii')
        return url
    except UnicodeEncodeError:
        from urllib.parse import quote

        parts = urlparse(url)
        path = quote(parts.path or '/', safe="/:@%+-._~")
        q = parts.query or ''
        query = quote(q, safe="=&?/%+:@") if q else ''
        return urlunparse((parts.scheme, parts.netloc, path, parts.params, query, parts.fragment))


def fetch_url(
    url,
    rate_limiter=None,
    timeout=30,
    max_retries=4,
    sleep_fn=time.sleep,
):
    """Fetch URL; return dict with body bytes, status, final_url, redirect_chain."""
    if rate_limiter:
        rate_limiter.wait()
    req_url = _idn_request_url(url)
    last_err = ''
    for attempt in range(max_retries):
        handler = _RecordingRedirectHandler()
        opener = build_opener(handler)
        req = Request(req_url, headers={'User-Agent': USER_AGENT})
        try:
            resp = opener.open(req, timeout=timeout)
            body = resp.read()
            status = getattr(resp, 'status', None) or resp.getcode()
            final = resp.geturl() or url
            chain = list(handler.chain)
            if status in _RETRY_STATUSES and attempt + 1 < max_retries:
                sleep_fn(min(8.0, 0.5 * (2 ** attempt)))
                continue
            return {
                'ok': True,
                'status': int(status),
                'final_url': final,
                'redirect_chain': chain,
                'body': body,
                'error': '',
            }
        except HTTPError as e:
            status = e.code
            body = e.read() if hasattr(e, 'read') else b''
            chain = list(handler.chain)
            if status in _RETRY_STATUSES and attempt + 1 < max_retries:
                sleep_fn(min(8.0, 0.5 * (2 ** attempt)))
                last_err = 'HTTP {0}'.format(status)
                continue
            return {
                'ok': False,
                'status': int(status),
                'final_url': e.geturl() if hasattr(e, 'geturl') else url,
                'redirect_chain': chain,
                'body': body,
                'error': 'HTTP {0}'.format(status),
            }
        except URLError as e:
            last_err = str(e.reason) if hasattr(e, 'reason') else str(e)
            if attempt + 1 < max_retries:
                sleep_fn(min(8.0, 0.5 * (2 ** attempt)))
                continue
            return {
                'ok': False,
                'status': 0,
                'final_url': url,
                'redirect_chain': list(handler.chain),
                'body': b'',
                'error': last_err,
            }
    return {
        'ok': False,
        'status': 0,
        'final_url': url,
        'redirect_chain': [],
        'body': b'',
        'error': last_err or 'max_retries',
    }


def crawl_url_record(
    requested_url,
    phase,
    base_url,
    rate_limiter=None,
    discover_extra=False,
    fetch_fn=None,
    audit_meta=None,
    page_clusters=None,
):
    fetch_fn = fetch_fn or (lambda u: fetch_url(u, rate_limiter=rate_limiter))
    fetched = fetch_fn(requested_url)
    charset = 'utf-8'
    body = fetched.get('body') or b''
    try:
        html_text = body.decode(charset, errors='replace')
    except Exception:
        html_text = body.decode('utf-8', errors='replace')
    seo = parse_html_seo(html_text)
    status = int(fetched.get('status') or 0)
    seo['fatal_error'] = detect_fatal_errors(html_text, status) or seo.get('fatal_error', '')
    chain = fetched.get('redirect_chain') or []
    chain_str = ' -> '.join(
        '{0}({1})'.format(c.get('to', ''), c.get('status', '')) for c in chain
    )
    row = {
        'phase': phase,
        'requested_url': requested_url,
        'final_url': fetched.get('final_url') or requested_url,
        'status': status,
        'redirect_chain': chain_str,
        'cluster': resolve_cluster(
            fetched.get('final_url') or requested_url,
            base_url,
            audit_meta=audit_meta,
            page_clusters=page_clusters,
        ),
        'discover_extra': 'no',
    }
    row.update(seo)
    if not row['fatal_error'] and fetched.get('error'):
        row['fatal_error'] = fetched['error']
    try:
        from lireate_seo_audit_issues import issues_for_row

        row['problems'] = issues_for_row(row)
    except Exception:
        row['problems'] = ''
    extra_links = []
    if discover_extra and status == 200 and html_text:
        base_netloc = urlparse(base_url).netloc
        extra_links = extract_internal_links(
            html_text, row['final_url'], base_netloc, limit=200
        )
    return row, extra_links


def format_redirect_chain(chain):
    if not chain:
        return ''
    return ' -> '.join(
        '{0}({1})'.format(c.get('to', ''), c.get('status', '')) for c in chain
    )