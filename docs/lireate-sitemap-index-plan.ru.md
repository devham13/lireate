# Sitemap index (план P1, без поломки news cron)

Cron и wave2 используют `lireate_fast_indexing.build_sitemap_xml` + `lireate_db_publish.php sitemap_urls` (**lireate-news**).

## Цель

- `sitemap.xml` — **sitemap index** с `lastmod` на дочерних картах.
- `sitemap-static.xml` — пути из `data/lireate-sitemap-static.json` **без** lastmod (кроме `/news` → `articles_max`).
- `sitemap-articles.xml` — только статьи, `lastmod` из `datetime` в БД.

## Инварианты

- Тот же набор URL, что сейчас (exclude/dedupe из JSON).
- `write_sitemap_atomic` для атомарной записи.
- Не трогать корневой `index.php`; генератор остаётся в cron/news-repo.

## Следующий шаг

- Библиотека: `scripts/lireate_sitemap_index_lib.py` (office-repo).
- Включение: `data/lireate-sitemap-static.json` → `"sitemap_index": {"enabled": false}` (пока false).
- Подключение cron: **lireate-news** `lireate_fast_indexing.py` (отдельный PR); deploy только после smoke: дочерние карты 200, `url_count` ≥ baseline, cron `:00/:30` без ошибок.