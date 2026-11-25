# LIREATE Light V4 — отчёт подготовки (без публикации на REG.RU)

Дата: 25.11.2026. Ветка: `cursor/lireate-light-v4-prep-b68f`. Источник дизайна: cherry-pick коммитов из draft PR #117 (`design/lireate-light-v4-system-2026-11-25`), **без merge #117**.

**Статус запуска:** только GitHub (ветка + PR + скриншоты). Ничего на сервер не выкладывалось, crontab не менялся.

## Краткий итог по семействам

| Семейство | Статус в PR | Примечание |
| --- | --- | --- |
| Главная CMS `/` | **Готово в репо, не на хостинге** | `template/page/home/*`, `scripts/lireate_home_light_v4_install.py`; патч `main.php` — dry-run на fixture; на live — ручная проверка `page_container` |
| Услуги (лонгриды `pages/services/`) | **7 slug + шаблон + ТЗ** | `lireate_light_v4_migrate.py --apply`; **не трогали** `registraciya-tovarnogo-znaka-s-oplatoy-za-rezultat` (другой агент) |
| `registraciya-tovarnogo-znaka/` | **Готово** | overlay v4 + white v3, контент без сокращений |
| Патентные задачи (13 slug) | **Не сделано** | migrate: BLOCKER `unknown service shell` — отдельная вёрстка |
| Блог список `/news/` | **Частично** | `lireate-blog-light-v4.css` в `lireate-blog-list.inc.php`; article layout — legacy + overlay |
| Блог статья | **Частично** | overlay CSS в template; полный белый article — отдельный этап |
| Команда `/team/` | **Не сделано** | тёмный `lireate-team.css`; нужен отдельный pass + body class |
| Контакты | **Не сделано** | аналогично |
| Кейсы/портфолио | **Не сделано** | CMS на хостинге |
| Инфо (о компании, документы, партнёры) | **Не сделано** | локальные CSS без v4 overlay |

## SSH / live main.php

Ключ SSH в окружении **не прошёл** аутентификацию (publickey). Живой `template/main.php` взят через HTTPS-фикстуру `scripts/lireate_fetch_main_contacts_fixture.py` (~62 KiB). Бэкап владельца: `~/lireate-autopub/backups/light-v4-pre-20261125T142321Z/`.

## Cron lireate-news (риск перезаписи)

Репозиторий **devham13/lireate-news** доступен (read). Каждые ~30 мин:

- `lireate_visibility_maintenance`
- `lireate_home_unslop` → `scripts/lireate_home_unslop.py` — добавляет `/template/css/lireate-home-unslop.css?v=20261122u1` в `template/main.php`, если в шаблоне есть маркеры `lireate-home-hero.css` и `lireate-home-dark`.
- `lireate_hosting_publish` — блог

**Чтобы cron не затёр Light V4 после выкладки:**

1. Обновить `lireate_home_unslop.py` (или отключить через `LIREATE_HOME_UNSLOP=no` в autopub `.env`) — скрипт **не должен** снова подключать legacy unslop/dark поверх v4.
2. Вынести канон главной в `office-repo`: `template/page/home/` + install; в **lireate-news** добавить шаг синхронизации ассетов из office-repo вместо правки hero в `main.php` вручную.
3. Зафиксировать в lireate-news `hosting-design/template/css/lireate-home-light-v4.css` (или копию из office) и новый маркер install вместо `lireate-home-unslop.css`.
4. Документировать: после merge office — **один** осознанный прогон install на REG.RU, затем правка cron/news.

## План выкладки на REG.RU (после «да» владельца)

1. Бэкап (уже есть) + свежий `template.tgz` при необходимости.
2. **Office-repo → хостинг:** `git pull` в `~/lireate-autopub/office-repo`.
3. **Главная:** `python3 scripts/lireate_home_light_v4_install.py --apply` с `LIREATE_DOCROOT=~/www/public_html`, `LIREATE_ALLOW_PUBLISH=yes`; проверить diff `main.php`.
4. **Копировать:** `template/page/home/**`, `pages/home/_shared/**`, `template/css/lireate-home-light-v4.css`, `template/js/lireate-home-light-v4.js`, `template/css|js/lireate-light-v4.*`.
5. **Услуги:** для каждого slug из PR — `python3 scripts/lireate_hosting_publish_service.py --slug <slug> --update --apply`; **не** трогать `registraciya-tovarnogo-znaka-s-oplatoy-za-rezultat` до завершения другого PR.
6. После publish услуг — вручную вернуть perf-блок `seo-perf-20261120-v1` в docroot `_shared` (как в AGENTS.md).
7. **Блог:** `python3 scripts/lireate_blog_sync_template.py` + install blog template на хостинге.
8. Команда/контакты/docs — отдельные install-скрипты (следующий PR).
9. Smoke: формы → `/mail.php`, одна инициализация Яндекс.Метрики (ID как на проде), canonical, mobile 390.

## Откат

1. Восстановить `~/lireate-autopub/backups/light-v4-pre-20261125T142321Z/template.tgz` → `~/www/public_html/template/`.
2. `python3 scripts/lireate_home_light_v4_install.py --restore-backup <путь из install>` если применяли home install.
3. Услуги: redeploy предыдущего коммита office-repo + `--update` по slug или restore из backup каталога `pages/services/<slug>/`.
4. В lireate-news: оставить `LIREATE_HOME_UNSLOP=no` до стабилизации.

## Проверки (локально)

| Проверка | Результат |
| --- | --- |
| `python3 scripts/lireate_light_v4_migrate.py` (dry-run) | 7 pending, 13 blockers (patentnye-zadachi) |
| `lireate_service_sync_shared.py` | OK |
| `lireate_blog_sync_template.py` | OK |
| `test_lireate_service_index_functions.py` | OK |
| `test_lireate_href_integrity` | OK |
| `test_lireate_service_live_markers` | FAIL на `registraciya-tovarnogo-znaka` — **то же на origin/main** (форма в inc-блоке, parity `mail.php`) |
| `lireate_service_lint.py --slug registraciya-tovarnogo-znaka` | ERROR parity — **предыстория main** |
| PHP 5.6 `php -l` | PHP CLI нет в Cloud VM |

## Скриншоты до/после

Каталог: `/opt/cursor/artifacts/light-v4-screenshots/` (прикреплены к PR).

- **До:** снимки текущего продакшена — home, TM, news, team, contacts, prodlenie (1440 + 390).
- **После:** proposal home HTML; TM и prodlenie — локальный `php -S` из ветки; blog article — preview; blog list after — нет (500 в preview).

## Осознанно не в этом PR

- Merge PR #81, #111, #117
- Slug `registraciya-tovarnogo-znaka-s-oplatoy-za-rezultat`
- Все 13 `patentnye-zadachi-*`
- Полный белый редизайн team/contacts/portfolio
- Публикация и merge в `main`