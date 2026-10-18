#!/usr/bin/env python3
"""
Insert a CMS sidebar item (class leftSideNaviInSection) after an anchor link in a DOCROOT template.

Does not touch data/db.php. Dry-run by default; --apply writes backup + patched file.
"""

from __future__ import annotations

import argparse
import difflib
import json
import os
import re
import shutil
import sys
from datetime import datetime, timezone
from pathlib import Path

REPO_ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(REPO_ROOT / "scripts"))

from lireate_dotenv import load_dotenv  # noqa: E402
from lireate_hosting_services_lib import (  # noqa: E402
    load_manifest,
    menu_after_for_slug,
)

DB_PHP_SUFFIX = Path("data") / "db.php"
ANCHOR_RE = re.compile(
    r"(<a\b[^>]*class=\"[^\"]*leftSideNaviInSection[^\"]*\"[^>]*>.*?</a>)",
    re.IGNORECASE | re.DOTALL,
)


def _default_autopub_home() -> Path:
    return Path(os.environ.get("LIREATE_AUTOPUB_HOME") or (Path.home() / "lireate-autopub"))


def _truthy(name: str) -> bool:
    v = (os.environ.get(name) or "").strip().lower()
    return v in ("1", "yes", "true", "on")


def _public_href(url_path: str) -> str:
    path = "/" + url_path.strip("/") + "/"
    return path.replace("//", "/")


def _menu_title(manifest: dict) -> str:
    return (manifest.get("menu_title") or manifest.get("title") or manifest.get("slug") or "").strip()


def _menu_after_text(manifest: dict, office_repo: Path, slug: str) -> str:
    return (
        (manifest.get("menu_after") or "").strip()
        or menu_after_for_slug(office_repo, slug)
        or "Регистрация товарного знака"
    )


def _iter_php_files(docroot: Path) -> list[Path]:
    skip_parts = {"data"}
    files: list[Path] = []
    for path in docroot.rglob("*.php"):
        if DB_PHP_SUFFIX in path.relative_to(docroot).parts:
            continue
        if "db.php" in path.name and "data" in path.parts:
            continue
        rel = path.relative_to(docroot)
        if rel.parts and rel.parts[0] in skip_parts and path.name == "db.php":
            continue
        if path.name == "db.php" and any(p == "data" for p in path.parts):
            continue
        files.append(path)
    return sorted(files)


def find_sidebar_template(docroot: Path, anchor_text: str) -> Path | None:
    needle = anchor_text.strip()
    for path in _iter_php_files(docroot):
        if path.match("**/data/db.php"):
            continue
        try:
            text = path.read_text(encoding="utf-8", errors="replace")
        except OSError:
            continue
        if "leftSideNaviInSection" not in text:
            continue
        if needle and needle not in text:
            continue
        if "registraciya_tovarnogo_znaka" in text or needle in text:
            return path
    return None


def _already_has_menu(text: str, slug: str, href: str) -> bool:
    marker = "lireate-menu:{0}".format(slug)
    if marker in text:
        return True
    if href in text and "leftSideNaviInSection" in text:
        return True
    return False


def _find_anchor_insert_pos(text: str, anchor_text: str) -> int | None:
    """Return index after the closing </a> of the anchor whose visible text matches."""
    for m in ANCHOR_RE.finditer(text):
        block = m.group(1)
        if anchor_text not in block:
            continue
        if "leftSideNaviInSection" not in block:
            continue
        return m.end()
    return None


def build_menu_line(href: str, title: str, slug: str, indent: str = "            ") -> str:
    return (
        '{0}<!-- lireate-menu:{1} -->\n'
        '{0}<a href="{2}" class="leftSideNaviInSection">{3}</a>\n'
    ).format(indent, slug, href, title)


