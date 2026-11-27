#!/usr/bin/env python3
"""
Comprehensive technical SEO crawl for the public LIREATE site (public HTTP only).

Reads sitemap.xml, optionally discovers in-site links, extracts on-page SEO signals,
and writes URL_INVENTORY.csv + SEO_ERRORS.csv under --output-dir.

Does not use hosting DB, GSC or Yandex Webmaster APIs.
"""
from __future__ import annotations

import argparse
import csv
import json
import os
import re
import sys
import time
from concurrent.futures import ThreadPoolExecutor, as_completed
from dataclasses import asdict, dataclass, field
from html.parser import HTMLParser
from pathlib import Path
from typing import Dict, List, Optional, Set, Tuple
from urllib.error import HTTPError, URLError
from urllib.parse import urljoin, urlparse, urlunparse
from urllib.request import Request, urlopen

DEFAULT_SITE = os.environ.get("SEO_SITE_URL", "").strip()
DEFAULT_UA = "LIREATE-ComprehensiveSEO/1.0 (audit-bot)"
SITEMAP_NS = {"sm": "http://www.sitemaps.org/schemas/sitemap/0.9"}

try:
    from xml.etree import ElementTree as ET
except ImportError:  # pragma: no cover
    ET = None


def normalize_url(url: str, site: str) -> str:
    parsed = urlparse(url.strip())
    if not parsed.scheme:
        parsed = urlparse(urljoin(site.rstrip("/") + "/", url.lstrip("/")))
    path = parsed.path or "/"
    if path != "/" and path.endswith("/"):
        path = path.rstrip("/")
    netloc = parsed.netloc.lower()
    if netloc.startswith("www."):
        netloc = netloc[4:]
    clean = parsed._replace(
        scheme="https",
        netloc=netloc,
        path=path,
        params="",
        query="",
        fragment="",
    )
    return urlunparse(clean)


def same_host(url: str, site: str) -> bool:
    host = urlparse(site).netloc.lower().lstrip("www.")
    uhost = urlparse(url).netloc.lower().lstrip("www.")
    return uhost == host or uhost == ""


class SeoHtmlParser(HTMLParser):
    def __init__(self) -> None:
        super().__init__()
        self.title = ""
        self._in_title = False
        self.meta: Dict[str, str] = {}
        self.links: List[Tuple[str, str]] = []
        self.h1: List[str] = []
        self._in_h1 = False
        self._h1_buf: List[str] = []
        self.canonical = ""
        self.json_ld_count = 0
        self.html_lang = ""

    def handle_starttag(self, tag: str, attrs: List[Tuple[str, Optional[str]]]) -> None:
        attr = {k.lower(): (v or "") for k, v in attrs}
        t = tag.lower()
        if t == "html" and not self.html_lang:
            self.html_lang = attr.get("lang", "")
        if t == "title":
            self._in_title = True
        elif t == "h1":
            self._in_h1 = True
            self._h1_buf = []
        elif t == "meta":
            name = (attr.get("name") or attr.get("property") or "").lower()
            content = attr.get("content", "")
            if name:
                self.meta[name] = content
        elif t == "link":
            rel = attr.get("rel", "").lower()
            href = attr.get("href", "")
            self.links.append((rel, href))
            if rel == "canonical" and href:
                self.canonical = href
        elif t == "script" and attr.get("type", "").lower() == "application/ld+json":
            self.json_ld_count += 1
        elif t == "a" and href:
            self.links.append(("a", href))

    def handle_endtag(self, tag: str) -> None:
        t = tag.lower()
        if t == "title":
            self._in_title = False
        elif t == "h1" and self._in_h1:
            self._in_h1 = False
            text = re.sub(r"\s+", " ", "".join(self._h1_buf)).strip()
            if text:
                self.h1.append(text)

    def handle_data(self, data: str) -> None:
        if self._in_title:
            self.title += data
        if self._in_h1:
            self._h1_buf.append(data)


@dataclass
class PageRecord:
    url: str
    final_url: str = ""
    status: int = 0
    content_type: str = ""
    title: str = ""
    description: str = ""
    robots_meta: str = ""
    canonical: str = ""
    canonical_ok: str = ""
    h1: str = ""
    h1_count: int = 0
    html_lang: str = ""
    json_ld_blocks: int = 0
    og_title: str = ""
    og_image: str = ""
    word_count_est: int = 0
    redirect_chain_len: int = 0
    error: str = ""
    fetch_ms: int = 0
    in_sitemap: bool = True
    x_robots_tag: str = ""


@dataclass
class Finding:
    priority: str
    url: str
    error_type: str
    evidence: str
    status: str = "open"


