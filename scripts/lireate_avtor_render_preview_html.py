#!/usr/bin/env python3
"""Render /avtor/ after-preview HTML (no PHP) for QA screenshots."""

import html
import re
import subprocess
import sys
from pathlib import Path

REPO = Path(__file__).resolve().parents[1]
OUT = REPO / "pages" / "avtor" / "_preview" / "rendered-preview.html"
SNAPSHOT = REPO / "pages" / "avtor" / "_preview" / "fixtures" / "avtor-live.snapshot.html"
CMS_ORIGIN = "https://" + "li" + "re" + "ate" + ".ru"
INFO_EMAIL = "info@" + "li" + "re" + "ate" + ".ru"

PUBLICATIONS = [
    ("https://vc.ru/id694569", "vc.ru", "Например обзор дела СИП-236/2026 о знаке «Капитал»"),
    (
        "https://t-j.ru/user2640218/",
        "Т—Ж, «Сообщество Про»",
        "В том числе статья «Регистрация ООО не защищает название бизнеса как бренд»",
    ),
    (
        "https://t-j.ru/law-practice-registratsiia-ooo-ne-zashchishchaet-nazvanie/",
        "Т—Ж — регистрация ООО и бренд",
        "law-practice-registratsiia-ooo-ne-zashchishchaet-nazvanie",
    ),
    ("https://www.klerk.ru/author/6739/", "Клерк.ру", "Страница автора"),
    ("https://habr.com/ru/articles/813959/", "Хабр", "Разработка и регистрация товарных знаков"),
    ("https://habr.com/ru/articles/894612/", "Хабр — сторонние компоненты в ПО", "Статья на Habr"),
    ("https://tenchat.ru/gorbachev_yuriy", "TenChat", "Профиль Юрия Горбачева"),
    ("https://vk.com/lireate", "ВКонтакте — «Лирейт — патентное бюро»", "Кейсы бюро"),
]

SPECS = [
    "товарные знаки: проверка обозначений, регистрация, споры;",
    "патентное право: изобретения, полезные модели, промышленные образцы;",
    "авторское право, смежные права и программы для ЭВМ;",
    "договоры отчуждения и лицензии на объекты интеллектуальной собственности;",
    "представительство в спорах, в том числе в суде;",
    "реклама и недобросовестная конкуренция.",
]

FACTS = [
    "ООО «ЛИРЕЙТ» зарегистрировано 6 июля 2015 года. Основной вид деятельности по ЕГРЮЛ — деятельность в области права.",
    "По данным компании, она работает в сфере охраны интеллектуальной собственности с 2005 года. Начинала в составе юридической компании «Энтер».",
    "В команде 15 специалистов: юристы по регистрации ИС и сотрудники патентного отдела.",
    "В группе ВКонтакте с ноября 2024 по ноября 2026 года опубликовано 133 разбора дел бюро: регистрация знаков, защита от претензий и исков, взыскание компенсаций, патенты.",
]


def ensure_snapshot():
    SNAPSHOT.parent.mkdir(parents=True, exist_ok=True)
    if SNAPSHOT.is_file():
        return
    proc = subprocess.run(
        ["curl", "-sL", "-A", "Mozilla/5.0", CMS_ORIGIN + "/avtor/"],
        capture_output=True,
        check=False,
    )
    if proc.returncode != 0 or not proc.stdout:
        raise SystemExit("BLOCKER: curl avtor snapshot failed")
    SNAPSHOT.write_bytes(proc.stdout)
    print("saved snapshot {0}".format(SNAPSHOT))


