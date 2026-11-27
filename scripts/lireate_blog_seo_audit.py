#!/usr/bin/env python3
"""Crawl blog/article URLs from a sitemap and write an SEO audit CSV (stdlib, Py3.6+)."""

from __future__ import print_function

import argparse
import csv
import json
import re
import sys
import xml.etree.ElementTree as ET
from concurrent.futures import ThreadPoolExecutor, as_completed
from html.parser import HTMLParser
from pathlib import Path

try:
    from urllib.error import HTTPError, URLError
    from urllib.parse import urlparse
    from urllib.request import Request, urlopen
except ImportError:
    from urllib2 import Request, urlopen, HTTPError, URLError  # type: ignore
    from urlparse import urlparse  # type: ignore

USER_AGENT = "lireate-blog-seo-audit/1.0"
FETCH_TIMEOUT = 30
CONCURRENCY = 4
VISIBLE_TEXT_LIMIT = 200

_CYRILLIC_LETTER = re.compile(r"^[а-яА-ЯёЁ]$")
_PROPER_ENDING = re.compile(r'[.!?:»"\u201d\u2026…)\]]$')
_LAST_WORD = re.compile(r"[^\s]+$", re.UNICODE)


def _local_tag(tag):
    if "}" in tag:
        return tag.rsplit("}", 1)[-1]
    return tag


def _read_bytes(source):
    """Return (bytes, base_url_for_relative) for a local path or http(s) URL."""
    if re.match(r"^https?://", source, re.I):
        req = Request(source, headers={"User-Agent": USER_AGENT})
        with urlopen(req, timeout=FETCH_TIMEOUT) as resp:
            return resp.read(), source
    path = Path(source)
    if not path.is_file():
        raise IOError("sitemap not found: {0}".format(source))
    return path.read_bytes(), None


def _parse_sitemap_xml(data):
    try:
        root = ET.fromstring(data)
    except ET.ParseError as exc:
        raise ValueError("invalid sitemap XML: {0}".format(exc))
    tag = _local_tag(root.tag)
    if tag == "sitemapindex":
        return "index", root
    if tag == "urlset":
        return "urlset", root
    return "unknown", root


def _locs_from_root(root):
    urls = []
    for elem in root.iter():
        if _local_tag(elem.tag) == "loc" and elem.text:
            urls.append(elem.text.strip())
    return urls


def collect_sitemap_urls(source, _visited=None):
    """Parse sitemap URL or file; expand sitemap index entries."""
    if _visited is None:
        _visited = set()
    if source in _visited:
        return []
    _visited.add(source)

    data, _ = _read_bytes(source)
    kind, root = _parse_sitemap_xml(data)
    locs = _locs_from_root(root)

    if kind == "index":
        all_urls = []
        for child in locs:
            all_urls.extend(collect_sitemap_urls(child, _visited))
        return all_urls
    if kind == "urlset":
        return locs

    # Fallback: treat every <loc> as a page URL
    return locs


class _VisibleTextParser(HTMLParser):
    def __init__(self):
        HTMLParser.__init__(self)
        self._pieces = []
        self._skip_depth = 0
        self._skip_tags = frozenset(
            ("script", "style", "noscript", "template", "svg", "head")
        )

    def handle_starttag(self, tag, attrs):
        t = tag.lower()
        if t in self._skip_tags:
            self._skip_depth += 1

    def handle_endtag(self, tag):
        t = tag.lower()
        if t in self._skip_tags and self._skip_depth:
            self._skip_depth -= 1

    def handle_data(self, data):
        if self._skip_depth:
            return
        chunk = data.strip()
        if chunk:
            self._pieces.append(chunk)

    def get_text(self):
        return re.sub(r"\s+", " ", " ".join(self._pieces)).strip()


class _SeoMetaParser(HTMLParser):
    def __init__(self):
        HTMLParser.__init__(self)
        self.title_parts = []
        self._in_title = False
        self.meta = {}
        self.link_canonical = ""
        self.h1_texts = []
        self._in_h1 = False
        self._h1_buf = []
        self.og = {}
        self._json_ld_chunks = []

    def handle_starttag(self, tag, attrs):
        t = tag.lower()
        ad = {k.lower(): (v or "") for k, v in attrs}

        if t == "title":
            self._in_title = True
        elif t == "meta":
            name = ad.get("name", "").lower()
            prop = ad.get("property", "").lower()
            content = ad.get("content", "")
            if name == "description" and content:
                self.meta["description"] = content
            if prop.startswith("og:") and content:
                self.og[prop] = content
        elif t == "link" and ad.get("rel", "").lower() == "canonical" and ad.get("href"):
            self.link_canonical = ad["href"]
        elif t == "h1":
            self._in_h1 = True
            self._h1_buf = []
        elif t == "script" and ad.get("type", "").lower() == "application/ld+json":
            self._json_ld_chunks.append("")

    def handle_endtag(self, tag):
        t = tag.lower()
        if t == "title":
            self._in_title = False
        elif t == "h1" and self._in_h1:
            self._in_h1 = False
            text = normalize_ws("".join(self._h1_buf))
            if text:
                self.h1_texts.append(text)

    def handle_data(self, data):
        if self._in_title:
            self.title_parts.append(data)
        if self._in_h1:
            self._h1_buf.append(data)
        if self._json_ld_chunks:
            self._json_ld_chunks[-1] += data


