#!/usr/bin/env python3
"""CI: full blog --apply simulation on fixture docroot + PHP 5.6 CGI render gate."""

from __future__ import annotations

import shutil
import sys
import tempfile
from pathlib import Path

SCRIPT_DIR = Path(__file__).resolve().parent
REPO = SCRIPT_DIR.parent
if str(SCRIPT_DIR) not in sys.path:
    sys.path.insert(0, str(SCRIPT_DIR))

import os
import subprocess

import lireate_blog_cgi_gate as cgi_gate  # noqa: E402
from lireate_cms_fixture_docroot import build_fixture_docroot  # noqa: E402


def main():
    php = cgi_gate.resolve_php_cgi_bin()
    if not php:
        print("BLOCKER: PHP 5.6 php-cgi required (set LIREATE_PHP_CGI_BIN or use setup-php 5.6)")
        return 2
    tmp = Path(tempfile.mkdtemp(prefix="lireate-blog-ci-fixture-"))
    try:
        docroot = build_fixture_docroot(tmp / "docroot", office_repo=REPO, mysql=True)
        list_tpl = REPO / "scripts" / "fixtures" / "cms-news-list-template.php"
        if list_tpl.is_file():
            dest = docroot / "template" / "page" / "news" / "lireate-blog-list-legacy.inc.php"
            dest.parent.mkdir(parents=True, exist_ok=True)
            shutil.copy2(list_tpl, dest)
        autopub = tmp / "autopub"
        autopub.mkdir(parents=True, exist_ok=True)
        env_path = autopub / ".env"
        env_path.write_text("LIREATE_DOCROOT={0}\n".format(docroot), encoding="utf-8")

        print("blog --apply on fixture docroot...")
        env = os.environ.copy()
        env["LIREATE_ENV_FILE"] = str(env_path)
        env["LIREATE_DOCROOT"] = str(docroot)
        env["LIREATE_AUTOPUB_HOME"] = str(autopub)
        env["LIREATE_OFFICE_REPO"] = str(REPO)
        env["LIREATE_BLOG_SKIP_PREAPPLY_GATE"] = "1"
        subprocess.check_call(
            [sys.executable, str(SCRIPT_DIR / "lireate_blog_install.py"), "--apply"],
            env=env,
            cwd=str(SCRIPT_DIR),
        )
        log_path = tmp / "cgi-error.log"
        fail, _sigs, _block = cgi_gate.run_cgi_suite(
            php, docroot, REPO, log_path
        )
        if fail:
            print("CGI FIXTURE GATE: FAIL")
            for line in fail[:40]:
                print("  - {0}".format(line))
            return 1
        print("CGI FIXTURE GATE: PASS")
        return 0
    finally:
        shutil.rmtree(tmp, ignore_errors=True)


if __name__ == "__main__":
    raise SystemExit(main())