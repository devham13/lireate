# Резервная копия (rollback)

Каталог `backup-old/` хранит снимок **legacy CMS** до редизайна LIREATE.

- Файл `cms-legacy-registraciya.html` — **только справочник** (верстка/тексты старой страницы). На хостинг и в CRON он не подключается.

## Откат на legacy-страницу CMS

Если нужно временно вернуть старую HTML-страницу на проде (вне репозитория услуг LIREATE), восстановите файл из бэкапа вручную на хостинге в каталог URL `/registraciya_tovarnogo_znaka/` согласно вашей схеме публикации CMS.

Локально, для сравнения с текущим `index.php`:

```bash
diff -u backup-old/cms-legacy-registraciya.html index.php
```

Актуальная публикуемая услуга — `../index.php` + `publish.manifest.json` (деплой через `scripts/lireate_hosting_publish_service.py` / CRON).

**Быстрый откат после merge (вернуть CMS на `/registraciya_tovarnogo_znaka/`):** в `DOCROOT/registraciya_tovarnogo_znaka/` подставьте содержимое из `cms-legacy-registraciya.html` (или бэкап из ISPmanager), удалите `.lireate-managed.json` при наличии.
