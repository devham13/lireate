# Деплой redesign «Документы» (/docs/)

Страница **не** standalone: патчится `template/page/docs/index.php` и условный класс `<body>` в `template/main.php`. Левое меню `#leftSideBar` не трогаем.

## Подготовка (office-repo)

```bash
python3 scripts/lireate_docs_sync_template.py
python3 scripts/lireate_docs_install.py --check --docroot "$LIREATE_DOCROOT"
```

`--check` и `--apply` гоняют `php -l` на пропатченные `template/page/docs/index.php` и `template/main.php` (если в docroot нет main.php — на snapshot `scripts/fixtures/cms-main-live.snapshot.php`).

## Деплой на REG.RU (не из Cloud Agent)

```bash
export LIREATE_DOCROOT=/path/to/site/docroot
export LIREATE_ENV_FILE=~/lireate-autopub/.env   # при необходимости
python3 scripts/lireate_docs_sync_template.py
python3 scripts/lireate_docs_install.py --apply --docroot "$LIREATE_DOCROOT"
```

Скрипт:

- копирует `pages/docs/_shared/*` в DOCROOT (`pages/docs/_shared/` и `template/page/docs/`);
- заменяет legacy-секции в `template/page/docs/index.php` на `lireate-docs-cms.inc.php`;
- добавляет в `template/main.php` фрагмент `if($temp['page']=='docs') echo ' lireate-docs-body'` в атрибут `class` у `<body>`;
- сохраняет snapshot legacy в `template/page/docs/lireate-docs-legacy.inc.php`.

**Не изменяет:** `data/db.php`, корневой `.htaccess`.

Счётчик Метрики: env `LIREATE_YANDEX_METRIKA_ID` (fallback как у контактов); цели — `lireate-metrika-goals.js`.

## Откат

ID бэкапа печатается при `--apply` (`backup: …`).

```bash
python3 scripts/lireate_docs_install.py --rollback 20261028T120000Z --docroot "$LIREATE_DOCROOT"
```

Восстанавливает файлы из бэкапа, legacy HTML из snapshot, удаляет файлы из `created.json`.

## Локальное превью

```bash
bash scripts/lireate_docs_preview_server.sh
# http://127.0.0.1:8892/docs_preview/live-cms-shell.php
```
