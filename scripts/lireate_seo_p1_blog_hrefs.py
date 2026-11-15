#!/usr/bin/env python3
"""
SEO P1 (15.11.2026): fix broken internal hrefs in CMS `news.full_story` (hosting only).

--check  Dry-run: list seo_url + replacement (live curl and/or MySQL).
--apply  mysqldump backup, UPDATE via SQL REPLACE (no full_story round-trip in Python).
--restore <dir>  Reload news_rows.sql from a backup directory.

Does not read data/db.php.
"""
from __future__ import print_function

import argparse
import base64
import json
import os
import re
import subprocess
import sys
import tempfile
import time
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
SCRIPTS = Path(__file__).resolve().parent
if str(SCRIPTS) not in sys.path:
    sys.path.insert(0, str(SCRIPTS))

from lireate_dotenv import load_env_file
from lireate_seo_p1_blog_hrefs_lib import (
    DONOR_PATHS,
    all_replacement_pairs,
    build_replace_expression,
    rows_for_story,
    verify_story_body,
)


def autopub_home():
    return Path(os.environ.get("LIREATE_AUTOPUB_HOME", str(Path.home() / "lireate-autopub"))).expanduser()


def default_env_file():
    explicit = os.environ.get("LIREATE_ENV_FILE", "").strip()
    if explicit:
        return Path(explicit).expanduser()
    return autopub_home() / ".env"


def ensure_mysql_env(env_file=None):
    if env_file:
        load_env_file(env_file, override=False)
    elif not os.environ.get("LIREATE_MYSQL_HOST"):
        load_env_file(default_env_file(), override=False)
    keys = ("LIREATE_MYSQL_HOST", "LIREATE_MYSQL_USER", "LIREATE_MYSQL_PASSWORD", "LIREATE_MYSQL_DATABASE")
    missing = [k for k in keys if not os.environ.get(k, "").strip()]
    if missing:
        raise SystemExit("BLOCKER: missing env: {0}".format(", ".join(missing)))


def mysql_env():
    return {
        "host": os.environ["LIREATE_MYSQL_HOST"],
        "user": os.environ["LIREATE_MYSQL_USER"],
        "password": os.environ["LIREATE_MYSQL_PASSWORD"],
        "database": os.environ["LIREATE_MYSQL_DATABASE"],
    }


def mysql_env_ok():
    try:
        ensure_mysql_env()
        return True
    except SystemExit:
        return False


def _mysql_subenv(cfg):
    env = os.environ.copy()
    env["MYSQL_PWD"] = cfg["password"]
    return env


def mysql_cmd_base(cfg, raw=False):
    cmd = [
        "mysql",
        "-h",
        cfg["host"],
        "-u",
        cfg["user"],
        "-D",
        cfg["database"],
        "-N",
        "-B",
    ]
    if raw:
        cmd.append("--raw")
    return cmd


def mysql_exec(cfg, sql, raw=False):
    cmd = mysql_cmd_base(cfg, raw=raw) + ["-e", sql]
    return subprocess.check_output(cmd, env=_mysql_subenv(cfg), text=True, errors="replace")


def mysql_exec_file(cfg, sql_body):
    fd, path = tempfile.mkstemp(prefix="lireate-blog-hrefs-", suffix=".sql", text=True)
    os.close(fd)
    try:
        Path(path).write_text(sql_body, encoding="utf-8")
        cmd = mysql_cmd_base(cfg, raw=False) + ["--binary-mode=1"]
        with open(path, "rb") as fh:
            subprocess.check_call(cmd, stdin=fh, env=_mysql_subenv(cfg))
    finally:
        try:
            os.unlink(path)
        except OSError:
            pass


