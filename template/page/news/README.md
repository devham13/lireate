# Шаблон статей блога LIREATE

Файлы копируются в `DOCROOT/template/page/news/` (см. `scripts/lireate_blog_sync_template.py`).

## Интеграция CMS

1. Подключите CSS/JS (уже в `lireate-blog-layout.inc.php`).
2. Замените вывод одной новости на `lireate-blog-cms.inc.php` (см. комментарий в файле).
3. Список `/news/`: `section#blog` → `lireate-blog-list-cms.inc.php` (см. `scripts/lireate_blog_install.py --check`).
4. Положите `data/blog-articles-index.json` в `DOCROOT/data/` для блока «Читайте также» и fallback списка.

## Новые статьи (lireate-news)

Тело в `article.html` — фрагмент с `<h2>`; при публикации в `full_story` попадает как сейчас. Обёртка страницы — из CMS include.

Пересборка HTML уже опубликованных статей в БД **не** выполняется автоматически: только при ручном `--update` в lireate-news или правке в админке. Смена include в CMS меняет **оболочку** для всех статей без изменения `full_story`.

## Конфиг MAX

`pages/blog/_shared/lireate-blog-config.php` — константа `LIREATE_BLOG_MAX_CHANNEL_URL`.
