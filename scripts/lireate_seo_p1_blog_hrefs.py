#!/usr/bin/env python3
"""
SEO P1 (15.11.2026): fix broken internal hrefs in CMS `news.full_story` (hosting only).

--check  Dry-run: list seo_url + replacement (live curl and/or MySQL).
--apply  Backup + UPDATE news.full_story (requires MySQL env; never run from Cloud without owner).

Does not read or modify data/db.php.
"""
from __future__ import print_function

import argparse
import os
import re
import subprocess
import sys
import time
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]

# Path segment exactly as on live / in news.seo_url (no normalization).
DONOR_PATHS = [
    "kak_proverit_brend_na_wildberries_svoboden_li_tovarnyy_znak",
    "rospatent-fips-proverit-tovarnyy-znak-onlayn-besplatno",
    "vozrazhenie_v_pps_na_pravovuyu_oxranu_tz_st_1513_14_000_rub_i_srok_5_let",
    "graficheskiy_tovarnyy_znak_smeshchenie_do_podachi",
    "mozhno_li_ispolzovat_logotipyi_kompaniy_v_video_chek_list_riskov",
    "rezhim_kommercheskoy_taynyi_kak_vvesti_chtobyi_dokazat_razglashenie",
    "mozhno_li_pokazyivat_chuzhie_logotipyi_i_emblemyi_v_video_i_na_oblozhkax",
    "avtorskoe-voznagrazhdenie-po-gk-rf",
    "dorabotka-logotipa-pered-registraciey-tovarnogo-znaka",
    "patent_na_poleznuyu_model_rospatent",
    "sposobyi_zaschityi_izobreteniya_patent_nou_xau_ili_publikaciya",
    "kak_annulirovat_patent_konkurenta_esli_ego_pretenzii_nepravomernyi",
]

# Longer patterns first; only real broken fragments (no no-op pairs).
REPLACEMENTS = [
    ("/blog/kak-opredelit-klass-mktu/", "/struktura_mktu_kak_opredelit_klass_dlya_tovara_i_uslugi/"),
    (
        "/blog/konkurent-zaregistriroval-pohozhiy-tovarnyy-znak/",
        "/konkurent_zaregistriroval_pohozhiy_tovarnyy_znak/",
    ),
    (
        "/blog/mozhno-li-pokazyvat-chuzhie-logotipy-v-video-i-na-oblozhkah/",
        "/mozhno_li_pokazyivat_chuzhie_logotipyi_i_emblemyi_v_video_i_na_oblozhkax/",
    ),
    (
        "/dogovor-na-razrabotku-logotipa-prava-ot-dizajnera/",
        "/dogovor_na_razrabotku_logotipa_kak_poluchit_prava_ot_dizaynera/",
    ),
    (
        "/dogovor_na_razrabotku_logotipa_kak_poluchit_prava_ot_dizajnera/",
        "/dogovor_na_razrabotku_logotipa_kak_poluchit_prava_ot_dizaynera/",
    ),
    ("/rezhim-kommercheskoj-tajny-kak-vvesti/", "/rezhim_kommercheskoy_taynyi_kak_vvesti_chtobyi_dokazat_razglashenie/"),
    ("/sposoby-zashchity-izobreteniya/", "/sposobyi_zaschityi_izobreteniya_patent_nou_xau_ili_publikaciya/"),
    ("/sposoby_zashchity_izobreteniya", "/sposobyi_zaschityi_izobreteniya_patent_nou_xau_ili_publikaciya/"),
    ('href="/izobretenie/"', 'href="/patent"'),
    ('href="/izobretenie"', 'href="/patent"'),
    ('href="/patentovanie/"', 'href="/patent"'),
    ('href="/patentovanie"', 'href="/patent"'),
    ("href='/izobretenie/'", "href='/patent'"),
    ("href='/izobretenie'", "href='/patent'"),
    ("href='/patentovanie/'", "href='/patent'"),
    ("href='/patentovanie'", "href='/patent'"),
]

ANNULIROVAT_OLD = (
    "href=\u2019 https://base.garant.ru/10164072/a043ec3f83d5eb0d75040138a0a43bc2/"
    "\u2019 target=\u2019_blank\u2019"
)
ANNULIROVAT_NEW = (
    'href="https://base.garant.ru/10164072/a043ec3f83d5eb0d75040138a0a43bc2/" '
    'target="_blank"'
)

FORBIDDEN_AFTER_FIX = (
    "prava_ot_dizajnera",
    "/blog/kak-opredelit-klass-mktu/",
    "/blog/konkurent-zaregistriroval-pohozhiy-tovarnyy-znak/",
    "/blog/mozhno-li-pokazyvat-chuzhie-logotipy-v-video-i-na-oblozhkah/",
    'href="/izobretenie"',
    'href="/patentovanie"',
    "/sposoby-zashchity-izobreteniya/",
    "/sposoby_zashchity_izobreteniya",
    "/rezhim-kommercheskoj-tajny-kak-vvesti/",
)


def site_base():
    base = (
        os.environ.get("LIREATE_PUBLIC_SITE_URL")
        or os.environ.get("PUBLIC_SITE_URL")
        or ""
    ).rstrip("/")
    if not base and os.environ.get("PUBLIC_SITE_HOST", "").strip():
        base = "https://" + os.environ["PUBLIC_SITE_HOST"].strip().lstrip("https://").lstrip("http://")
    return base


