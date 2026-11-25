#!/usr/bin/env python3
"""Compare home Light V4 copy vs live + design proposal — report for owner."""
import os
import re
import urllib.request
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
HOME_PHP = ROOT / "template/page/home/lireate-home-page.inc.php"
DESIGN_HTML = ROOT / "design-proposals/lireate-light-v4/home/index.html"
LIVE_URL = os.environ.get(
    "LIREATE_PUBLIC_SITE_URL",
    "https://" + "[REDACTED]/",  # pragma: allowlist secret
).rstrip("/") + "/"


def plain(html):
    html = re.sub(r"<\?php.*?\?>", " ", html, flags=re.S)
    html = re.sub(r"<[^>]+>", " ", html)
    html = re.sub(r"\s+", " ", html)
    return html.strip()


def snippets_from_file(path):
    text = path.read_text(encoding="utf-8", errors="replace")
    out = []
    for m in re.finditer(r">([^<>]{12,200})<", text):
        s = m.group(1).strip()
        if s and not s.startswith("<?"):
            out.append(s)
    for m in re.finditer(r'<p[^>]*>([^<]{8,})</p>', text, re.S):
        out.append(re.sub(r"\s+", " ", m.group(1)).strip())
    for m in re.finditer(r"<h[23][^>]*>([^<]{4,})</h[23]>", text, re.S):
        out.append(re.sub(r"\s+", " ", m.group(1)).strip())
    return list(dict.fromkeys(out))


def main():
    live = urllib.request.urlopen(LIVE_URL, timeout=30).read().decode("utf-8", "replace")
    live_plain = plain(live)
    design_plain = plain(DESIGN_HTML.read_text(encoding="utf-8", errors="replace")) if DESIGN_HTML.is_file() else ""
    home_snips = snippets_from_file(HOME_PHP)
    rows = []
    for s in home_snips:
        if s.startswith("window.lireate") or "window.lireate" in s:
            continue
        if s in live_plain:
            continue
        if "Через 15 минут вы получите ответ" in s and "Через 15 минут" in live_plain:
            continue
        src = "дизайн PR #117" if design_plain and s in design_plain else "только ветка / не live"
        rows.append((s[:120], src))
    out = [
        "## Тексты главной вне live (на согласование владельца)",
        "",
        "| Текст (фрагмент) | Источник |",
        "| --- | --- |",
    ]
    for text, src in rows:
        text = text.replace("|", "\\|")
        out.append("| {0} | {1} |".format(text, src))
    out.append("")
    out.append("_Разрешены только: (а) тексты prod-сайта, (б) design-proposals PR #117 home index._")
    report = ROOT / "docs/lireate-light-v4-prep-report.ru.md"
    body = report.read_text(encoding="utf-8")
    marker = "## Тексты главной вне live"
    block = "\n".join(out)
    if marker in body:
        pre, _ = body.split(marker, 1)
        rest = _.split("\n## ", 1)
        tail = "\n## " + rest[1] if len(rest) > 1 else ""
        body = pre.rstrip() + "\n\n" + block + tail
    else:
        body = body.rstrip() + "\n\n" + block + "\n"
    report.write_text(body, encoding="utf-8")
    print("rows=", len(rows))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())