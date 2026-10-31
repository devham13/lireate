# Шаблон «Партнёры» LIREATE (CMS /partners/)

Файлы копируются в `DOCROOT/template/page/partners/` (см. `scripts/lireate_partners_sync_template.py`).

## Интеграция CMS

1. `python3 scripts/lireate_partners_sync_template.py`
2. На хостинге: `python3 scripts/lireate_partners_install.py --check --docroot "$LIREATE_DOCROOT"`
3. `--apply` / `--rollback` — см. `docs/lireate-partners-hosting.ru.md`

Патч заменяет legacy-блок (`pageTitle` … `personsSlide`) на `lireate-partners-cms.inc.php` и добавляет `lireate-partners-body` только для `$temp['page']=='partners'`.

Подробнее: `docs/lireate-partners-hosting.ru.md`.
