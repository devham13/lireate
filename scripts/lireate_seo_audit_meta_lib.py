"""SEO-audit P1 metadata + content text patches for hosting DOCROOT (not data/db.php)."""

from __future__ import annotations

import json
import re
import shutil
from datetime import datetime, timezone
from pathlib import Path

CMS_ROUTES_REL = Path("data/seo-audit-2026-11-15/p1-cms-pages.json")
META_MANIFEST_REL = Path("data/seo-audit-2026-11-15/p1-metadata.json")
CONTENT_MANIFEST_REL = Path("data/seo-audit-2026-11-15/p1-content-replacements.json")

TITLE_RE = re.compile(r"<title>[^<]*</title>", re.IGNORECASE)
META_DESC_RE = re.compile(
    r'<meta\s+name=["\']description["\']\s+content=["\'][^"\']*["\']',
    re.IGNORECASE,
)
OG_TITLE_RE = re.compile(
    r'<meta\s+property=["\']og:title["\']\s+content=["\'][^"\']*["\']',
    re.IGNORECASE,
)
OG_DESC_RE = re.compile(
    r'<meta\s+property=["\']og:description["\']\s+content=["\'][^"\']*["\']',
    re.IGNORECASE,
)
TW_TITLE_RE = re.compile(
    r'<meta\s+name=["\']twitter:title["\']\s+content=["\'][^"\']*["\']',
    re.IGNORECASE,
)
TW_DESC_RE = re.compile(
    r'<meta\s+name=["\']twitter:description["\']\s+content=["\'][^"\']*["\']',
    re.IGNORECASE,
)
PHP_SEO_TITLE_RE = re.compile(r"\$seo_title\s*=\s*['\"][^'\"]*['\"];?")
PHP_SEO_DESC_RE = re.compile(r"\$seo_description\s*=\s*['\"][^'\"]*['\"];?")
TITLE_AP_H1_RE = re.compile(
    r'(<div\s+class=["\']title-ap["\']>\s*<h1[^>]*>)[^<]*(</h1>)',
    re.IGNORECASE,
)


def load_json(office_repo: Path, rel: Path) -> dict:
    path = office_repo / rel
    if not path.is_file():
        raise FileNotFoundError("missing {0}".format(rel))
    return json.loads(path.read_text(encoding="utf-8"))


def load_cms_routes(office_repo: Path) -> list[dict]:
    return load_json(office_repo, CMS_ROUTES_REL).get("pages") or []


def load_cms_metadata(office_repo: Path) -> list[dict]:
    routes = {r["path"]: r for r in load_cms_routes(office_repo)}
    all_pages = load_json(office_repo, META_MANIFEST_REL).get("pages") or []
    out = []
    for page in all_pages:
        path = page.get("path")
        if path not in routes:
            continue
        merged = dict(page)
        merged["cms_page"] = routes[path].get("cms_page")
        merged["needles"] = routes[path].get("needles") or []
        merged["prefer_files"] = routes[path].get("prefer_files") or []
        out.append(merged)
    return out


def load_content_replacements(office_repo: Path) -> list[dict]:
    data = load_json(office_repo, CONTENT_MANIFEST_REL)
    items = data.get("replacements") or []
    if not items:
        raise ValueError("empty content replacements")
    return items


def _iter_php_files(root: Path, *, include_html: bool = False) -> list[Path]:
    suffixes = {".php"}
    if include_html:
        suffixes.add(".html")
    skip_roots = {".git", "node_modules", "vendor", ".cache"}
    files: list[Path] = []
    for path in root.rglob("*"):
        if not path.is_file():
            continue
        if path.suffix.lower() not in suffixes:
            continue
        rel = path.relative_to(root)
        if rel.parts and rel.parts[0] in skip_roots:
            continue
        if path.name == "db.php":
            continue
        files.append(path)
    return sorted(files)


def _score_file(path: Path, root: Path, meta: dict) -> int:
    try:
        text = path.read_text(encoding="utf-8", errors="replace")
    except OSError:
        return -1
    score = 0
    rel = str(path.relative_to(root)).replace("\\", "/")
    for pref in meta.get("prefer_files") or []:
        if pref in rel:
            score += 40
    for needle in meta.get("needles") or []:
        if needle in text:
            score += 25
    cms_page = meta.get("cms_page")
    if cms_page and cms_page != "main":
        if "$temp['page']=='{0}'".format(cms_page) in text:
            score += 60
    elif cms_page == "main":
        if "$temp['page']==''" in text:
            score += 35
    return score


def find_template_for_meta(root: Path, meta: dict) -> Path | None:
    best_score = 0
    best_path = None
    for path in _iter_php_files(root):
        score = _score_file(path, root, meta)
        if score > best_score:
            best_score = score
            best_path = path
    if best_score < 25:
        return None
    return best_path


def _esc_attr(value: str) -> str:
    return value.replace("&", "&amp;").replace('"', "&quot;")


def _esc_php(value: str) -> str:
    return value.replace("\\", "\\\\").replace("'", "\\'")


