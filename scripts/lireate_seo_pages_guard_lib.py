#!/usr/bin/env python3
"""Validate SEO meta for data-layer JSON and service index.php files (no HTTP)."""
from __future__ import print_function

import json
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
DATA = ROOT / "data"
SERVICES = ROOT / "pages" / "services"


def _load_json(path):
    if not path.is_file():
        return None
    return json.loads(path.read_text(encoding="utf-8"))


def validate_cms_overrides():
    errors = []
    path = DATA / "lireate-seo-cms-overrides.json"
    data = _load_json(path)
    if not isinstance(data, dict):
        errors.append("missing or invalid lireate-seo-cms-overrides.json")
        return errors
    titles = {}
    for slug, meta in data.items():
        if not isinstance(meta, dict):
            continue
        title = (meta.get("title") or "").strip()
        desc = (meta.get("description") or "").strip()
        if title:
            if title in titles:
                errors.append("duplicate override title: {0} ({1}, {2})".format(
                    title[:50], titles[title], slug))
            titles[title] = slug
            if len(title) < 25 or len(title) > 70:
                errors.append("title length {0} for {1}: {2}".format(len(title), slug, title[:40]))
        if desc:
            if len(desc) < 85 or len(desc) > 175:
                errors.append("description length {0} for {1} (want 85-175)".format(len(desc), slug))
    return errors


def validate_mktu_classes():
    errors = []
    path = DATA / "lireate-seo-mktu-classes.json"
    data = _load_json(path)
    if not isinstance(data, dict) or len(data) != 45:
        errors.append("mktu-classes must have 45 entries")
        return errors
    for n in range(1, 46):
        key = str(n)
        if key not in data:
            errors.append("missing MKTU class {0}".format(n))
            continue
        row = data[key]
        for field in ("title", "description", "h1"):
            if not (row.get(field) or "").strip():
                errors.append("MKTU {0} missing {1}".format(n, field))
        h1 = row.get("h1") or ""
        if not re.search(r"\bкласс\s+мкту\b", h1, re.I):
            errors.append("MKTU {0} h1 format suspicious: {1}".format(n, h1[:60]))
    return errors


def validate_changed_services(changed_paths=None):
    """If changed_paths is None, scan all service index.php."""
    errors = []
    targets = []
    if changed_paths:
        for p in changed_paths:
            path = Path(p)
            if path.name == "index.php" and "pages/services" in str(path):
                targets.append(path)
    else:
        if SERVICES.is_dir():
            for index in SERVICES.glob("*/index.php"):
                if index.parent.name.startswith("_"):
                    continue
                targets.append(index)
    title_re = re.compile(r"\$seo_title\s*=\s*'([^']*)'")
    desc_re = re.compile(r"\$seo_description\s*=\s*'([^']*)'")
    for index in targets:
        text = index.read_text(encoding="utf-8", errors="replace")
        tm = title_re.search(text)
        dm = desc_re.search(text)
        slug = index.parent.name
        if not tm:
            errors.append("{0}: missing $seo_title".format(slug))
            continue
        title = tm.group(1)
        if len(title) < 20 or len(title) > 72:
            errors.append("{0}: title length {1}".format(slug, len(title)))
        if dm:
            dlen = len(dm.group(1))
            if dlen < 130 or dlen > 165:
                errors.append("{0}: description length {1} (lint wants 140-160)".format(slug, dlen))
    return errors