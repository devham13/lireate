#!/usr/bin/env python3
"""Lint PHP/HTML for broken href/src quotes (SEO-audit link patcher guard)."""

from __future__ import annotations

import re
import shutil
import subprocess
import sys
from html.parser import HTMLParser
from pathlib import Path

HREF_OPEN_RE = re.compile(r"\bhref\s*=\s*\"", re.IGNORECASE)


class _FragParser(HTMLParser):
    def __init__(self):
        super().__init__()
        self.errors: list[str] = []

    def handle_starttag(self, tag, attrs):
        for name, value in attrs:
            if name in ("href", "src", "action") and value is None:
                self.errors.append("{0}: empty {1}".format(tag, name))


def _scan_unclosed_hrefs(path_label: str, text: str) -> list[str]:
    issues: list[str] = []
    for m in HREF_OPEN_RE.finditer(text):
        i = m.end()
        closed = False
        while i < len(text):
            if text.startswith("?>", i):
                i += 2
                continue
            ch = text[i]
            if ch == '"':
                closed = True
                break
            if ch == ">" and (i == 0 or text[i - 1] != "?"):
                snippet = text[m.start() : min(i + 24, len(text))].replace("\n", " ")
                issues.append("{0}: unclosed href: {1}".format(path_label, snippet))
                break
            i += 1
        if not closed and i >= len(text):
            issues.append("{0}: unclosed href at EOF".format(path_label))
    return issues


def lint_text(path_label: str, text: str) -> list[str]:
    issues = _scan_unclosed_hrefs(path_label, text)
    if "<" in text and ">" in text:
        try:
            parser = _FragParser()
            parser.feed(text)
            issues.extend(parser.errors)
        except Exception as exc:  # noqa: BLE001
            issues.append("{0}: HTMLParser: {1}".format(path_label, exc))
    return issues


def lint_paths(paths: list[Path], *, php_bin: str = "php") -> list[str]:
    issues: list[str] = []
    for path in paths:
        if not path.is_file():
            continue
        try:
            text = path.read_text(encoding="utf-8", errors="replace")
        except OSError as exc:
            issues.append("{0}: read: {1}".format(path, exc))
            continue
        issues.extend(lint_text(str(path), text))
        if path.suffix.lower() == ".php" and shutil.which(php_bin):
            proc = subprocess.run(
                [php_bin, "-l", str(path)],
                stdout=subprocess.PIPE,
                stderr=subprocess.PIPE,
                text=True,
            )
            if proc.returncode != 0:
                issues.append("{0}: php -l: {1}".format(path, (proc.stderr or proc.stdout).strip()))
    return issues


def main(argv: list[str] | None = None) -> int:
    args = argv if argv is not None else sys.argv[1:]
    if not args:
        print("usage: lireate_seo_audit_markup_lint.py <files...>", file=sys.stderr)
        return 2
    paths = [Path(a) for a in args]
    issues = lint_paths(paths)
    if issues:
        for line in issues:
            print(line)
        return 1
    print("OK: {0} file(s)".format(len(paths)))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())