def patch_sidebar(text: str, slug: str, href: str, title: str, anchor_text: str) -> tuple[str, bool]:
    if _already_has_menu(text, slug, href):
        return text, False
    pos = _find_anchor_insert_pos(text, anchor_text)
    if pos is None:
        raise ValueError("anchor not found for menu_after={0!r}".format(anchor_text))
    line = build_menu_line(href, title, slug)
    return text[:pos] + "\n" + line + text[pos:], True


def main() -> int:
    parser = argparse.ArgumentParser(description="Add LIREATE service link to CMS sidebar template")
    parser.add_argument("--slug", required=True)
    parser.add_argument("--apply", action="store_true")
    parser.add_argument("--office-repo", type=Path, default=None)
    parser.add_argument("--env-file", type=Path, default=None)
    args = parser.parse_args()

    home = _default_autopub_home()
    env_file = args.env_file or Path(os.environ.get("LIREATE_ENV_FILE") or (home / ".env"))
    if env_file.is_file():
        load_dotenv(env_file, override=False)

    slug = args.slug.strip()
    office_repo = (
        args.office_repo
        or Path(os.environ.get("LIREATE_OFFICE_REPO") or (home / "office-repo"))
    ).expanduser().resolve()

    docroot_raw = (os.environ.get("LIREATE_DOCROOT") or "").strip()
    if not docroot_raw:
        print("STATUS: BLOCKER — set LIREATE_DOCROOT")
        return 2
    docroot = Path(docroot_raw).expanduser().resolve()
    if not docroot.is_dir():
        print("STATUS: BLOCKER — LIREATE_DOCROOT is not a directory")
        return 2

    service_dir = office_repo / "pages" / "services" / slug
    if not service_dir.is_dir():
        print("STATUS: BLOCKER — missing pages/services/{0}".format(slug))
        return 2

    try:
        manifest = load_manifest(service_dir)
    except (ValueError, json.JSONDecodeError) as exc:
        print("STATUS: BLOCKER — {0}".format(exc))
        return 2

    href = _public_href(manifest["url_path"])
    title = _menu_title(manifest)
    anchor_text = _menu_after_text(manifest, office_repo, slug)

    template = find_sidebar_template(docroot, anchor_text)
    if template is None:
        print("STATUS: BLOCKER — sidebar template not found (leftSideNaviInSection + {0})".format(anchor_text))
        return 2

    print("=== MENU ADD PLAN ===")
    print("slug: {0}".format(slug))
    print("template: {0}".format(template))
    print("menu_after: {0}".format(anchor_text))
    print("insert: {0} → {1}".format(title, href))

    original = template.read_text(encoding="utf-8", errors="replace")
    try:
        patched, changed = patch_sidebar(original, slug, href, title, anchor_text)
    except ValueError as exc:
        print("STATUS: BLOCKER — {0}".format(exc))
        return 2

    if not changed:
        print("STATUS: MENU_UNCHANGED — idempotent skip")
        return 0

    diff = difflib.unified_diff(
        original.splitlines(keepends=True),
        patched.splitlines(keepends=True),
        fromfile=str(template),
        tofile=str(template) + " (patched)",
    )
    sys.stdout.writelines(diff)

    if not args.apply:
        allow = _truthy("LIREATE_ALLOW_MENU_EDIT")
        if not allow:
            print("STATUS: DRY_RUN — use --apply and LIREATE_ALLOW_MENU_EDIT=yes to write")
        else:
            print("STATUS: DRY_RUN — use --apply to write")
        return 0

    if not _truthy("LIREATE_ALLOW_MENU_EDIT"):
        print("STATUS: BLOCKER — LIREATE_ALLOW_MENU_EDIT is not yes")
        return 1

    ts = datetime.now(timezone.utc).strftime("%Y%m%dT%H%M%SZ")
    backup = template.with_suffix(template.suffix + ".bak-" + ts)
    shutil.copy2(str(template), str(backup))
    template.write_text(patched, encoding="utf-8")
    print("backup: {0}".format(backup))
    print("STATUS: MENU_APPLIED")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
