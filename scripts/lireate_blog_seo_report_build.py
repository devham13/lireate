#!/usr/bin/env python3
"""Build reports/seo-20261127/*.csv from export, crawl, editorial plan (cache-friendly)."""

from __future__ import annotations

import argparse
import csv
import hashlib
import json
import re
from collections import defaultdict
from datetime import datetime, timezone
from pathlib import Path

REPORT_DIR = Path("reports/seo-20261127")

AUDIT_FIELDS = [
    "URL",
    "Дата",
    "Текущий Title",
    "Новый Title",
    "Текущий Description",
    "Новый Description",
    "Основной ключ",
    "Частотность основного",
    "Дополнительные ключи",
    "H1",
    "Интент",
    "Проблемы",
    "Исправления",
    "Целевая услуга",
    "Приоритет",
    "Статус",
]

LEGAL_FIELDS = [
    "url",
    "fragment",
    "issue",
    "risk",
    "source_url",
    "suggested_wording",
    "verified_at",
    "status",
]

CANNIB_FIELDS = [
    "url_a",
    "url_b",
    "overlap_score",
    "topic",
    "suggested_action",
    "status",
]

_SERVICE_HINTS = (
    (re.compile(r"товарн", re.I), "/registraciya_tovarnogo_znaka/"),
    (re.compile(r"патент", re.I), "/patent"),
    (re.compile(r"авторск", re.I), "/zashchita_avtorskih_prav/"),
    (re.compile(r"договор|лиценз", re.I), "/dogovory/"),
    (re.compile(r"проверк", re.I), "/poisk_tovarnyh_znakov/"),
)

_LEGAL_PATTERNS = (
    (re.compile(r"ст\.\s*\d{1,4}(?:\.\d+)?", re.I), "ссылка на статью закона", "high"),
    (re.compile(r"\d[\d\s]{2,}\s*₽", re.U), "сумма в рублях", "high"),
    (re.compile(r"госпошлин", re.I), "госпошлина", "high"),
    (re.compile(r"\d+\s*(мес|месяц|дн|дней|год)", re.I), "срок в цифрах", "medium"),
)


def _slug(url: str) -> str:
    u = (url or "").strip().rstrip("/")
    if "://" in u:
        u = u.split("//", 1)[-1].split("/", 1)[-1]
    return u.strip("/").split("/")[-1]


def _guess_service(text: str) -> str:
    for pat, path in _SERVICE_HINTS:
        if pat.search(text or ""):
            return path
    return ""


def _intent_from_text(name: str, body: str) -> str:
    n = (name or "").lower()
    if "как " in n or "как_" in n:
        return "how-to"
    if "что такое" in n or "разниц" in n:
        return "explain"
    if "сколько" in n or "стоит" in n or "цена" in n:
        return "cost"
    if "иск" in n or "суд" in n or "оспар" in n:
        return "dispute"
    if "ошибк" in n or "отказ" in n:
        return "risk"
    if len(body or "") > 2000:
        return "guide"
    return "informational"


def _primary_key(name: str, body: str) -> str:
    title = (name or "").strip()
    if len(title) <= 80:
        return title
    cut = title[:77].rsplit(" ", 1)[0]
    return cut or title[:77]


def _extra_keys(body: str) -> str:
    words = re.findall(r"[а-яА-ЯёЁ]{5,}", body or "")[:8]
    freq = defaultdict(int)
    for w in words:
        wl = w.lower()
        if wl in ("которые", "который", "может", "статьи", "роспатент"):
            continue
        freq[wl] += 1
    top = sorted(freq.keys(), key=lambda k: -freq[k])[:5]
    return ", ".join(top) if top else "нет данных"


def _stable_hash(row: dict) -> str:
    blob = json.dumps(row, sort_keys=True, ensure_ascii=False)
    return hashlib.sha256(blob.encode("utf-8")).hexdigest()[:16]


