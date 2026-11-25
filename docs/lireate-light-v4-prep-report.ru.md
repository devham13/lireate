# LIREATE Light V4 — отчёт подготовки (без публикации на REG.RU)

Дата: 25.11.2026. Ветка: `cursor/lireate-light-v4-prep-b68f`. PR: **#118**. Источник дизайна: cherry-pick из draft PR #117, **без merge #117**.

**Статус:** только GitHub. Ничего на сервер не выкладывалось, crontab не менялся, main не мержили.

## CI PR #118 (последний зелёный прогон на ветке)

| Check | Workflow | Результат |
| --- | --- | --- |
| **php56** | PHP 5.6 compatibility | success (~27s) |
| **Performance patch checks** | Performance patch checks | success (~28s) |

Падение `test_lireate_service_live_markers` по parity `mail.php` на `registraciya-tovarnogo-znaka` — **то же на `origin/main`** (форма в `blocks/lireate-contact-form.inc.php`, не в `index.php`).

## Скриншоты в репозитории

Каталог: **`docs/light-v4-screenshots/`** (см. `README.md`).

**После главной** — `after-home-cms-*.png` из `pages/home/_preview/cms-shell.php` + `template/page/home/*` (одна инициализация Метрики в shell, форма `/mail.php`, consent `/private_data` + `/politika_konfidencialnosti`, без `noindex`).

## Главная: live vs CMS (структурная сверка)

| Проверка | Live (типичный main.php) | CMS after (`cms-shell.php` + home inc) |
| --- | --- | --- |
| `robots` noindex | нет на проде | нет |
| canonical | `/` | `href="/"` |
| title / description | из CMS | заданы в shell (SEO-тексты совпадают по смыслу) |
| H1 | один на главной | один в hero |
| Форма | `ajax_form` → `/mail.php` | да; hidden `mail_token`, `fuck_spamers`, `consent_form`, UTM через `lireate-utm.js` |
| Яндекс.Метрика | `ym(..., "init", …)` в main | **1×** init в shell; цели `data-lx-ym-goal` как на team/contacts |
| Schema.org | JSON-LD в CMS/контенте | блоки FAQ/контент без выдуманных entity; полный graph как на проде — после install в main |

## Страницы: статус и скриншоты

| URL | Сделано | Скрин до | Скрин после |
| --- | --- | --- | --- |
| `/` | CMS-пакет + install | `before-home-*` | `after-home-cms-*` |
| `/registraciya_tovarnogo_znaka/` | overlay v4 | `before-tm-*` | `after-tm-*` |
| `/prodlenie_tovarnogo_znaka/` (услуга) | migrate v4 | — | `after-service-desktop.png` |
| `/patentnye-zadachi/` каталог + 13 tools | migrate v4 (patent footer shell) | — | `after-patent-*` (пример: poshliny-registraciya) |
| `/news/` | list overlay + fix preview | `before-news-desktop` | `after-blog-list-*` |
| статья блога | layout overlay | — | `after-blog-article-*` |
| `/team/` | overlay CSS/JS + body class | `before-team-desktop` | `after-team-*` |
| `/contacts/` | overlay | `before-contacts-desktop` | `after-contacts-*` |
| `/o_kompanii/`, `/docs/`, `/partners/` | overlay в template | — | следующий прогон (аналогично team) |
| `/portfolio/`, кейсы CMS | **нет в репо** | — | только live «до»; нужен install с хостинга |

**Не трогали:** `registraciya-tovarnogo-znaka-s-oplatoy-za-rezultat/` (другой агент).

## +73k строк в diff — состав

Это **не** vendor/node_modules. Основной объём — повторяющиеся копии **`lireate_service_sync_shared.py`** в `pages/services/*/assets/lireate-shared/` (по одному на slug), в т.ч.:

- `lireate-qrcode-npm.js` (~2089 строк × множество каталогов)
- `lireate-patent-tools.js` (~1142 строк)
- `lireate-light-v4.css/js`, patent payment cores, reviews.json, blocks `*.inc.php`

Крупнейшие **уникальные** новые файлы: `template/css/lireate-home-light-v4.css`, `design-proposals/.../index.html`, `template/page/home/lireate-home-page.inc.php`.

## Патентные задачи

`scripts/lireate_light_v4_migrate.py` — режим **patent shell**: страницы без `</body>` в `index.php`, JS вставляется перед `lireate-patent-tool-footer.inc.php`. **13/13** + каталог применены в ветке.

## Блог

- Исправлен `pages/blog/_preview/list.php` (load.php + config → нет 500).
- `lireate-theme-light-v4` на list/article; новые статьи получают overlay через `template/page/news/*` + `_shared`.

## Cron lireate-news

Push в `lireate-news` **403** для бота. Diff guard: **`docs/lireate-news-home-unslop-guard.diff`** (локальный коммит `afbb6a0`). Нужен ручной PR в `devham13/lireate-news` с ветки `cursor/light-v4-home-guard-b68f` или `git apply` diff.

## План выкладки (порядок)

1. Бэкап `light-v4-pre-20261125T142321Z`.
2. `git pull` office-repo на хостинге.
3. `LIREATE_HOME_LIGHT_V4=1` или merge guard в lireate-news **до** включения главной.
4. `lireate_home_light_v4_install.py --apply` + `lireate_cms_light_v4_overlay_install.py --apply`.
5. Team/contacts/about/docs/partners — соответствующие `*_install.py`.
6. Услуги: `lireate_hosting_publish_service.py --slug … --update --apply` (кроме s-oplatoy).
7. Вернуть perf `seo-perf-20261120-v1` в docroot `_shared`.
8. Smoke: формы, 1× Metrika, mobile 390.

## Откат

Restore `template.tgz` из бэкапа; `lireate_home_light_v4_install --restore-backup`; redeploy предыдущего SHA услуг; `LIREATE_HOME_UNSLOP=no`.