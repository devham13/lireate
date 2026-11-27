#!/usr/bin/env python3
"""Derive per-URL SEO issue flags from crawl CSV rows (stdlib)."""
from __future__ import print_function

import re


def issues_for_row(row):
    problems = []
    try:
        status = int(row.get("status") or 0)
    except (TypeError, ValueError):
        status = 0
    if status != 200:
        problems.append("http_{0}".format(status))
    if row.get("fatal_error"):
        problems.append("fatal_html")
    try:
        h1c = int(row.get("h1_count") or 0)
    except (TypeError, ValueError):
        h1c = 0
    if h1c == 0:
        problems.append("missing_h1")
    elif h1c > 1:
        problems.append("multiple_h1")
    title = (row.get("title") or "").strip()
    if not title:
        problems.append("missing_title")
    elif len(title) < 20:
        problems.append("title_short")
    elif len(title) > 70:
        problems.append("title_long")
    desc = (row.get("meta_description") or "").strip()
    if not desc:
        problems.append("missing_description")
    elif len(desc) < 80:
        problems.append("description_short")
    elif len(desc) > 175:
        problems.append("description_long")
    canon = (row.get("canonical") or "").strip()
    if status == 200 and not canon:
        problems.append("missing_canonical")
    robots = (row.get("robots_meta") or "").lower()
    if "noindex" in robots:
        problems.append("meta_noindex")
    jt = (row.get("jsonld_types") or "").strip()
    if status == 200 and not jt:
        problems.append("no_jsonld")
    if status == 200 and "BreadcrumbList" not in jt:
        url = row.get("requested_url") or ""
        if "/" in url.split("//", 1)[-1].strip("/"):
            problems.append("no_breadcrumb_jsonld")
    return ";".join(problems)