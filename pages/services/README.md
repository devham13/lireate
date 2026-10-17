# Страницы услуг LIREATE (исходники для хостинга)

Cloud Agent **не** выкладывает файлы на `lireate.ru` напрямую (RCPC блокирует облако). Он коммитит готовый каталог сюда; на REG.RU скрипт `scripts/lireate_hosting_publish_service.py` делает `git pull` и копирует в DOCROOT.

## Структура одной услуги

```text
pages/services/<slug>/
  index.php          # самодостаточная страница (полный HTML или PHP-обёртка без CMS-темы)
  publish.manifest.json
  assets/            # опционально: css, js, изображения
```

## publish.manifest.json

```json
{
  "slug": "registraciya-tovarnogo-znaka",
  "url_path": "uslugi/registraciya-tovarnogo-znaka",
  "title": "Регистрация товарного знака",
  "description": "Краткое meta description для журнала"
}
```

- `url_path` — путь от корня сайта **без** ведущего `/`. По умолчанию: `uslugi/<slug>`.
- Публичный URL: `https://${PUBLIC_SITE_HOST}/<url_path>/` (каталог с `index.php`).

Проверьте, что путь не конфликтует с маршрутизацией CMS. Для статических каталогов Apache обычно отдаёт `index.php` напрямую, если нет перехвата в `.htaccess`.

## Дизайн

Вёрстка и ассеты — **из этого репозитория** (`shared/longread-page-design-system.md`, `agents/natasha`, `agents/alina`, `agents/boris`, `assets/`). Не копировать визуальный стиль текущего шаблона lireate.ru. Страница должна работать без CSS/JS родительской CMS (inline или `assets/` рядом).

## Маркер на проде

После публикации в каталоге появляется служебный `.lireate-managed.json` (список файлов, которые трогал автопубликатор). Скрипт **не перезаписывает** чужие файлы вне этого списка.
