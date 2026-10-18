#!/usr/bin/env python3
"""Lint LIREATE service pages (SEO + shared design assets)."""

import argparse
import json
import re
import sys
from pathlib import Path

REPO_ROOT = Path(__file__).resolve().parents[1]
SERVICES = REPO_ROOT / "pages" / "services"
SHARED_MARK = "assets/lireate-shared/lireate-service-dark.css"


def _read(path):
    return path.read_text(encoding="utf-8", errors="replace")


def _php_assign(html, var_name):
    m = re.search(
        r"\$" + re.escape(var_name) + r"\s*=\s*'((?:\\'|[^'])*)'",
        html,
    )
    if m:
        return m.group(1).replace("\\'", "'")
    m = re.search(
        r'\$' + re.escape(var_name) + r'\s*=\s*"((?:\\"|[^"])*)"',
        html,
    )
    if m:
        return m.group(1).replace('\\"', '"')
    return None


def _meta_content(html, name=None, prop=None):
    if name:
        m = re.search(
            r'<meta\s+name=["\']' + re.escape(name) + r'["\']\s+content=["\']([^"\']*)["\']',
            html,
            re.I,
        )
    else:
        m = re.search(
            r'<meta\s+property=["\']' + re.escape(prop) + r'["\']\s+content=["\']([^"\']*)["\']',
            html,
            re.I,
        )
    return m.group(1) if m else None


def _title_text(html):
    m = re.search(r"<title>([^<]+)</title>", html, re.I)
    return m.group(1).strip() if m else None


def _json_ld_blocks(html):
    return re.findall(
        r'<script\s+type=["\']application/ld\+json["\'][^>]*>(.*?)</script>',
        html,
        re.I | re.S,
    )


def _flatten_types(obj, found=None):
    if found is None:
        found = set()
    if isinstance(obj, dict):
        t = obj.get("@type")
        if isinstance(t, str):
            found.add(t)
        elif isinstance(t, list):
            found.update(t)
        for v in obj.values():
            _flatten_types(v, found)
    elif isinstance(obj, list):
        for item in obj:
            _flatten_types(item, found)
    return found


