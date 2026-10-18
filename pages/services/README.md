# Страницы услуг LIREATE (исходники для хостинга)

Cloud Agent **не** выкладывает файлы на `lireate.ru` напрямую. Он коммитит каталог сюда; на REG.RU — `scripts/lireate_hosting_publish_service.py`.

## Новая услуга

1. Скопировать `pages/services/_template/` → `pages/services/<slug>/`.
2. Заполнить `index.php`, `publish.manifest.json`, при необходимости `.htaccess` (см. эталон Китая).
3. Следовать `docs/lireate-service-design.ru.md` и `shared/lireate-service-seo-checklist.md`.
4. Выполнить:

```bash
python3 scripts/lireate_service_sync_shared.py --slug <slug>
python3 scripts/lireate_service_lint.py --slug <slug>
```

## Структура

```text
pages/services/
  _shared/              # канон CSS/JS (копируются в каждую услугу)
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
  "description": "…140–160 символов…"
}
```

Публичный URL: `https://<домен>/<url_path>/`.

## Дизайн (обязательный)

Тёмная дизайн-система услуг: `pages/services/_shared/lireate-service-dark.css`, `lireate-reveal.js`. Подробно — `docs/lireate-service-design.ru.md`. Эталон: **регистрация ТЗ в Китае**.

Публикатор автоматически добавляет файлы из `_shared` в `assets/lireate-shared/` на сервере (даже если забыли sync в git — лучше синхронизировать в репозитории).

## Маркер на проде

`.lireate-managed.json` в каталоге услуги — список файлов автопубликатора.
