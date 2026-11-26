#!/usr/bin/env python3
"""Transform fetched public-site HTML into Nav V3 overlay (PR #129 + hosting install)."""
from __future__ import annotations

import re
import subprocess
from pathlib import Path
from urllib.parse import urljoin, urlparse

ROOT = Path(__file__).resolve().parents[1]
def _live_site_root():
    import os

    host = (os.environ.get("PUBLIC_SITE_HOST") or "").strip()
    if not host:
        raise RuntimeError("Set PUBLIC_SITE_HOST for live overlay capture")
    return ("https://" + host).rstrip("/")


SITE = _live_site_root()
NAV_QS = "20261126r7"

SERVICE_PATHS = {
    "/findtz/",
    "/registraciya_tovarnogo_znaka/",
    "/prodlenie_tovarnogo_znaka/",
}

SIX_SERVICE_PATHS = {
    "/findtz/",
    "/registraciya_tovarnogo_znaka/",
    "/prodlenie_tovarnogo_znaka/",
}

PR_CSS = [
    "/template/css/lireate-navigation-dual-v3.css",
    "/template/css/lireate-public-cms-chrome.css",
    "/template/css/lireate-global-subnav-light-v4.css",
    "/template/css/lireate-cms-pages-light-v4.css",
    "/template/css/lireate-service-cms-chrome-layout.css",
]
PR_JS = ["/template/js/lireate-navigation-dual-v3.js"]

ATTR_RE = re.compile(
    r'\b(href|src|action|data-src|data-href|poster)=(["\'])(.*?)\2',
    re.I | re.S,
)


def fetch_live(path: str) -> str:
    import urllib.request

    url = SITE + path if path.startswith("/") else SITE + "/" + path
    req = urllib.request.Request(url, headers={"User-Agent": "lireate-nav-v3-live-overlay/1.0"})
    with urllib.request.urlopen(req, timeout=120) as resp:
        final = resp.geturl()
        body = resp.read().decode("utf-8", errors="replace")
    base_path = urlparse(final).path or path
    if not base_path.endswith("/"):
        base_path = base_path.rsplit("/", 1)[0] + "/"
    return absolutize_html(body, base_path)


def resolve_url(val: str, page_path: str) -> str:
    v = val.strip()
    if not v or v.startswith(("#", "data:", "mailto:", "tel:", "javascript:")):
        return val
    if v.startswith("//"):
        return "https:" + v
    if re.match(r"^https?://", v, re.I):
        return v
    if v.startswith("/"):
        return SITE + v
    base = page_path if page_path.endswith("/") else page_path + "/"
    joined = urljoin(base, v)
    if joined.startswith("/"):
        return SITE + joined
    return SITE + "/" + joined.lstrip("/")


def absolutize_html(html: str, page_path: str) -> str:
    def repl(m):
        attr, quote, url = m.group(1), m.group(2), m.group(3)
        return '%s=%s%s%s' % (attr, quote, resolve_url(url, page_path), quote)

    return ATTR_RE.sub(repl, html)


def php_fragment(uri: str, mode: str = "both") -> str:
    proc = subprocess.run(
        ["php", str(ROOT / "pages/_preview/nav-v3-chrome-fragment.php"), uri, mode],
        cwd=str(ROOT),
        stdout=subprocess.PIPE,
        stderr=subprocess.PIPE,
        universal_newlines=True,
    )
    if proc.returncode != 0:
        raise RuntimeError("fragment failed: " + proc.stderr)
    return proc.stdout


def finalize_absolute_assets(soup, local_origin: str, page_path: str = "/"):
    for tag in soup.find_all(True):
        for attr in ("href", "src", "content", "data-src", "poster"):
            if not tag.has_attr(attr):
                continue
            val = tag.get(attr) or ""
            if not val or val.startswith(("#", "data:", "mailto:", "tel:", "javascript:")):
                continue
            if val.startswith(local_origin) or val.startswith("http://127.0.0.1"):
                continue
            if val.startswith(SITE):
                continue
            if val.startswith("//"):
                tag[attr] = "https:" + val
            elif val.startswith("/"):
                tag[attr] = SITE + val
            elif "/" not in val.split("?", 1)[0]:
                base = page_path if page_path.endswith("/") else page_path + "/"
                tag[attr] = resolve_url(val, base)


