#!/usr/bin/env python3
"""
index.php услуг: вызовы lireate_* только из pages/services/_shared (как боевой _shared).
Явный запрет lireate_service_max_channel_url() — на проде нет.
"""

import re
import sys
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
SHARED = REPO / "pages" / "services" / "_shared"

BANNED = {"lireate_service_max_channel_url"}

DEF_RE = re.compile(r"function\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\(")
LIREATE_CALL_RE = re.compile(r"\b(lireate_[a-zA-Z0-9_]+)\s*\(")


def collect_shared_functions():
    names = set()
    for path in SHARED.rglob("*.php"):
        text = path.read_text(encoding="utf-8", errors="replace")
        for match in DEF_RE.finditer(text):
            names.add(match.group(1))
    return names


def local_functions(text):
    return {m.group(1) for m in DEF_RE.finditer(text)}


def audit_index(path, shared_funcs):
    text = path.read_text(encoding="utf-8", errors="replace")
    local = local_functions(text)
    allowed = shared_funcs | local
    errors = []
    for match in LIREATE_CALL_RE.finditer(text):
        name = match.group(1)
        if name in BANNED:
            errors.append("{0}: banned {1}()".format(path.relative_to(REPO), name))
        elif name not in allowed:
            errors.append("{0}: {1}() not in index or _shared".format(path.relative_to(REPO), name))
    return errors


def main():
    shared = collect_shared_functions()
    services = REPO / "pages" / "services"
    targets = [services / "_template" / "index.php"]
    targets += sorted(
        p / "index.php"
        for p in services.iterdir()
        if p.is_dir() and p.name not in ("_shared", "_template")
    )
    all_errors = []
    for path in targets:
        if path.is_file():
            all_errors.extend(audit_index(path, shared))
    if all_errors:
        for line in sorted(set(all_errors)):
            print("FAIL:", line)
        return 1
    print("OK: service index lireate_* callables ({0} files)".format(len(targets)))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())