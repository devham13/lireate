#!/usr/bin/env python3
"""
CI: validate SEO data bundle (no HTTP). For live registry use lireate_seo_sitemap_crawl.py.
"""

from __future__ import print_function

import json
import re
import subprocess
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
DATA = ROOT / "data"
SCRIPTS = Path(__file__).resolve().parent

if str(SCRIPTS) not in sys.path:
    sys.path.insert(0, str(SCRIPTS))

import lireate_seo_pages_guard_lib as guard  # noqa: E402

REQUIRED_PHP = (
    "lireate-seo-head.inc.php",
    "lireate-seo-page-meta.inc.php",
    "lireate-seo-runtime.php",
    "lireate-seo-schema.inc.php",
)


def main():
    errors = []
    for name in REQUIRED_PHP:
        path = DATA / name
        if not path.is_file():
            errors.append("missing {0}".format(name))
            continue
        php_bin = "php"
        for candidate in ("php", "php8.3", "php8.2", "php7.4"):
            from shutil import which

            if which(candidate):
                php_bin = candidate
                break
        proc = subprocess.run(
            [php_bin, "-l", str(path)],
            capture_output=True,
            text=True,
        )
        if proc.returncode != 0:
            errors.append("php -l {0}: {1}".format(name, proc.stderr or proc.stdout))

    mktu = DATA / "lireate-seo-mktu-classes.json"
    if mktu.is_file():
        data = json.loads(mktu.read_text(encoding="utf-8"))
        if len(data) != 45:
            errors.append("mktu-classes expected 45 keys, got {0}".format(len(data)))
    else:
        errors.append("missing lireate-seo-mktu-classes.json")

    meta = DATA / "lireate-seo-page-meta.inc.php"
    if meta.is_file():
        text = meta.read_text(encoding="utf-8")
        if "??" in text or "catch (Error" in text:
            errors.append("PHP 7+ syntax in page-meta")

    rt = DATA / "lireate-seo-runtime.php"
    if rt.is_file() and not re.search(r"\(\^\|\&\)mode\(\$\|=\)", rt.read_text(encoding="utf-8")):
        errors.append("mode=noindex regex not hardened")

    errors.extend(guard.validate_mktu_classes())
    errors.extend(guard.validate_cms_overrides())

    changed = []
    try:
        import subprocess

        out = subprocess.check_output(
            ["git", "diff", "--name-only", "origin/main...HEAD"],
            cwd=str(ROOT),
            stderr=subprocess.DEVNULL,
            text=True,
        )
        changed = [line.strip() for line in out.splitlines() if line.strip()]
    except (subprocess.CalledProcessError, FileNotFoundError, OSError):
        changed = None
    if changed:
        errors.extend(guard.validate_changed_services(changed))

    if errors:
        print("\n".join(errors))
        return 1
    print("STATUS: SEO_REGISTRY_CHECK_OK")
    return 0


if __name__ == "__main__":
    sys.exit(main())