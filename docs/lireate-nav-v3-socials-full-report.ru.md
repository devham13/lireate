# Nav V3 + соцсети (полный chrome) — отчёт для Юры

Дата: 2026-11-26. **PR без merge и без выкладки на REG.RU.**

## PR и коммит

| Поле | Значение |
|------|----------|
| PR | https://github.com/devham13/lireate/pull/127 |
| Ветка | `cursor/nav-v3-socials-full-b68f` |
| SHA | *(см. последний push в PR)* |
| Asset QS | `20261126r3` |

## Обязательные пункты ТЗ (выполнено в PR)

1. **Desktop collapse** — `body.side-collapsed`, кнопка `#collapseSide`, узкая панель 78px; состояние в `localStorage` ключ `lireateNavV3SideCollapsed`.
2. **Mobile «Разделы»** — `#anchorToggle` + `#anchorMobile` (сетка якорей текущей страницы), ≤1150px, в т.ч. **768px** при скрытом subnav.
3. **is-scrolled** — класс на `.lh4-subnav` / `.ln3-page-subnav` при `scrollY > 36`, усиленная тень.
4. **Без наложения пунктов** — горизонтальный скролл subnav на 1151–1390px; phone/CTA уменьшены; mobile якоря отдельной полосой.

## Скриншоты (репозиторий)

Каталог: **`docs/screenshots/nav-v3/`**

| Slug | Live BEFORE | AFTER preview |
|------|-------------|---------------|
| `home` | `before/home/<width>.png` | `after/home/<width>.png` |
| `team` | `before/team/…` | `after/team/…` |
| `uslugi` | `before/uslugi/…` | `after/uslugi/…` |
| `mktu` | `before/mktu/…` | `after/mktu/…` |
| `service-rtz` | `before/service-rtz/…` | `after/service-rtz/…` |
| `blog-article` | `before/blog-article/…` | `after/blog-article/…` |

Ширины: **320, 375, 390, 768, 1280, 1440, 1920**.

Дополнительно AFTER:

- `*-menu-open.png` — бургер (≤768px)
- `*-sections-open.png` — «Разделы» (320–768)
- `1440-side-collapsed.png` — свёрнутый сайдбар

Снятие: `python3 scripts/capture_nav_v3_screenshots.py` (`LIREATE_PUBLIC_SITE_URL`; статья — `LIREATE_NAV_BLOG_ARTICLE_PATH`, по умолчанию slug проверки ТЗ).

## Основные файлы PR

- `pages/_shared/lireate-navigation-v3-bundle-lib.php`
- `pages/_shared/lireate-public-cms-chrome-lib.php`
- `pages/_shared/lireate-public-cms-chrome.inc.php`
- `pages/_shared/lireate-public-cms-sidebar.inc.php`
- `pages/_shared/lireate-lh4-subnav-lib.php`
- `pages/_shared/lireate-lh4-subnav.inc.php`
- `pages/_shared/lireate-lh4-mobile-anchors.inc.php`
- `template/css/lireate-navigation-dual-v3.css`
- `template/css/lireate-public-cms-chrome.css`
- `template/css/lireate-global-subnav-light-v4.css`
- `template/js/lireate-navigation-dual-v3.js`
- `scripts/lireate_hosting_navigation_v3.py`
- `scripts/capture_nav_v3_screenshots.py`
- `scripts/test_lireate_nav_v3_full.py`
- `pages/_preview/nav-v3-router.php`
- Превью: `pages/home/_preview/cms-shell.php`, `pages/team/_preview/`, `template/page/uslugi/_preview/`, `pages/mktu/_preview/`, `pages/blog/_preview/article-chrome.php`, `pages/services/registraciya-tovarnogo-znaka/_preview/`

## Соцсети (5 шт., превью sidebar)

| Сеть | URL в `lireate-public-cms-sidebar.inc.php` | Статус |
|------|--------------------------------------------|--------|
| Telegram | https://t.me/lireate_patent | live-цель из CMS |
| WhatsApp | https://wa.me/79600484957 | live |
| Max | https://max.ru/channel_lireate | live (иконка CMS) |
| VK | https://vk.ru/lireate | **подтверждён** на prod footer |
| YouTube | https://www.youtube.com/channel/UCpg1LWotCQWwzviPDb_9Rew | **подтверждён** канал |

## «Оплата услуг» / Tochka

- В превью-sidebar **нет** пункта Tochka.
- На live CMS в HTML ссылка может остаться; скрытие: CSS `#leftSideBar a…tochka…` + JS `hidePaymentLink()` в `lireate-navigation-dual-v3.js`.

## Ограничения

- Полный chrome на **live** CMS пока через global head + существующий `#leftSideBar`; `pages/_shared` chrome — превью и wire услуг.
- RTZ AFTER — фрагмент с chrome для навигации, не полный longread деплой.

## Референс

`design/nav-v3-socials-reference` @ `c18f1ff` — не в PR, только чтение.