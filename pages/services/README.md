# Страницы услуг LIREATE (исходники для хостинга)

Cloud Agent **не** выкладывает файлы на `lireate.ru` напрямую. Он коммитит каталог сюда; на REG.RU — `scripts/lireate_hosting_publish_service.py`.

## Очередь (`queue.md`)

Приоритеты и запросы — в [`queue.md`](queue.md) (спрос из экспортов Яндекс.Метрики).

```bash
python3 scripts/lireate_service_queue.py
```

- Печатает `Sxx<TAB>slug` для первой записи со `status: new`, если каталога `pages/services/<slug>/` ещё нет.
- Иначе `NO_NEW_SERVICE` — автоматизация останавливается (без новой страницы и без PR).

**Один прогон automation:** одна услуга → PR → **merge в `main`** (после зелёного lint) → в очереди `status: done`. Хостинг в течение ~30 мин публикует slug (`scripts/lireate_hosting_services_cron.sh`); `published` фиксирует state на REG.RU. Не менять уже существующие каталоги услуг и `data/db.php`.

## Новая услуга

1. Взять `slug` из очереди (или из брифа) и скопировать `pages/services/_template/` → `pages/services/<slug>/`.
2. Заполнить `index.php`, `publish.manifest.json` (в шаблоне уже есть `.htaccess`; иначе публикатор подставит `_shared/service-directory.htaccess`).
3. Следовать `docs/lireate-service-design.ru.md` и `shared/lireate-service-seo-checklist.md`.
4. Выполнить:

```bash
python3 scripts/lireate_service_sync_shared.py --slug <slug>
python3 scripts/lireate_service_lint.py --slug <slug>
```

## Структура

```text
pages/services/
  _shared/              # канон CSS/JS + service-directory.htaccess для Apache
  _template/            # заготовка новой услуги
  <slug>/
    index.php
    publish.manifest.json
    .htaccess           # опционально (DirectoryIndex для CMS)
    assets/
      lireate-shared/   # копия из _shared (git + publish)
      …                 # локальные картинки услуги
```

## publish.manifest.json

```json
{
  "slug": "registraciya-tovarnogo-znaka-v-kitae",
  "url_path": "registraciya_tovarnogo_znaka_v_kitae",
  "title": "Регистрация товарного знака в Китае",
  "description": "…140–160 символов…",
  "menu_after": "Регистрация товарного знака"
}
```

Опционально: `menu_title`, `menu_after` (или то же поле в `queue.md`) — для `scripts/lireate_hosting_menu_add.py` после публикации.

Публичный URL: `https://<домен>/<url_path>/`.

## Дизайн (обязательный)

Тёмная дизайн-система услуг: `pages/services/_shared/lireate-service-dark.css`, `lireate-reveal.js`. Подробно — `docs/lireate-service-design.ru.md`. Эталон: **регистрация ТЗ в Китае**.

Публикатор автоматически добавляет файлы из `_shared` в `assets/lireate-shared/` на сервере (даже если забыли sync в git — лучше синхронизировать в репозитории).

## Маркер на проде

`.lireate-managed.json` в каталоге услуги — список файлов автопубликатора.