def normalize_ws(text):
    return re.sub(r"\s+", " ", (text or "")).strip()


def visible_text(html, limit=VISIBLE_TEXT_LIMIT):
    parser = _VisibleTextParser()
    try:
        parser.feed(html or "")
        parser.close()
    except Exception:
        return ""
    text = parser.get_text()
    if len(text) <= limit:
        return text
    cut = text[:limit]
    if " " in cut:
        cut = cut.rsplit(" ", 1)[0]
    return cut + "…"


def extract_json_ld_types(html):
    types = []
    for m in re.finditer(
        r'<script[^>]+type=["\']application/ld\+json["\'][^>]*>(.*?)</script>',
        html or "",
        re.I | re.S,
    ):
        raw = m.group(1).strip()
        if not raw:
            continue
        try:
            data = json.loads(raw)
        except (ValueError, TypeError):
            continue
        types.extend(_collect_ld_types(data))
    return _dedupe_preserve(types)


def _collect_ld_types(node):
    found = []
    if isinstance(node, dict):
        t = node.get("@type")
        if isinstance(t, list):
            found.extend([str(x) for x in t])
        elif t:
            found.append(str(t))
        graph = node.get("@graph")
        if graph is not None:
            found.extend(_collect_ld_types(graph))
        for v in node.values():
            if isinstance(v, (dict, list)):
                found.extend(_collect_ld_types(v))
    elif isinstance(node, list):
        for item in node:
            found.extend(_collect_ld_types(item))
    return found


def _dedupe_preserve(items):
    seen = set()
    out = []
    for x in items:
        if x not in seen:
            seen.add(x)
            out.append(x)
    return out


def parse_page_seo(html):
    """Extract SEO fields from HTML document string."""
    parser = _SeoMetaParser()
    try:
        parser.feed(html or "")
        parser.close()
    except Exception:
        pass

    title = normalize_ws("".join(parser.title_parts))
    description = parser.meta.get("description", "")
    og_joined = "; ".join(
        "{0}={1}".format(k, parser.og[k]) for k in sorted(parser.og.keys())
    )
    h1_joined = " | ".join(parser.h1_texts)

    return {
        "title": title,
        "meta_description": description,
        "canonical": parser.link_canonical,
        "h1_count": str(len(parser.h1_texts)),
        "h1_text": h1_joined,
        "og_tags": og_joined,
        "json_ld_types": ", ".join(extract_json_ld_types(html)),
        "visible_text_200": visible_text(html, VISIBLE_TEXT_LIMIT),
    }


def has_proper_ending(text):
    s = (text or "").rstrip()
    if not s:
        return False
    return bool(_PROPER_ENDING.search(s))


def ends_mid_word_cyrillic(text):
    """Heuristic: SERP snippet cut inside a Cyrillic word."""
    s = (text or "").strip()
    if len(s) < 2:
        return False
    if has_proper_ending(s):
        return False
    last = s[-1]
    if not _CYRILLIC_LETTER.match(last):
        return False
    m = _LAST_WORD.search(s)
    if not m:
        return False
    last_word = m.group(0)
    if last_word.endswith("-"):
        return True
    # Typical template/CMS cut near 155–160 (description) or ~70 (title)
    if len(s) in range(69, 72) or len(s) in range(130, 161):
        return True
    # Fragment after last space is short and ends on a consonant — likely cut
    vowels = set("аеёиоуыэюяАЕЁИОУЫЭЮЯ")
    if len(last_word) <= 4 and last not in vowels:
        return True
    return False


def title_length_truncation_risk(title):
    n = len((title or "").strip())
    if n not in (69, 70, 71):
        return False
    return not has_proper_ending(title)


def truncation_flags(title, description):
    flags = []
    if ends_mid_word_cyrillic(title) or title_length_truncation_risk(title):
        flags.append("title_truncated")
    if ends_mid_word_cyrillic(description):
        flags.append("description_truncated")
    return flags


