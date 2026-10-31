#!/usr/bin/env python3
"""Build lireate-partners-data.php from live /partners/ HTML snapshot."""

import json
import re
import sys
import urllib.request
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
OUT = REPO / "pages" / "partners" / "_shared" / "lireate-partners-data.php"
URL = "https://" + "li" + "re" + "ate.ru/partners/"  # pragma: allowlist secret


def clean_html(s):
    s = re.sub(r"<br\s*/?>", "\n", s, flags=re.I)
    s = re.sub(r"<[^>]+>", "", s)
    s = re.sub(r"\s+", " ", s).strip()
    return s


def norm_photo(url):
    url = url.strip("'\"")
    if url.startswith("template/"):
        url = "/template/" + url[len("template/") :]
    elif not url.startswith("/") and not url.startswith("http"):
        url = "/template/" + url.lstrip("/")
    return url


def parse_fields(inner):
    fields = []
    inner2 = re.sub(
        r'<p class="hideContent">Остальная деятельность</p>\s*<div class="hideContentBox">(.*?)</div>',
        r'<p class="contactTitle">Остальная деятельность</p>\1',
        inner,
        flags=re.DOTALL,
    )
    pos = 0
    while True:
        m = re.search(r'<p class="contactTitle">(.*?)</p>', inner2[pos:], re.DOTALL)
        if not m:
            break
        title = clean_html(m.group(1))
        start = pos + m.end()
        m2 = re.search(r'<p class="contactTitle">', inner2[start:], re.DOTALL)
        chunk = inner2[start : start + m2.start()] if m2 else inner2[start:]
        items = [
            clean_html(x)
            for x in re.findall(r'<p[^>]*class="pageP"[^>]*>(.*?)</p>', chunk, re.DOTALL)
        ]
        items = [x for x in items if x]
        if title == "Остальная деятельность" and fields:
            fields[-1]["items"].extend(items)
        elif items or title in ("Адрес", "Деятельность"):
            fields.append({"title": title, "items": items})
        pos = start + (m2.start() if m2 else len(inner2) - start)
        if not m2:
            break
    return fields


def parse_contacts(contact_html):
    contacts = []
    pos = 0
    while True:
        m = re.search(r'<p class="contactTitle">(.*?)</p>', contact_html[pos:], re.DOTALL)
        if not m:
            break
        title = clean_html(m.group(1))
        start = pos + m.end()
        m2 = re.search(r'<p class="contactTitle">', contact_html[start:], re.DOTALL)
        chunk = contact_html[start : start + m2.start()] if m2 else contact_html[start:]
        phones = re.findall(r'<a[^>]+href="tel:([^"]+)"[^>]*>([^<]*)</a>', chunk)
        links = re.findall(r'<a[^>]+href="(?!tel:)([^"]+)"[^>]*>([^<]*)</a>', chunk)
        if phones:
            for tel, disp in phones:
                contacts.append(
                    {"title": title, "text": clean_html(disp) or tel, "tel": tel.strip()}
                )
        elif links:
            for url, disp in links:
                contacts.append(
                    {
                        "title": title,
                        "text": clean_html(disp) or clean_html(chunk),
                        "url": url.strip(),
                    }
                )
        else:
            txt = clean_html(chunk)
            if txt:
                contacts.append({"title": title, "text": txt})
        pos = start + (m2.start() if m2 else len(contact_html) - start)
        if not m2:
            break
    return contacts


