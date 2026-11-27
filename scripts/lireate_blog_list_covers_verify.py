#!/usr/bin/env python3
"""Crawl /news/ list pages; curl every list-card cover img (expect HTTP 200, no redirect)."""

from __future__ import annotations

import argparse
import json
import os
import re
import subprocess
import sys
import urllib.error
import urllib.request
from pathlib import Path


def curl_code(base: str, path: str) -> int:
    url = path if path.startswith("http") else base.rstrip("/") + (path if path.startswith("/") else "/" + path)
    try:
        return int(
            subprocess.check_output(
                ["curl", "-sS", "-o", "/dev/null", "-w", "%{http_code}", "--max-redirs", "0", url],
                text=True,
            ).strip()
        )
    except subprocess.CalledProcessError:
        return 0


def fetch(url: str) -> str:
    req = urllib.request.Request(url, headers={"User-Agent": "lireate-blog-covers-verify/1"})
    with urllib.request.urlopen(req, timeout=90) as resp:
        return resp.read().decode("utf-8", errors="replace")


def extract_imgs(html: str) -> list[str]:
    imgs: list[str] = []
    for block in re.findall(
        r'class="lireate-blog-list-(?:card|featured)-cover[^"]*"[^>]*>(.*?)</a>',
        html,
        flags=re.S | re.I,
    ):
        for src in re.findall(r'<img[^>]+src="([^"]+)"', block, re.I):
            imgs.append(src)
    return imgs


def main() -> int:
    ap = argparse.ArgumentParser()
    ap.add_argument("--base", default=os.environ.get("LIREATE_PUBLIC_SITE_URL", "").rstrip("/"))
    ap.add_argument("--max-pages", type=int, default=30)
    ap.add_argument("--json-out", type=Path, default=None)
    args = ap.parse_args()
    if not args.base:
        print("BLOCKER: --base or LIREATE_PUBLIC_SITE_URL", file=sys.stderr)
        return 2

    checked: dict[str, int] = {}
    pages = 0
    diag_missing: list[int] = []
    for page in range(1, args.max_pages + 1):
        url = args.base + "/news/" + (f"?page={page}" if page > 1 else "")
        try:
            html = fetch(url)
        except (urllib.error.URLError, TimeoutError) as exc:
            print("fetch_fail", url, exc, file=sys.stderr)
            break
        if "lireate-blog-list-page" not in html and page > 1:
            break
        pages += 1
        m = re.search(r"lireate-blog-list-diag-covers-missing:(\d+)", html)
        if m:
            diag_missing.append(int(m.group(1)))
        for src in extract_imgs(html):
            if src in checked:
                continue
            checked[src] = curl_code(args.base, src)

    bad = {k: v for k, v in checked.items() if v != 200}
    summary = {
        "base": args.base,
        "pages_crawled": pages,
        "unique_cover_imgs": len(checked),
        "http_200": len(checked) - len(bad),
        "not_200": len(bad),
        "diag_covers_missing_per_page": diag_missing,
        "bad_urls": [{"src": k, "http": v} for k, v in sorted(bad.items())],
    }
    print(json.dumps(summary, ensure_ascii=False, indent=2))
    if args.json_out:
        args.json_out = Path(args.json_out)
        args.json_out.parent.mkdir(parents=True, exist_ok=True)
        args.json_out.write_text(json.dumps(summary, ensure_ascii=False, indent=2), encoding="utf-8")
    return 1 if bad else 0


if __name__ == "__main__":
    raise SystemExit(main())