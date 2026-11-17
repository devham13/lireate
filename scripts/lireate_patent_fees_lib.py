"""Расчёт пошлин Роспатента (п. 2.1, 2.4, 2.11, 2.14, 2.22, 2.25) для раздела «Патентные задачи»."""

from __future__ import annotations

import re
from typing import Any

FEE_RULESET_ID = "rospatent-pp941-table-2025-10-03"
FEE_RULESET_CHECKED = "2026-11-17"
FEE_SOURCE_URL = "https://rospatent.gov.ru/ru/activities/dues/table"


def _e_total(class_counts: dict[int, int]) -> int:
    total = 0
    for _cls, n in class_counts.items():
        if n is not None and n > 10:
            total += n - 10
    return total


def _c_count(class_counts: dict[int, int]) -> int:
    return len(class_counts)


def registration_lines(class_counts: dict[int, int], paper: bool = False) -> dict[str, Any]:
    """class_counts: {class_no: position_count} for distinct classes."""
    if not class_counts:
        return {"ok": False, "error": "empty"}
    c = _c_count(class_counts)
    e = _e_total(class_counts)
    p21 = 4000 + 1000 * max(0, c - 1)
    p24 = 13000 + 2500 * max(0, c - 1) + 500 * e
    p211 = 18000 + 2000 * max(0, c - 5)
    p214 = 3000 if paper else 0
    filing = p21 + p24
    after = p211 + p214
    total = filing + after
    return {
        "ok": True,
        "c": c,
        "e": e,
        "lines": [
            {"id": "2.1", "label": "Регистрация заявки и формальная экспертиза", "amount": p21},
            {"id": "2.4", "label": "Экспертиза обозначения", "amount": p24},
        ],
        "after_lines": [
            {"id": "2.11", "label": "Регистрация и электронное свидетельство", "amount": p211},
        ],
        "optional_lines": (
            [{"id": "2.14", "label": "Бумажное свидетельство (по ходатайству)", "amount": p214}]
            if paper
            else []
        ),
        "filing_subtotal": filing,
        "after_subtotal": after,
        "total": total,
    }


def renewal_lines(
    class_counts: dict[int, int], extra_term: bool = False, paper_note: bool = False
) -> dict[str, Any]:
    if not class_counts:
        return {"ok": False, "error": "empty"}
    c = _c_count(class_counts)
    e = _e_total(class_counts)
    p222 = 22000 + 2000 * max(0, c - 5) + 500 * e
    p225 = 3000 if extra_term else 0
    total = p222 + p225
    lines = [{"id": "2.22", "label": "Продление", "amount": p222}]
    if extra_term:
        lines.append(
            {
                "id": "2.25",
                "label": "Ходатайство о дополнительном шестимесячном сроке",
                "amount": p225,
            }
        )
    result: dict[str, Any] = {
        "ok": True,
        "c": c,
        "e": e,
        "lines": lines,
        "total": total,
        "paper_excluded": True,
    }
    if paper_note:
        result["paper_note"] = (
            "Бумажная часть п. 2.22 применяется по ходатайству; по умолчанию не включена в сумму."
        )
    return result


CLASS_LINE_RE = re.compile(
    r"^\s*(?:класс\s*)?(\d{1,2})\s*[.:\-–—]?\s*(.*)$",
    re.IGNORECASE,
)


def normalize_class_num(raw: str) -> int | None:
    raw = raw.strip()
    if not raw.isdigit():
        return None
    n = int(raw, 10)
    if n < 1 or n > 45:
        return None
    return n


def parse_mktu_text(
    text: str,
    line_mode: bool = False,
) -> dict[str, Any]:
    """
    Parse MKTU listing into classes and positions.
    line_mode: each non-empty line in class body is one position (except class headers).
    """
    warnings: list[str] = []
    unrecognized: list[str] = []
    duplicates: list[dict[str, Any]] = []
    class_counts: dict[int, list[str]] = {}
    current: int | None = None
    lines = text.replace("\r\n", "\n").replace("\r", "\n").split("\n")

    def add_position(cls: int, term: str) -> None:
        term = term.strip()
        if not term:
            return
        if cls not in class_counts:
            class_counts[cls] = []
        for existing in class_counts[cls]:
            if existing.lower() == term.lower():
                duplicates.append({"class": cls, "term": term})
                return
        class_counts[cls].append(term)

    def split_items(chunk: str) -> list[str]:
        chunk = chunk.strip()
        if not chunk:
            return []
        if line_mode:
            return [chunk]
        parts: list[str] = []
        buf: list[str] = []
        paren = 0
        for ch in chunk:
            if ch == "(":
                paren += 1
            elif ch == ")" and paren > 0:
                paren -= 1
            if ch == ";" and paren == 0:
                parts.append("".join(buf).strip())
                buf = []
            else:
                buf.append(ch)
        tail = "".join(buf).strip()
        if tail:
            parts.append(tail)
        return [p for p in parts if p]

    for raw_line in lines:
        line = raw_line.strip()
        if not line:
            continue
        m = CLASS_LINE_RE.match(line)
        if m:
            cls = normalize_class_num(m.group(1))
            if cls is None:
                unrecognized.append(line)
                continue
            if cls in class_counts and current is not None and cls != current:
                warnings.append("Повтор класса {0}: уточните перечень.".format(cls))
            current = cls
            if cls not in class_counts:
                class_counts[cls] = []
            rest = (m.group(2) or "").strip()
            if rest:
                for item in split_items(rest):
                    add_position(cls, item)
            continue
        if current is None:
            unrecognized.append(line)
            continue
        if line_mode:
            add_position(current, line)
        else:
            for item in split_items(line):
                add_position(current, item)

    counts = {k: len(v) for k, v in class_counts.items()}
    return {
        "ok": True,
        "classes": class_counts,
        "counts": counts,
        "warnings": warnings,
        "unrecognized": unrecognized,
        "duplicates": duplicates,
    }


def format_mktu_export(classes: dict[int, list[str]]) -> str:
    out: list[str] = []
    for cls in sorted(classes.keys()):
        out.append("{0:02d}".format(cls))
        for term in classes[cls]:
            out.append("  " + term)
        out.append("")
    return "\n".join(out).strip() + "\n"