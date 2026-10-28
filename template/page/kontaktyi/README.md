# Шаблон контактов LIREATE (CMS /kontaktyi/)

Файлы копируются в `DOCROOT/template/page/kontaktyi/` (см. `scripts/lireate_contacts_sync_template.py`).

## Интеграция CMS

1. Синхронизировать ассеты: `python3 scripts/lireate_contacts_sync_template.py`
2. На хостинге: `python3 scripts/lireate_contacts_install.py --check` (env: `LIREATE_DOCROOT`)
3. Применение: `python3 scripts/lireate_contacts_install.py --apply`
4. Откат: `python3 scripts/lireate_contacts_install.py --rollback <BACKUP_ID>`

Патч заменяет legacy-блок (`title-ap` … перед `page_container`) на `lireate-contacts-cms.inc.php` и добавляет класс `lireate-contacts-body` на `<body>`.

Страница остаётся в **обычном CMS-шаблоне** с `#leftSideBar`; стиль — как `/news` (тёмный фон, карточки, бейджи).

Превью с левым меню: `pages/kontaktyi/_preview/cms-shell.php` (локальный PHP-сервер).

Подробнее: `docs/lireate-contacts-hosting.ru.md`.