def try_pymysql_apply(cfg, updates):
    try:
        import pymysql
    except ImportError:
        return False
    conn = pymysql.connect(
        host=cfg["host"],
        user=cfg["user"],
        password=cfg["password"],
        database=cfg["database"],
        charset="utf8mb4",
        autocommit=False,
    )
    try:
        with conn.cursor() as cur:
            pairs = all_replacement_pairs()
            for row_id in updates:
                expr = build_replace_expression("full_story", pairs)
                sql = "UPDATE news SET full_story = {0} WHERE id = %s".format(expr)
                cur.execute(sql, (row_id,))
        conn.commit()
    finally:
        conn.close()
    return True


def site_base():
    base = (
        os.environ.get("LIREATE_PUBLIC_SITE_URL")
        or os.environ.get("PUBLIC_SITE_URL")
        or ""
    ).rstrip("/")
    if not base and os.environ.get("PUBLIC_SITE_HOST", "").strip():
        base = "https://" + os.environ["PUBLIC_SITE_HOST"].strip().lstrip("https://").lstrip("http://")
    return base


def fetch_live_article(path):
    base = site_base()
    if not base:
        raise SystemExit("BLOCKER: set LIREATE_PUBLIC_SITE_URL for --source live")
    url = base + "/" + path + "/"
    proc = subprocess.run(
        ["curl", "-sSL", "-o", "/dev/null", "-w", "%{http_code}", url],
        capture_output=True,
        text=True,
    )
    if proc.stdout.strip() != "200":
        return None, url, proc.stdout.strip()
    html = subprocess.check_output(["curl", "-sSL", url], text=True, errors="replace")
    m = re.search(r'<div\s+id=["\']textBox["\'][^>]*>(.*)</div>', html, re.I | re.S)
    if m:
        return m.group(1), url, "200"
    return html, url, "200"


def check_live():
    rows = []
    skip_count = 0
    for path in DONOR_PATHS:
        body, url, code = fetch_live_article(path)
        if body is None:
            print("SKIP (not {0}): {1}  ({2})".format(code, path, url))
            skip_count += 1
            continue
        rows.extend(rows_for_story(path, body))
    return rows, skip_count


def resolve_donor_rows(cfg):
    """Return list of dicts {id, seo_url, path} without reading full_story."""
    seen = {}
    for path in DONOR_PATHS:
        esc = path.replace("'", "''")
        sql = (
            "SELECT id, seo_url FROM news WHERE seo_url = '{0}' OR alt_name = '{0}' LIMIT 1".format(esc)
        )
        raw = mysql_exec(cfg, sql).strip()
        if not raw or "\t" not in raw:
            print("SKIP (no row):", path)
            continue
        rid, slug = raw.split("\t", 1)
        rid = int(rid)
        if rid not in seen:
            seen[rid] = {"id": rid, "seo_url": slug, "paths": [path]}
        else:
            seen[rid]["paths"].append(path)
    return sorted(seen.values(), key=lambda r: r["id"])


def decode_full_story_b64(b64_text):
    return base64.b64decode(b64_text.encode("ascii"), validate=True).decode("utf-8")


def fetch_full_stories_raw(cfg, ids):
    """Fetch full_story via TO_BASE64 (one line per row; avoids batch newline breakage)."""
    if not ids:
        return {}
    id_list = ",".join(str(i) for i in ids)
    sql = (
        "SELECT id, seo_url, TO_BASE64(full_story) FROM news WHERE id IN ({0}) ORDER BY id".format(
            id_list
        )
    )
    out = mysql_exec(cfg, sql, raw=True)
    stories = {}
    for line in out.splitlines():
        if "\t" not in line:
            continue
        parts = line.split("\t", 2)
        if len(parts) < 3:
            continue
        rid = int(parts[0])
        stories[rid] = {"seo_url": parts[1], "full_story": decode_full_story_b64(parts[2])}
    return stories


def newline_metrics_sql(cfg, ids):
    id_list = ",".join(str(i) for i in ids)
    sql = (
        "SELECT id, LENGTH(full_story), "
        "(LENGTH(full_story) - LENGTH(REPLACE(full_story, CHAR(10), ''))) "
        "FROM news WHERE id IN ({0}) ORDER BY id".format(id_list)
    )
    raw = mysql_exec(cfg, sql)
    metrics = {}
    for line in raw.splitlines():
        if "\t" not in line:
            continue
        rid, length_s, nl_s = line.split("\t")
        metrics[int(rid)] = {"length": int(length_s), "newlines": int(nl_s)}
    return metrics


