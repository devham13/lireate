# PR B — публичные меню и Light V4

Ветка: `cursor/light-v4-menus-shell-b68f` (база `main`). **Merge: не выполнять.**

## Изменения (r22 chrome)

- Светлый единый `#leftSideBar` через `lireate-public-cms-chrome.css` + `lireate-cms-pages-light-v4.css` (иконки соцсетей 20px, без огромного Telegram).
- Колонка контента `.lireate-public-cms-content` с `margin-left:280px` (≥1151px); subnav внутри колонки, справа телефон и «Оставить заявку».
- Превью страниц обёрнуты в `content_open` + `subnav`.
- Версия ассетов: `LIGHT_V4_ASSET_VERSION=20261126r22`.

## Скриншоты (scrollY=0)

Каталог: `docs/screenshots/pr-b-public-menus/`. Генерация: `python3 scripts/capture_pr_b_public_menus_screenshots.py`.

| Страница | 1440×900 | 390×844 |
|---|---|---|
| Главная `/` | `home-1440.png` | `home-390.png` |
| `/uslugi/` | `uslugi-1440.png` | `uslugi-390.png` |
| `/mktu` | `mktu-1440.png` | `mktu-390.png` |
| `/partners` | `partners-1440.png` | `partners-390.png` |
| `/docs` | `docs-1440.png` | `docs-390.png` |
| `/contacts/` | `contacts-1440.png` | `contacts-390.png` |
| `/team` | `team-1440.png` | `team-390.png` |
| `/o_kompanii` | `o-kompanii-1440.png` | `o-kompanii-390.png` |
| Блог список | `blog-list-1440.png` | `blog-list-390.png` |
| Статья блога | `blog-article-1440.png` | `blog-article-390.png` |
| Регистрация ТЗ | `registraciya-tovarnogo-znaka-1440.png` | `registraciya-tovarnogo-znaka-390.png` |
| Пошлины (патент) | `patent-poshliny-1440.png` | `patent-poshliny-390.png` |
| `/findtz/` | `findtz-1440.png` | `findtz-390.png` |
| Продление | `prodlenie-1440.png` | `prodlenie-390.png` |

Дополнительно: `mktu-before-1440.png` (live).

### Проверено визуально

- `/mktu`: заголовок не под сайдбаром; subnav с CTA; светлое меню.
- Услуга: тот же светлый сайдбар, lh4-subnav + якорное lx-nav.
- Иконки мессенджеров в сайдбаре одного размера.

## Deploy / rollback

См. предыдущие блоки overlay + `lireate_hosting_publish_service` (копирует `pages/_shared` chrome на docroot).