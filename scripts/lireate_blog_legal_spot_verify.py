#!/usr/bin/env python3
"""Spot-check high-risk legal flags; keep unconfirmed rows for specialist review (no prod edits)."""

from __future__ import annotations

import argparse
import csv
import json
import re
import subprocess
from collections import Counter
from datetime import datetime, timezone
from pathlib import Path

# Official pages used for spot checks (structure / terminology only — not article-specific amounts).
OFFICIAL_CHECKS = (
    {
        "id": "gk_1483",
        "pattern": re.compile(r"ст\.\s*1483", re.I),
        "url": "http://pravo.gov.ru/proxy/ips/?docbody=&nd=102468204&r=0",
        "needle": re.compile(r"1483", re.I),
        "note": "Норма ГК РФ ч.4 о регистрации ТЗ (наличие статьи)",
    },
    {
        "id": "gk_1252",
        "pattern": re.compile(r"ст\.\s*1252", re.I),
        "url": "http://pravo.gov.ru/proxy/ips/?docbody=&nd=102468204&r=0",
        "needle": re.compile(r"1252", re.I),
        "note": "ГК РФ об охране исключительных прав (наличие статьи)",
    },
    {
        "id": "rospatent_fees",
        "pattern": re.compile(r"госпошлин", re.I),
        "url": "https://rospatent.gov.ru/ru/activities/dues/table",
        "needle": re.compile(r"госпошлин|пошлин", re.I),
        "note": "Официальная таблица пошлин Роспатента (наличие раздела)",
    },
)


def fetch_text(url: str) -> str:
    try:
        return subprocess.check_output(
            ["curl", "-sS", "-k", "-L", "--max-time", "60", url],
            text=True,
            errors="replace",
        )
    except subprocess.CalledProcessError:
        return ""


def main() -> int:
    ap = argparse.ArgumentParser()
    ap.add_argument("--legal-csv", type=Path, required=True)
    ap.add_argument("--out-csv", type=Path, default=None)
    ap.add_argument("--json-out", type=Path, default=None)
    ap.add_argument("--sample-high", type=int, default=40, help="max high-risk rows to spot-check")
    args = ap.parse_args()

    rows = list(csv.DictReader(args.legal_csv.open(encoding="utf-8-sig", newline="")))
    official_cache: dict[str, tuple[bool, str]] = {}
    for chk in OFFICIAL_CHECKS:
        body = fetch_text(chk["url"])
        ok = bool(chk["needle"].search(body))
        official_cache[chk["id"]] = (ok, chk["note"])

    high = [r for r in rows if (r.get("risk") or "").lower() == "high"]
    sample = high[: args.sample_high]
    spot_by_issue = Counter()
    updated = 0
    for row in rows:
        issue = row.get("issue") or ""
        fragment = row.get("fragment") or ""
        hit = None
        for chk in OFFICIAL_CHECKS:
            if chk["pattern"].search(issue) or chk["pattern"].search(fragment):
                ok, note = official_cache[chk["id"]]
                if ok:
                    row["status"] = "spot_reference_ok"
                    row["suggested_wording"] = note
                    row["verified_at"] = datetime.now(timezone.utc).strftime("%Y-%m-%d")
                    spot_by_issue[chk["id"]] += 1
                    updated += 1
                    hit = chk["id"]
                else:
                    row["status"] = "unverified_specialist"
                    row["suggested_wording"] = "official_fetch_failed"
                break
        if hit is None and (row.get("risk") or "").lower() == "high":
            row["status"] = row.get("status") or "unverified_specialist"

    out = args.out_csv or args.legal_csv
    fields = list(rows[0].keys()) if rows else []
    if "verification_note" not in fields:
        fields.append("verification_note")
    for row in rows:
        if row.get("status") == "spot_reference_ok":
            row["verification_note"] = "Структура нормы/раздела на официальном ресурсе; цифры в тексте статьи не перепроверялись."
        elif (row.get("risk") or "").lower() == "high":
            row["verification_note"] = "Требуется юрист: суммы, сроки и контекст фрагмента."

    with out.open("w", encoding="utf-8-sig", newline="") as fh:
        w = csv.DictWriter(fh, fieldnames=fields, extrasaction="ignore")
        w.writeheader()
        w.writerows(rows)

    summary = {
        "legal_rows_total": len(rows),
        "high_risk_rows": len(high),
        "high_risk_spot_sampled": len(sample),
        "rows_status_spot_reference_ok": sum(1 for r in rows if r.get("status") == "spot_reference_ok"),
        "rows_unverified_specialist": sum(1 for r in rows if r.get("status") == "unverified_specialist"),
        "official_checks": {k: {"ok": v[0], "note": v[1]} for k, v in official_cache.items()},
        "spot_hits_by_check": dict(spot_by_issue),
    }
    print(json.dumps(summary, ensure_ascii=False, indent=2))
    if args.json_out:
        args.json_out.parent.mkdir(parents=True, exist_ok=True)
        args.json_out.write_text(json.dumps(summary, ensure_ascii=False, indent=2), encoding="utf-8")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())