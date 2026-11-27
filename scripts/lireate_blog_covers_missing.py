#!/usr/bin/env python3
"""List blog articles whose list-cover candidates are missing on disk (SSH or local DOCROOT)."""

from __future__ import annotations

import argparse
import json
import os
import re
import shutil
import subprocess
from pathlib import Path

IMG_RE = re.compile(r"""src\s*=\s*['"]([^'"]+)""", re.I)


def candidate_paths(slug: str, body: str) -> list[str]:
    paths: list[str] = []
    for m in IMG_RE.finditer(body or ""):
        src = m.group(1)
        base = os.path.basename(src.split("?")[0])
        if base:
            paths.append(f"/template/images/blog/small/{base}")
            paths.append(f"/template/images/blog/{base}")
    for ext in ("jpg", "jpeg", "webp", "png"):
        paths.append(f"/template/images/blog/small/{slug}.{ext}")
        paths.append(f"/template/images/blog/{slug}.{ext}")
    out: list[str] = []
    for p in paths:
        if p not in out:
            out.append(p)
    return out


def main() -> int:
    ap = argparse.ArgumentParser()
    ap.add_argument("--export", type=Path, required=True)
    ap.add_argument("--docroot", type=Path, help="local document root; if set, check files")
    ap.add_argument("--sync-small", action="store_true", help="copy blog/*.jpg to blog/small/ when missing")
    ap.add_argument("--json-out", type=Path)
    args = ap.parse_args()
    data = json.loads(args.export.read_text(encoding="utf-8"))
    missing_rows = []
    synced = 0
    docroot = args.docroot
    for art in data.get("articles") or []:
        slug = (art.get("seo_url") or "").strip("/")
        if not slug:
            continue
        body = art.get("full_story") or ""
        found = False
        tried = []
        for rel in candidate_paths(slug, body):
            tried.append(rel)
            if docroot:
                if (docroot / rel.lstrip("/")).is_file():
                    found = True
                    break
        if found:
            continue
        if args.sync_small and docroot:
            for ext in ("jpg", "jpeg", "webp", "png"):
                src = docroot / "template/images/blog" / f"{slug}.{ext}"
                if not src.is_file():
                    continue
                dst = docroot / "template/images/blog/small" / src.name
                dst.parent.mkdir(parents=True, exist_ok=True)
                if not dst.is_file():
                    shutil.copy2(src, dst)
                    synced += 1
                found = dst.is_file()
                break
        if not found:
            missing_rows.append({"slug": slug, "candidates": tried[:6]})
    if args.json_out:
        args.json_out.write_text(json.dumps({"missing": missing_rows, "synced": synced}, indent=2), encoding="utf-8")
    print(f"missing={len(missing_rows)} synced={synced}")
    for row in missing_rows[:20]:
        print(row["slug"])
    return 0 if not missing_rows else 1


if __name__ == "__main__":
    raise SystemExit(main())