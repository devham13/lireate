# Резервная копия (rollback)

Каталог `backup-old/` хранит снимок **legacy CMS** до редизайна LIREATE.

- Файл `cms-legacy-findtz.html` — **только справочник** (верстка/тексты старой страницы `/findtz/`). На хостинг и в CRON он не подключается.

## Откат на legacy-страницу CMS

Если нужно временно вернуть старую HTML-страницу на проде, восстановите содержимое из `cms-legacy-findtz.html` в каталог URL `/findtz/` на хостинге по вашей схеме CMS.

Локально, для сравнения с текущим `index.php`:

```bash
diff -u backup-old/cms-legacy-findtz.html index.php
```

Актуальная публикуемая услуга — `../index.php` + `publish.manifest.json` (деплой через `scripts/lireate_hosting_publish_service.py` / CRON).

**Быстрый откат после merge (вернуть CMS-страницу на `/findtz/`):** на хостинге в `DOCROOT/findtz/` замените `index.php` содержимым из `cms-legacy-findtz.html` (или восстановите каталог из бэкапа ISPmanager). Затем удалите `.lireate-managed.json` в этом каталоге, если он был создан публикатором.
