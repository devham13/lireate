#!/usr/bin/env python3
"""Render partners preview HTML (no PHP) for QA screenshots."""

import html
import re
import sys
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
SNAPSHOT = REPO / "pages" / "partners" / "_preview" / "fixtures" / "partners-live.snapshot.html"
OUT = REPO / "pages" / "partners" / "_preview" / "rendered-preview.html"
CMS_ORIGIN = "https://" + "li" + "re" + "ate.ru"  # pragma: allowlist secret

sys.path.insert(0, str(REPO / "scripts"))
import lireate_partners_install_lib as lib  # noqa: E402
import lireate_partners_build_data as build  # noqa: E402


def render_contact(contact):
    title = contact.get("title", "")
    text = contact.get("text", "")
    tel = contact.get("tel")
    url = contact.get("url")
    parts = []
    if title:
        parts.append("<dt>{0}</dt>".format(html.escape(title)))
    inner = ""
    if tel:
        href = "tel:" + re.sub(r"\s+", "", tel)
        inner = '<a href="{0}">{1}</a>'.format(
            html.escape(href, quote=True), html.escape(text or tel)
        )
    elif url:
        href = url
        if not href.startswith(("http", "mailto", "tel")):
            href = "http://" + href.lstrip("/")
        inner = '<a href="{0}" target="_blank" rel="noopener noreferrer">{1}</a>'.format(
            html.escape(href, quote=True), html.escape(text or url)
        )
    else:
        inner = html.escape(text)
    parts.append("<dd>{0}</dd>".format(inner))
    return "<div>" + "".join(parts) + "</div>"


def render_inner(intro, partners, team, assets_base, photo_origin):
    lines = [
        '<link rel="stylesheet" href="{0}/lireate-partners.css?v=preview">'.format(
            html.escape(assets_base)
        ),
        '<script>document.documentElement.classList.add("lireate-partners-html");'
        'if(document.body){document.body.classList.add("lireate-partners-body");}</script>',
        '<div class="lireate-partners-page">',
        '<header class="lireate-partners-hero"><div class="lireate-partners-wrap">',
        '<p class="lireate-partners-kicker">LIREATE</p>',
        '<h1 class="lireate-partners-h1">Наши Партнёры</h1>',
    ]
    if intro:
        lines.append(
            '<p class="lireate-partners-lead">{0}</p>'.format(html.escape(intro))
        )
    lines.extend(
        [
            "</div></header>",
            '<section class="lireate-partners-wrap lireate-partners-list" aria-label="Партнёры LIREATE">',
            '<div class="lireate-partners-grid">',
        ]
    )
    for p in partners:
        photo = p.get("photo", "")
        if photo and not photo.startswith("http"):
            photo = photo_origin + photo
        lines.append('<article class="lireate-partners-card">')
        if photo:
            lines.append(
                '<div class="lireate-partners-photo" style="background-image:url(\'{0}\')"></div>'.format(
                    html.escape(photo, quote=True)
                )
            )
        lines.append('<div class="lireate-partners-card-body">')
        if p.get("name"):
            lines.append(
                '<h2 class="lireate-partners-name">{0}</h2>'.format(html.escape(p["name"]))
            )
        if p.get("city"):
            lines.append(
                '<p class="lireate-partners-city">{0}</p>'.format(html.escape(p["city"]))
            )
        if p.get("fields"):
            lines.append('<dl class="lireate-partners-fields">')
            for field in p["fields"]:
                if not field.get("items"):
                    continue
                lines.append('<div class="lireate-partners-field">')
                if field.get("title"):
                    lines.append("<dt>{0}</dt>".format(html.escape(field["title"])))
                lines.append("<dd>")
                for item in field["items"]:
                    lines.append("<p>{0}</p>".format(html.escape(item)))
                lines.append("</dd></div>")
            lines.append("</dl>")
        if p.get("contacts"):
            lines.append('<dl class="lireate-partners-contacts">')
            for c in p["contacts"]:
                lines.append(render_contact(c))
            lines.append("</dl>")
        lines.append("</div></article>")
    lines.extend(["</div></section>", '<section class="lireate-partners-wrap lireate-partners-team" aria-label="Команда LIREATE">', '<div class="lireate-partners-team-grid">'])
    for member in team:
        photo = member.get("photo", "")
        if photo and not photo.startswith("http"):
            photo = photo_origin + photo
        lines.append(
            '<div class="lireate-partners-team-card"><span class="lireate-partners-team-photo" '
            'style="background-image:url(\'{0}\')"></span><span class="lireate-partners-team-name">{1}</span>'
            '<span class="lireate-partners-team-role">{2}</span></div>'.format(
                html.escape(photo, quote=True),
                html.escape(member.get("name", "")),
                html.escape(member.get("role", "")),
            )
        )
    lines.append("</div></section></div>")
    return "\n".join(lines)


def patch_snapshot(page_html, inner):
    span = lib._span_partners_block(page_html)
    if not span:
        raise SystemExit("BLOCKER: partners block span not found")
    section = '<section id="lireate-partners">' + inner + "</section>"
    patched = page_html[: span[0]] + section + page_html[span[1] :]
    patched = re.sub(r"<base\b[^>]*>", "", patched, flags=re.I)
    patched = re.sub(
        r'(\s(?:href|src|action)=["\'])\/(?!/)',
        r"\1" + CMS_ORIGIN + "/",
        patched,
        flags=re.I,
    )
    patched = re.sub(
        r'(\sstyle="[^"]*background-image:url\()template/',
        r"\1" + CMS_ORIGIN + "/template/",
        patched,
        flags=re.I,
    )
    if "lireate-partners-body" not in patched:
        patched = re.sub(
            r"(<body\b[^>]*\bclass=\")([^\"]*)(\")",
            r"\1\2 lireate-partners-body\3",
            patched,
            count=1,
            flags=re.I,
        )
    patched = patched.replace(
        'href="/template/page/partners/style.css"',
        'href="/template/page/partners/lireate-partners.css"',
    )
    return patched


def main():
    text = SNAPSHOT.read_text(encoding="utf-8", errors="replace")
    intro, partners, team = build.parse_partners_html(text)
    inner = render_inner(
        intro,
        partners,
        team,
        assets_base="/template/page/partners",
        photo_origin=CMS_ORIGIN,
    )
    patched = patch_snapshot(text, inner)
    OUT.write_text(patched, encoding="utf-8")
    print("wrote {0}".format(OUT))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
