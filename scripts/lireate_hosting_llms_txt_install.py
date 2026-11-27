#!/usr/bin/env python3
"""Deploy combined llms.txt to document root (GEO/AEO). PHP 5.6 host may use Python 3.6."""
from __future__ import print_function

import argparse
import hashlib
import os
import shutil
import subprocess
import sys
from datetime import datetime, timezone

try:
    from pathlib import Path
except ImportError:
    Path = None


def sha256(path):
    h = hashlib.sha256()
    with open(str(path), "rb") as fh:
        for chunk in iter(lambda: fh.read(1024 * 1024), b""):
            h.update(chunk)
    return h.hexdigest()


def _repo_root():
    return os.path.dirname(os.path.dirname(os.path.abspath(__file__)))


def _build_combined(staging, docroot):
    repo = _repo_root()
    build = os.path.join(repo, "scripts", "lireate_seo_build_llms_txt.py")
    template = os.path.join(repo, "template", "llms.txt")
    base_url = os.environ.get("LIREATE_PUBLIC_SITE_URL", "https://lireate.ru").strip()
    news_php = os.environ.get(
        "LIREATE_NEWS_DB_PHP",
        os.path.expanduser("~/lireate-autopub/repo/scripts/lireate_db_publish.php"),
    )
    if not os.path.isfile(build):
        with open(template, encoding="utf-8") as fh:
            body = fh.read()
        host = base_url.replace("https://", "").replace("http://", "").split("/")[0]
        with open(staging, "w", encoding="utf-8") as out:
            out.write(body.replace("${PUBLIC_SITE_HOST}", host))
        return
    subprocess.check_call(
        [
            sys.executable,
            build,
            "--output",
            staging,
            "--docroot",
            docroot,
            "--base-url",
            base_url,
            "--news-php",
            news_php,
        ]
    )


def install(docroot, dry_run=False):
    repo = _repo_root()
    template = os.path.join(repo, "template", "llms.txt")
    if not os.path.isfile(template):
        sys.exit("BLOCKER: missing template/llms.txt")
    target = os.path.join(docroot, "llms.txt")
    staging = os.path.join(docroot, "llms.txt.build-staging")
    if dry_run:
        print("llms.txt: would build+install -> {0}".format(target))
        return 0
    _build_combined(staging, docroot)
    if os.path.isfile(target) and sha256(target) == sha256(staging):
        if os.path.isfile(staging):
            os.unlink(staging)
        print("llms.txt: already up to date")
        return 0
    if os.path.isfile(target):
        stamp = datetime.now(timezone.utc).strftime("%Y%m%dT%H%M%SZ")
        backup = target + ".bak-" + stamp
        shutil.copy2(target, backup)
        print("backup:", backup)
    shutil.copy2(staging, target)
    if os.path.isfile(staging):
        os.unlink(staging)
    print("llms.txt: installed ({0} bytes)".format(os.path.getsize(target)))
    return 0


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--docroot", required=True)
    ap.add_argument("--check", action="store_true")
    args = ap.parse_args()
    docroot = os.path.abspath(os.path.expanduser(args.docroot))
    return install(docroot, dry_run=args.check)


if __name__ == "__main__":
    raise SystemExit(main())