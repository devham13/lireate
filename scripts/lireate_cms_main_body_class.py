"""Safe <body class=\"cms-default…\"> conditional snippets for CMS main.php."""

CMS_DEFAULT = "cms-default"

DOCS_BODY_COND = "<?php if($temp['page']=='docs') echo ' lireate-docs-body';?>"
CONTACTS_BODY_COND = "<?php if($temp['page']=='kontaktyi') echo ' lireate-contacts-body';?>"
PARTNERS_BODY_COND = "<?php if($temp['page']=='partners') echo ' lireate-partners-body';?>"
TEAM_BODY_COND = "<?php if($temp['page']=='team') echo ' lireate-team-body';?>"
CONTACTS_PAGE_BODY_COND = "<?php if($temp['page']=='contacts') echo ' lireate-contacts-body';?>"
ABOUT_BODY_COND = "<?php if($temp['page']=='o_kompanii') echo ' lireate-about-body';?>"
HOME_LIGHT_V4_BODY_COND = (
    "<?php if($temp['page']=='home' || (isset($_SERVER['REQUEST_URI']) && "
    "preg_match('#^/(?:index\\.php)?$#', parse_url((string)$_SERVER['REQUEST_URI'], PHP_URL_PATH)?:''))) "
    "echo ' lireate-home-light-v4 lireate-theme-light-v4';?>"
)


def _insert_after_pos(text, pos, snippet):
    if snippet in text:
        return text, False
    if pos < 0:
        return text, False
    return text[:pos] + snippet + text[pos:], True


def cms_default_insert_pos(text):
    idx = text.find(CMS_DEFAULT)
    if idx == -1:
        return -1
    return idx + len(CMS_DEFAULT)


def patch_docs_body_conditional(text):
    """Insert docs conditional immediately after literal cms-default."""
    if DOCS_BODY_COND in text:
        return text, False
    pos = cms_default_insert_pos(text)
    return _insert_after_pos(text, pos, DOCS_BODY_COND)


def patch_contacts_body_conditional(text):
    """Insert contacts conditional after docs snippet if present, else after cms-default."""
    if CONTACTS_BODY_COND in text:
        return text, False
    if DOCS_BODY_COND in text:
        pos = text.find(DOCS_BODY_COND) + len(DOCS_BODY_COND)
    else:
        pos = cms_default_insert_pos(text)
    return _insert_after_pos(text, pos, CONTACTS_BODY_COND)


def patch_partners_body_conditional(text):
    """Insert partners conditional after contacts snippet if present, else after cms-default."""
    if PARTNERS_BODY_COND in text:
        return text, False
    if CONTACTS_BODY_COND in text:
        pos = text.find(CONTACTS_BODY_COND) + len(CONTACTS_BODY_COND)
    elif DOCS_BODY_COND in text:
        pos = text.find(DOCS_BODY_COND) + len(DOCS_BODY_COND)
    else:
        pos = cms_default_insert_pos(text)
    return _insert_after_pos(text, pos, PARTNERS_BODY_COND)


def patch_team_body_conditional(text):
    """Insert team conditional after partners snippet if present, else after contacts/docs/cms-default."""
    if TEAM_BODY_COND in text:
        return text, False
    if PARTNERS_BODY_COND in text:
        pos = text.find(PARTNERS_BODY_COND) + len(PARTNERS_BODY_COND)
    elif CONTACTS_BODY_COND in text:
        pos = text.find(CONTACTS_BODY_COND) + len(CONTACTS_BODY_COND)
    elif DOCS_BODY_COND in text:
        pos = text.find(DOCS_BODY_COND) + len(DOCS_BODY_COND)
    else:
        pos = cms_default_insert_pos(text)
    return _insert_after_pos(text, pos, TEAM_BODY_COND)


def remove_body_conditional_snippets(text, snippets):
    restored = text
    for snippet in snippets:
        restored = restored.replace(snippet, "")
    return restored


def remove_static_body_class(text, class_name):
    """Legacy rollback: strip static class name from body class attribute."""
    return (
        text.replace(" " + class_name, "")
        .replace(class_name + " ", "")
        .replace('class="' + class_name + '"', 'class="cms-default"')
    )


def patch_contacts_page_body_conditional(text):
    """Insert /contacts/ body class after /kontaktyi/ snippet if present."""
    if CONTACTS_PAGE_BODY_COND in text:
        return text, False
    if CONTACTS_BODY_COND in text:
        pos = text.find(CONTACTS_BODY_COND) + len(CONTACTS_BODY_COND)
    elif DOCS_BODY_COND in text:
        pos = text.find(DOCS_BODY_COND) + len(DOCS_BODY_COND)
    else:
        pos = cms_default_insert_pos(text)
    return _insert_after_pos(text, pos, CONTACTS_PAGE_BODY_COND)


