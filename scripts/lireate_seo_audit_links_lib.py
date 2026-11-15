"""Apply SEO-audit internal link replacements across CMS PHP (never data/db.php)."""

from __future__ import annotations

import json
import re
import shutil
from datetime import datetime, timezone
from pathlib import Path
from urllib.parse import urlparse

MANIFEST_REL = Path("data/seo-audit-2026-11-15/link-replacements.json")
INSTALL_MARKER = "lireate-seo-audit-links:2026-11-15"
HREF_ATTR_RE = re.compile(
    r"(?P<prefix>\bhref\s*=\s*)(?P<quote>['\"])(?P<url>[^'\"]*)(?P=quote)",
    re.IGNORECASE,
)


def load_replacements(office_repo: Path) -> list[dict]:
    path = office_repo / MANIFEST_REL
    if not path.is_file():
        raise FileNotFoundError("missing {0}".format(MANIFEST_REL))
    data = json.loads(path.read_text(encoding="utf-8"))
    items = data.get("replacements") or []
    if not items:
        raise ValueError("empty replacements in manifest")
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
        if "data" in rel.parts and path.name == "db.php":
            continue
        if path.name == "db.php" and any(p == "data" for p in rel.parts):
            continue
        files.append(path)
    return sorted(files)


def _strip_host(url: str) -> str:
    u = url.strip()
    if u.startswith("//"):
        u = "https:" + u
    if "://" in u:
        parsed = urlparse(u)
        path = parsed.path or "/"
        if parsed.query:
            path = path + "?" + parsed.query
        return path
    return u


def _replacement_lookup(replacements: list[dict]) -> list[dict]:
    ordered = sorted(replacements, key=lambda r: len(str(r.get("from") or "")), reverse=True)
    return ordered


def _match_replacement(url: str, item: dict) -> bool:
    src = str(item.get("from") or "")
    if not src:
        return False
    if src.startswith("http"):
        return url == src or _strip_host(url) == _strip_host(src)
    path = _strip_host(url)
    if path == src:
        return True
    alt = src.rstrip("/")
    if path.rstrip("/") == alt:
        return True
    return path == src.rstrip("/")


def _rewrite_url(url: str, item: dict) -> str:
    dst = str(item.get("to") or "")
    if not dst:
        return url
    if url.startswith("http") and dst.startswith("/"):
        parsed = urlparse(url)
        scheme = parsed.scheme or "https"
        host = parsed.netloc
        if host:
            return "{0}://{1}{2}".format(scheme, host, dst)
    return dst


def patch_href_value(url: str, replacements: list[dict]) -> str:
    for item in _replacement_lookup(replacements):
        if _match_replacement(url, item):
            return _rewrite_url(url, item)
    return url


def patch_text_content(text: str, replacements: list[dict]) -> tuple[str, int]:
    changes = 0
    patched = text

    def repl_href(m: re.Match) -> str:
        nonlocal changes
        old = m.group("url")
        new = patch_href_value(old, replacements)
        if new != old:
            changes += 1
        q = m.group("quote")
        return "{0}{1}{2}{1}".format(m.group("prefix"), q, new)

    patched = HREF_ATTR_RE.sub(repl_href, patched)

    # Stray closing quote in href (typo on live patent article).
    bad_paths = (
        "/kak_annulirovat_patent_konkurenta_esli_ego_pretenzii_nepravomernyi/\u2019",
        "/kak_annulirovat_patent_konkurenta_esli_ego_pretenzii_nepravomernyi/’",
    )
    good = (
        "https://www.consultant.ru/document/cons_doc_LAW_64629/"
        "d64961e488e2fc6119b55f68178ea9051a94353f/"
    )
    for bad in bad_paths:
        needle = 'href="{0}"'.format(bad)
        if needle in patched:
            patched = patched.replace(needle, 'href="{0}"'.format(good))
            changes += 1

    for item in _replacement_lookup(replacements):
        src = str(item.get("from") or "")
        dst = str(item.get("to") or "")
        if not src or src.startswith("http"):
            continue
        for quote in ('"', "'"):
            for variant in {src, src.rstrip("/"), src.rstrip("/") + "/"}:
                token = "{0}{1}{0}".format(quote, variant)
                if token in patched and variant != dst:
                    patched = patched.replace(token, "{0}{1}{0}".format(quote, dst))
                    changes += 1
    return patched, changes


def scan_tree(root: Path, replacements: list[dict], *, include_html: bool = False) -> list[tuple[Path, int]]:
    hits: list[tuple[Path, int]] = []
    for path in _iter_php_files(root, include_html=include_html):
        try:
            original = path.read_text(encoding="utf-8", errors="replace")
        except OSError:
            continue
        patched, n = patch_text_content(original, replacements)
        if n > 0:
            hits.append((path, n))
    return hits


def apply_tree(root: Path, replacements: list[dict], *, include_html: bool = False) -> list[tuple[Path, int]]:
    applied: list[tuple[Path, int]] = []
    for path, _ in scan_tree(root, replacements, include_html=include_html):
        original = path.read_text(encoding="utf-8", errors="replace")
        patched, n = patch_text_content(original, replacements)
        if patched != original:
            path.write_text(patched, encoding="utf-8")
            applied.append((path, n))
    return applied


def make_backup_id() -> str:
    return datetime.now(timezone.utc).strftime("%Y%m%dT%H%M%SZ")


def backup_files(backup_dir: Path, docroot: Path, paths: list[Path]) -> None:
    backup_dir.mkdir(parents=True, exist_ok=True)
    for path in paths:
        rel = path.relative_to(docroot)
        dest = backup_dir / rel
        dest.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(path, dest)


def restore_backup(backup_dir: Path, docroot: Path) -> int:
    restored = 0
    for src in sorted(backup_dir.rglob("*")):
        if not src.is_file():
            continue
        rel = src.relative_to(backup_dir)
        if rel.name == "created.json":
            continue
        dest = docroot / rel
        dest.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(src, dest)
        restored += 1
    return restored