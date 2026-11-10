#!/usr/bin/env python3
"""Тысячи и ₽: узкий неразрывный пробел (U+202F), перед ₽ — NBSP; п. 2.1 — NBSP после точки."""
import re
import sys
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
SERVICES = REPO / "pages" / "services"
SLUGS = (
    "registraciya-tovarnogo-znaka",
    "proverka-tovarnogo-znaka",
    "registraciya-tovarnogo-znaka-s-oplatoy-za-rezultat",
    "registraciya-tovarnogo-znaka-v-kitae",
    "vozrazhenie-protiv-registracii-tovarnogo-znaka",
    "zashchita-avtorskih-prav",
    "prodlenie-tovarnogo-znaka",
)
NNBSP = "\u202f"
NBSP = "\xa0"


def transform(text: str) -> str:
    prev = None
    while prev != text:
        prev = text
        text = re.sub(r"(\d) (\d{3})(?!\d)", r"\1" + NNBSP + r"\2", text)
        text = re.sub(r"(\d)&nbsp;(\d{3})(?!\d)", r"\1" + NNBSP + r"\2", text)
    text = re.sub(r"(\d) ₽", r"\1" + NBSP + "₽", text)
    text = re.sub(r"(\d)&nbsp;₽", r"\1" + NBSP + "₽", text)
    text = re.sub(r"п\. (\d)", r"п." + NBSP + r"\1", text)
    text = re.sub(r"п\.&nbsp;(\d)", r"п." + NBSP + r"\1", text)
    return text


def main():
    changed = []
    for slug in SLUGS:
        root = SERVICES / slug
        if not root.is_dir():
            continue
        paths = [root / "index.php"]
        blocks = root / "blocks"
        if blocks.is_dir():
            paths.extend(sorted(blocks.glob("*.php")))
        for path in paths:
            raw = path.read_text(encoding="utf-8")
            out = transform(raw)
            if out != raw:
                path.write_text(out, encoding="utf-8")
                changed.append(str(path.relative_to(REPO)))
    print("updated", len(changed), "files")
    for p in changed:
        print(" ", p)
    return 0


if __name__ == "__main__":
    sys.exit(main())