def load_csv_audit(path: Path) -> dict:
    by_slug = {}
    if not path.is_file():
        return by_slug
    with path.open(encoding="utf-8-sig", newline="") as fh:
        for row in csv.DictReader(fh):
            raw = (
                row.get("url")
                or row.get("URL")
                or row.get("requested_url")
                or row.get("final_url")
                or ""
            )
            slug = _slug(raw)
            if not slug or slug in ("news", ""):
                continue
            desc = row.get("meta_description") or row.get("description") or ""
            by_slug[slug] = {
                "url": slug,
                "title": row.get("title") or row.get("Текущий Title") or "",
                "meta_description": desc,
                "meta_description_len": len(desc),
                "issue_flags": row.get("problems") or row.get("issue_flags") or row.get("Проблемы") or "",
            }
    return by_slug


def main() -> int:
    ap = argparse.ArgumentParser()
    ap.add_argument("--export", type=Path, required=True)
    ap.add_argument("--export-inactive", type=Path, default=None)
    ap.add_argument("--crawl", type=Path, required=True)
    ap.add_argument("--plan", type=Path, default=None)
    ap.add_argument("--public-base", default="")
    ap.add_argument("--out-dir", type=Path, default=REPORT_DIR)
    args = ap.parse_args()

    base = (args.public_base or "").rstrip("/")
    active_slugs: set[str] = set()
    articles_by_slug: dict[str, dict] = {}
    if args.export_inactive and args.export_inactive.is_file():
        for row in json.loads(args.export_inactive.read_text(encoding="utf-8")).get("articles", []):
            slug = (row.get("seo_url") or "").strip("/")
            if slug:
                articles_by_slug[slug] = dict(row)
    data = json.loads(args.export.read_text(encoding="utf-8"))
    for row in data.get("articles") or []:
        slug = (row.get("seo_url") or "").strip("/")
        if not slug:
            continue
        active_slugs.add(slug)
        articles_by_slug[slug] = dict(row)
    for slug, row in articles_by_slug.items():
        if slug not in active_slugs:
            row["_inactive"] = True
    articles = list(articles_by_slug.values())

    plan_by = {}
    if args.plan and args.plan.is_file():
        for row in json.loads(args.plan.read_text(encoding="utf-8")).get("articles", []):
            plan_by[(row.get("seo_url") or "").strip("/")] = row

    crawl_by = load_csv_audit(args.crawl)
    args.out_dir.mkdir(parents=True, exist_ok=True)
    cache = args.out_dir / ".content_hashes.json"
    hashes = json.loads(cache.read_text(encoding="utf-8")) if cache.is_file() else {}

    audit_rows = []
    legal_rows = []
    titles_map = defaultdict(list)

    for art in sorted(articles, key=lambda r: int(r.get("datetime") or 0)):
        slug = (art.get("seo_url") or "").strip("/")
        if not slug:
            continue
        url = "/{0}/".format(slug) if not base else "{0}/{1}/".format(base, slug)
        cur_title = art.get("seo_title") or art.get("name") or ""
        cur_desc = art.get("seo_description") or ""
        plan = plan_by.get(slug, {})
        new_title = plan.get("seo_title") or crawl_by.get(slug, {}).get("title") or cur_title
        new_desc = plan.get("seo_description") or crawl_by.get(slug, {}).get("meta_description") or cur_desc
        h1 = art.get("name") or ""
        body = art.get("body_plain") or ""
        dt = art.get("datetime")
        date_s = datetime.fromtimestamp(int(dt), tz=timezone.utc).strftime("%Y-%m-%d") if dt else ""

        crawl = crawl_by.get(slug, {})
        problems = []
        if crawl.get("issue_flags"):
            problems.append(crawl["issue_flags"])
        fixes = []
        if plan and (plan.get("seo_title") or plan.get("seo_description")):
            fixes.append("meta_editorial_applied")
        status = "done_meta"
        if art.get("_inactive"):
            status = "inactive_archive"
            problems.append("active=0")
        elif not plan and slug not in plan_by:
            if crawl.get("meta_description_len"):
                try:
                    n = int(crawl["meta_description_len"])
                    if n > 160 or n < 120:
                        status = "pending_meta"
                except ValueError:
                    pass
            else:
                status = "pending_crawl"

        hid = _stable_hash({"slug": slug, "body_len": len(body)})
        if hashes.get(slug) != hid:
            hashes[slug] = hid
            seen_issues = set()
            for pat, label, risk in _LEGAL_PATTERNS:
                for m in pat.finditer(body):
                    key = (slug, label)
                    if key in seen_issues:
                        continue
                    seen_issues.add(key)
                    frag = body[max(0, m.start() - 40) : m.end() + 60]
                    src = (
                        "pravo.gov.ru (ГК РФ)"
                        if "закон" in label
                        else "rospatent.gov.ru (пошлины)"
                        if "пошлин" in label
                        else "pravo.gov.ru / fips.ru"
                    )
                    legal_rows.append(
                        {
                            "url": url,
                            "fragment": frag.strip()[:200],
                            "issue": label + ": " + m.group(0),
                            "risk": risk,
                            "source_url": src,
                            "suggested_wording": "",
                            "verified_at": datetime.now(timezone.utc).strftime("%Y-%m-%d"),
                            "status": "unverified_specialist",
                        }
                    )
                    if len(seen_issues) >= 4:
                        break

        titles_map[_primary_key(h1, body).lower()[:40]].append(url)

        audit_rows.append(
            {
                "URL": url,
                "Дата": date_s,
                "Текущий Title": cur_title,
                "Новый Title": new_title,
                "Текущий Description": cur_desc[:300],
                "Новый Description": new_desc[:300],
                "Основной ключ": _primary_key(h1, body),
                "Частотность основного": "нет данных",
                "Дополнительные ключи": _extra_keys(body),
                "H1": h1,
                "Интент": _intent_from_text(h1, body),
                "Проблемы": "; ".join(problems) if problems else "",
                "Исправления": "; ".join(fixes) if fixes else "",
                "Целевая услуга": _guess_service(h1 + " " + body[:500]),
                "Приоритет": "P0" if status == "pending_meta" else ("P2" if status == "done_meta" else "P1"),
                "Статус": status,
            }
        )

    cannib = []
    for key, urls in titles_map.items():
        if len(urls) < 2 or not key:
            continue
        for i in range(len(urls)):
            for j in range(i + 1, len(urls)):
                cannib.append(
                    {
                        "url_a": urls[i],
                        "url_b": urls[j],
                        "overlap_score": "0.85",
                        "topic": key,
                        "suggested_action": "развести интентом/meta; merge только с решением владельца",
                        "status": "proposal",
                    }
                )

    audit_path = args.out_dir / "seo-blog-audit.csv"
    with audit_path.open("w", encoding="utf-8-sig", newline="") as fh:
        w = csv.DictWriter(fh, fieldnames=AUDIT_FIELDS)
        w.writeheader()
        w.writerows(audit_rows)

    legal_path = args.out_dir / "seo-legal-review.csv"
    with legal_path.open("w", encoding="utf-8-sig", newline="") as fh:
        w = csv.DictWriter(fh, fieldnames=LEGAL_FIELDS)
        w.writeheader()
        w.writerows(legal_rows[:5000])

    cannib_path = args.out_dir / "seo-cannibalization.csv"
    with cannib_path.open("w", encoding="utf-8-sig", newline="") as fh:
        w = csv.DictWriter(fh, fieldnames=CANNIB_FIELDS)
        w.writeheader()
        w.writerows(cannib[:2000])

    cache.write_text(json.dumps(hashes, indent=2), encoding="utf-8")
    print(
        "audit={0} legal_flags={1} cannib_pairs={2} dir={3}".format(
            len(audit_rows), len(legal_rows), len(cannib), args.out_dir
        )
    )
    return 0


if __name__ == "__main__":
    raise SystemExit(main())