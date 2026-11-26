#!/usr/bin/env python3
"""Redact credentials from a live template/main.php copy before committing as fixture."""

import re
import sys
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
DEFAULT_OUT = REPO / "tests/fixtures/live-render/live-assets/template/main.php"

RULES = [
    (re.compile(r"mysqli_connect\s*\(\s*['\"][^'\"]+['\"]\s*,\s*['\"][^'\"]+['\"]\s*,\s*['\"][^'\"]+['\"]"), "mysqli_connect('127.0.0.1','FIXTURE','FIXTURE'"),
    (re.compile(r"['\"]password['\"]\s*=>\s*['\"][^'\"]+['\"]"), "'password' => 'FIXTURE'"),
    (re.compile(r"['\"]passwd['\"]\s*=>\s*['\"][^'\"]+['\"]"), "'passwd' => 'FIXTURE'"),
    (re.compile(r"\$mysql_password\s*=\s*['\"][^'\"]+['\"]"), "$mysql_password = 'FIXTURE'"),
    (re.compile(r"\$mysql_login\s*=\s*['\"][^'\"]+['\"]"), "$mysql_login = 'FIXTURE'"),
]


def sanitize(text):
    out = text
    for pattern, repl in RULES:
        out = pattern.sub(repl, out)
    return out


def main():
    src = Path(sys.argv[1]) if len(sys.argv) > 1 else None
    dest = Path(sys.argv[2]) if len(sys.argv) > 2 else DEFAULT_OUT
    if not src or not src.is_file():
        print("usage: sanitize_live_main_php_fixture.py <src.php> [dest.php]")
        return 2
    body = sanitize(src.read_text(encoding="utf-8", errors="replace"))
    if "FIXTURE" not in body and ("password" in body.lower() or "mysqli_connect" in body):
        print("WARN: possible uncaught credentials — review before commit")
    dest.parent.mkdir(parents=True, exist_ok=True)
    header = (
        "<?php\n"
        "/** Sanitized live template/main.php (CI). Credentials → FIXTURE. */\n"
        "?>\n"
    )
    if body.lstrip().startswith("<?php"):
        dest.write_text(body, encoding="utf-8")
    else:
        dest.write_text(header + body, encoding="utf-8")
    print("saved: {0} ({1} bytes)".format(dest, dest.stat().st_size))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())