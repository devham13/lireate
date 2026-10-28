# Деплой redesign «Контакты» (/kontaktyi/)

Страница **не** standalone: патчится PHP-шаблон CMS, левое меню `#leftSideBar` не трогаем.

## Подготовка (office-repo)

```bash
python3 scripts/lireate_contacts_sync_template.py
python3 scripts/lireate_contacts_install.py --check --docroot "$LIREATE_DOCROOT"
```

## Деплой на REG.RU (с машины с доступом к DOCROOT, не Cloud Agent)

```bash
export LIREATE_DOCROOT=/path/to/site/docroot
export LIREATE_ENV_FILE=~/lireate-autopub/.env   # при необходимости
python3 scripts/lireate_contacts_sync_template.py
python3 scripts/lireate_contacts_install.py --apply --docroot "$LIREATE_DOCROOT"
```

Скрипт:

- копирует `template/page/kontaktyi/*` и `pages/kontaktyi/_shared/*` в DOCROOT;
- находит CMS PHP с блоком «Контакты Лирейт» и подключает `lireate-contacts-cms.inc.php`;
- сохраняет snapshot legacy в `template/page/kontaktyi/lireate-contacts-legacy.inc.php`.

**Не изменяет:** `data/db.php`, корневой `.htaccess`.

Счётчик Метрики из CMS `<head>` (env `LIREATE_YANDEX_METRIKA_ID`); цели кликов — `lireate-metrika-goals.js` (data-атрибут на `<html>`).

## Откат

ID бэкапа печатается при `--apply` (`backup: …`).

```bash
python3 scripts/lireate_contacts_install.py --rollback 20261028T120000Z --docroot "$LIREATE_DOCROOT"
```

Восстанавливает файлы из бэкапа и legacy HTML-блок из snapshot.

## Локальное превью (скриншоты / QA)

```bash
python3 scripts/lireate_contacts_fetch_live_snapshot.py
bash scripts/lireate_contacts_preview_server.sh
# http://127.0.0.1:8891/kontaktyi_preview/live-cms-shell.php
```
