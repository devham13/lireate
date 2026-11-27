# SEO блога LIREATE — пакет отчётов (2026-11-27)

Для общего `SEO_REPORT.md` (агент `cursor/seo-master-*`).

| Файл | Назначение |
|------|------------|
| `seo-blog-audit.csv` | Все статьи active (+ inactive при экспорте): URL, meta до/после, интент, услуга, статус |
| `seo-legal-review.csv` | Фрагменты с нормами/суммами/сроками — **требуют верификации** по официальным источникам |
| `seo-cannibalization.csv` | Предложения по пересечению тем (без 301/мерджа на проде) |

Генерация: `python3 scripts/lireate_blog_seo_report_build.py` (см. комментарий в скрипте).

**Meta на проде:** editorial wave `blog-seo-wave2-editorial.json` (apply 2026-11-27).  
Тексты `full_story` — только в `seo-legal-review.csv`; правки текста — отдельный пакет по решению владельца.