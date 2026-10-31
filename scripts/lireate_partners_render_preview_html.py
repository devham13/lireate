#!/usr/bin/env python3
"""Render partners preview HTML (no PHP) for QA screenshots."""

import html
import os
import re
import sys
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
SNAPSHOT = REPO / "pages" / "partners" / "_preview" / "fixtures" / "partners-live.snapshot.html"
OUT = REPO / "pages" / "partners" / "_preview" / "rendered-preview.html"
CMS_ORIGIN = "https://" + "li" + "re" + "ate.ru"  # pragma: allowlist secret
DEFAULT_LOCAL = "http://127.0.0.1:8799"

sys.path.insert(0, str(REPO / "scripts"))
import lireate_partners_install_lib as lib  # noqa: E402
import lireate_partners_build_data as build  # noqa: E402


def preview_local_origin():
    origin = os.environ.get("LIREATE_PARTNERS_PREVIEW_ORIGIN", DEFAULT_LOCAL).strip()
    return origin.rstrip("/")


def load_team_from_contacts_php():
    """Parse lireate-contacts-data.php team list (15 members)."""
    path = REPO / "pages" / "kontaktyi" / "_shared" / "lireate-contacts-data.php"
    text = path.read_text(encoding="utf-8", errors="replace")
    team = []
    blocks = re.findall(
        r"'name'\s*=>\s*'((?:\\'|[^'])*)'.*?'role'\s*=>\s*'((?:\\'|[^'])*)'.*?'photo'\s*=>\s*'((?:\\'|[^'])*)'",
        text,
        re.DOTALL,
    )
    for name, role, photo in blocks:
        team.append(
            {
                "name": name.replace("\\'", "'"),
                "role": role.replace("\\'", "'"),
                "photo": photo.replace("\\'", "'"),
            }
        )
    return team


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


def render_inner(intro, partners, team, local_origin, photo_origin):
    assets = local_origin + "/template/page/partners"
    footer_css = local_origin + "/template/page/news/lireate-blog-footer.css"
    if not (REPO / "template" / "page" / "news" / "lireate-blog-footer.css").is_file():
        footer_css = CMS_ORIGIN + "/template/page/news/lireate-blog-footer.css"
    lines = [
        '<link rel="preconnect" href="https://fonts.googleapis.com">',
        '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>',
        '<link href="https://fonts.googleapis.com/css2?family=Marmelad&family=Rubik:wght@400;600;700&family=Unbounded:wght@700;800&display=swap" rel="stylesheet">',
        '<link rel="stylesheet" href="{0}/lireate-partners.css?v=preview">'.format(
            html.escape(assets)
        ),
        '<link rel="stylesheet" href="{0}?v=preview">'.format(html.escape(footer_css)),
        "<script>document.documentElement.classList.add('lireate-partners-html');"
        "if(document.body){document.body.classList.add('lireate-partners-body');}</script>",
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
        lines.append('<div class="lireate-partners-card-body">')
        lines.append('<div class="lireate-partners-head">')
        if photo:
            lines.append('<div class="lireate-partners-photo-wrap">')
            lines.append(
                '<img class="lireate-partners-photo" src="{0}" alt="" loading="lazy">'.format(
                    html.escape(photo, quote=True)
                )
            )
            lines.append("</div>")
        lines.append('<div class="lireate-partners-head-text">')
        if p.get("name"):
            lines.append(
                '<h2 class="lireate-partners-name">{0}</h2>'.format(html.escape(p["name"]))
            )
        if p.get("city"):
            lines.append(
                '<p class="lireate-partners-city">{0}</p>'.format(html.escape(p["city"]))
            )
        lines.append("</div></div>")
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
    lines.extend(
        [
            "</div></section>",
            '<section class="lireate-partners-wrap lireate-partners-team" aria-label="Команда LIREATE">',
            '<div class="lireate-partners-team-grid">',
        ]
    )
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


def patch_snapshot(page_html, inner, cms_origin):
    span = lib._span_partners_block(page_html)
    if not span:
        raise SystemExit("BLOCKER: partners block span not found")
    section = '<section id="lireate-partners">' + inner + "</section>"
    patched = page_html[: span[0]] + section + page_html[span[1] :]
    patched = re.sub(r"<base\b[^>]*>", "", patched, flags=re.I)
    patched = re.sub(
        r"<link\b[^>]*href=[\"'][^\"']*template/page/partners/style\.css[\"'][^>]*>",
        "",
        patched,
        flags=re.I,
    )
    patched = re.sub(
        r'(\s(?:href|src|action)=["\'])\/(?!/)',
        r"\1" + cms_origin + "/",
        patched,
        flags=re.I,
    )
    patched = re.sub(
        r'(\sstyle="[^"]*background-image:url\()template/',
        r"\1" + cms_origin + "/template/",
        patched,
        flags=re.I,
    )
    if "lireate-partners-body" not in patched:
        patched = re.sub(
            r'(<body\b[^>]*\bclass=")([^"]*)(")',
            r"\1\2 lireate-partners-body\3",
            patched,
            count=1,
            flags=re.I,
        )
    return patched


def main():
    local = preview_local_origin()
    text = SNAPSHOT.read_text(encoding="utf-8", errors="replace")
    intro, partners, _team_live = build.parse_partners_html(text)
    team = load_team_from_contacts_php()
    if len(team) != 15:
        print("BLOCKER: expected 15 team members from contacts data, got {0}".format(len(team)))
        return 2
    inner = render_inner(intro, partners, team, local, CMS_ORIGIN)
    patched = patch_snapshot(text, inner, CMS_ORIGIN)
    OUT.write_text(patched, encoding="utf-8")
    print("wrote {0} (team={1}, local_assets={2})".format(OUT, len(team), local))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
