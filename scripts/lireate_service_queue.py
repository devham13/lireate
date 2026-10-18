#!/usr/bin/env python3
"""Print the next LIREATE service-page from pages/services/queue.md."""

from __future__ import annotations

import argparse
import re
import sys
from pathlib import Path

REPO_ROOT = Path(__file__).resolve().parents[1]
QUEUE_PATH = REPO_ROOT / "pages" / "services" / "queue.md"
SERVICES_DIR = REPO_ROOT / "pages" / "services"
SKIP_DIRS = {"_template", "_shared"}

SECTION_RE = re.compile(r"^##\s+(S\d+)\.\s+", re.M)
FIELD_RE = re.compile(r"^-\s+\*\*(slug|status):\*\*\s+`?([^`\n]+)`?\s*$", re.M)


def _parse_queue(text: str) -> list[dict[str, str]]:
    entries: list[dict[str, str]] = []
    parts = SECTION_RE.split(text)
    # parts: [preamble, id1, body1, id2, body2, ...]
    if len(parts) < 3:
        return entries
    it = iter(parts[1:])
    for entry_id in it:
        body = next(it, "")
        fields = {m.group(1): m.group(2).strip() for m in FIELD_RE.finditer(body)}
        slug = fields.get("slug", "").strip()
        status = fields.get("status", "").strip().lower()
        if not slug:
            continue
        entries.append({"id": entry_id, "slug": slug, "status": status})
    return entries


def _slug_dir_exists(slug: str) -> bool:
    path = SERVICES_DIR / slug
    return path.is_dir()


def next_service(queue_path: Path | None = None) -> dict[str, str] | None:
    path = queue_path or QUEUE_PATH
    if not path.is_file():
        return None
    text = path.read_text(encoding="utf-8")
    for entry in _parse_queue(text):
        if entry["status"] != "new":
            continue
        if entry["slug"] in SKIP_DIRS:
            continue
        if _slug_dir_exists(entry["slug"]):
            continue
        return entry
    return None


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument(
        "--queue",
        type=Path,
        default=QUEUE_PATH,
        help="Path to queue.md (default: pages/services/queue.md)",
    )
    args = parser.parse_args()

    entry = next_service(args.queue)
    if entry is None:
        print("NO_NEW_SERVICE")
        return 0
    print(f"{entry['id']}\t{entry['slug']}")
    return 0


if __name__ == "__main__":
    sys.exit(main())
