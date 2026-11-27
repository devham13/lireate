# Деплой redesign профиля автора (/avtor/)

Страница в CMS: `full_story` заменяется на include из репозитория (как `/team/`).

## Подготовка (office-repo)

```bash
python3 scripts/lireate_avtor_sync_template.py
```

## Файлы на хостинге

| Источник (git) | Назначение |
|----------------|------------|
| `pages/avtor/_shared/*` | `DOCROOT/pages/avtor/_shared/` |
| те же файлы | `DOCROOT/template/page/avtor/` |

## Патч CMS

В `full_story` страницы `avtor` вставить:

```html
<section id="lireate-avtor-cms">
<?php /* lireate-avtor-install:v1 */ ?>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/template/page/avtor/lireate-avtor-cms.inc.php'; ?>
</section>
```

В `<body class="cms-default…">` добавить при `$temp['page']=='avtor'` классы `lireate-avtor-body lireate-theme-light-v4` (если ещё нет).

**Не изменяет:** `index.php`, `.htaccess`, `mail.php`, `consent-gate.php`, `inc/lireate-amo-lead.php`, `data/db.php`.

## Превью

```bash
python3 scripts/lireate_avtor_sync_template.py
python3 -m http.server 8799 --directory /path/to/office-repo
# http://127.0.0.1:8799/pages/avtor/_preview/index.php
```