#!/usr/bin/env python3
"""
Yandex-oriented robots.txt checks (analyzer-style, read-only on live).

Refs: Yandex Webmaster robots.txt / Clean-param; duplicate query via Clean-param not Disallow */?.
"""
from __future__ import print_function

import os
import re
import sys
from pathlib import Path

import urllib.request

ROOT = Path(__file__).resolve().parents[1]
RECOMMENDED = ROOT / "shared" / "robots.txt.yandex-recommended"


def fetch_body(url):
    with urllib.request.urlopen(url, timeout=25) as resp:
        return resp.read().decode("utf-8", errors="replace")


def analyze(body):
    errors = []
    warnings = []
    lines = [ln.rstrip() for ln in body.splitlines()]
    for i, ln in enumerate(lines, 1):
        if not ln or ln.lstrip().startswith("#"):
            continue
        if re.match(r"^\s*Disallow:\s*/\?", ln, re.I):
            errors.append(
                "L{0}: Disallow с «/?…» — в Яндексе лучше Clean-param, не Disallow query".format(i)
            )
        if re.match(r"^\s*Disallow:\s*/\*\?", ln, re.I):
            errors.append(
                "L{0}: Disallow «/*?…» — типичная ERROR_IN_ROBOTS_TXT; используйте Clean-param".format(i)
            )
        m = re.match(r"^\s*Clean-param:\s*(.+?)\s+(\S+)\s*$", ln, re.I)
        if m:
            params, path = m.group(1), m.group(2)
            if "&" not in params and " " in params.strip():
                warnings.append("L{0}: Clean-param — параметры через «&», без пробелов".format(i))
            if not path.startswith("/"):
                errors.append("L{0}: Clean-param path должен начинаться с /".format(i))
    if "Clean-param:" in body and "mode" not in body:
        warnings.append("Нет Clean-param для mode/test (дубли ?mode=)")
    if "Sitemap:" not in body:
        warnings.append("Нет директивы Sitemap:")
    return errors, warnings


def main():
    base = (os.environ.get("LIREATE_PUBLIC_SITE_URL") or "https://lireate.ru").rstrip("/")
    url = base + "/robots.txt"
    try:
        live = fetch_body(url)
    except Exception as exc:
        print("BLOCKER: robots fetch:", exc)
        return 2
    err, warn = analyze(live)
    if RECOMMENDED.is_file():
        ref_err, _ = analyze(RECOMMENDED.read_text(encoding="utf-8"))
        if not ref_err:
            print("HINT: shared/robots.txt.yandex-recommended passes same checks")
    for w in warn:
        print("WARN:", w)
    if err:
        print("STATUS: YANDEX_ROBOTS_ERRORS")
        for e in err:
            print(" ", e)
        return 1
    print("STATUS: YANDEX_ROBOTS_OK")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())