def fetch_url(url: str, ua: str, max_redirects: int = 8) -> Tuple[bytes, PageRecord, List[str]]:
    chain: List[str] = []
    current = url
    body = b""
    rec = PageRecord(url=url)
    start = time.time()
    for _ in range(max_redirects + 1):
        req = Request(current, headers={"User-Agent": ua, "Accept": "text/html,application/xhtml+xml"})
        try:
            last_err: Optional[Exception] = None
            for attempt in range(4):
                try:
                    resp_ctx = urlopen(req, timeout=45)
                    break
                except (URLError, TimeoutError, OSError) as err:
                    last_err = err
                    time.sleep(1.5 * (attempt + 1))
            else:
                raise last_err  # type: ignore[misc]
            with resp_ctx as resp:
                rec.status = getattr(resp, "status", 200)
                rec.final_url = resp.geturl()
                rec.content_type = resp.headers.get("Content-Type", "").split(";")[0].strip()
                rec.x_robots_tag = resp.headers.get("X-Robots-Tag", "")
                body = resp.read(1_500_000)
                if 300 <= rec.status < 400:
                    loc = resp.headers.get("Location")
                    if loc:
                        chain.append(current)
                        current = urljoin(current, loc)
                        continue
                break
        except HTTPError as e:
            rec.status = e.code
            rec.final_url = current
            rec.error = str(e.reason)
            try:
                body = e.read(200_000)
            except Exception:
                body = b""
            break
        except URLError as e:
            rec.error = str(e.reason)
            break
        except Exception as e:
            rec.error = str(e)
            break
    rec.redirect_chain_len = len(chain)
    rec.fetch_ms = int((time.time() - start) * 1000)
    return body, rec, chain


def parse_html(body: bytes, rec: PageRecord, site: str) -> List[str]:
    discovered: List[str] = []
    try:
        text = body.decode("utf-8", "replace")
    except Exception:
        return discovered
    rec.word_count_est = len(re.sub(r"<[^>]+>", " ", text).split())
    parser = SeoHtmlParser()
    try:
        parser.feed(text)
    except Exception:
        pass
    rec.title = re.sub(r"\s+", " ", parser.title).strip()
    rec.description = parser.meta.get("description", "")
    rec.robots_meta = parser.meta.get("robots", "")
    rec.og_title = parser.meta.get("og:title", "")
    rec.og_image = parser.meta.get("og:image", "")
    rec.canonical = parser.canonical
    rec.html_lang = parser.html_lang
    rec.json_ld_blocks = parser.json_ld_count
    if not parser.h1:
        regex_h1 = re.findall(
            r"<h1\b[^>]*>(.*?)</h1>",
            text,
            flags=re.IGNORECASE | re.DOTALL,
        )
        for fragment in regex_h1:
            plain = re.sub(r"<[^>]+>", " ", fragment)
            plain = re.sub(r"\s+", " ", plain).strip()
            if plain:
                parser.h1.append(plain)
    rec.h1_count = len(parser.h1)
    rec.h1 = parser.h1[0] if parser.h1 else ""
    if "__rcpc" in text.lower() or "доступ ограничен" in text.lower():
        rec.error = (rec.error + " hosting_protection_page").strip()
    if rec.canonical:
        canon_norm = normalize_url(rec.canonical, site)
        final_norm = normalize_url(rec.final_url or rec.url, site)
        rec.canonical_ok = "yes" if canon_norm == final_norm else "no"
    else:
        rec.canonical_ok = "missing"
    for rel, href in parser.links:
        if rel == "a" and href and not href.startswith(("#", "mailto:", "tel:", "javascript:")):
            abs_u = urljoin(rec.final_url or rec.url, href)
            if same_host(abs_u, site):
                discovered.append(normalize_url(abs_u, site))
    return discovered


def load_sitemap_urls(site: str, ua: str) -> List[str]:
    req = Request(f"{site.rstrip('/')}/sitemap.xml", headers={"User-Agent": ua})
    data = b""
    last_err: Optional[Exception] = None
    for attempt in range(5):
        try:
            with urlopen(req, timeout=60) as resp:
                data = resp.read()
            break
        except (URLError, TimeoutError, OSError) as err:
            last_err = err
            time.sleep(2.0 * (attempt + 1))
    else:
        raise URLError(last_err)  # type: ignore[arg-type]
    root = ET.fromstring(data)
    locs: List[str] = []
    for url_el in root.findall("sm:url", SITEMAP_NS):
        loc = url_el.find("sm:loc", SITEMAP_NS)
        if loc is not None and loc.text:
            locs.append(normalize_url(loc.text.strip(), site))
    for sm_el in root.findall("sm:sitemap", SITEMAP_NS):
        loc = sm_el.find("sm:loc", SITEMAP_NS)
        if loc is not None and loc.text:
            sub_req = Request(loc.text.strip(), headers={"User-Agent": ua})
            with urlopen(sub_req, timeout=60) as sub:
                sub_root = ET.fromstring(sub.read())
            for url_el in sub_root.findall("sm:url", SITEMAP_NS):
                loc2 = url_el.find("sm:loc", SITEMAP_NS)
                if loc2 is not None and loc2.text:
                    locs.append(normalize_url(loc2.text.strip(), site))
    return sorted(set(locs))


