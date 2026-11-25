#!/usr/bin/env python3
"""Build fixture DOCROOT, apply Light V4 installers, print docroot path for php -S router."""

from __future__ import annotations

import os
import shutil
import subprocess
import sys
import tempfile
from pathlib import Path

SCRIPT_DIR = Path(__file__).resolve().parent
REPO = SCRIPT_DIR.parent
sys.path.insert(0, str(SCRIPT_DIR))

from lireate_cms_fixture_docroot import build_fixture_docroot  # noqa: E402


def main():
    out = os.environ.get("LIREATE_FIXTURE_DOCROOT", "").strip()
    if not out:
        out = str(Path(tempfile.mkdtemp(prefix="lireate-screenshot-docroot-")))
    out = Path(out)
    mysql = os.environ.get("LIREATE_SKIP_FIXTURE_MYSQL", "").strip() != "1"
    build_fixture_docroot(out, office_repo=REPO, mysql=mysql)
    env = os.environ.copy()
    env["LIREATE_DOCROOT"] = str(out)
    _log = sys.stderr
    subprocess.check_call(
        [sys.executable, str(SCRIPT_DIR / "lireate_home_light_v4_install.py"), "--docroot", str(out), "--apply"],
        env=env,
        stdout=_log,
        stderr=_log,
    )
    subprocess.check_call(
        [sys.executable, str(SCRIPT_DIR / "lireate_cms_light_v4_overlay_install.py"), "--docroot", str(out), "--apply"],
        env=env,
        stdout=_log,
        stderr=_log,
    )
    subprocess.check_call(
        [sys.executable, str(SCRIPT_DIR / "lireate_hosting_pf_install.py"), "--docroot", str(out), "--apply"],
        env=env,
        stdout=_log,
        stderr=_log,
    )
    list_tpl = REPO / "scripts" / "fixtures" / "cms-news-list-template.php"
    if list_tpl.is_file():
        dest = out / "template" / "page" / "news" / "lireate-blog-list-legacy.inc.php"
        dest.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(list_tpl, dest)
    import lireate_blog_install_lib as blog_lib  # noqa: E402

    blog_lib.apply_blog_install_to_docroot(REPO, out)
    print(str(out))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())