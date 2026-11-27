"""Isolated /avtor/ CMS install — no sitewide sidebar/js deploy."""

from __future__ import print_function

import datetime
import hashlib
import shutil
import subprocess
import sys
import tempfile
from pathlib import Path

if str(Path(__file__).resolve().parent) not in sys.path:
    sys.path.insert(0, str(Path(__file__).resolve().parent))

import lireate_cms_main_body_class as cms_body  # noqa: E402

INSTALL_MARKER = "lireate-avtor-install:v1"
SHARED_REL = Path("pages/avtor/_shared")
TEMPLATE_REL = Path("template/page/avtor")
AVTOR_PAGE_REL = Path("template/page/avtor.php")

COPY_NAMES = (
    "lireate-avtor-config.php",
    "lireate-avtor-data.php",
    "lireate-avtor-lib.php",
    "lireate-avtor-page.inc.php",
    "lireate-avtor-cms.inc.php",
    "lireate-avtor.css",
)

# Explicit exclude: sitewide assets must not be part of /avtor/ rollout.
NEVER_DEPLOY = (
    "template/js/lireate-sitewide-sidebar-v7.js",
    "template/css/lireate-sitewide-sidebar-v7.css",
    "template/lireate/lireate-sidebar-v7.html",
    "template/css/lireate-cms-pages-light-v4.css",
    "data/lireate-seo-",
)


def deploy_file_list():
    items = []
    for name in COPY_NAMES:
        items.append(str(SHARED_REL / name))
        items.append(str(TEMPLATE_REL / name))
    items.append(str(AVTOR_PAGE_REL))
    items.append("template/main.php  # patch only: avtor body classes")
    return items


def copy_plan(office_repo):
    repo = Path(office_repo).resolve()
    pairs = []
    shared = repo / SHARED_REL
    for name in COPY_NAMES:
        src = shared / name
        if src.is_file():
            pairs.append((src, SHARED_REL / name))
            pairs.append((src, TEMPLATE_REL / name))
    page_php = repo / AVTOR_PAGE_REL
    if page_php.is_file():
        pairs.append((page_php, AVTOR_PAGE_REL))
    return pairs


def find_main_template(docroot):
    main = Path(docroot) / "template" / "main.php"
    return main if main.is_file() else None


def plan_main_php(text):
    return cms_body.patch_avtor_body_inline(text)[0]


def resolve_php_bin():
    for candidate in ("php", "php8.3", "php8.2", "php7.4"):
        path = shutil.which(candidate)
        if path:
            return path
    return "php"


def lint_php_content(php_bin, content, label="patched.php"):
    tmp = tempfile.NamedTemporaryFile("w", suffix=".php", delete=False, encoding="utf-8")
    try:
        tmp.write(content)
        tmp.close()
        proc = subprocess.run([php_bin, "-l", tmp.name], capture_output=True, text=True, timeout=30)
        if proc.returncode != 0:
            return (proc.stdout or "") + (proc.stderr or "") or "php -l failed for {0}".format(label)
        return None
    finally:
        Path(tmp.name).unlink(missing_ok=True)


def apply_copies(office_repo, docroot, pairs):
    created = []
    docroot = Path(docroot)
    for src, rel in pairs:
        dst = docroot / rel
        dst.parent.mkdir(parents=True, exist_ok=True)
        if dst.is_file():
            if hashlib.sha256(dst.read_bytes()).digest() == hashlib.sha256(src.read_bytes()).digest():
                continue
        shutil.copy2(str(src), str(dst))
        if not dst.is_file():
            created.append(rel)
    return created


def apply_main_patch(docroot, php_bin):
    main = find_main_template(docroot)
    if main is None:
        return ["WARN: template/main.php not found"]
    original = main.read_text(encoding="utf-8", errors="replace")
    planned, changed = cms_body.patch_avtor_body_inline(original)
    if not changed:
        return ["main.php: avtor body already patched"]
    err = lint_php_content(php_bin, planned, "template/main.php")
    if err:
        raise RuntimeError("main.php lint failed: {0}".format(err))
    main.write_text(planned, encoding="utf-8")
    return ["patched template/main.php (avtor body classes only)"]


def make_backup_id():
    return datetime.datetime.now(datetime.timezone.utc).strftime("%Y%m%d-%H%M%S")