def patch_metadata_text(text: str, meta: dict) -> tuple[str, int]:
    total = 0
    title = meta.get("title") or ""
    description = meta.get("description") or ""
    h1 = meta.get("h1") or ""

    if title:
        new_title_tag = "<title>{0}</title>".format(_esc_attr(title))
        m = TITLE_RE.search(text)
        if m and m.group(0) != new_title_tag:
            text, n = TITLE_RE.subn(new_title_tag, text, count=1)
            total += n
        og = '<meta property="og:title" content="{0}">'.format(_esc_attr(title))
        if OG_TITLE_RE.search(text):
            text, n = OG_TITLE_RE.subn(og, text, count=1)
            total += n
        tw = '<meta name="twitter:title" content="{0}">'.format(_esc_attr(title))
        if TW_TITLE_RE.search(text):
            text, n = TW_TITLE_RE.subn(tw, text, count=1)
            total += n
        php = "$seo_title = '{0}';".format(_esc_php(title))
        if PHP_SEO_TITLE_RE.search(text):
            text, n = PHP_SEO_TITLE_RE.subn(php, text, count=1)
            total += n

    if description:
        meta_tag = '<meta name="description" content="{0}">'.format(_esc_attr(description))
        if META_DESC_RE.search(text):
            text, n = META_DESC_RE.subn(meta_tag, text, count=1)
            total += n
        og = '<meta property="og:description" content="{0}">'.format(_esc_attr(description))
        if OG_DESC_RE.search(text):
            text, n = OG_DESC_RE.subn(og, text, count=1)
            total += n
        tw = '<meta name="twitter:description" content="{0}">'.format(_esc_attr(description))
        if TW_DESC_RE.search(text):
            text, n = TW_DESC_RE.subn(tw, text, count=1)
            total += n
        php = "$seo_description = '{0}';".format(_esc_php(description))
        if PHP_SEO_DESC_RE.search(text):
            text, n = PHP_SEO_DESC_RE.subn(php, text, count=1)
            total += n

    if h1 and TITLE_AP_H1_RE.search(text):
        new_h1 = r"\1{0}\2".format(h1)
        text, n = TITLE_AP_H1_RE.subn(new_h1, text, count=1)
        total += n

    return text, total


def scan_meta_tree(root: Path, office_repo: Path) -> list[tuple[Path, dict, int]]:
    metas = load_cms_metadata(office_repo)
    hits: list[tuple[Path, dict, int]] = []
    for meta in metas:
        path = find_template_for_meta(root, meta)
        if path is None:
            continue
        text = path.read_text(encoding="utf-8", errors="replace")
        _, n = patch_metadata_text(text, meta)
        if n > 0:
            hits.append((path, meta, n))
    return hits


def apply_meta_tree(root: Path, office_repo: Path) -> list[tuple[Path, dict, int]]:
    applied: list[tuple[Path, dict, int]] = []
    for path, meta, _ in scan_meta_tree(root, office_repo):
        text = path.read_text(encoding="utf-8", errors="replace")
        new_text, n = patch_metadata_text(text, meta)
        if n and new_text != text:
            path.write_text(new_text, encoding="utf-8")
            applied.append((path, meta, n))
    return applied


def patch_content_text(text: str, replacements: list[dict], *, file_hint: str = "") -> tuple[str, int]:
    total = 0
    rel_hint = file_hint.replace("\\", "/")
    for rule in replacements:
        needles = rule.get("file_needles") or []
        if needles and not any(n in rel_hint or n in text for n in needles):
            continue
        old = rule.get("from") or ""
        new = rule.get("to") or ""
        if not old or old not in text:
            continue
        count = text.count(old)
        text = text.replace(old, new)
        total += count
    return text, total


def scan_content_tree(root: Path, office_repo: Path, *, include_html: bool = False) -> list[tuple[Path, int]]:
    replacements = load_content_replacements(office_repo)
    hits: list[tuple[Path, int]] = []
    for path in _iter_php_files(root, include_html=include_html):
        rel = str(path.relative_to(root))
        text = path.read_text(encoding="utf-8", errors="replace")
        _, n = patch_content_text(text, replacements, file_hint=rel)
        if n:
            hits.append((path, n))
    return hits


def apply_content_tree(root: Path, office_repo: Path, *, include_html: bool = False) -> list[tuple[Path, int]]:
    replacements = load_content_replacements(office_repo)
    applied: list[tuple[Path, int]] = []
    for path in _iter_php_files(root, include_html=include_html):
        rel = str(path.relative_to(root))
        text = path.read_text(encoding="utf-8", errors="replace")
        new_text, n = patch_content_text(text, replacements, file_hint=rel)
        if n and new_text != text:
            path.write_text(new_text, encoding="utf-8")
            applied.append((path, n))
    return applied


def make_backup_id() -> str:
    return datetime.now(timezone.utc).strftime("%Y%m%dT%H%M%SZ")


def backup_files(backup_dir: Path, root: Path, paths: list[Path]) -> None:
    backup_dir.mkdir(parents=True, exist_ok=True)
    for path in paths:
        rel = path.relative_to(root)
        dest = backup_dir / rel
        dest.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(path, dest)