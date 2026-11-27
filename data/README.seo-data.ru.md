# SEO data layer (`data/lireate-seo-*`)

Файлы подключаются из `index.php` / `template/main.php` на продакшене (`DOCROOT/data/`).

- **Не править статьи блога** (`news` в `lireate-seo-audit-meta.json`, MySQL) — ведёт репозиторий [lireate-news](https://github.com/devham13/lireate-news).
- Перед выкладкой: `python3 scripts/lireate_seo_data_hosting_install.py` (сравнение SHA с baseline и с сервером).
- Обход реестра: `python3 scripts/lireate_seo_sitemap_crawl.py`.

`shared/lireate-seo-audit-meta.json` — симлинк на `data/lireate-seo-audit-meta.json`.