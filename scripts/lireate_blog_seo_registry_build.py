#!/usr/bin/env python3
"""Merge production crawl CSV + GROK wave JSON into blog-registry.csv."""

from __future__ import annotations

import argparse
import csv
import json
from pathlib import Path


def read_grok(path):
    data = json.loads(path.read_text(encoding="utf-8"))
    by_slug = {}
    for row in data.get("articles", []):
        slug = (row.get("seo_url") or "").strip("/")
        if slug:
            by_slug[slug] = row
    return by_slug


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--audit", type=Path, required=True, help="blog_audit_baseline.csv")
    ap.add_argument("--grok", type=Path, required=True)
    ap.add_argument("--out", type=Path, required=True)
    args = ap.parse_args()
    grok = read_grok(args.grok)
    rows_out = []
    with args.audit.open(encoding="utf-8-sig", newline="") as fh:
        reader = csv.DictReader(fh)
        for row in reader:
            url = row.get("url") or ""
            slug = url.rstrip("/").split("/")[-1]
            g = grok.get(slug, {})
            rows_out.append(
                {
                    "url": url,
                    "status": row.get("status", ""),
                    "source": "mysql_cms",
                    "current_title": row.get("title", ""),
                    "current_description": row.get("meta_description", ""),
                    "current_h1": row.get("h1_text", ""),
                    "proposed_title": g.get("seo_title") or "",
                    "proposed_description": g.get("seo_description") or "",
                    "proposed_h1": g.get("name") or "",
                    "canonical": row.get("canonical", ""),
                    "schema_types": row.get("json_ld_types", ""),
                    "truncation_flags": row.get("truncation_flags", ""),
                    "cluster": g.get("cluster", ""),
                    "review_status": "planned" if g else "crawl_only",
                    "changed": "pending",
                }
            )
    fieldnames = list(rows_out[0].keys()) if rows_out else []
    args.out.parent.mkdir(parents=True, exist_ok=True)
    with args.out.open("w", encoding="utf-8-sig", newline="") as fh:
        w = csv.DictWriter(fh, fieldnames=fieldnames)
        w.writeheader()
        w.writerows(rows_out)
    print("rows", len(rows_out))


if __name__ == "__main__":
    main()