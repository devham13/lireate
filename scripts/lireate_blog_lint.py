#!/usr/bin/env python3
"""Lint LIREATE blog article template."""

import json
import subprocess
import sys
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
REQUIRED = [
    REPO / "pages/blog/_shared/lireate-blog-config.php",
    REPO / "pages/blog/_shared/lireate-blog-lib.php",
    REPO / "pages/blog/_shared/lireate-blog-list-data.php",
    REPO / "pages/blog/_shared/lireate-blog-layout.inc.php",
    REPO / "pages/blog/_shared/lireate-blog-article.css",
    REPO / "pages/blog/_shared/lireate-blog-article.js",
    REPO / "pages/blog/_shared/lireate-blog-list.inc.php",
    REPO / "pages/blog/_shared/lireate-blog-list.css",
    REPO / "pages/blog/_preview/index.php",
    REPO / "pages/blog/_preview/list.php",
    REPO / "template/page/news/lireate-blog-cms.inc.php",
    REPO / "data/blog-articles-index.json",
]


def main():
    errors = []
    for path in REQUIRED:
        if not path.is_file():
            errors.append("missing {0}".format(path.relative_to(REPO)))

    idx = REPO / "data/blog-articles-index.json"
    if idx.is_file():
        data = json.loads(idx.read_text(encoding="utf-8"))
        arts = data.get("articles") or []
        if len(arts) < 3:
            errors.append("blog-articles-index needs at least 3 articles")
        for a in arts[:3]:
            if not a.get("url") or not a.get("title"):
                errors.append("invalid article entry in index")

    cfg = (REPO / "pages/blog/_shared/lireate-blog-config.php").read_text(encoding="utf-8")
    if "LIREATE_BLOG_MAX_CHANNEL_URL" not in cfg:
        errors.append("MAX URL constant missing")
    if "LIREATE_BLOG_AUTHOR_AVATAR_PATH" not in cfg:
        errors.append("author avatar path constant missing")
    for ext in (".jpg", ".webp"):
        av = REPO / ("template/images/blog/author/yuriy-gorbachev" + ext)
        if not av.is_file():
            errors.append("missing {0}".format(av.relative_to(REPO)))

    preview = subprocess.run(
        ["php", "-l", str(REPO / "pages/blog/_preview/index.php")],
        capture_output=True,
        text=True,
    )
    preview_list = subprocess.run(
        ["php", "-l", str(REPO / "pages/blog/_preview/list.php")],
        capture_output=True,
        text=True,
    )
    if preview.returncode != 0:
        errors.append("php -l preview: " + preview.stderr.strip())
    if preview_list.returncode != 0:
        errors.append("php -l list preview: " + preview_list.stderr.strip())

    lib = REPO / "pages/blog/_shared/lireate-blog-lib.php"
    test_html = '<h2>Раздел один</h2><p>x</p><h2>Второй</h2>'
    php = (
        "require '{0}'; "
        "$h = lireate_blog_prepare_body_html('{1}'); "
        "echo substr_count($h, 'id=') >= 2 ? 'OK' : 'FAIL';"
    ).format(lib, test_html.replace("'", "\\'"))
    r = subprocess.run(["php", "-r", php], capture_output=True, text=True)
    if "OK" not in (r.stdout or ""):
        errors.append("h2 id injection failed")

    if errors:
        for e in errors:
            print("ERROR:", e)
        return 1
    print("OK: blog template lint")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
