#!/usr/bin/env python3
"""
SEO P1 (15.11.2026): fix broken internal hrefs in CMS `news.full_story` (hosting only).

--check  Dry-run: list seo_url + replacement (live curl and/or MySQL).
--apply  Backup + UPDATE news.full_story (requires MySQL env; never run from Cloud without owner).

Does not read or modify data/db.php.
"""
from __future__ import print_function

import argparse
import json
import os
import re
import subprocess
import sys
import time
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]

# Longer patterns first
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

ANNULIROVAT_SLUG = "kak_annulirovat_patent_konkurenta_esli_ego_pretenzii_nepravomernyi"
ANNULIROVAT_OLD = (
    "href=\u2019 https://base.garant.ru/10164072/a043ec3f83d5eb0d75040138a0a43bc2/"
    "\u2019 target=\u2019_blank\u2019"
)
ANNULIROVAT_NEW = (
    'href="https://base.garant.ru/10164072/a043ec3f83d5eb0d75040138a0a43bc2/" '
    'target="_blank"'
)

DONOR_SLUGS = [
    "kak_proverit_brend_na_wildberries_i_kupit_tovarnyiy_znak_u_drugogo_prodavtsa",
    "rospatent_fips_proverit_tovarnogo_znaka_onlayn",
    "vozrazhenie_v_pps_na_pravovuyu_oxranu_tz_st_1513_gk_rf",
    "graficheskiy_tovarnyy_znak_smeshchenie_do_podachi",
    "mozhno_li_ispolzovat_logotipyi_kompaniy_v_video_i_na_oblozhkah",
    "rezhim_kommercheskoy_taynyi_kak_vvesti_chtobyi_dokazat_razglashenie",
    "mozhno_li_pokazyivat_chuzhie_logotipyi_i_emblemyi_v_video_i_na_oblozhkax",
    "avtorskoe-voznagrazhdenie-po-gk-rf",
    "dorabotka_logotipa_pered_registraciey_tovarnogo_znaka",
    "patent_na_poleznuyu_model_rospatent",
    "sposobyi_zaschityi_izobreteniya_patent_nou_xau_ili_publikaciya",
    ANNULIROVAT_SLUG,
]


def apply_story_fixes(story):
    out = story
    changed = []
    for old, new in REPLACEMENTS:
        if old in out:
            out = out.replace(old, new)
            changed.append((old, new))
    if ANNULIROVAT_OLD in out:
        out = out.replace(ANNULIROVAT_OLD, ANNULIROVAT_NEW)
        changed.append(("typographic garant href", ANNULIROVAT_NEW[:40] + "…"))
    return out, changed


def fetch_live(slug):
    base = os.environ.get("LIREATE_PUBLIC_SITE_URL", "").rstrip("/")
    if not base:
        raise SystemExit("BLOCKER: set LIREATE_PUBLIC_SITE_URL for --source live")
    url = base + "/" + slug + "/"
    proc = subprocess.run(
        ["curl", "-sS", "-o", "/dev/null", "-w", "%{http_code}", url],
        capture_output=True,
        text=True,
    )
    if proc.stdout.strip() != "200":
        return None
    html = subprocess.check_output(["curl", "-sS", url], text=True, errors="replace")
    m = re.search(r'<div\s+id=["\']textBox["\'][^>]*>(.*)</div>', html, re.I | re.S)
    if m:
        return m.group(1)
    return html


def check_live():
    rows = []
    for slug in DONOR_SLUGS:
        body = fetch_live(slug)
        if body is None:
            print("SKIP (not 200):", slug)
            continue
        for old, new in REPLACEMENTS:
            if old in body:
                rows.append({"source": "live", "seo_url": slug, "old": old, "new": new})
        if ANNULIROVAT_OLD in body:
            rows.append(
                {
                    "source": "live",
                    "seo_url": slug,
                    "old": ANNULIROVAT_OLD[:50] + "…",
                    "new": ANNULIROVAT_NEW,
                }
            )
    return rows


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
    in_list = ",".join("'" + s.replace("'", "''") + "'" for s in DONOR_SLUGS)
    sql = "SELECT seo_url, full_story FROM news WHERE seo_url IN ({0})".format(in_list)
    raw = mysql_query(sql)
    rows = []
    for line in raw.splitlines():
        if "\t" not in line:
            continue
        slug, story = line.split("\t", 1)
        for old, new in REPLACEMENTS:
            if old in story:
                rows.append({"source": "mysql", "seo_url": slug, "old": old, "new": new})
        if ANNULIROVAT_OLD in story:
            rows.append(
                {"source": "mysql", "seo_url": slug, "old": "typographic garant", "new": ANNULIROVAT_NEW}
            )
    return rows


def cmd_apply(backups_root):
    if not mysql_env_ok():
        raise SystemExit("BLOCKER: --apply requires LIREATE_MYSQL_* env on hosting")
    backups_root = Path(backups_root).expanduser()
    stamp = time.strftime("%Y%m%dT%H%M%SZ", time.gmtime())
    backup_dir = backups_root / ("seo-p1-blog-hrefs-{0}".format(stamp))
    backup_dir.mkdir(parents=True, exist_ok=True)
    in_list = ",".join("'" + s.replace("'", "''") + "'" for s in DONOR_SLUGS)
    sql = "SELECT seo_url, full_story FROM news WHERE seo_url IN ({0})".format(in_list)
    raw = mysql_query(sql)
    updated = 0
    for line in raw.splitlines():
        if "\t" not in line:
            continue
        slug, story = line.split("\t", 1)
        new_story, changes = apply_story_fixes(story)
        if not changes:
            continue
        (backup_dir / (slug + ".full_story.html")).write_text(story, encoding="utf-8")
        esc = new_story.replace("\\", "\\\\").replace("'", "\\'")
        upd = "UPDATE news SET full_story = '{0}' WHERE seo_url = '{1}'".format(
            esc, slug.replace("'", "''")
        )
        mysql_query(upd)
        updated += 1
        print("APPLY", slug, len(changes), "replacement(s)")
    print("backup:", backup_dir)
    print("updated rows:", updated)
    return 0


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
        rows = check_mysql()
    else:
        rows = check_live()
    if not rows:
        print("OK: no matching broken hrefs in scanned articles")
        return 0
    for row in rows:
        print(
            "{seo_url}\t{old}\t=>\t{new}".format(
                seo_url=row["seo_url"],
                old=row["old"],
                new=row["new"],
            )
        )
    print("TOTAL", len(rows), "replacement(s); source=", source)
    print(
        "Articles in repo: blog templates only; bodies live in MySQL news.full_story on hosting DOCROOT."
    )
    return 0


if __name__ == "__main__":
    raise SystemExit(main())