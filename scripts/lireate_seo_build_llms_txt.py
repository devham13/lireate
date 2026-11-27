#!/usr/bin/env python3
"""Build combined /llms.txt: office template + blog article index from live DB (no article edits)."""

from __future__ import print_function

import argparse
import json
import os
import re
import subprocess
import sys
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
TEMPLATE = REPO / "template" / "llms.txt"
BLOG_SECTION = "## Статьи блога"
MAX_BLOG = 400


def site_host(base_url):
    base = (base_url or "").strip().rstrip("/")
    if not base:
        return "lireate.ru"
    return re.sub(r"^https?://", "", base).split("/")[0]


def load_template(base_url):
    text = TEMPLATE.read_text(encoding="utf-8")
    host = site_host(base_url)
    return text.replace("${PUBLIC_SITE_HOST}", host)


def parse_sitemap_json(stdout):
    data = json.loads(stdout.strip())
    if not data.get("ok"):
        return None, data.get("error", "sitemap_urls failed")
    entries = []
    for row in data.get("rows") or []:
        seo = (row.get("seo_url") or "").strip()
        title = (row.get("seo_title") or row.get("title") or "").strip()
        if not seo or not title:
            continue
        desc = (row.get("seo_description") or "").strip()
        if desc.lower() == "workflow":
            desc = ""
        entries.append({"seo_url": seo, "title": title[:160], "description": desc[:220]})
    entries.sort(key=lambda x: x["seo_url"])
    return entries[:MAX_BLOG], None


def blog_via_local_php(docroot, php_path):
    php_path = os.path.expanduser(os.path.expandvars(php_path))
    if not os.path.isfile(php_path):
        return None, "missing php script {0}".format(php_path)
    proc = subprocess.run(
        ["php", php_path, "sitemap_urls", docroot.rstrip("/")],
        stdout=subprocess.PIPE,
        stderr=subprocess.PIPE,
        universal_newlines=True,
    )
    if proc.returncode != 0:
        return None, proc.stderr or proc.stdout or "local php error"
    try:
        return parse_sitemap_json(proc.stdout)
    except ValueError as exc:
        return None, str(exc)


def blog_via_ssh(docroot, php_rel):
    key = os.environ.get("LIREATE_SSH_KEY", "").strip()
    host = os.environ.get("LIREATE_SSH_HOST", "").strip()
    user = os.environ.get("LIREATE_SSH_USER", "").strip()
    if not (key and host and user):
        return None, "ssh env missing"
    key_file = Path("/tmp/lireate_seo_ssh_key")
    key_file.write_text(key + "\n", encoding="utf-8")
    key_file.chmod(0o600)
    remote = (
        "php {php} sitemap_urls {doc}".format(php=php_rel, doc=docroot.rstrip("/"))
    )
    cmd = [
        "ssh",
        "-4",
        "-i",
        str(key_file),
        "-o",
        "StrictHostKeyChecking=no",
        "-o",
        "ConnectTimeout=35",
        "{0}@{1}".format(user, host),
        remote,
    ]
    proc = subprocess.run(cmd, stdout=subprocess.PIPE, stderr=subprocess.PIPE, universal_newlines=True)
    if proc.returncode != 0:
        return None, proc.stderr or proc.stdout or "ssh error"
    try:
        return parse_sitemap_json(proc.stdout)
    except ValueError as exc:
        return None, str(exc)


def merge_document(base_text, blog_entries, base_url):
    base = base_url.rstrip("/")
    if not blog_entries:
        return base_text
    lines = [base_text.rstrip(), "", BLOG_SECTION, ""]
    for e in blog_entries:
        url = "{0}/{1}/".format(base, e["seo_url"].strip("/"))
        desc = e.get("description") or ""
        lines.append("- [{0}]({1}): {2}".format(e["title"], url, desc))
    lines.append("")
    return "\n".join(lines)


def main():
    ap = argparse.ArgumentParser(description=__doc__)
    ap.add_argument("--base-url", default=os.environ.get("LIREATE_PUBLIC_SITE_URL", "https://lireate.ru"))
    ap.add_argument("--docroot", default="/var/www/u3650956/data/www/public_html")
    ap.add_argument(
        "--news-php",
        default=os.environ.get(
            "LIREATE_NEWS_DB_PHP",
            "/var/www/u3650956/data/lireate-autopub/repo/scripts/lireate_db_publish.php",
        ),
    )
    ap.add_argument("--output", type=Path, required=True)
    ap.add_argument("--no-blog", action="store_true")
    args = ap.parse_args()
    body = load_template(args.base_url)
    blog_count = 0
    if not args.no_blog:
        news_php = os.path.expanduser(os.path.expandvars(args.news_php))
        entries, err = blog_via_ssh(args.docroot, news_php)
        if entries is None:
            entries, err_local = blog_via_local_php(args.docroot, news_php)
            if entries is None:
                print(
                    "WARN: blog section skipped: ssh={0} local={1}".format(err, err_local),
                    file=sys.stderr,
                )
                entries = []
        body = merge_document(body, entries, args.base_url.rstrip("/"))
        blog_count = len(entries)
    args.output.parent.mkdir(parents=True, exist_ok=True)
    args.output.write_text(body, encoding="utf-8")
    print("llms: {0} bytes, blog_entries={1}".format(len(body.encode("utf-8")), blog_count))
    return 0


if __name__ == "__main__":
    sys.exit(main())