# Деплой redesign «Партнёры» (/partners/)

Страница **не** standalone: патчится PHP-шаблон CMS, левое меню `#leftSideBar` не трогаем.

## Подготовка (office-repo)

```bash
python3 scripts/lireate_partners_sync_template.py
python3 scripts/lireate_partners_install.py --check --docroot "$LIREATE_DOCROOT"
```

## Деплой на REG.RU (с машины с доступом к DOCROOT, не Cloud Agent)

```bash
export LIREATE_DOCROOT=/path/to/site/docroot
python3 scripts/lireate_partners_sync_template.py
python3 scripts/lireate_partners_install.py --apply --docroot "$LIREATE_DOCROOT"
```

Скрипт копирует:

- `pages/partners/_shared/*` → `DOCROOT/pages/partners/_shared/` и `DOCROOT/template/page/partners/`
- `lireate-metrika-goals.js` → `template/page/partners/`

Патчит CMS PHP с блоком «Наши Партнёры» / `personsSlide`, подключает `lireate-partners-cms.inc.php`, сохраняет legacy в `template/page/partners/lireate-partners-legacy.inc.php`.

**Не изменяет:** `data/db.php`, корневой `.htaccess`, `template/parts/leftSideNav.php`, `topBar.php`.

## Откат

```bash
python3 scripts/lireate_partners_install.py --rollback <BACKUP_ID> --docroot "$LIREATE_DOCROOT"
```

## Превью

```bash
bash scripts/lireate_partners_preview_server.sh
# http://127.0.0.1:8892/partners_preview/live-cms-shell.php
```