def parse_partners_html(text):
    intro_m = re.search(
        r'<p class="pageTitleDisc">\s*(.*?)\s*</p>', text, re.DOTALL | re.IGNORECASE
    )
    intro = clean_html(intro_m.group(1)) if intro_m else ""
    sec = re.search(r'<section id="partners"[^>]*>(.*?)</section>', text, re.DOTALL)
    if not sec:
        raise RuntimeError("partners section not found")
    raw_blocks = re.split(r"(?=<div class=\"partner\">)", sec.group(1))
    raw_blocks = [b for b in raw_blocks if 'class="partner"' in b]
    partners = []
    for block in raw_blocks:
        img = re.search(r"background-image:url\(([^)]+)\)", block)
        photo = norm_photo(img.group(1)) if img else ""
        desc_m = re.search(
            r'<div class="description">(.*?)</div>\s*<div class="contact">', block, re.DOTALL
        )
        if not desc_m:
            continue
        inner = desc_m.group(1)
        h4s = [clean_html(x) for x in re.findall(r"<h4[^>]*>(.*?)</h4>", inner, re.DOTALL)]
        name = h4s[0] if h4s else ""
        city = h4s[1] if len(h4s) > 1 else ""
        fields = parse_fields(inner)
        contact_m = re.search(r'<div class="contact">(.*)', block, re.DOTALL)
        contacts = parse_contacts(contact_m.group(1)) if contact_m else []
        partners.append(
            {
                "photo": photo,
                "name": name,
                "city": city,
                "fields": fields,
                "contacts": contacts,
            }
        )
    slides = re.findall(
        r'<div class="slide">\s*<div class="slidePhoto"><img src="([^"]+)"></div>\s*'
        r'<div class="slideDescription">\s*<h4>(.*?)</h4>\s*<p>(.*?)</p>',
        text,
        re.DOTALL,
    )
    team = []
    for photo, name, role in slides:
        role = re.sub(r"^Должность:\s*", "", role.strip())
        team.append({"name": name.strip(), "role": role, "photo": photo.strip()})
    return intro, partners, team


def php_str(s):
    return "'" + s.replace("\\", "\\\\").replace("'", "\\'") + "'"


def emit_php(intro, partners, team):
    lines = [
        "<?php",
        "/**",
        " * Данные live /partners/ (партнёры + блок personsSlide).",
        " * Сгенерировано scripts/lireate_partners_build_data.py",
        " */",
        "if (!function_exists('lireate_partners_page_intro')) {",
        "function lireate_partners_page_intro()",
        "{",
        "    return {0};".format(php_str(intro)),
        "}",
        "}",
        "",
        "if (!function_exists('lireate_partners_list')) {",
        "function lireate_partners_list()",
        "{",
        "    return array(",
    ]
    for p in partners:
        lines.append("        array(")
        lines.append("            'photo' => {0},".format(php_str(p["photo"])))
        lines.append("            'name' => {0},".format(php_str(p["name"])))
        lines.append("            'city' => {0},".format(php_str(p["city"])))
        lines.append("            'fields' => array(")
        for f in p["fields"]:
            lines.append("                array(")
            lines.append("                    'title' => {0},".format(php_str(f["title"])))
            lines.append("                    'items' => array(")
            for item in f["items"]:
                lines.append("                        {0},".format(php_str(item)))
            lines.append("                    ),")
            lines.append("                ),")
        lines.append("            ),")
        lines.append("            'contacts' => array(")
        for c in p["contacts"]:
            lines.append("                array(")
            for key in ("title", "text", "tel", "url"):
                if key in c and c[key]:
                    lines.append("                    '{0}' => {1},".format(key, php_str(c[key])))
            lines.append("                ),")
        lines.append("            ),")
        lines.append("        ),")
    lines.extend(
        [
            "    );",
            "}",
            "}",
            "",
            "if (!function_exists('lireate_partners_team_members')) {",
            "function lireate_partners_team_members()",
            "{",
            "    return array(",
        ]
    )
    for t in team:
        lines.append("        array(")
        lines.append("            'name' => {0},".format(php_str(t["name"])))
        lines.append("            'role' => {0},".format(php_str(t["role"])))
        lines.append("            'photo' => {0},".format(php_str(t["photo"])))
        lines.append("        ),")
    lines.extend(["    );", "}", "}", ""])
    return "\n".join(lines)


def main():
    html_path = None
    if len(sys.argv) > 1:
        html_path = Path(sys.argv[1])
        text = html_path.read_text(encoding="utf-8", errors="replace")
    else:
        req = urllib.request.Request(URL, headers={"User-Agent": "lireate-partners-build/1.0"})
        with urllib.request.urlopen(req, timeout=90) as resp:
            text = resp.read().decode("utf-8", errors="replace")
    if "Наши Партнёры" not in text or 'id="partners"' not in text:
        print("BLOCKER: unexpected HTML")
        return 2
    intro, partners, team = parse_partners_html(text)
    if len(partners) < 20:
        print("BLOCKER: too few partners parsed: {0}".format(len(partners)))
        return 2
    OUT.parent.mkdir(parents=True, exist_ok=True)
    OUT.write_text(emit_php(intro, partners, team), encoding="utf-8")
    print("wrote {0} ({1} partners, {2} team)".format(OUT, len(partners), len(team)))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