def backup_news_rows(cfg, backup_dir, ids):
    backup_dir = Path(backup_dir)
    backup_dir.mkdir(parents=True, exist_ok=True)
    dump_path = backup_dir / "news_rows.sql"
    id_list = ",".join(str(i) for i in ids)
    where = 'id IN ({0})'.format(id_list)
    cmd = [
        "mysqldump",
        "-h",
        cfg["host"],
        "-u",
        cfg["user"],
        cfg["database"],
        "news",
        "--where={0}".format(where),
        "--skip-extended-insert",
        "--default-character-set=utf8mb4",
    ]
    env = _mysql_subenv(cfg)
    with open(dump_path, "wb") as fh:
        subprocess.check_call(cmd, stdout=fh, env=env)
    if dump_path.stat().st_size < 64:
        raise SystemExit("BLOCKER: backup empty: {0}".format(dump_path))
    (backup_dir / "ids.json").write_text(json.dumps(ids, indent=2), encoding="utf-8")
    return dump_path


def apply_replace_updates(cfg, ids):
    pairs = all_replacement_pairs()
    if try_pymysql_apply(cfg, ids):
        print("APPLY via PyMySQL:", len(ids), "row(s)")
        return
    statements = []
    for rid in ids:
        expr = build_replace_expression("full_story", pairs)
        statements.append("UPDATE news SET full_story = {0} WHERE id = {1};".format(expr, int(rid)))
    sql_body = "SET NAMES utf8mb4;\n" + "\n".join(statements) + "\n"
    mysql_exec_file(cfg, sql_body)
    print("APPLY via mysql SQL file:", len(ids), "row(s)")


def cmd_apply(backups_root, env_file):
    ensure_mysql_env(env_file)
    cfg = mysql_env()
    donors = resolve_donor_rows(cfg)
    if not donors:
        raise SystemExit("BLOCKER: no donor rows resolved")
    ids = [d["id"] for d in donors]
    stamp = time.strftime("%Y%m%dT%H%M%SZ", time.gmtime())
    backup_dir = Path(backups_root).expanduser() / "blog-hrefs" / stamp
    before_metrics = newline_metrics_sql(cfg, ids)
    before_stories = fetch_full_stories_raw(cfg, ids)

    dump_path = backup_news_rows(cfg, backup_dir, ids)
    print("backup:", dump_path)

    apply_replace_updates(cfg, ids)

    after_metrics = newline_metrics_sql(cfg, ids)
    after_stories = fetch_full_stories_raw(cfg, ids)

    errors = []
    for rid in ids:
        if rid not in before_metrics or rid not in after_metrics:
            errors.append("id {0}: missing metrics".format(rid))
            continue
        b = before_metrics[rid]
        a = after_metrics[rid]
        if b["newlines"] != a["newlines"]:
            errors.append(
                "id {0}: newline count changed SQL {1} -> {2}".format(rid, b["newlines"], a["newlines"])
            )
        if rid in before_stories and rid in after_stories:
            if before_stories[rid]["full_story"] == after_stories[rid]["full_story"]:
                slug = after_stories[rid]["seo_url"]
                if rows_for_story(slug, before_stories[rid]["full_story"]):
                    errors.append("id {0} ({1}): no change after UPDATE".format(rid, slug))
            errors.extend(
                verify_story_body(
                    after_stories[rid]["seo_url"],
                    before_stories[rid]["full_story"],
                    after_stories[rid]["full_story"],
                )
            )
    meta = {
        "stamp": stamp,
        "ids": ids,
        "before_metrics": before_metrics,
        "after_metrics": after_metrics,
    }
    (backup_dir / "apply-meta.json").write_text(json.dumps(meta, indent=2), encoding="utf-8")
    if errors:
        print("VERIFY FAILED:")
        for e in errors:
            print(" ", e)
        raise SystemExit(1)
    print("VERIFY OK:", len(ids), "row(s); backup:", backup_dir)
    return 0


