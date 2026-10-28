# Шаблон контактов LIREATE (CMS /kontaktyi/)

Файлы копируются в `DOCROOT/template/page/kontaktyi/` (см. `scripts/lireate_contacts_sync_template.py`).

## Интеграция CMS

1. Синхронизировать ассеты: `python3 scripts/lireate_contacts_sync_template.py`
2. На хостинге: `python3 scripts/lireate_contacts_install.py --check` (env: `LIREATE_DOCROOT`)
3. Применение: `python3 scripts/lireate_contacts_install.py --apply`
4. Откат: `python3 scripts/lireate_contacts_install.py --rollback <BACKUP_ID>`

Патч заменяет legacy-блок (`title-ap` … закрытие inner row `margin-bottom:20px`) на `lireate-contacts-cms.inc.php` и вставляет в атрибут `class` тела страницы PHP-фрагмент `if($temp['page']=='kontaktyi') echo ' lireate-contacts-body'` (класс только на `/kontaktyi/`).

Страница остаётся в **обычном CMS-шаблоне** с `#leftSideBar`; стиль — как `/news` (тёмный фон, карточки, бейджи).

CSS/JS подключаются из **`/template/page/kontaktyi/`** (корневые пути, без `<base>`): `lireate-contacts.css`, `lireate-contacts.js`, `lireate-metrika-goals.js`. После `--apply` файлы лежат в `DOCROOT/template/page/kontaktyi/`.

Превью с live sidebar: `live-cms-shell.php` (CSS с локального preview-origin, CMS-ассеты с prod-origin).

Подробнее: `docs/lireate-contacts-hosting.ru.md`.
