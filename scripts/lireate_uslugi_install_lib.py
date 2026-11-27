"""Uslugi hub (/uslugi/) Light V4 install helpers."""

import re
import shutil
from datetime import datetime, timezone
from pathlib import Path

REPO_FILES = (
    Path("template/page/uslugi/lireate-uslugi-cms.inc.php"),
    Path("template/page/uslugi/lireate-uslugi-lib.php"),
    Path("template/page/uslugi/lireate-uslugi.css"),
    Path("template/css/lireate-cms-pages-light-v4.css"),
)

MAIN_REL = Path("template/main.php")
USLUGI_BODY_OLD = "if($temp['page']=='uslugi') echo ' lireate-uslugi-body';"
USLUGI_BODY_NEW = (
    "if($temp['page']=='uslugi') echo ' lireate-uslugi-body lireate-theme-light-v4';"
)


def make_backup_id():
    return datetime.now(timezone.utc).strftime("%Y%m%d-%H%M%S")


def copy_plan(office_repo):
    repo = Path(office_repo).resolve()
    pairs = []
    for rel in REPO_FILES:
        src = repo / rel
        if not src.is_file():
            return None, "BLOCKER: missing {0}".format(rel)
        pairs.append((src, rel))
    return pairs, None


def patch_main_uslugi_light(text):
    """Stop loading legacy dark CMS bundle on /uslugi/; add Light V4 body token."""
    planned = text
    changed = False
    for pattern in (r",\s*'uslugi'\s*", r"'uslugi'\s*,\s*"):
        nxt = re.sub(pattern, "", planned)
        if nxt != planned:
            planned = nxt
            changed = True
    if USLUGI_BODY_OLD in planned and USLUGI_BODY_NEW not in planned:
        planned = planned.replace(USLUGI_BODY_OLD, USLUGI_BODY_NEW, 1)
        changed = True
    return planned, changed