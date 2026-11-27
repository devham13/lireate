#!/usr/bin/env python3
"""Build KEYWORD_MAP.csv from pages/services/queue.md (confirmed Metrika demand only)."""
from __future__ import annotations

import csv
import re
import sys
from pathlib import Path

REPO = Path(__file__).resolve().parents[2]
QUEUE = REPO / "pages" / "services" / "queue.md"
OUT = REPO / "reports" / "seo" / "KEYWORD_MAP.csv"

SECTION = re.compile(r"^##\s+(S\d+)\.\s+(.+)$")
FIELD = re.compile(r"^-\s+\*\*(\w[\w_]*)\*\*:\s*(.*)$")


def parse_queue(text: str):
    blocks = []
    cur = None
    for line in text.splitlines():
        m = SECTION.match(line.strip())
        if m:
            if cur:
                blocks.append(cur)
            cur = {"id": m.group(1), "title": m.group(2).strip(), "fields": {}}
            continue
        if cur:
            fm = FIELD.match(line.strip())
            if fm:
                cur["fields"][fm.group(1)] = fm.group(2).strip()
    if cur:
        blocks.append(cur)
    return blocks


def landing_url(fields: dict) -> str:
    slug = fields.get("slug", "").strip()
    old = fields.get("old_url", "").strip()
    if old and old not in ("—", "-"):
        return old.split()[0]
    if slug:
        return "/{0}/".format(slug.replace("-", "_") if "_" in slug else slug)
    return ""


def main() -> int:
    if not QUEUE.is_file():
        print("Missing", QUEUE, file=sys.stderr)
        return 1
    blocks = parse_queue(QUEUE.read_text(encoding="utf-8"))
    OUT.parent.mkdir(parents=True, exist_ok=True)
    with OUT.open("w", newline="", encoding="utf-8") as f:
        w = csv.DictWriter(
            f,
            fieldnames=[
                "cluster_id",
                "service_title",
                "main_query",
                "supporting_queries",
                "intent",
                "region",
                "demand_visits_metrika",
                "current_landing",
                "target_landing",
                "status",
                "priority",
            ],
        )
        w.writeheader()
        for b in blocks:
            fields = b["fields"]
            sup = fields.get("supporting_queries", "")
            w.writerow(
                {
                    "cluster_id": b["id"],
                    "service_title": b["title"],
                    "main_query": fields.get("main_query", ""),
                    "supporting_queries": sup.replace("\n", " | "),
                    "intent": "commercial",
                    "region": "RU; MSK; SPB; KZN",
                    "demand_visits_metrika": fields.get("demand", ""),
                    "current_landing": fields.get("old_url", "") or landing_url(fields),
                    "target_landing": (
                        "https://${PUBLIC_SITE_HOST}"
                        + landing_url(fields)
                        if landing_url(fields).startswith("/")
                        else landing_url(fields)
                    ),
                    "status": fields.get("status", ""),
                    "priority": "P1" if fields.get("status") == "new" else "P2",
                }
            )
    print("Wrote", OUT, "rows=", len(blocks))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())