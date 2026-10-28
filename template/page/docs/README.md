# LIREATE — CMS «Документы» (`/docs/`)

Исходники: `pages/docs/_shared/`. После правок:

```bash
python3 scripts/lireate_docs_sync_template.py
```

Патч install заменяет содержимое `template/page/docs/index.php` (секции «Документы компании» + «Реквизиты») на `lireate-docs-cms.inc.php` и добавляет условный класс `lireate-docs-body` в `template/main.php`.

Подробнее: `docs/lireate-docs-hosting.ru.md`.
