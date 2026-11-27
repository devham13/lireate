#!/usr/bin/env python3
"""Inject sitewide sidebar V7 assets into template/main.php only (CMS /patent*, /news, …)."""
from __future__ import print_function
import sys
from pathlib import Path

_SCRIPTS = Path(__file__).resolve().parent
sys.path.insert(0, str(_SCRIPTS))
import lireate_sitewide_sidebar_v7_install as core  # noqa: E402


def run(argv=None):
    import argparse
    ap = argparse.ArgumentParser(description=__doc__)
    ap.add_argument("--docroot", required=True)
    ap.add_argument("--apply", action="store_true")
    ap.add_argument("--php-bin", default="php")
    args = ap.parse_args(argv)
    forwarded = [
        "--docroot", args.docroot,
        "--php-bin", args.php_bin,
    ]
    if args.apply:
        forwarded.append("--apply")
    return core.run(forwarded)


if __name__ == "__main__":
    raise SystemExit(run())