#!/usr/bin/env python3
"""Sync blog template assets into template/page/news and footer stub."""

import hashlib
import shutil
import sys
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
SHARED_BLOG = REPO / "pages" / "blog" / "_shared"
DEST_NEWS = REPO / "template" / "page" / "news"
DEST_LIREATE = REPO / "template" / "lireate"
FOOTER_SRC = REPO / "pages" / "services" / "_shared" / "lireate-service-footer.inc.php"
MESSENGER_SRC = REPO / "pages" / "services" / "_shared" / "lireate-messenger-urls.php"
DATA_INDEX = REPO / "data" / "blog-articles-index.json"

COPY_FILES = (
    "lireate-blog-config.php",
    "lireate-blog-lib.php",
    "lireate-blog-article.css",
    "lireate-blog-article.js",
    "lireate-blog-footer.css",
)


def sha(path):
    h = hashlib.sha256()
    with path.open("rb") as fh:
        for chunk in iter(lambda: fh.read(65536), b""):
            h.update(chunk)
    return h.hexdigest()


def copy_if_changed(src, dst):
    if dst.is_file() and sha(src) == sha(dst):
        return False
    dst.parent.mkdir(parents=True, exist_ok=True)
    shutil.copy2(str(src), str(dst))
    return True


def main():
    changed = []
    DEST_NEWS.mkdir(parents=True, exist_ok=True)
    for name in COPY_FILES:
        src = SHARED_BLOG / name
        if not src.is_file():
            print("BLOCKER: missing {0}".format(src))
            return 2
        dst = DEST_NEWS / name
        if copy_if_changed(src, dst):
            changed.append(str(dst.relative_to(REPO)))
    if FOOTER_SRC.is_file():
        dst = DEST_LIREATE / "lireate-service-footer.inc.php"
        if copy_if_changed(FOOTER_SRC, dst):
            changed.append(str(dst.relative_to(REPO)))
    if MESSENGER_SRC.is_file():
        dst = DEST_LIREATE / "lireate-messenger-urls.php"
        if copy_if_changed(MESSENGER_SRC, dst):
            changed.append(str(dst.relative_to(REPO)))
    if changed:
        print("synced: " + ", ".join(changed))
    else:
        print("OK: blog template up to date")
    if not DATA_INDEX.is_file():
        print("WARN: missing data/blog-articles-index.json")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
