#!/usr/bin/env python3
"""Sitemap index + child urlsets (services, blog, mktu, tools, portfolio, pages)."""
from __future__ import print_function

import json
import re
from datetime import datetime, timezone
from pathlib import Path
from xml.sax.saxutils import escape

NS = "http://www.sitemaps.org/schemas/sitemap/0.9"
ROOT = Path(__file__).resolve().parents[1]
DEFAULT_JSON = ROOT / "data" / "lireate-sitemap-static.json"

GROUP_ORDER = (
    "sitemap-services.xml",
    "sitemap-blog.xml",
    "sitemap-mktu.xml",
    "sitemap-tools.xml",
    "sitemap-portfolio.xml",
    "sitemap-pages.xml",
)


def _site_base(site_url):
    base = (site_url or "").strip().rstrip("/")
    if not base:
        return ""
    if "://" not in base:
        base = "https://" + base
    return base


def datetime_to_lastmod(ts):
    try:
        n = int(ts)
    except (TypeError, ValueError):
        return None
    return datetime.fromtimestamp(n, tz=timezone.utc).strftime("%Y-%m-%d")


def load_config(path=None):
    path = Path(path) if path else DEFAULT_JSON
    if not path.is_file():
        return {}
    return json.loads(path.read_text(encoding="utf-8"))


def classify_path(path):
    p = (path or "/").strip()
    if not p.startswith("/"):
        p = "/" + p
    if p.rstrip("/") == "/news" or p == "/news/":
        return "blog"
    if re.match(r"^/\d{1,2}/?$", p) or p.rstrip("/") == "/mktu":
        return "mktu"
    if p.startswith("/portfolio"):
        return "portfolio"
    if p.startswith("/patentnye-zadachi"):
        return "tools"
    for h in (
        "/registr", "/findtz", "/prodlenie", "/poisk_", "/ekspertiza",
        "/mezhdunarod", "/vozrazhenie", "/zashchita", "/zashhita",
        "/dogovor", "/spory", "/prekrash", "/priznanie", "/tovarnyi_znak",
        "/uslugi", "/avtor/", "/patent",
    ):
        if p.startswith(h):
            return "services"
    return "pages"


def _static_loc(base, path):
    path = (path or "/").strip()
    if path == "/":
        return base + "/"
    if not path.startswith("/"):
        path = "/" + path
    return base + path


def _url_node(loc, lastmod=None):
    loc_e = escape(loc)
    if lastmod:
        return (
            "  <url>\n    <loc>{0}</loc>\n    <lastmod>{1}</lastmod>\n  </url>".format(
                loc_e, lastmod
            )
        )
    return "  <url>\n    <loc>{0}</loc>\n  </url>".format(loc_e)


def build_urlset(nodes):
    body = "\n".join(nodes)
    return (
        '<?xml version="1.0" encoding="UTF-8"?>\n'
        '<urlset xmlns="{0}">\n'.format(NS) + body + "\n</urlset>\n"
    )


def build_index_xml(base, children):
    parts = []
    for fname, lm in children:
        loc = escape("{0}/{1}".format(base, fname))
        if lm:
            parts.append(
                "  <sitemap>\n    <loc>{0}</loc>\n    <lastmod>{1}</lastmod>\n  </sitemap>".format(
                    loc, lm
                )
            )
        else:
            parts.append("  <sitemap>\n    <loc>{0}</loc>\n  </sitemap>".format(loc))
    body = "\n".join(parts)
    return (
        '<?xml version="1.0" encoding="UTF-8"?>\n'
        '<sitemapindex xmlns="{0}">\n'.format(NS) + body + "\n</sitemapindex>\n"
    )


def build_sitemap_bundle(site_url, article_rows, config=None):
    cfg = config if config is not None else load_config()
    base = _site_base(site_url)
    if not base:
        raise ValueError("site_url required")

    exclude = set(cfg.get("exclude_article_slugs") or [])
    dedupe_once = set(cfg.get("dedupe_article_slugs") or [])
    seen_dedupe = set()
    seen_urls = set()
    groups = {g.replace("sitemap-", "").replace(".xml", ""): [] for g in GROUP_ORDER}

    max_article_ts = 0
    for row in article_rows or []:
        seo = (row.get("seo_url") or "").strip().strip("/")
        if not seo or seo in exclude:
            continue
        if seo in dedupe_once:
            if seo in seen_dedupe:
                continue
            seen_dedupe.add(seo)
        ts = row.get("datetime") or 0
        try:
            ts = int(ts)
        except (TypeError, ValueError):
            ts = 0
        if ts > max_article_ts:
            max_article_ts = ts
        loc = "{0}/{1}/".format(base, seo)
        if loc in seen_urls:
            continue
        seen_urls.add(loc)
        groups["blog"].append((loc, datetime_to_lastmod(ts)))

    site_lm = datetime_to_lastmod(max_article_ts) if max_article_ts else None

    for item in cfg.get("paths") or []:
        p = item.get("path") if isinstance(item, dict) else item
        if not p:
            continue
        grp = item.get("group") if isinstance(item, dict) and item.get("group") else None
        if not grp:
            grp = classify_path(p if p.startswith("/") else "/" + p)
        raw_loc = _static_loc(base, p)
        if raw_loc in seen_urls:
            continue
        seen_urls.add(raw_loc)
        src = item.get("lastmod_source") if isinstance(item, dict) else "site"
        lm = site_lm if src == "articles_max" else None
        groups.setdefault(grp, []).append((raw_loc, lm))

    out = {}
    index_children = []
    for fname in GROUP_ORDER:
        group_key = fname.replace("sitemap-", "").replace(".xml", "")
        nodes = []
        child_max = None
        for loc, lm in groups.get(group_key, []):
            nodes.append(_url_node(loc, lm))
            if lm and (child_max is None or lm > child_max):
                child_max = lm
        if nodes:
            out[fname] = build_urlset(nodes)
            index_children.append((fname, child_max))

    out["sitemap.xml"] = build_index_xml(base, index_children)
    return out


def url_count(bundle):
    total = 0
    for name, xml in bundle.items():
        if name == "sitemap.xml":
            continue
        total += xml.count("<loc>")
    return total