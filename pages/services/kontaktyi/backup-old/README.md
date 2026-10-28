# Резервная копия (rollback)

Каталог `backup-old/` хранит снимок **legacy CMS** до редизайна LIREATE.

- Файл `cms-legacy-kontaktyi.html` — **только справочник** (верстка/тексты старой страницы `/kontaktyi/` с левым меню CMS). На хостинг и в CRON он не подключается.

## Откат на legacy-страницу CMS

Если нужно временно вернуть старую HTML-страницу на проде:

1. На хостинге в `DOCROOT/kontaktyi/` замените `index.php` содержимым из `cms-legacy-kontaktyi.html` (или восстановите каталог из бэкапа ISPmanager).
2. Удалите `.lireate-managed.json` в этом каталоге, если он был создан публикатором.
3. Убедитесь, что корневой rewrite CMS снова отдаёт шаблон с левым меню (при необходимости удалите локальный `.htaccess` услуги).

Локально, для сравнения с текущим `index.php`:

```bash
diff -u backup-old/cms-legacy-kontaktyi.html index.php
```

Актуальная публикуемая страница — `../index.php` + `publish.manifest.json` (деплой через `scripts/lireate_hosting_publish_service.py` / CRON, **только после approve владельца**).

**Важно:** после публикации LIREATE-страницы левое меню CMS не рендерится (как у `/findtz/` и `/prodlenie_tovarnogo_znaka/`). Откат через этот бэкап возвращает CMS-верстку с боковым меню.