def patch_about_body_conditional(text):
    """Insert /o_kompanii body class after team snippet if present."""
    if ABOUT_BODY_COND in text:
        return text, False
    if TEAM_BODY_COND in text:
        pos = text.find(TEAM_BODY_COND) + len(TEAM_BODY_COND)
    elif PARTNERS_BODY_COND in text:
        pos = text.find(PARTNERS_BODY_COND) + len(PARTNERS_BODY_COND)
    elif CONTACTS_PAGE_BODY_COND in text:
        pos = text.find(CONTACTS_PAGE_BODY_COND) + len(CONTACTS_PAGE_BODY_COND)
    elif CONTACTS_BODY_COND in text:
        pos = text.find(CONTACTS_BODY_COND) + len(CONTACTS_BODY_COND)
    else:
        pos = cms_default_insert_pos(text)
    return _insert_after_pos(text, pos, ABOUT_BODY_COND)


def patch_home_light_v4_suppress_dark_body(text):
    """Ensure main.php does not emit cms dark shell on home when Light V4 is active."""
    import re

    marker = "lireate-home-light-v4-install:suppress-home-dark"
    if marker in text:
        return text, False
    changed = False
    planned = text
    dark_echo = re.compile(
        r"<\?php\s+if\s*\(([^)]*)\)\s*echo\s+'([^']*lireate-cms-page-dark[^']*)'\s*;\s*\?>",
        re.IGNORECASE,
    )
    home_dark_echo = re.compile(
        r"<\?php\s+if\s*\(([^)]*)\)\s*echo\s+'([^']*lireate-home-dark[^']*)'\s*;\s*\?>",
        re.IGNORECASE,
    )
    dark_marker = "lireate_design_cms_dark_pages_v2"

    def _guard_echo(match):
        cond = match.group(1).strip()
        if "$temp['page']!='home'" in cond or '$temp["page"]!="home"' in cond:
            return match.group(0)
        return (
            "<?php if(({0}) && $temp['page']!='home') echo '{1}'; /* {2} */ ?>".format(
                cond, match.group(2).replace("'", "\\'"), marker
            )
        )

    nxt = dark_echo.sub(_guard_echo, planned)
    if nxt != planned:
        changed = True
        planned = nxt
    nxt = home_dark_echo.sub(_guard_echo, planned)
    if nxt != planned:
        changed = True
        planned = nxt
    if dark_marker in planned:
        start = planned.find(dark_marker)
        end = planned.find("?>", start)
        if end == -1:
            end = len(planned)
        chunk = planned[start:end]
        chunk_new = re.sub(r"array\s*\(\s*'home'\s*,", "array(", chunk)
        chunk_new = re.sub(r",\s*'home'\s*(?=[,\)])", "", chunk_new)
        if chunk_new != chunk:
            planned = planned[:start] + chunk_new + planned[end:]
            changed = True
    return planned, changed


def patch_home_light_v4_body_conditional(text):
    """Insert home Light V4 body classes after about snippet if present."""
    if HOME_LIGHT_V4_BODY_COND in text:
        return text, False
    if ABOUT_BODY_COND in text:
        pos = text.find(ABOUT_BODY_COND) + len(ABOUT_BODY_COND)
    elif TEAM_BODY_COND in text:
        pos = text.find(TEAM_BODY_COND) + len(TEAM_BODY_COND)
    elif PARTNERS_BODY_COND in text:
        pos = text.find(PARTNERS_BODY_COND) + len(PARTNERS_BODY_COND)
    elif CONTACTS_PAGE_BODY_COND in text:
        pos = text.find(CONTACTS_PAGE_BODY_COND) + len(CONTACTS_PAGE_BODY_COND)
    elif CONTACTS_BODY_COND in text:
        pos = text.find(CONTACTS_BODY_COND) + len(CONTACTS_BODY_COND)
    elif DOCS_BODY_COND in text:
        pos = text.find(DOCS_BODY_COND) + len(DOCS_BODY_COND)
    else:
        pos = cms_default_insert_pos(text)
    return _insert_after_pos(text, pos, HOME_LIGHT_V4_BODY_COND)


def _append_slug_to_arrays_with(text, required_slug, new_slug):
    """Add 'new_slug' to PHP array(...) literals that already contain 'required_slug'."""
    import re
    token = "'" + new_slug + "'"
    required = "'" + required_slug + "'"
    pattern = re.compile(r"array\((?:'(?:\\'|[^'])+',)*" + re.escape(required) + r"(?:,'(?:\\'|[^'])+')*\)")

    def repl(match):
        body = match.group(0)
        if token in body:
            return body
        return body[:-1] + "," + token + ")"

    return pattern.sub(repl, text)


def patch_cms_dark_and_static_page_slugs(text, extra_slugs):
    """Add slugs to CMS dark/static arrays that already list kontaktyi."""
    changed = False
    planned = text
    for slug in extra_slugs:
        nxt = _append_slug_to_arrays_with(planned, "kontaktyi", slug)
        if nxt != planned:
            changed = True
            planned = nxt
    return planned, changed