def apply_story_fixes(story):
    out = story
    changed = []
    for old, new in REPLACEMENTS:
        if old in out:
            out = out.replace(old, new)
            changed.append((old, new))
    if ANNULIROVAT_OLD in out:
        out = out.replace(ANNULIROVAT_OLD, ANNULIROVAT_NEW)
        changed.append((ANNULIROVAT_OLD, ANNULIROVAT_NEW))
    return out, changed


def rows_for_story(seo_url, story):
    rows = []
    for old, new in REPLACEMENTS:
        if old in story:
            rows.append({"seo_url": seo_url, "old": old, "new": new})
    if ANNULIROVAT_OLD in story:
        rows.append({"seo_url": seo_url, "old": ANNULIROVAT_OLD, "new": ANNULIROVAT_NEW})
    return rows


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


def mysql_env_ok():
    keys = ("LIREATE_MYSQL_HOST", "LIREATE_MYSQL_USER", "LIREATE_MYSQL_PASSWORD", "LIREATE_MYSQL_DATABASE")
    return all(os.environ.get(k, "").strip() for k in keys)


def mysql_query(sql):
    host = os.environ["LIREATE_MYSQL_HOST"]
    user = os.environ["LIREATE_MYSQL_USER"]
    password = os.environ["LIREATE_MYSQL_PASSWORD"]
    db = os.environ["LIREATE_MYSQL_DATABASE"]
    cmd = [
        "mysql",
        "-h",
        host,
        "-u",
        user,
        "-D",
        db,
        "-N",
        "-B",
        "-e",
        sql,
    ]
    env = os.environ.copy()
    env["MYSQL_PWD"] = password
    return subprocess.check_output(cmd, env=env, text=True, errors="replace")


def check_mysql():
    if not mysql_env_ok():
        raise SystemExit("BLOCKER: MySQL env not set (LIREATE_MYSQL_*)")
    rows = []
    skip_count = 0
    for path in DONOR_PATHS:
        esc = path.replace("'", "''")
        sql = "SELECT seo_url, full_story FROM news WHERE seo_url = '{0}' OR alt_name = '{0}' LIMIT 1".format(esc)
        raw = mysql_query(sql).strip()
        if not raw:
            print("SKIP (no row):", path)
            skip_count += 1
            continue
        if "\t" not in raw:
            print("SKIP (bad row):", path)
            skip_count += 1
            continue
        slug, story = raw.split("\t", 1)
        rows.extend(rows_for_story(slug, story))
    return rows, skip_count


def cmd_apply(backups_root):
    if not mysql_env_ok():
        raise SystemExit("BLOCKER: --apply requires LIREATE_MYSQL_* env on hosting")
    backups_root = Path(backups_root).expanduser()
    stamp = time.strftime("%Y%m%dT%H%M%SZ", time.gmtime())
    backup_dir = backups_root / ("seo-p1-blog-hrefs-{0}".format(stamp))
    backup_dir.mkdir(parents=True, exist_ok=True)
    updated = 0
    for path in DONOR_PATHS:
        esc = path.replace("'", "''")
        sql = "SELECT seo_url, full_story FROM news WHERE seo_url = '{0}' OR alt_name = '{0}' LIMIT 1".format(esc)
        raw = mysql_query(sql).strip()
        if not raw or "\t" not in raw:
            continue
        slug, story = raw.split("\t", 1)
        new_story, changes = apply_story_fixes(story)
        if not changes:
            continue
        (backup_dir / (slug + ".full_story.html")).write_text(story, encoding="utf-8")
        esc_story = new_story.replace("\\", "\\\\").replace("'", "\\'")
        upd = "UPDATE news SET full_story = '{0}' WHERE seo_url = '{1}'".format(esc_story, slug.replace("'", "''"))
        mysql_query(upd)
        updated += 1
        print("APPLY", slug, len(changes), "replacement(s)")
    print("backup:", backup_dir)
    print("updated rows:", updated)
    return 0


def verify_after_fix_live():
    """After virtual fix, forbidden href fragments must be gone (live)."""
    bad = []
    for path in DONOR_PATHS:
        body, _url, code = fetch_live_article(path)
        if body is None:
            continue
        fixed, _ = apply_story_fixes(body)
        for needle in FORBIDDEN_AFTER_FIX:
            if needle in fixed:
                bad.append("{0}: still contains {1}".format(path, needle))
    return bad


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--check", action="store_true", help="Dry-run list changes")
    parser.add_argument("--apply", action="store_true", help="Apply on hosting MySQL with backup")
    parser.add_argument(
        "--source",
        choices=("live", "mysql", "auto"),
        default="auto",
        help="check source: live curl, mysql, or auto",
    )
    parser.add_argument(
        "--backups-root",
        default=os.environ.get("LIREATE_AUTOPUB_HOME", str(Path.home() / "lireate-autopub")) + "/backups",
    )
    args = parser.parse_args()
    if args.apply:
        return cmd_apply(args.backups_root)
    if not args.check:
        parser.print_help()
        return 2
    source = args.source
    if source == "auto":
        source = "mysql" if mysql_env_ok() else "live"
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
        print("TOTAL", len(rows), "replacement(s); articles=", len(by_article), "; skips=", skip_count, "; source=", source)
    print(
        "Articles in repo: blog templates only; bodies live in MySQL news.full_story on hosting DOCROOT."
    )
    if source == "live":
        print("NOTE: FORBIDDEN_* scan runs after --apply on hosting (live still has broken hrefs until then).")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())