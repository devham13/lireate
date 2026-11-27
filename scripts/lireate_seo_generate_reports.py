#!/usr/bin/env python3
"""Build reports/seo-20261127 from before/after crawl CSVs."""
from __future__ import print_function

import argparse
import csv
import shutil
import sys
from collections import Counter
from datetime import datetime, timezone
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
OUT = REPO / "reports" / "seo-20261127"
ART = Path("/opt/cursor/artifacts")
SCRIPTS = REPO / "scripts"


def _issues_module():
    if str(SCRIPTS) not in sys.path:
        sys.path.insert(0, str(SCRIPTS))
    import lireate_seo_audit_issues as mod  # noqa: E402

    return mod


def read_csv(path):
    if not path.is_file():
        return []
    with open(str(path), newline="", encoding="utf-8-sig") as fh:
        return list(csv.DictReader(fh))


def write_csv(path, fields, rows):
    path.parent.mkdir(parents=True, exist_ok=True)
    with open(str(path), "w", encoding="utf-8-sig", newline="") as fh:
        w = csv.DictWriter(fh, fieldnames=fields, extrasaction="ignore")
        w.writeheader()
        for r in rows:
            w.writerow({k: r.get(k, "") for k in fields})


def enrich_problems(rows):
  issues = _issues_module().issues_for_row
  out = []
  for r in rows:
    row = dict(r)
    row["problems"] = issues(row)
    out.append(row)
  return out


def counts(rows):
    c = Counter()
    for r in rows:
        for i in (r.get("problems") or "").split(";"):
            if i:
                c[i] += 1
    return c


def inventory_rows(after_rows):
    fields = (
        "requested_url",
        "status",
        "cluster",
        "title",
        "meta_description",
        "h1",
        "canonical",
        "jsonld_types",
        "problems",
    )
    inv = []
    for r in after_rows:
        inv.append({k: r.get(k, "") for k in fields})
    return fields, inv


def error_rows(rows):
    errs = []
    for r in rows:
        prob = (r.get("problems") or "").strip()
        if not prob:
            continue
        for issue in prob.split(";"):
            if not issue:
                continue
            errs.append(
                {
                    "phase": r.get("phase", ""),
                    "url": r.get("requested_url", ""),
                    "issue_type": issue,
                    "status": r.get("status", ""),
                    "title": r.get("title", ""),
                }
            )
    return errs


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--before", type=Path, default=ART / "seo-registry.csv")
    ap.add_argument("--after", type=Path, default=ART / "seo-registry-after-20261127.csv")
    ap.add_argument("--inventory", type=Path, default=None)
    args = ap.parse_args()

    before_raw, after_raw = read_csv(args.before), read_csv(args.after)
    before = enrich_problems(before_raw)
    after = enrich_problems(after_raw)

    OUT.mkdir(parents=True, exist_ok=True)
    crawl_fields = before[0].keys() if before else (after[0].keys() if after else [])

    if crawl_fields:
        write_csv(OUT / "seo-audit-before.csv", crawl_fields, before)
        write_csv(OUT / "seo-audit-after.csv", crawl_fields, after)

    inv_fields, inv = inventory_rows(after)
    write_csv(OUT / "seo-url-inventory.csv", inv_fields, inv)

    errs = error_rows(after)
    write_csv(
        OUT / "seo-errors.csv",
        ("phase", "url", "issue_type", "status", "title"),
        errs,
    )

    bc, ac = counts(before), counts(after)
    rem = [
        {
            "issue_type": k,
            "count_before": bc.get(k, 0),
            "count_after": ac.get(k, 0),
            "delta": ac.get(k, 0) - bc.get(k, 0),
        }
        for k in sorted(set(bc) | set(ac))
    ]
    rem.append(
        {
            "issue_type": "GSC_WORDSTAT",
            "count_before": "",
            "count_after": "",
            "delta": "BLOCKED no API",
        }
    )
    write_csv(
        OUT / "seo-remaining-issues.csv",
        ("issue_type", "count_before", "count_after", "delta"),
        rem,
    )

    if not (OUT / "seo-blog-audit.csv").is_file():
        write_csv(
            OUT / "seo-blog-audit.csv",
            ("status", "source", "notes"),
            [
                {
                    "status": "see",
                    "source": "lireate_blog_seo_report_build.py",
                    "notes": "blog pack in same dir when built",
                }
            ],
        )

    ba_lines = [
        "# Before / after (seo master 2026-11-27)",
        "",
        "UTC: " + datetime.now(timezone.utc).strftime("%Y-%m-%dT%H:%M:%SZ"),
        "",
        "| issue | before | after | delta |",
        "| --- | ---: | ---: | ---: |",
    ]
    for row in rem:
        if row["issue_type"] in ("GSC_WORDSTAT",):
            continue
        ba_lines.append(
            "| {0} | {1} | {2} | {3} |".format(
                row["issue_type"],
                row["count_before"],
                row["count_after"],
                row["delta"],
            )
        )
    (OUT / "before-after.md").write_text("\n".join(ba_lines) + "\n", encoding="utf-8")

    lines = [
        "# SEO_REPORT",
        "",
        "Пакет **seo-20261127** (site crawl + blog CSVs в каталоге).",
        "",
        "UTC: " + datetime.now(timezone.utc).isoformat(),
        "",
        "## Crawl",
        "",
        "- before: `{0}` ({1} URLs)".format(args.before, len(before)),
        "- after: `{0}` ({1} URLs)".format(args.after, len(after)),
        "",
        "## Issue counts (problems column)",
        "",
    ]
    for k in sorted(set(bc) | set(ac)):
        lines.append(
            "- **{0}**: before={1}, after={2}, Δ={3}".format(
                k, bc.get(k, 0), ac.get(k, 0), ac.get(k, 0) - bc.get(k, 0)
            )
        )
    lines.extend(
        [
            "",
            "## Deployed P0 (prod)",
            "",
            "- Home title ≤58 chars (51), description без изменений.",
            "- MКТУ 1–45: WebPage + BreadcrumbList JSON-LD.",
            "",
            "## Artifacts",
            "",
            "- `seo-audit-before.csv`, `seo-audit-after.csv`",
            "- `seo-url-inventory.csv`, `seo-errors.csv`, `seo-remaining-issues.csv`",
            "- `seo-blog-audit.csv`, `seo-legal-review.csv`, `seo-cannibalization.csv` (blog)",
            "- `before-after.md`",
            "",
            "## Blocked",
            "",
            "- GSC / Wordstat / IndexNow — нет credentials в Cloud.",
            "- lireate-news #187 — merge только по approve владельца.",
        ]
    )
    (OUT / "SEO_REPORT.md").write_text("\n".join(lines) + "\n", encoding="utf-8")

    for name in (
        "seo-audit-before.csv",
        "seo-audit-after.csv",
        "seo-url-inventory.csv",
        "seo-errors.csv",
        "seo-remaining-issues.csv",
        "seo-blog-audit.csv",
        "seo-yandex-webmaster.csv",
        "seo-yandex-diagnostics-p0.csv",
        "before-after.md",
        "SEO_REPORT.md",
    ):
        p = OUT / name
        if p.is_file():
            shutil.copy2(p, ART / p.name)
    shutil.copy2(OUT / "before-after.md", ART / "before-after.md")
    print("reports_ok", OUT)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())