def analyze_record(rec: PageRecord, site: str) -> List[Finding]:
    out: List[Finding] = []
    u = rec.url

    def add(priority: str, etype: str, evidence: str) -> None:
        out.append(Finding(priority=priority, url=u, error_type=etype, evidence=evidence))

    if rec.error and rec.status == 0:
        add("P0", "fetch_error", rec.error[:200])
        return out
    if rec.status >= 500:
        add("P0", "http_5xx", f"HTTP {rec.status}")
    elif rec.status == 404:
        add("P0", "http_404", "Not found")
    elif rec.status >= 400:
        add("P1", "http_4xx", f"HTTP {rec.status}")
    elif rec.redirect_chain_len > 2:
        add("P2", "redirect_chain", f"chain length {rec.redirect_chain_len}")
    if "noindex" in (rec.robots_meta + " " + rec.x_robots_tag).lower():
        add("P2", "noindex", rec.robots_meta or rec.x_robots_tag)
    if rec.canonical_ok == "missing" and 200 <= rec.status < 300:
        add("P1", "canonical_missing", "no rel=canonical")
    elif rec.canonical_ok == "no":
        add("P1", "canonical_mismatch", f"canonical={rec.canonical} final={rec.final_url}")
    if rec.status == 200 and rec.title == "":
        add("P1", "title_missing", "")
    elif rec.status == 200 and len(rec.title) > 70:
        add("P3", "title_long", f"len={len(rec.title)}")
    if rec.status == 200 and rec.description == "":
        add("P2", "description_missing", "")
    elif rec.status == 200 and len(rec.description) > 170:
        add("P3", "description_long", f"len={len(rec.description)}")
    if rec.status == 200 and rec.h1_count == 0:
        add("P1", "h1_missing", "")
    elif rec.status == 200 and rec.h1_count > 1:
        add("P2", "h1_multiple", f"count={rec.h1_count}")
    if rec.status == 200 and rec.word_count_est < 120 and "/news" not in u:
        add("P3", "thin_content", f"words~={rec.word_count_est}")
    if rec.status == 200 and rec.json_ld_blocks == 0 and any(
        x in u for x in ("/registraciya", "/patent", "/findtz", "/portfolio", "/contacts")
    ):
        add("P2", "json_ld_missing", "no application/ld+json on commercial URL pattern")
    if rec.og_image and rec.og_image.startswith("http://"):
        add("P2", "mixed_og_http", rec.og_image[:120])
    return out


def write_inventory(path: Path, rows: List[PageRecord]) -> None:
    if not rows:
        return
    fields = list(asdict(rows[0]).keys())
    with path.open("w", newline="", encoding="utf-8") as f:
        w = csv.DictWriter(f, fieldnames=fields)
        w.writeheader()
        for r in rows:
            w.writerow(asdict(r))


def write_errors(path: Path, findings: List[Finding]) -> None:
    fields = ["priority", "url", "error_type", "evidence", "status"]
    with path.open("w", newline="", encoding="utf-8") as f:
        w = csv.DictWriter(f, fieldnames=fields)
        w.writeheader()
        for item in findings:
            w.writerow(asdict(item))