def lint_slug(slug):
    errors = []
    warnings = []
    service_dir = SERVICES / slug
    index = service_dir / "index.php"
    if not index.is_file():
        index = service_dir / "index.html"
    if not index.is_file():
        return ["missing index.php or index.html"], warnings

    raw = _read(index)
    html = raw
    manifest = service_dir / "publish.manifest.json"
    if not manifest.is_file():
        errors.append("missing publish.manifest.json")

    if SHARED_MARK not in html:
        errors.append("must link " + SHARED_MARK)
    shared_css = service_dir / "assets" / "lireate-shared" / "lireate-service-dark.css"
    if not shared_css.is_file():
        errors.append("run scripts/lireate_service_sync_shared.py (missing " + str(shared_css.relative_to(service_dir)) + ")")

    h1 = re.findall(r"<h1\b", html, re.I)
    if len(h1) != 1:
        errors.append("expected exactly one <h1>, found {0}".format(len(h1)))

    title = _title_text(html)
    if not title or "<?php" in title:
        title = _php_assign(raw, "seo_title") or title
    if not title or "TODO" in title:
        errors.append("missing or placeholder <title> / $seo_title")
    elif len(title) > 60:
        errors.append("title longer than 60 chars ({0})".format(len(title)))

    desc = _meta_content(html, name="description")
    if not desc or "$seo_description" in desc:
        desc = _php_assign(raw, "seo_description")
    if not desc:
        errors.append("missing meta description")
    else:
        n = len(desc)
        if n < 140 or n > 160:
            errors.append("meta description length {0} (want 140–160)".format(n))

    if not re.search(r'<link\s+rel=["\']canonical["\']', html, re.I):
        errors.append("missing canonical link")

    for prop in ("og:title", "og:description", "og:url", "og:type"):
        if not _meta_content(html, prop=prop):
            errors.append("missing meta property " + prop)
    for name in ("twitter:card", "twitter:title", "twitter:description"):
        if not _meta_content(html, name=name):
            errors.append("missing meta name " + name)

    if re.search(r'noindex', html, re.I):
        errors.append("contains noindex")

    types = set()
    for block in _json_ld_blocks(html):
        if "<?php" in block or "json_encode" in block:
            if "'@type' => 'Service'" in raw or '"@type": "Service"' in raw:
                types.add("Service")
            if "LegalService" in raw:
                types.add("LegalService")
            if "Organization" in raw:
                types.add("Organization")
            if "BreadcrumbList" in raw:
                types.add("BreadcrumbList")
            if "FAQPage" in raw:
                types.add("FAQPage")
            continue
        try:
            data = json.loads(block.strip())
        except json.JSONDecodeError:
            errors.append("invalid JSON-LD block")
            continue
        types |= _flatten_types(data)

    if "Service" not in types:
        errors.append("JSON-LD must include @type Service")
    if not (types & {"LegalService", "Organization"}):
        errors.append("JSON-LD must include LegalService or Organization (LIREATE)")
    if "BreadcrumbList" not in types:
        errors.append("JSON-LD must include BreadcrumbList")
    if "FAQPage" not in types:
        errors.append("JSON-LD must include FAQPage")

    h2_before_h1 = re.search(r"<h2\b.*<h1\b", html, re.I | re.S)
    if h2_before_h1:
        errors.append("h2 appears before h1")

    for img in re.finditer(r"<img\b[^>]*>", html, re.I):
        tag = img.group(0)
        if not re.search(r'\balt=["\']', tag, re.I):
            errors.append("img without alt attribute")

    internal = re.findall(r'<a\s+[^>]*href=["\'](/[^"\']+)["\']', html, re.I)
    internal = [u for u in internal if not u.startswith("//")]
    if len(internal) < 2:
        errors.append("need at least 2 internal links (href starting with /)")

    if "link-telegram" not in html and "data-lireate-telegram" not in html:
        errors.append("missing messenger hook (link-telegram or data-lireate-telegram on <html>)")

    if "lireate-contact.js" not in html:
        errors.append("must include assets/lireate-shared/lireate-contact.js")

    if re.search(r'id=["\']lf-email["\']', html, re.I):
        errors.append("lead form must not include lf-email (use name + phone only)")
    if re.search(r'id=["\']lf-company["\']', html, re.I):
        errors.append("lead form must not include lf-company")
    if re.search(r'id=["\']lf-cert["\']', html, re.I):
        errors.append("lead form must not include lf-cert")
    if re.search(r'id=["\']lf-msg["\']', html, re.I):
        errors.append("lead form must not include lf-msg textarea")

    if "Получить консультацию" not in html:
        errors.append('submit button must read "Получить консультацию"')
    if "Отправить бриф" in html:
        errors.append('deprecated submit label "Отправить бриф"')

    if re.search(r'id=["\']dock-telegram["\'][^>]*>\s*TG\s*<', html, re.I):
        errors.append("dock-telegram must use SVG icon, not text TG")
    if re.search(r'id=["\']dock-max["\'][^>]*>\s*M\s*<', html, re.I):
        errors.append("dock-max must use SVG icon, not text M")

    if 'id="hero-max"' not in html and "id='hero-max'" not in html:
        errors.append("missing hero CTA id=hero-max (Написать в Max)")

    if "lx-form-alt" not in html:
        errors.append("missing lx-form-alt block under lead form")

    if 'href="/private_data"' not in html and "href='/private_data'" not in html:
        errors.append("lead form consent must link to /private_data")
    if 'href="/politika_konfidencialnosti"' not in html and "href='/politika_konfidencialnosti'" not in html:
        errors.append("lead form consent must link to /politika_konfidencialnosti")
    for bad in ("/privacy", "/privacy/", "/policy", "/policy/"):
        if bad in html:
            errors.append("forbidden privacy URL {0}; use /private_data and /politika_konfidencialnosti".format(bad))

    if not re.search(r'href=["\']tel:', html, re.I) and "/contacts/" not in html:
        warnings.append("no tel: link; ensure /contacts/ for phone")

    if "lireate-svc-page" not in html and "lireate-cn-page" not in html:
        errors.append("main must use class lireate-svc-page (or legacy lireate-cn-page)")

    if "lx-nav-pill" not in html:
        errors.append("missing lx-nav-pill (standard service layout)")

    has_footer_include = "lireate-service-footer.inc.php" in raw
    if "lx-site-footer" not in html and not has_footer_include:
        errors.append("missing lx-site-footer (include lireate-service-footer.inc.php)")
    if 'href="/politika_konfidencialnosti"' not in html and "href='/politika_konfidencialnosti'" not in html:
        pass  # checked above for form; footer duplicate ok
    footer_block = re.search(r'class=["\'][^"\']*lx-site-footer', html, re.I)
    if footer_block:
        foot_slice = html[footer_block.start() : footer_block.start() + 2500]
        if "/politika_konfidencialnosti" not in foot_slice:
            errors.append("footer must link to /politika_konfidencialnosti")
        if "/private_data" not in foot_slice:
            errors.append("footer must link to /private_data")

    for script in (
        "lireate-motion-init.js",
        "lireate-mascot.js",
        "lireate-hero-panel.js",
    ):
        if script not in html:
            errors.append("must include assets/lireate-shared/" + script)

    if "lireate-service-footer.inc.php" not in raw and "lx-site-footer" not in html:
        errors.append("missing footer include lireate-service-footer.inc.php")

    if slug != "_template":
        if "lx_reviews_demo_only" in raw or "lx-review-demo-badge" in raw:
            errors.append("demo review card allowed only in pages/services/_template")
        if "Пример оформления" in raw:
            errors.append("demo review badge must not appear on published service pages")

    return errors, warnings


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--slug", required=True)
    args = parser.parse_args()
    errors, warnings = lint_slug(args.slug)
    if warnings:
        for w in warnings:
            print("WARN: " + w)
    if errors:
        for e in errors:
            print("ERROR: " + e)
        return 1
    print("OK: " + args.slug)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
