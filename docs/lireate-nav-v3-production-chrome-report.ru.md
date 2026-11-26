# Nav V3 — единое левое меню (боевой chrome)

| | |
|--|--|
| PR | https://github.com/devham13/lireate/pull/129 |
| Ветка | `cursor/nav-v3-production-chrome-b68f` |
| База | `main` (без merge с агента) |

## Суть (требование Юры)

- **Левое меню как на главной** на CMS-страницах (`template/main.php` после `lireate_hosting_navigation_v3.py --apply`) и на standalone-услугах (`lireate_service_cms_chrome_wire_*`).
- Пункты, сворачивание `#collapseSide`, mobile «Разделы» (`lireate-lh4-mobile-anchors.inc.php`).
- **PR #128 (six-services photo v4):** `template/css/lireate-service-cms-chrome-layout.css` — chrome не перекрывает hero / expert stack при `body.lx-photo-v4`.
- SEO, формы, `mail.php` не трогались.

## Реализация

| Слой | Что сделано |
|------|-------------|
| CMS | `lireate-cms-main-chrome-shell.inc.php`; install заменяет `leftSideNav.php`, оборачивает `page_container` в `.lireate-public-cms-content` + lh4 subnav |
| Услуги | Список приоритетных slug + default для остальных каталогов услуг; патч `scripts/lireate_patch_service_cms_chrome.py` |
| Ассеты | QS `20261126r7`: + `lireate-cms-pages-light-v4.css`, `lireate-service-cms-chrome-layout.css` |
| Capture | `nav-v3-production-router.php` + `capture_nav_v3_production_screenshots.py` |

## Установка (REG.RU)

```bash
python3 scripts/lireate_hosting_navigation_v3.py \
  --docroot "$HOME/www/public_html" \
  --backup-root "$HOME/lireate-autopub/backups/navigation" \
  --apply --php-bin php
```

Опубликовать обновлённые `index.php` услуг — обычный cron `lireate_hosting_publish_service.py` по slug.

## Скрины (production-like, 1440 и 390)

`python3 scripts/capture_nav_v3_production_screenshots.py` → `docs/screenshots/nav-v3/production/`

Маршруты: `/`, `/registraciya_tovarnogo_znaka/`, `/findtz/`, `/prodlenie_tovarnogo_znaka/`, `/mktu/`, `/news/`, `/team/`, `/contacts/`.

**Без merge в `main` и без выкладки с агента.**