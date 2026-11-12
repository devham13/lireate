#!/usr/bin/env python3
"""Structural diff: production HTML vs local PHP preview (findtz + registraciya)."""

import json
import os
import re
import sys
import urllib.request
from difflib import unified_diff
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
OUT = Path(os.environ.get("LIREATE_DIFF_OUT", "/opt/cursor/artifacts/live-align-pr75"))
PREVIEW = os.environ.get("LIREATE_PREVIEW_BASE", "http://127.0.0.1:8888")
SITE = os.environ.get("LIREATE_PUBLIC_SITE_URL", "").rstrip("/")

PAGES = (
    ("findtz", "/findtz/", "proverka-tovarnogo-znaka"),
    ("registraciya", "/registraciya_tovarnogo_znaka/", "registraciya-tovarnogo-znaka"),
)


def fetch(url):
    req = urllib.request.Request(url, headers={"User-Agent": "lireate-live-diff/1.0"})
    with urllib.request.urlopen(req, timeout=120) as resp:
        return resp.read().decode("utf-8", errors="replace")


def normalize(html):
    html = re.sub(r"\?v=[0-9a-zA-Z]+", "?v=STATIC", html)
    html = re.sub(r"<!--[^>]*-->", "", html)
    html = re.sub(r"\s+", " ", html)
  # strip inline scripts that vary
    html = re.sub(r"<script[^>]*>.*?</script>", "<script>…</script>", html, flags=re.I)
    return html.strip()


def extract_chunks(html):
    chunks = {}
    for key, pat in (
        ("title", r"<title>([^<]*)</title>"),
        ("risk_grids", r'<div class="lx-risk-grid"'),
        ("form_mail", r'<form[^>]*id="lireate-lead-form"[^>]*>'),
        ("mobile_bar_css", r"lireate-service-mobile-bar\.css"),
        ("template_metrika_js", r"lireate-service-metrika-goals\.js"),
        ("template_mobile_js", r"lireate-service-mobile-bar\.js"),
        ("assets_mobile_js", r"assets/lireate-shared/lireate-mobile-bar\.js"),
        ("vk_cases", r'id="lireate-vk-cases-title"'),
        ("landing_body", r'registraciya-landing-body'),
        ("landing_css", r"registraciya-landing\.css"),
        ("reviews_rail", r'class="lx-reviews-rail-viewport"'),
    ):
        if key == "risk_grids":
            chunks[key] = len(re.findall(pat, html, re.I))
        elif key == "form_mail":
            m = re.search(pat, html, re.I)
            chunks[key] = m.group(0)[:220] if m else None
        else:
            chunks[key] = bool(re.search(pat, html, re.I))
    return chunks


def main():
    if not SITE:
        print("Set LIREATE_PUBLIC_SITE_URL to run live diff", file=sys.stderr)
        return 2
    OUT.mkdir(parents=True, exist_ok=True)
    report = {"pages": {}, "notes": []}

    for tag, path, slug in PAGES:
        nc = "livealign" + tag
        live_url = "{0}{1}?nc={2}".format(SITE, path, nc)
        prev_url = "{0}{1}".format(PREVIEW.rstrip("/"), path)
        live_html = fetch(live_url)
        try:
            prev_html = fetch(prev_url)
        except Exception as exc:
            report["pages"][tag] = {"error": "preview fetch failed: {0}".format(exc)}
            continue

        live_n = normalize(live_html)
        prev_n = normalize(prev_html)
        live_c = extract_chunks(live_html)
        prev_c = extract_chunks(prev_html)

        diff_lines = list(
            unified_diff(
                live_n.split(),
                prev_n.split(),
                fromfile="live",
                tofile="preview",
                lineterm="",
                n=0,
            )
        )
        # collapse huge word-diff to size metric
        diff_excerpt = diff_lines[:80]
        if len(diff_lines) > 80:
            diff_excerpt.append("… ({0} diff lines total)".format(len(diff_lines)))

        mismatches = []
        for k, v in live_c.items():
            pv = prev_c.get(k)
            if pv != v:
                mismatches.append({"marker": k, "live": v, "preview": pv})

        report["pages"][tag] = {
            "slug": slug,
            "live_url": live_url,
            "preview_url": prev_url,
            "chunk_mismatches": mismatches,
            "diff_line_count": len(diff_lines),
            "diff_excerpt": diff_excerpt,
        }

    out_json = OUT / "live-vs-preview-report.json"
    out_md = OUT / "live-vs-preview-report.md"
    out_json.write_text(json.dumps(report, ensure_ascii=False, indent=2), encoding="utf-8")

    lines = ["# Live vs preview (branch cursor/live-align-pr75-c394)", ""]
    for tag, data in report["pages"].items():
        lines.append("## {0}".format(tag))
        if "error" in data:
            lines.append(data["error"])
            continue
        lines.append("- live: {0}".format(data["live_url"]))
        lines.append("- preview: {0}".format(data["preview_url"]))
        lines.append("- normalized word-diff lines: **{0}**".format(data["diff_line_count"]))
        if data["chunk_mismatches"]:
            lines.append("")
            lines.append("### Marker mismatches")
            for m in data["chunk_mismatches"]:
                lines.append(
                    "- **{0}**: live={1!r}, preview={2!r}".format(
                        m["marker"], m["live"], m["preview"]
                    )
                )
        else:
            lines.append("")
            lines.append("_No marker mismatches (risk grid count, form, mobile bar assets, landing flags)._")
        lines.append("")
        if data["diff_excerpt"]:
            lines.append("<details><summary>Diff excerpt (normalized)</summary>")
            lines.append("")
            lines.append("```diff")
            lines.extend(data["diff_excerpt"][:60])
            lines.append("```")
            lines.append("</details>")
        lines.append("")

    out_md.write_text("\n".join(lines), encoding="utf-8")
    print(out_md)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
