# Nav V3 — единое левое меню (боевой chrome)

| | |
|--|--|
| PR | https://github.com/devham13/lireate/pull/129 |
| Ветка | `cursor/nav-v3-production-chrome-b68f` |

## Честный capture (live overlay)

1. `curl -L` HTML с боевого сайта (`PUBLIC_SITE_HOST`) для каждого URL.
2. Оверлей PR #129: `scripts/lireate_nav_v3_live_overlay.py` (как install на `main.php` / wire услуг) + PHP-фрагменты `pages/_preview/nav-v3-chrome-fragment.php`.
3. Стили/скрипты страницы — **абсолютные** с хоста; ассеты PR (`20261126r7`) — с локального HTTP (repo).
4. На услугах учтён **PR #128** (`lx-photo-v4`, `lireate-six-services-photo-v4` с прода).
5. Playwright: `font-family` ≠ Times, `#leftSideBar` видим, ширина ≈ главная (280px @ 1440).

Запуск: `python3 scripts/capture_nav_v3_production_screenshots.py`

Результат + validations: `docs/screenshots/nav-v3/production/manifest.json`

### Скрины (1440 и 390)

| Slug | Файлы |
|------|--------|
| home | `home/1440.png`, `home/390.png` |
| RTZ | `service-rtz/1440.png`, `390.png` |
| findtz | `findtz/1440.png`, `390.png`, `390-sections-open.png` (шторка «Разделы») |
| prodlenie | `prodlenie/1440.png`, `390.png` |
| mktu, news, team, contacts, portfolio, patent | `<slug>/1440.png`, `390.png` |

**Без merge в `main` и без выкладки с агента.**