def fetch_url(url):
    req = Request(url, headers={"User-Agent": USER_AGENT})
    try:
        with urlopen(req, timeout=FETCH_TIMEOUT) as resp:
            status = getattr(resp, "status", None) or resp.getcode()
            body = resp.read()
            charset = "utf-8"
            ct = resp.headers.get("Content-Type", "")
            m = re.search(r"charset=([^\s;]+)", ct, re.I)
            if m:
                charset = m.group(1).strip('"\'')
            try:
                html = body.decode(charset, errors="replace")
            except LookupError:
                html = body.decode("utf-8", errors="replace")
            return status, html, ""
    except HTTPError as exc:
        return exc.code, "", str(exc)
    except URLError as exc:
        return "", "", str(exc.reason if hasattr(exc, "reason") else exc)
    except Exception as exc:
        return "", "", str(exc)


def audit_url(url):
    status, html, err = fetch_url(url)
    row = {
        "url": url,
        "status": str(status),
        "error": err,
        "title": "",
        "meta_description": "",
        "canonical": "",
        "h1_count": "0",
        "h1_text": "",
        "og_tags": "",
        "json_ld_types": "",
        "visible_text_200": "",
        "truncation_flags": "",
    }
    if html:
        row.update(parse_page_seo(html))
    flags = truncation_flags(row.get("title", ""), row.get("meta_description", ""))
    row["truncation_flags"] = ", ".join(flags)
    return row


CSV_FIELDS = [
    "url",
    "status",
    "error",
    "title",
    "meta_description",
    "canonical",
    "h1_count",
    "h1_text",
    "og_tags",
    "json_ld_types",
    "visible_text_200",
    "truncation_flags",
]


def write_csv(rows, out_path):
    out_path = Path(out_path)
    out_path.parent.mkdir(parents=True, exist_ok=True)
    with out_path.open("w", encoding="utf-8-sig", newline="") as fh:
        writer = csv.DictWriter(fh, fieldnames=CSV_FIELDS, extrasaction="ignore")
        writer.writeheader()
        for row in rows:
            writer.writerow(row)


def main(argv=None):
    parser = argparse.ArgumentParser(description="LIREATE blog SEO audit from sitemap")
    parser.add_argument(
        "sitemap",
        help="sitemap.xml URL or local file path",
    )
    parser.add_argument("--out", required=True, help="output CSV path (UTF-8 BOM)")
    parser.add_argument(
        "--limit",
        type=int,
        default=0,
        help="max URLs to crawl (0 = all)",
    )
    parser.add_argument(
        "--url-list",
        type=Path,
        default=None,
        help="file with one URL per line (instead of sitemap)",
    )
    parser.add_argument(
        "--articles-only",
        action="store_true",
        help="skip known non-article sitemap paths (services, mktu, static)",
    )
    args = parser.parse_args(argv)

    if args.url_list and args.url_list.is_file():
        urls = [
            ln.strip()
            for ln in args.url_list.read_text(encoding="utf-8").splitlines()
            if ln.strip()
        ]
    else:
        try:
            urls = collect_sitemap_urls(args.sitemap)
        except Exception as exc:
            print("ERROR: sitemap: {0}".format(exc), file=sys.stderr)
            return 2
    if args.articles_only:
        skip_tokens = (
            "/news",
            "/mktu",
            "/team",
            "/contacts",
            "/partners",
            "/patent",
            "/registraciya",
            "/dogovor",
            "/pages/",
            "/findtz",
            "/uslugi",
        )
        filtered = []
        for u in urls:
            path = urlparse(u).path if "://" in u else u
            if any(tok in path for tok in skip_tokens):
                continue
            if path in ("/", ""):
                continue
            filtered.append(u)
        urls = filtered

    if not urls:
        print("ERROR: no URLs in sitemap", file=sys.stderr)
        return 2

    if args.limit and args.limit > 0:
        urls = urls[: args.limit]

    rows = []
    with ThreadPoolExecutor(max_workers=CONCURRENCY) as pool:
        futures = {pool.submit(audit_url, u): u for u in urls}
        for fut in as_completed(futures):
            url = futures[fut]
            try:
                rows.append(fut.result())
            except Exception as exc:
                rows.append(
                    {
                        "url": url,
                        "status": "",
                        "error": str(exc),
                        "truncation_flags": "",
                    }
                )

    rows.sort(key=lambda r: r.get("url") or "")
    write_csv(rows, args.out)
    print("urls={0} wrote={1}".format(len(rows), args.out))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())