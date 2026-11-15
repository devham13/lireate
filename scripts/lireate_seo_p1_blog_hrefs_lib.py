"""Helpers for lireate_seo_p1_blog_hrefs.py (testable, no DB I/O)."""

from __future__ import print_function

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


def all_replacement_pairs():
    pairs = list(REPLACEMENTS)
    pairs.append((ANNULIROVAT_OLD, ANNULIROVAT_NEW))
    return pairs


def mysql_string_literal(value):
    """Escape *value* as a MySQL single-quoted SQL string literal (no surrounding quotes)."""
    parts = []
    for ch in value:
        o = ord(ch)
        if ch == "\0":
            parts.append("\\0")
        elif ch == "\n":
            parts.append("\\n")
        elif ch == "\r":
            parts.append("\\r")
        elif ch == "\t":
            parts.append("\\t")
        elif ch == "\\":
            parts.append("\\\\")
        elif ch == "'":
            parts.append("''")
        elif ch == '"':
            parts.append('"')
        elif o == 0x2018 or o == 0x2019:
            parts.append(ch)
        else:
            parts.append(ch)
    return "'" + "".join(parts) + "'"


def parse_mysql_string_literal(literal):
    """Parse a MySQL single-quoted literal produced by mysql_string_literal."""
    if not literal or literal[0] != "'" or literal[-1] != "'":
        raise ValueError("not a single-quoted MySQL literal")
    body = literal[1:-1]
    out = []
    i = 0
    while i < len(body):
        ch = body[i]
        if ch == "'":
            if i + 1 < len(body) and body[i + 1] == "'":
                out.append("'")
                i += 2
                continue
            raise ValueError("unescaped quote")
        if ch == "\\":
            if i + 1 >= len(body):
                raise ValueError("truncated escape")
            nxt = body[i + 1]
            if nxt == "n":
                out.append("\n")
            elif nxt == "r":
                out.append("\r")
            elif nxt == "t":
                out.append("\t")
            elif nxt == "0":
                out.append("\0")
            elif nxt == "\\":
                out.append("\\")
            else:
                out.append(nxt)
            i += 2
            continue
        out.append(ch)
        i += 1
    return "".join(out)


def build_replace_expression(column, pairs):
    """Nested REPLACE(column, old, new) … for SQL UPDATE (content stays in MySQL)."""
    expr = column
    for old, new in pairs:
        expr = "REPLACE({0}, {1}, {2})".format(expr, mysql_string_literal(old), mysql_string_literal(new))
    return expr


def simulate_replace_chain(text, pairs):
    out = text
    for old, new in pairs:
        out = out.replace(old, new)
    return out


def count_newlines(text):
    return text.count("\n")


def rows_for_story(seo_url, story):
    rows = []
    for old, new in REPLACEMENTS:
        if old in story:
            rows.append({"seo_url": seo_url, "old": old, "new": new})
    if ANNULIROVAT_OLD in story:
        rows.append({"seo_url": seo_url, "old": ANNULIROVAT_OLD, "new": ANNULIROVAT_NEW})
    return rows


def verify_story_body(seo_url, before_story, after_story):
    """Return list of error strings."""
    errors = []
    for needle in FORBIDDEN_AFTER_FIX:
        if needle in after_story:
            errors.append("{0}: forbidden still present: {1}".format(seo_url, needle))
    expected = rows_for_story(seo_url, before_story)
    for row in expected:
        old, new = row["old"], row["new"]
        if old in after_story:
            errors.append("{0}: old fragment still present after apply".format(seo_url))
        if new not in after_story:
            errors.append("{0}: expected new fragment missing: {1}".format(seo_url, new[:60]))
    if count_newlines(after_story) != count_newlines(before_story):
        errors.append(
            "{0}: newline count changed {1} -> {2}".format(
                seo_url, count_newlines(before_story), count_newlines(after_story)
            )
        )
    return errors