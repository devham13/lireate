#!/usr/bin/env python3
"""Validate blog service-hub catalog URLs and hub/related links on live articles."""

from __future__ import annotations

import argparse
import csv
import json
import re
import os
import subprocess
import sys
import urllib.error
import urllib.request
from collections import Counter
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
SERVICE_MAP = REPO / "pages" / "blog" / "_shared" / "lireate-blog-service-map.inc.php"
HUB_HREF_RE = re.compile(
    r'class="lireate-blog-service-hub-cta"\s+href="([^"]+)"',
    re.I,
)
CARD_HREF_RE = re.compile(
    r'class="lireate-blog-card-(?:cover|title|btn)"[^>]*\s+href="([^"]+)"',
    re.I,
)
URL_IN_MAP_RE = re.compile(r"'url'\s*=>\s*'([^']+)'")


def curl_status(base: str, path: str, follow: bool = False) -> tuple[int, str]:
    url = base.rstrip("/") + (path if path.startswith("/") else "/" + path)
    max_redirs = "-L" if follow else "--max-redirs"
    cmd = ["curl", "-sS", "-o", "/dev/null", "-w", "%{http_code}", max_redirs]
    if not follow:
        cmd.append("0")
    cmd.append(url)
    try:
        code = subprocess.check_output(cmd, text=True).strip()
        return int(code), url
    except (subprocess.CalledProcessError, ValueError) as exc:
        return 0, f"{url} ({exc})"


def fetch_html(base: str, path: str) -> str:
    url = base.rstrip("/") + path
    req = urllib.request.Request(url, headers={"User-Agent": "lireate-blog-hub-validate/1.0"})
    with urllib.request.urlopen(req, timeout=60) as resp:
        return resp.read().decode("utf-8", errors="replace")


def load_article_paths(audit_csv: Path) -> list[str]:
    paths: list[str] = []
    with audit_csv.open(encoding="utf-8", newline="") as fh:
        reader = csv.DictReader(fh)
        for row in reader:
            path = (row.get("path") or row.get("url") or row.get("URL") or "").strip()
            if not path:
                first = next(iter(row.values()), "").strip()
                if first.startswith("/"):
                    path = first
            if not path or path == "/news/":
                continue
            if not path.startswith("/"):
                path = "/" + path
            if not path.endswith("/"):
                path = path.rstrip("/") + "/"
            status = (row.get("status") or row.get("Статус") or "").lower()
            problems = row.get("Проблемы") or row.get("problems") or ""
            if "inactive" in status or "archive" in status or "active=0" in problems:
                continue
            paths.append(path)
    return sorted(set(paths))


def parse_service_catalog() -> list[tuple[str, bool]]:
    text = SERVICE_MAP.read_text(encoding="utf-8")
    entries: list[tuple[str, bool]] = []
    blocks = re.split(r"array\s*\(", text)
    for block in blocks:
        m_url = URL_IN_MAP_RE.search(block)
        if not m_url:
            continue
        path = m_url.group(1)
        trailing = "trailing_slash' => false" not in block
        entries.append((path, trailing))
    return entries


def main() -> int:
    ap = argparse.ArgumentParser()
    ap.add_argument(
        "--base",
        default="",
        help="Public site origin (no trailing slash); default: env LIREATE_PUBLIC_SITE_URL",
    )
    ap.add_argument(
        "--audit-csv",
        type=Path,
        default=REPO / "reports" / "seo-20261127" / "seo-blog-audit.csv",
    )
    ap.add_argument("--out-json", type=Path, default=None)
    ap.add_argument("--limit", type=int, default=0, help="Limit articles (0 = all)")
    args = ap.parse_args()
    base = (args.base or os.environ.get("LIREATE_PUBLIC_SITE_URL") or "").strip().rstrip("/")
    if not base:
        print("BLOCKER: set --base or LIREATE_PUBLIC_SITE_URL", file=sys.stderr)
        return 2

    failures: list[dict] = []
    catalog = parse_service_catalog()
    catalog_ok = 0
    for path, trailing in catalog:
        code, url = curl_status(base, path, follow=False)
        ok = code == 200
        if ok:
            catalog_ok += 1
        else:
            failures.append({"kind": "catalog", "href": path, "http": code, "url": url})

    article_paths: list[str] = []
    if args.audit_csv.is_file():
        article_paths = load_article_paths(args.audit_csv)
    if args.limit > 0:
        article_paths = article_paths[: args.limit]

    hub_links: Counter[str] = Counter()
    related_links: Counter[str] = Counter()
    articles_crawled = 0
    articles_with_hub = 0
    redirect_301 = 0
    not_found_404 = 0
    bad_hub_related = 0

    for path in article_paths:
        try:
            html = fetch_html(base, path)
        except (urllib.error.URLError, TimeoutError) as exc:
            failures.append({"kind": "article_fetch", "href": path, "error": str(exc)})
            continue
        articles_crawled += 1
        hub = HUB_HREF_RE.search(html)
        if hub:
            articles_with_hub += 1
            hub_links[hub.group(1)] += 1
        for href in CARD_HREF_RE.findall(html):
            if href.startswith("/news"):
                continue
            related_links[href] += 1

    def check_href(href: str, kind: str) -> None:
        nonlocal redirect_301, not_found_404, bad_hub_related
        if not href.startswith("/"):
            return
        code, url = curl_status(base, href, follow=False)
        if code == 301 or code == 302:
            redirect_301 += 1
            bad_hub_related += 1
            failures.append({"kind": kind, "href": href, "http": code, "url": url})
        elif code == 404:
            not_found_404 += 1
            bad_hub_related += 1
            failures.append({"kind": kind, "href": href, "http": code, "url": url})
        elif code != 200:
            bad_hub_related += 1
            failures.append({"kind": kind, "href": href, "http": code, "url": url})

    for href in hub_links:
        check_href(href, "hub_cta")
    for href in related_links:
        check_href(href, "related")

    summary = {
        "base": base,
        "service_catalog_urls": len(catalog),
        "service_catalog_http200": catalog_ok,
        "articles_in_audit": len(article_paths),
        "articles_crawled": articles_crawled,
        "articles_with_service_hub": articles_with_hub,
        "unique_hub_cta_hrefs": len(hub_links),
        "unique_related_hrefs": len(related_links),
        "hub_related_301": redirect_301,
        "hub_related_404": not_found_404,
        "hub_related_other_fail": bad_hub_related - redirect_301 - not_found_404,
        "failure_count": len(failures),
    }
    print(json.dumps(summary, ensure_ascii=False, indent=2))
    if args.out_json:
        args.out_json.parent.mkdir(parents=True, exist_ok=True)
        payload = {"summary": summary, "failures": failures[:500]}
        args.out_json.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")
        print("wrote", args.out_json)

    if failures:
        print("FAILURES (first 20):", file=sys.stderr)
        for item in failures[:20]:
            print(item, file=sys.stderr)
        return 1
    return 0


if __name__ == "__main__":
    raise SystemExit(main())