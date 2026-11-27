#!/usr/bin/env python3
"""Verify robots.txt Clean-param lines on live site (read-only)."""
from __future__ import print_function

import os
import sys
import urllib.request

REQUIRED = (
    "Clean-param: utm_source",
    "Clean-param: yclid",
    "Clean-param: mode",
)


def main():
    base = (os.environ.get("LIREATE_PUBLIC_SITE_URL") or "https://lireate.ru").rstrip("/")
    url = base + "/robots.txt"
    try:
        with urllib.request.urlopen(url, timeout=25) as resp:
            body = resp.read().decode("utf-8", errors="replace")
    except Exception as exc:
        print("BLOCKER: robots fetch failed:", exc)
        return 2
    missing = [line for line in REQUIRED if line not in body]
    if missing:
        print("STATUS: ROBOTS_CLEANPARAM_MISSING", missing)
        return 1
    print("STATUS: ROBOTS_CLEANPARAM_OK")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())