def footer_html():
    email = INFO_EMAIL
    return (
        '<footer class="lx-site-footer" role="contentinfo"><div class="lx-site-footer-inner">'
        '<p class="lx-site-footer-title">LIREATE • Товарные знаки • Интеллектуальная собственность</p>'
        '<p class="lx-site-footer-lead">Регистрация и защита товарных знаков, патентов и авторских прав.</p>'
        '<p class="lx-site-footer-meta">ООО «ЛИРЕЙТ»</p>'
        '<p class="lx-site-footer-meta">ИНН 1648040894 • КПП 164801001 • ОГРН 1151673001578</p>'
        '<p class="lx-site-footer-contact">'
        '<a href="mailto:{0}">{0}</a>'
        '<span class="lx-site-footer-dot" aria-hidden="true">•</span>'
        '<a href="tel:+79600484957">+7 (960) 048-49-57</a>'
        '<span class="lx-site-footer-dot" aria-hidden="true">•</span>'
        '<a href="tel:+74999384595">+7 (499) 938-45-95</a>'
        '<span class="lx-site-footer-dot" aria-hidden="true">•</span>'
        '<a href="https://t.me/lireate_patent" target="_blank" rel="noopener noreferrer">Telegram</a>'
        '</p>'
        '<div class="lx-site-footer-nav">'
        '<a href="/politika_konfidencialnosti">Политика конфиденциальности</a>'
        '<a href="/private_data">Согласие на обработку персональных данных</a>'
        '<a href="/news/">Блог</a>'
        '<a class="lx-site-footer-documents-link" href="/docs">Реквизиты</a>'
        '</div>'
        '<p class="lx-site-footer-copy">© 2026 LIREATE</p>'
        '</div></footer>'
    ).format(email)


def render_inner(local_origin):
    assets = local_origin + "/template/page/avtor"
    lines = [
        '<link rel="preconnect" href="https://fonts.googleapis.com">',
        '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>',
        '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">',
        '<link rel="stylesheet" href="{0}/template/css/lireate-cms-pages-light-v4.css?v=20261127avtor">'.format(html.escape(local_origin)),
        '<link rel="stylesheet" href="{0}/template/css/lireate-light-v4.css?v=preview">'.format(html.escape(local_origin)),
        '<link rel="stylesheet" href="{0}/lireate-avtor.css?v=preview">'.format(html.escape(assets)),
        '<link rel="stylesheet" href="{0}/template/page/news/lireate-blog-footer.css?v=preview">'.format(html.escape(local_origin)),
        "<script>document.documentElement.classList.add('lireate-avtor-html','lireate-theme-light-v4');"
        "if(document.body){document.body.classList.add('lireate-avtor-body');}</script>",
        '<div id="lireate-avtor" class="lu-page"><div class="lu-glow" aria-hidden="true"></div><div class="lu-wrap">',
        '<nav class="lu-crumbs" aria-label="Хлебные крошки"><ol>',
        '<li><a href="/">Главная</a></li><li><span aria-current="page">Автор</span></li></ol></nav>',
        '<header class="la-hero" aria-labelledby="la-h1"><div class="la-hero-text">',
        '<p class="lu-eyebrow">LIREATE · патентное бюро · автор статей</p>',
        '<h1 class="lu-h1" id="la-h1">Горбачев Юрий Александрович</h1>',
        '<p class="la-role"><strong>Директор и управляющий партнёр ООО «ЛИРЕЙТ», патентное бюро «Лирейт»</strong></p>',
        '<div class="la-hero-cta">',
        '<a class="lu-btn lu-btn-primary" href="https://t.me/lireate_patent">Telegram</a>',
        '<a class="lu-btn lu-btn-ghost" href="https://wa.me/79600484957">WhatsApp</a>',
        '<a class="lu-btn lu-btn-ghost" href="tel:+79600484957">+7 (960) 048-49-57</a>',
        "</div></div>",
        '<figure class="la-photo"><picture>',
        '<source srcset="{0}/template/images/team/person/11.webp" type="image/webp">'.format(html.escape(CMS_ORIGIN)),
        '<img src="{0}/template/images/team/person/11.jpg" alt="Горбачев Юрий Александрович" width="350" height="525" loading="eager">'.format(
            html.escape(CMS_ORIGIN)
        ),
        "</picture></figure></header>",
        '<section class="lu-section" aria-labelledby="la-about"><h2 class="lu-h2" id="la-about">Чем занимается</h2>',
        '<div class="la-prose">',
        "<p>Юрий Горбачев руководит патентным бюро «Лирейт» из Зеленодольска (Республика Татарстан). Бюро регистрирует и защищает товарные знаки, патенты и авторские права. Он директор ООО «ЛИРЕЙТ» с января 2021 года и один из двух совладельцев компании.</p>",
        "<p>Основные направления его работы:</p><ul class=\"la-list\">",
    ]
    for item in SPECS:
        lines.append("<li>{0}</li>".format(html.escape(item)))
    lines.extend(
        [
            "</ul></div></section>",
            '<section class="lu-section" aria-labelledby="la-company"><h2 class="lu-h2" id="la-company">Компания и цифры</h2><ul class="la-list la-list--cards">',
        ]
    )
    for fact in FACTS:
        lines.append('<li class="la-fact-card">{0}</li>'.format(html.escape(fact)))
    lines.extend(
        [
            "</ul></section>",
            '<section class="lu-section" aria-labelledby="la-pubs"><h2 class="lu-h2" id="la-pubs">Публикации</h2>',
            '<p class="lu-section-lead">Юрий Горбачев пишет для предпринимателей о товарных знаках, патентах и авторском праве. Его материалы выходят здесь:</p>',
            '<div class="la-pub-grid">',
        ]
    )
    for href, title, text in PUBLICATIONS:
        lines.append(
            '<a class="la-pub-card lu-card" href="{0}" target="_blank" rel="noopener"><strong>{1}</strong><span>{2}</span></a>'.format(
                html.escape(href, quote=True), html.escape(title), html.escape(text)
            )
        )
    lines.extend(
        [
            "</div></section>",
            '<section class="lu-section" aria-labelledby="la-contacts"><h2 class="lu-h2" id="la-contacts">Контакты компании</h2>',
            '<div class="la-contacts la-prose">',
            "<p>ООО «ЛИРЕЙТ», ИНН 1648040894, ОГРН 1151673001578</p>",
            "<p>Телефон: <a href=\"tel:+79600484957\">+7 (960) 048-49-57</a><br>"
            'E-mail: <a href="mailto:{0}">{0}</a></p>'.format(INFO_EMAIL),
            '<p class="la-nav-links"><a href="/team/">Команда</a><span aria-hidden="true">·</span>'
            '<a href="/docs">Документы и реквизиты</a><span aria-hidden="true">·</span>'
            '<a href="/kontaktyi">Контакты</a></p></div></section></div></div>',
            footer_html(),
        ]
    )
    return "\n".join(lines)


