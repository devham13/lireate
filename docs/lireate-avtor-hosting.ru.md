# Деплой redesign профиля автора (/avtor/) — изолированно

**Запрещено** в одном прогоне с `/avtor/`: `template/js/lireate-sitewide-sidebar-v7.js`, `template/css/lireate-sitewide-sidebar-v7.css`, `template/lireate/lireate-sidebar-v7.*`, `template/css/lireate-cms-pages-light-v4.css`, `data/lireate-seo-*`, `index.php`, `.htaccess`, `mail.php`, `consent-gate.php`, `inc/lireate-amo-lead.php`, `data/db.php`.

В левом меню **никогда** не показывать фото `person/*` (экспертный блок).

## Подготовка (office-repo)

```bash
python3 scripts/lireate_avtor_sync_template.py
python3 scripts/lireate_avtor_install.py --list
```

## Файлы на хостинге (только /avtor/)

| Источник (git) | Назначение |
|----------------|------------|
| `pages/avtor/_shared/*` | `DOCROOT/pages/avtor/_shared/` |
| те же файлы | `DOCROOT/template/page/avtor/` |
| `template/page/avtor.php` | `DOCROOT/template/page/avtor.php` |

## Патч CMS

`template/page/avtor.php` (include):

```php
/* lireate-avtor-install:v1 */
require $_SERVER['DOCUMENT_ROOT'] . '/template/page/avtor/lireate-avtor-cms.inc.php';
```

В длинной строке `<body class="cms-default<?php …">` в `template/main.php` после `echo ' lireate-team-body';` вставить (одной PHP-строкой, без вложенных `<?php`):

```php
 if($temp['page']=='avtor') echo ' lireate-avtor-body lireate-theme-light-v4';/* lireate-avtor-install:body-v1 */
```

Проверка плана (без apply из Cloud):

```bash
python3 scripts/lireate_avtor_install.py --check --docroot "$LIREATE_DOCROOT"
```

Бэкап перед apply на хостинге: `~/lireate-autopub/backups/avtor/<timestamp>/` (файлы из manifest + `template/main.php`).

## Превью

```bash
python3 scripts/lireate_avtor_sync_template.py
python3 scripts/lireate_avtor_render_preview_html.py
python3 -m http.server 8799 --directory /path/to/office-repo
# http://127.0.0.1:8799/pages/avtor/_preview/index.php  (PHP) или
python3 scripts/lireate_avtor_build_shell_preview_html.py
python3 scripts/lireate_avtor_capture_final_screenshots.py  # → /opt/cursor/artifacts/avtor-final/
```