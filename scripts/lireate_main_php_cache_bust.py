"""Bump ?v= on main.js inside template/main.php."""

import re

from lireate_light_v4_asset_version import MAIN_JS_QUERY_VERSION

MAIN_JS_RE = re.compile(
    r"(/template/js/main\.js\?v=)([A-Za-z0-9._-]+)",
    re.IGNORECASE,
)


def patch_main_js_query(text):
    if not MAIN_JS_RE.search(text):
        return text, False
    new_text = MAIN_JS_RE.sub(r"\g<1>" + MAIN_JS_QUERY_VERSION, text)
    return new_text, new_text != text