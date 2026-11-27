# SEO_MONITORING

## Ежедневно

| Проверка | Инструмент |
| --- | --- |
| HTTP 200 главной, robots, sitemap | `.github/workflows/seo-daily-health.yml` (cron 05:00 UTC) |
| Маркер `lireate-performance-patch` | тот же workflow |
| `llms.txt` 200 | тот же workflow (после деплоя) |

## Еженедельно

- Ручной `workflow_dispatch`: **LIREATE live SEO audit** (`priorities` или `sample`).
- VPS: `bash tools/seo/audit.sh smoke` при доступе.

## Ежемесячно

- `python3 tools/seo/lireate_comprehensive_audit.py --url-list tools/seo/.cache/sitemap-urls.txt` на VPS.
- SEOnaut diff (ссылки, редиректы, title).
- Обновление `CONTENT_PLAN_90_DAYS.csv` и очереди услуг.

Кэш краулинга: `tools/seo/.cache/crawl-latest.json` (не коммитится).