def find_cms_wrap_target(soup):
    pc = soup.find("div", class_="page_container")
    if pc and len(pc.get_text(strip=True)) >= 60:
        return pc
    for cls in ("txt-content", "lireate-contacts-body", "lireate-team-body"):
        node = soup.find(class_=cls)
        if node and len(node.get_text(strip=True)) >= 60:
            parent = node
            for _ in range(4):
                if parent.parent and parent.parent.name == "div":
                    parent = parent.parent
                else:
                    break
            return parent
    for sec_id in ("lireate-contacts", "lireate-about", "lireate-home"):
        sec = soup.find(id=sec_id)
        if sec:
            return sec
    return pc


def inject_pr_assets(soup, local_origin: str):
    from bs4 import BeautifulSoup

    block = BeautifulSoup("", "html.parser")
    for path in PR_CSS:
        tag = soup.new_tag(
            "link",
            rel="stylesheet",
            href="%s%s?v=%s" % (local_origin, path, NAV_QS),
        )
        tag["data-lireate-pr-overlay"] = "1"
        block.append(tag)
    for path in PR_JS:
        tag = soup.new_tag(
            "script",
            src="%s%s?v=%s" % (local_origin, path, NAV_QS),
            defer="defer",
        )
        tag["data-lireate-pr-overlay"] = "1"
        block.append(tag)
    if soup.head:
        soup.head.append(block)


def apply_overlay(html: str, page_path: str, local_origin: str) -> str:
    from bs4 import BeautifulSoup

    uri = page_path if page_path.endswith("/") else page_path + "/"
    if not uri.startswith("/"):
        uri = "/" + uri

    soup = BeautifulSoup(html, "html.parser")
    if not soup.body or not soup.head:
        return html

    is_service = any(
        uri.rstrip("/") == p.rstrip("/") or uri.startswith(p)
        for p in SERVICE_PATHS
    ) and soup.find("main", class_=re.compile(r"lireate-svc-page"))

    chrome = php_fragment(uri, "chrome")
    subnav = php_fragment(uri, "subnav")

    # Legacy CMS sidebar
    legacy = soup.find(id="leftSideBar")
    if legacy and legacy.name == "section" and "lireate-public-cms-sidebar" not in (
        legacy.get("class") or []
    ):
        legacy.decompose()
    elif legacy and "lireate-public-cms-sidebar" not in (legacy.get("class") or []):
        legacy.decompose()

    soup.body.insert(0, BeautifulSoup(chrome, "html.parser"))

    body_cls = soup.body.get("class") or []
    if isinstance(body_cls, str):
        body_cls = body_cls.split()
    for cls in (
        "lireate-public-cms-chrome",
        "lireate-theme-light-v4",
    ):
        if cls not in body_cls:
            body_cls.append(cls)
    if is_service:
        if "lireate-svc-cms-chrome" not in body_cls:
            body_cls.append("lireate-svc-cms-chrome")
        rpath = uri.rstrip("/") + "/"
        if any(rpath.startswith(p) for p in SIX_SERVICE_PATHS) and "lx-photo-v4" not in body_cls:
            body_cls.append("lx-photo-v4")
        shell = soup.find("div", class_=re.compile(r"\blx-nav-shell\b"))
        if shell:
            shell["hidden"] = "hidden"
            shell["style"] = "display:none!important"
        main = soup.find("main")
        if main:
            wrap = soup.new_tag("div", **{"class": "lireate-public-cms-content"})
            main.insert_before(wrap)
            wrap.append(BeautifulSoup(subnav, "html.parser"))
            wrap.append(main.extract())
    else:
        target = find_cms_wrap_target(soup)
        if target:
            wrap = soup.new_tag("div", **{"class": "lireate-public-cms-content"})
            target.insert_before(wrap)
            wrap.append(BeautifulSoup(subnav, "html.parser"))
            wrap.append(target.extract())

    soup.body["class"] = body_cls
    inject_pr_assets(soup, local_origin)
    finalize_absolute_assets(soup, local_origin, page_path)
    out = str(soup)
    return absolutize_html(out, page_path)