def patch_snapshot(page_html, inner, local_origin):
    page_html = re.sub(r"<base\b[^>]*>", "", page_html, flags=re.I)
    page_html = re.sub(
        r'<link[^>]+href="[^"]*lireate-blog-footer-light-v4\.css[^"]*"[^>]*>',
        "",
        page_html,
        flags=re.I,
    )
    page_html = re.sub(
        r'<link[^>]+href="[^"]*template/page/avtor/lireate-avtor\.css[^"]*"[^>]*>',
        "",
        page_html,
        flags=re.I,
    )
    page_html = re.sub(
        r'(<body\b[^>]*\bclass=")([^"]*)(")',
        lambda m: m.group(1) + m.group(2) + " lireate-avtor-body lireate-theme-light-v4" + m.group(3),
        page_html,
        count=1,
        flags=re.I,
    )
    pattern = re.compile(r"<div class=\"page_container\">[\s\S]*?</div><!-- /page_container -->", re.I)
    replacement = '<div class="page_container">' + inner + "</div><!-- /page_container -->"
    patched, n = pattern.subn(replacement, page_html, count=1)
    if n != 1:
        raise SystemExit("BLOCKER: page_container replace failed")
    patched = re.sub(
        r'(\s(?:href|src|action)=["\'])\/(?!/)',
        r"\1" + CMS_ORIGIN + "/",
        patched,
        flags=re.I,
    )
    patched = patched.replace(CMS_ORIGIN, local_origin)
    patched = patched.replace(
        local_origin + "/template/images/",
        CMS_ORIGIN + "/template/images/",
    )
    return patched


def main():
    local = "http://127.0.0.1:8799"
    ensure_snapshot()
    text = SNAPSHOT.read_text(encoding="utf-8", errors="replace")
    inner = render_inner(local)
    patched = patch_snapshot(text, inner, local)
    OUT.parent.mkdir(parents=True, exist_ok=True)
    OUT.write_text(patched, encoding="utf-8")
    print("wrote {0}".format(OUT))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())