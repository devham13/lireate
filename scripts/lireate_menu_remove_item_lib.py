"""Remove a CMS left-sidebar menu item (leftSideNaviInSection) from hosting DOCROOT."""

from __future__ import annotations

import re
from pathlib import Path

from lireate_hosting_menu_add import _iter_php_files, _public_href  # noqa: E402
from lireate_hosting_services_lib import load_manifest  # noqa: E402

DEFAULT_REMOVE_SLUG = "registraciya-tovarnogo-znaka-s-oplatoy-za-rezultat"
MENU_MARKER_PREFIX = "lireate-menu:"

ANCHOR_WITH_MARKER_RE = re.compile(
    r"(\s*<!--\s*"
    + re.escape(MENU_MARKER_PREFIX)
    + r"(?P<slug>[\w\-]+)\s*-->\s*)"
    r"(<a\b[^>]*class=\"[^\"]*leftSideNaviInSection[^\"]*\"[^>]*>.*?</a>\s*)",
    re.IGNORECASE | re.DOTALL,
)

HREF_ANCHOR_RE = re.compile(
    r"\s*<a\b[^>]*href=[\"'](?P<href>[^\"']+)[\"'][^>]*class=[\"'][^\"']*leftSideNaviInSection[^\"']*[\"'][^>]*>.*?</a>\s*",
    re.IGNORECASE | re.DOTALL,
)


def menu_title_from_manifest(manifest: dict) -> str:
    return (manifest.get("menu_title") or manifest.get("title") or "").strip()


def resolve_target(office_repo: Path, slug: str) -> tuple[str, str, str]:
    """Return (slug, public_href, menu_title)."""
    service_dir = office_repo / "pages" / "services" / slug
    if not service_dir.is_dir():
        raise ValueError("missing pages/services/{0}".format(slug))
    manifest = load_manifest(service_dir)
    href = _public_href(manifest["url_path"])
    title = menu_title_from_manifest(manifest)
    if not title:
        raise ValueError("empty menu title in manifest for {0}".format(slug))
    return slug, href, title


def find_sidebar_templates(docroot: Path, slug: str, href: str, title: str) -> list[Path]:
    marker = MENU_MARKER_PREFIX + slug
    hits: list[Path] = []
    for path in _iter_php_files(docroot):
        try:
            text = path.read_text(encoding="utf-8", errors="replace")
        except OSError:
            continue
        if marker in text or (href in text and title in text and "leftSideNaviInSection" in text):
            hits.append(path)
    return hits


def _normalize_href(href: str) -> str:
    return "/" + href.strip("/") + "/"


def remove_menu_item(text: str, slug: str, href: str, title: str) -> tuple[str, bool]:
    """Strip marker + anchor for slug; fallback: anchor by href."""
    marker = MENU_MARKER_PREFIX + slug
    changed = False

    def repl_marker(m: re.Match) -> str:
        nonlocal changed
        if m.group("slug") != slug:
            return m.group(0)
        changed = True
        return ""

    new_text = ANCHOR_WITH_MARKER_RE.sub(repl_marker, text)
    if changed:
        return new_text, True

    norm = _normalize_href(href)
    for m in list(HREF_ANCHOR_RE.finditer(new_text)):
        block_href = _normalize_href(m.group("href"))
        if block_href != norm and m.group("href") != href:
            continue
        if title not in m.group(0):
            continue
        new_text = new_text[: m.start()] + new_text[m.end() :]
        changed = True
        break

    if marker in new_text:
        new_text = re.sub(
            r"\s*<!--\s*" + re.escape(marker) + r"\s*-->\s*",
            "",
            new_text,
            count=1,
        )
        changed = True

    return new_text, changed


def scan_office_repo_sidebar_copies(office_repo: Path, href: str, title: str) -> list[Path]:
    """PHP files in office-repo that embed the same CMS sidebar link (not service body)."""
    norm = _normalize_href(href)
    found: list[Path] = []
    skip = {"data", ".git", "node_modules", ".cache"}
    for path in sorted(office_repo.rglob("*.php")):
        rel = path.relative_to(office_repo)
        if rel.parts and rel.parts[0] in skip:
            continue
        if "data" in rel.parts and path.name == "db.php":
            continue
        try:
            text = path.read_text(encoding="utf-8", errors="replace")
        except OSError:
            continue
        if "leftSideNaviInSection" not in text:
            continue
        if norm not in text and href not in text:
            continue
        if title not in text:
            continue
        found.append(path)
    return found


def verify_menu_http(base_url: str, href: str, title: str, page_path: str) -> list[str]:
    from lireate_blog_install_lib import http_probe

    errors: list[str] = []
    import time

    bust = str(int(time.time()))
    home_status, home_body = http_probe(base_url, "/", cache_bust=bust)
    if home_status != 200:
        errors.append("homepage HTTP {0}".format(home_status))
    else:
        needle = 'href="{0}" class="leftSideNaviInSection"'.format(_normalize_href(href))
        needle2 = 'leftSideNaviInSection">{0}</a>'.format(title)
        if needle in home_body or needle2 in home_body:
            errors.append("homepage still contains sidebar link ({0})".format(title))

    page_status, _ = http_probe(base_url, page_path, cache_bust=bust)
    if page_status != 200:
        errors.append("service page HTTP {0} for {1}".format(page_status, page_path))

    return errors


def backup_dir_for_id(backups_root: Path, backup_id: str) -> Path:
    return backups_root / ("menu-remove-" + backup_id)
