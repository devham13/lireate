# SEO блога LIREATE — пакет отчётов (2026-11-27)

Сводка: **`SEO_REPORT.md`**. Site crawl: `seo-audit-*.csv`, `seo-errors.csv`, `before-after.md`.

| Файл | Назначение |
|------|------------|
| `SEO_REPORT.md` | Сводка P0–P1, crawl, Яндекс Webmaster |
| `seo-yandex-webmaster.csv` | ИКС, в поиске/исключено, **запросы: нет данных** |
| `seo-yandex-diagnostics-p0.csv` | 5 диагностик Вебмастера → действия P0 |
| `seo-audit-before.csv` / `seo-audit-after.csv` | Реестр краула sitemap |
| `seo-url-inventory.csv` / `seo-errors.csv` / `seo-remaining-issues.csv` | Инвентарь и дельты |
| `seo-blog-audit.csv` | Все статьи active (+ inactive при экспорте): URL, meta до/после, интент, услуга, статус |
| `seo-legal-review.csv` | Фрагменты с нормами/суммами/сроками — **требуют верификации** по официальным источникам |
| `seo-cannibalization.csv` | Предложения по пересечению тем (без 301/мерджа на проде) |

Генерация: `python3 scripts/lireate_blog_seo_report_build.py` (см. комментарий в скрипте).

**Meta на проде:** editorial wave `blog-seo-wave2-editorial.json` (apply 2026-11-27).  
Тексты `full_story` — только в `seo-legal-review.csv`; правки текста — отдельный пакет по решению владельца.