def cmd_restore(restore_dir, env_file):
    ensure_mysql_env(env_file)
    cfg = mysql_env()
    restore_dir = Path(restore_dir).expanduser()
    dump_path = restore_dir / "news_rows.sql"
    if not dump_path.is_file():
        raise SystemExit("BLOCKER: missing {0}".format(dump_path))
    if dump_path.stat().st_size < 64:
        raise SystemExit("BLOCKER: backup too small")
    cmd = mysql_cmd_base(cfg, raw=False)
    env = _mysql_subenv(cfg)
    with open(dump_path, "rb") as fh:
        subprocess.check_call(cmd, stdin=fh, env=env)
    print("RESTORE OK:", dump_path)
    return 0


def check_mysql():
    cfg = mysql_env()
    rows = []
    skip_count = 0
    stories = {}
    for path in DONOR_PATHS:
        esc = path.replace("'", "''")
        sql = (
            "SELECT id, seo_url, TO_BASE64(full_story) FROM news WHERE seo_url = '{0}' OR alt_name = '{0}' LIMIT 1".format(
                esc
            )
        )
        raw = mysql_exec(cfg, sql, raw=True).strip()
        if not raw:
            print("SKIP (no row):", path)
            skip_count += 1
            continue
        parts = raw.split("\t", 2)
        if len(parts) < 3:
            print("SKIP (bad row):", path)
            skip_count += 1
            continue
        slug, story = parts[1], decode_full_story_b64(parts[2])
        rows.extend(rows_for_story(slug, story))
        stories[slug] = story
    return rows, skip_count


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--check", action="store_true", help="Dry-run list changes")
    parser.add_argument("--apply", action="store_true", help="Apply on hosting MySQL with backup")
    parser.add_argument("--restore", metavar="DIR", help="Restore news_rows.sql from backup directory")
    parser.add_argument(
        "--source",
        choices=("live", "mysql", "auto"),
        default="auto",
        help="check source: live curl, mysql, or auto",
    )
    parser.add_argument("--env-file", default="", help="Path to .env (default: ~/lireate-autopub/.env)")
    parser.add_argument(
        "--backups-root",
        default=str(autopub_home() / "backups"),
        help="Backups root (blog-hrefs/<stamp>/ under here)",
    )
    args = parser.parse_args()
    env_file = args.env_file.strip() or None

    if args.restore:
        return cmd_restore(args.restore, env_file)
    if args.apply:
        return cmd_apply(args.backups_root, env_file)
    if not args.check:
        parser.print_help()
        return 2
    source = args.source
    if source == "auto":
        if env_file or mysql_env_ok():
            try:
                ensure_mysql_env(env_file)
                source = "mysql"
            except SystemExit:
                source = "live"
        else:
            source = "live"
    if source == "mysql":
        rows, skip_count = check_mysql()
    else:
        rows, skip_count = check_live()
    if not rows:
        print("OK: no matching broken hrefs in scanned articles")
    else:
        by_article = {}
        for row in rows:
            by_article.setdefault(row["seo_url"], []).append(row)
        for seo_url in sorted(by_article.keys()):
            print("---", seo_url, "---")
            for row in by_article[seo_url]:
                print("  {old}\t=>\t{new}".format(old=row["old"], new=row["new"]))
        print(
            "TOTAL",
            len(rows),
            "replacement(s); articles=",
            len(by_article),
            "; skips=",
            skip_count,
            "; source=",
            source,
        )
    print(
        "Articles in repo: blog templates only; bodies live in MySQL news.full_story on hosting DOCROOT."
    )
    if source == "live":
        print("NOTE: --apply uses SQL REPLACE + mysqldump; no full_story round-trip in Python.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())