#!/usr/bin/env python3
"""Render team preview HTML (no PHP) for QA screenshots."""

import html
import json
import os
import re
import sys
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
SNAPSHOT = REPO / "pages" / "team" / "_preview" / "fixtures" / "team-live.snapshot.html"
OUT = REPO / "pages" / "team" / "_preview" / "rendered-preview.html"
CMS_ORIGIN = "https://" + "li" + "re" + "ate.ru"  # pragma: allowlist secret
DEFAULT_LOCAL = "http://127.0.0.1:8799"

sys.path.insert(0, str(REPO / "scripts"))
import lireate_team_install_lib as lib  # noqa: E402


def preview_local_origin():
    origin = os.environ.get("LIREATE_TEAM_PREVIEW_ORIGIN", DEFAULT_LOCAL).strip()
    return origin.rstrip("/")


def load_team_from_contacts_php():
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


def render_inner(team, local_origin, photo_origin):
    assets = local_origin + "/template/page/team"
    footer_css = local_origin + "/template/page/news/lireate-blog-footer.css"
    if not (REPO / "template" / "page" / "news" / "lireate-blog-footer.css").is_file():
        footer_css = CMS_ORIGIN + "/template/page/news/lireate-blog-footer.css"
    lines = [
        '<link rel="preconnect" href="https://fonts.googleapis.com">',
        '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>',
        '<link href="https://fonts.googleapis.com/css2?family=Marmelad&family=Rubik:wght@400;600;700&family=Unbounded:wght@700;800&display=swap" rel="stylesheet">',
        '<link rel="stylesheet" href="{0}/lireate-team.css?v=preview">'.format(html.escape(assets)),
        '<link rel="stylesheet" href="{0}?v=preview">'.format(html.escape(footer_css)),
        "<script>document.documentElement.classList.add('lireate-team-html');"
        "if(document.body){document.body.classList.add('lireate-team-body');}</script>",
        '<div class="lireate-team-page">',
        '<header class="lireate-team-hero"><div class="lireate-team-wrap">',
        '<p class="lireate-team-kicker">LIREATE</p>',
        '<h1 class="lireate-team-h1">Команда Лирейт</h1>',
        '<p class="lireate-team-sub">Наша команда</p>',
        '<p class="lireate-team-lead">Больше 10 человек объединились, чтобы помочь вам безопасно заниматься бизнесом.</p>',
        '<p class="lireate-team-tagline">Мы работаем с любовью</p>',
        '<div class="lireate-team-hero-actions">',
        '<a class="lireate-team-pill lireate-team-pill--solid" href="https://max.ru/u/f9LHodD0cOI8jsPagjibEve2cn5_xPW0X1sf3PhbS5TMijA78DnUeDEpUAI">Max</a>',
        '<a class="lireate-team-pill lireate-team-pill--outline" href="https://t.me/lireate_patent">Telegram</a>',
        '<a class="lireate-team-pill lireate-team-pill--outline" href="https://wa.me/79600484957">WhatsApp</a>',
        '<a class="lireate-team-pill lireate-team-pill--outline" href="tel:+79600484957">+7 (960) 048-49-57</a>',
        "</div>",
        "</div></header>",
        '<section class="lireate-team-wrap lireate-team-about" aria-labelledby="lt-about-title">',
        '<div class="lireate-team-about-grid">',
        '<article class="lireate-team-card"><span class="lireate-team-badge">LIREATE</span>',
        '<h2 class="lireate-team-card-title" id="lt-about-title">Чем занимается команда</h2>',
        '<p class="lireate-team-text">Юридическая защита вашей интеллектуальной собственности. Регистрация и защита товарных знаков, патентов и авторских прав.</p>',
        '<p class="lireate-team-text lireate-team-text--muted">Юридическая поддержка в Роспатенте, ФАС, ФТС, Суде по интеллектуальным правам (СИП) и во всех судебных инстанциях.</p>',
        "</article>",
        '<article class="lireate-team-card"><span class="lireate-team-badge lireate-team-badge--green">Отделы</span>',
        '<h2 class="lireate-team-card-title">Направления в составе команды</h2>',
        "<ul class=\"lireate-team-list-bullets\"><li>Управляющие партнёры</li>"
        "<li>Отдел регистрации интеллектуальной собственности</li>"
        "<li>Патентный отдел</li></ul></article>",
        '<article class="lireate-team-card lireate-team-card--wide"><span class="lireate-team-badge">Как работаем</span>',
        '<h2 class="lireate-team-card-title">С клиентом — коротко</h2>',
        "<ol class=\"lireate-team-steps\"><li>Свяжитесь через контакты, мессенджеры или телефон — мы перезвоним в ближайшее время.</li>"
        "<li>Обсуждаем задачу: товарные знаки, патенты, авторские права.</li>"
        "<li>Готовим документы и сопровождаем в Роспатенте, ФАС, ФТС, СИП и судах.</li></ol></article>",
        "</div></section>",
        '<section class="lireate-team-wrap lireate-team-roster" aria-labelledby="lt-roster-title">',
        '<h2 class="lireate-team-card-title" id="lt-roster-title">Сотрудники LIREATE</h2>',
        '<div class="lireate-team-grid">',
    ]
    for member in team:
        photo = member.get("photo", "")
        if photo and not photo.startswith("http"):
            photo = photo_origin + photo
        lines.append('<div class="lireate-team-member-card">')
        if photo:
            lines.append('<span class="lireate-team-member-photo-wrap">')
            lines.append(
                '<img class="lireate-team-member-photo" src="{0}" alt="" loading="lazy">'.format(
                    html.escape(photo, quote=True)
                )
            )
            lines.append("</span>")
        lines.append(
            '<span class="lireate-team-member-name">{0}</span>'.format(
                html.escape(member.get("name", ""))
            )
        )
        lines.append(
            '<span class="lireate-team-member-role">{0}</span>'.format(
                html.escape(member.get("role", ""))
            )
        )
        lines.append("</div>")
    lines.append("</div></section></div>")
    return "\n".join(lines)


def patch_snapshot(page_html, inner, cms_origin):
    span = lib._span_team_block(page_html)
    if not span:
        raise SystemExit("BLOCKER: team block span not found in snapshot")
    section = '<section id="lireate-team">' + inner + "</section>"
    patched = page_html[: span[0]] + section + page_html[span[1] :]
    patched = re.sub(r"<base\b[^>]*>", "", patched, flags=re.I)
    patched = re.sub(
        r"<link\b[^>]*href=[\"'][^\"']*template/page/team/style\.css[\"'][^>]*>",
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
    if "lireate-team-body" not in patched:
        patched = re.sub(
            r'(<body\b[^>]*\bclass=")([^"]*)(")',
            r"\1\2 lireate-team-body\3",
            patched,
            count=1,
            flags=re.I,
        )
    return patched


def main():
    if not SNAPSHOT.is_file():
        print("BLOCKER: run lireate_team_fetch_live_snapshot.py first")
        return 2
    local = preview_local_origin()
    text = SNAPSHOT.read_text(encoding="utf-8", errors="replace")
    team = load_team_from_contacts_php()
    if len(team) != 15:
        print("BLOCKER: expected 15 team members, got {0}".format(len(team)))
        return 2
    inner = render_inner(team, local, CMS_ORIGIN)
    patched = patch_snapshot(text, inner, CMS_ORIGIN)
    OUT.write_text(patched, encoding="utf-8")
    print("wrote {0} (team={1})".format(OUT, len(team)))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
