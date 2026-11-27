# Комплект SEO-отчётов LIREATE (2026-11-27)

| Файл | Назначение |
| --- | --- |
| [SEO_AUDIT_REPORT.md](./SEO_AUDIT_REPORT.md) | Сводный технический аудит |
| [SEO_ERRORS.csv](./SEO_ERRORS.csv) | Реестр ошибок |
| [URL_INVENTORY.csv](./URL_INVENTORY.csv) | Выборка страниц с метаданными |
| [SEO_FIXES.md](./SEO_FIXES.md) | Выполненные и согласуемые исправления |
| [KEYWORD_MAP.csv](./KEYWORD_MAP.csv) | Кластеры услуг из `pages/services/queue.md` |
| [COMPETITOR_ANALYSIS.md](./COMPETITOR_ANALYSIS.md) | Обзор конкурентов (без частотности) |
| [CONTENT_PLAN_90_DAYS.csv](./CONTENT_PLAN_90_DAYS.csv) | Контент-план на 90 дней |
| [GEO_AEO_REPORT.md](./GEO_AEO_REPORT.md) | GEO/AEO и `llms.txt` |
| [PERFORMANCE_REPORT.md](./PERFORMANCE_REPORT.md) | Unlighthouse (лаборатория) |
| [SEO_ROADMAP.md](./SEO_ROADMAP.md) | Дорожная карта 30/60/90 |
| [SEO_MONITORING.md](./SEO_MONITORING.md) | Постоянный контроль |

Пересборка markdown после краула:

```bash
python3 tools/seo/generate_seo_deliverables.py
```