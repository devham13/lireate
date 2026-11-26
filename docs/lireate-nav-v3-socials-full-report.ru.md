# Nav V3 — краткий отчёт для Юры

| | |
|--|--|
| PR | https://github.com/devham13/lireate/pull/127 |
| SHA | *(после push)* |
| Скрины | `docs/screenshots/nav-v3/{before,after}/<slug>/` |
| QA | `docs/screenshots/nav-v3/capture-qa.json` |

## Исправление брака скринов (CSS)

- Роутер: статика по `PHP_URL_PATH` **до** HTML-маршрутов; `php -S … -t <repo>`.
- Снимки: только viewport, `deviceScaleFactor=1`, проверка `scrollWidth == innerWidth` (mobile), CSS 200 в network.
- SVG соцсетей: `width="24" height="24"` в разметке sidebar.

## Проверки (capture-qa.json, AFTER home)

| Кадр | PNG w | overflow | hasNavCss |
|------|-------|----------|-----------|
| home/1440 | 1440 | нет | да |
| home/390-menu-open | 390 | нет | да |
| service-rtz/1440-side-collapsed | 1440 | нет | да |

Визуально: оформленное меню, collapse, мобильный drawer — см. файлы выше.

## Соцсети / Оплата

TG, WA, Max, **VK vk.ru/lireate**, **YouTube UCpg1LWotCQWwzviPDb_9Rew** — в sidebar превью.  
Tochka «Оплата услуг»: скрытие CSS+JS на live; в превью-sidebar пункта нет.

**Без merge и без выкладки.**