def main() -> int:
    ap = argparse.ArgumentParser()
    ap.add_argument(
        "--site",
        default=DEFAULT_SITE,
        help="Public origin (or set SEO_SITE_URL)",
    )
    ap.add_argument("--output-dir", default="reports/seo")
    ap.add_argument("--workers", type=int, default=12)
    ap.add_argument("--max-urls", type=int, default=0, help="0 = all sitemap URLs")
    ap.add_argument("--discover-links", action="store_true", help="Also queue in-site links from HTML")
    ap.add_argument("--cache", default="tools/seo/.cache/crawl-latest.json")
    ap.add_argument(
        "--url-list",
        default="",
        help="Text file with one URL per line (skip live sitemap fetch)",
    )
    args = ap.parse_args()
    if not args.site:
        print("BLOCKER: pass --site or set SEO_SITE_URL", file=sys.stderr)
        return 2

    out_dir = Path(args.output_dir)
    out_dir.mkdir(parents=True, exist_ok=True)
    cache_path = Path(args.cache)
    cache_path.parent.mkdir(parents=True, exist_ok=True)

    list_path = Path(args.url_list) if args.url_list else None
    if list_path and list_path.is_file():
        urls = [
            normalize_url(line.strip(), args.site)
            for line in list_path.read_text(encoding="utf-8").splitlines()
            if line.strip() and not line.startswith("#")
        ]
        urls = sorted(set(urls))
        print(f"Loaded {len(urls)} URLs from {list_path}", flush=True)
    else:
        print("Loading sitemap...", flush=True)
        urls = load_sitemap_urls(args.site, DEFAULT_UA)
    if args.max_urls > 0:
        urls = urls[: args.max_urls]
    print(f"Sitemap URLs to audit: {len(urls)}", flush=True)

    records: Dict[str, PageRecord] = {}
    findings: List[Finding] = []
    discovered: Set[str] = set(urls)
    pending: List[str] = list(urls)
    seen: Set[str] = set()

    def process_one(target: str) -> Tuple[str, PageRecord, List[str], List[Finding]]:
        body, rec, _ = fetch_url(target, DEFAULT_UA)
        links = []
        if body and (rec.content_type.startswith("text/html") or b"<html" in body[:5000].lower()):
            links = parse_html(body, rec, args.site)
        f = analyze_record(rec, args.site)
        return target, rec, links, f

    def ingest(target: str) -> None:
        try:
            key, rec, links, f = process_one(target)
        except Exception as e:
            rec = PageRecord(url=target, error=str(e)[:200])
            f = analyze_record(rec, args.site)
            key, links = target, []
        records[key] = rec
        findings.extend(f)
        if args.discover_links:
            for link in links:
                if link not in discovered:
                    discovered.add(link)
                    pending.append(link)

    def flush_partial() -> None:
        rows = sorted(records.values(), key=lambda r: r.url)
        write_inventory(out_dir / "URL_INVENTORY.csv", rows)
        write_errors(
            out_dir / "SEO_ERRORS.csv",
            sorted(findings, key=lambda x: (x.priority, x.url, x.error_type)),
        )

    if args.workers <= 1:
        for idx, u in enumerate(urls, 1):
            if u in seen:
                continue
            seen.add(u)
            ingest(u)
            if idx % 25 == 0:
                flush_partial()
            if idx % 10 == 0 or idx == len(urls):
                print(f"  crawled {idx}/{len(urls)}", flush=True)
    else:
        with ThreadPoolExecutor(max_workers=max(1, args.workers)) as pool:
            while pending:
                batch: List[str] = []
                while pending and len(batch) < args.workers * 2:
                    u = pending.pop(0)
                    if u in seen:
                        continue
                    seen.add(u)
                    batch.append(u)
                if not batch:
                    break
                futures = {pool.submit(process_one, u): u for u in batch}
                for fut in as_completed(futures):
                    u = futures[fut]
                    try:
                        key, rec, links, f = fut.result()
                    except Exception as e:
                        rec = PageRecord(url=u, error=str(e)[:200])
                        f = analyze_record(rec, args.site)
                        key, links = u, []
                    records[key] = rec
                    findings.extend(f)
                    if args.discover_links:
                        for link in links:
                            if link not in discovered:
                                discovered.add(link)
                                pending.append(link)
                print(
                    f"  crawled {len(records)}/{len(urls)} pending={len(pending)}",
                    flush=True,
                )

    rows = sorted(records.values(), key=lambda r: r.url)
    inv_path = out_dir / "URL_INVENTORY.csv"
    err_path = out_dir / "SEO_ERRORS.csv"
    write_inventory(inv_path, rows)
    write_errors(err_path, sorted(findings, key=lambda x: (x.priority, x.url, x.error_type)))
    summary = {
        "site": args.site,
        "audited_at_utc": time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime()),
        "pages": len(rows),
        "findings": len(findings),
        "by_priority": {},
        "status_histogram": {},
    }
    for f in findings:
        summary["by_priority"][f.priority] = summary["by_priority"].get(f.priority, 0) + 1
    for r in rows:
        k = str(r.status)
        summary["status_histogram"][k] = summary["status_histogram"].get(k, 0) + 1
    cache_path.write_text(json.dumps({"summary": summary, "records": [asdict(r) for r in rows]}, ensure_ascii=False), encoding="utf-8")
    (out_dir / "CRAWL_SUMMARY.json").write_text(json.dumps(summary, ensure_ascii=False, indent=2), encoding="utf-8")
    print(json.dumps(summary, ensure_ascii=False, indent=2))
    return 0


if __name__ == "__main__":
    sys.exit(main())