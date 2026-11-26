# Nav V3 + соцсети (полный chrome) — отчёт PR

Дата: 2026-11-26. **Без merge и без выкладки на хостинг.**

## Что сделано

- Единый bundle `pages/_shared/lireate-navigation-v3-bundle-lib.php`: CSS/JS Nav V3 + `lireate-public-cms-chrome.css` + `lireate-global-subnav-light-v4.css`.
- Публичный chrome: `lireate-public-cms-chrome-lib.php`, sidebar с 5 соцсетями (YouTube `UCpg1LWotCQWwzviPDb_9Rew`, VK `vk.ru/lireate`), без Tochka/«Оплата услуг» в разметке превью.
- Верхнее меню: динамические якоря по `REQUEST_URI` (`lireate-lh4-subnav-lib.php`), стили 16–19px, sticky, телефон + CTA.
- JS: spy активного якоря на главной и CMS-страницах; Escape/outside click для мобильного меню.
- Превью: home/team/uslugi/mktu/blog + фрагмент услуги RTZ; роутер `pages/_preview/nav-v3-router.php`.
- Install (только репо): `scripts/lireate_hosting_navigation_v3.py` → `NAV_ASSET_QS=20261126r2`, доп. CSS в head.
- CI: `scripts/test_lireate_nav_v3_full.py` + существующий hide-oplata.

## Скриншоты

Каталог: `docs/screenshots/nav-v3/`

| Фаза | Пути |
|------|------|
| BEFORE (live) | `before/<slug>/<width>.png` |
| AFTER (preview) | `after/<slug>/<width>.png`, на 320–390 также `*-menu-open.png` |

Страницы: `home`, `team`, `uslugi`, `mktu`, `service-rtz`, `blog`.  
Ширины: 320, 375, 390, 768, 1280, 1440, 1920.

Снятие: `python3 scripts/capture_nav_v3_screenshots.py` (нужен `LIREATE_PUBLIC_SITE_URL`).

## Спорные / ограничения

- На **live** левое меню по-прежнему из CMS `template/main.php`; chrome из `pages/_shared` — для превью и будущей интеграции услуг (wire), не автодеплой в этом PR.
- Главная в AFTER: sidebar через chrome-inc (визуально ближе к референсу), на проде до отдельной интеграции остаётся CMS-меню + глобальный head Nav V3.
- Услуга RTZ в AFTER — укороченный фрагмент с chrome, не полный longread.
- Якоря внутренних страниц завязаны на id в Light V4 разметке; если id на проде отличаются, spy подстроить после выкладки страниц.

## Референс

Ветка `design/nav-v3-socials-reference` @ `c18f1ff` — **не включена** в PR, использовалась только для чтения.

### Соответствие референсу (разбор [Extract reference nav HTML patterns](bc-bea202e1-c3c4-59c8-80b2-ae12cfcedcaa))

| Паттерн референса | В #127 |
|-------------------|--------|
| Sticky top-nav, 16–19px, разделители, `ln3-active` + подчёркивание | Да: `lh4-subnav` + `lireate-global-subnav-light-v4.css`, home — `lireate-navigation-dual-v3.css` |
| `scroll-progress` в шапке | Да на главной Light V4 (`lireate-home-page.inc.php`); в CMS chrome — нет |
| 5 соцсетей, inline SVG, grid | Да в `lireate-public-cms-sidebar.inc.php` + patch path в JS |
| Mobile drawer sidebar + shade | Да: `lireate-public-cms-chrome` burger/veil, ≤1150px |
| Desktop collapse sidebar → ~78px (`body.side-collapsed`) | **Нет** — отдельная итерация (CMS `#leftSideBar` на проде без collapse) |
| ≤660px: скрыть top-nav, кнопка «Якоря» + `anchor-mobile` | **Частично:** subnav скрыт с ≤1150px (как у главной); отдельной полосы «Разделы»/anchor-mobile нет — якоря только через бургер или контент |
| `is-scrolled` + усиленная тень шапки | **Нет** в chrome subnav (на home — логика в home JS) |

Вывод: визуал и соцсети близки к референсу; сознательно не переносили collapse desktop и mobile-only anchor-grid, чтобы не ломать CMS-разметку и согласованность с брейкпоинтом 1150px главной.