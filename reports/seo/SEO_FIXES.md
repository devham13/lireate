# SEO_FIXES

Дата: 2026-11-27 12:40 UTC

## Выполнено в этом PR

| Компонент | Изменение | Проверка |
| --- | --- | --- |
| `template/llms.txt` | Индекс GEO/AEO для LLM-краулеров | файл в репозитории |
| `scripts/lireate_hosting_llms_txt_install.py` | Деплой `llms.txt` на DOCUMENT_ROOT | `python3 -m py_compile …` |
| `scripts/lireate_hosting_services_cron.sh` | Вызов установки `llms.txt` после perf-patch | diff cron |
| `tools/seo/lireate_comprehensive_audit.py` | Краул sitemap → CSV реестры | запуск с `--url-list` |
| `.github/workflows/seo-daily-health.yml` | Ежедневные пробы homepage/robots/sitemap/llms | workflow_dispatch |
| `tools/seo/build_keyword_map.py` | `KEYWORD_MAP.csv` из очереди услуг | `python3 tools/seo/build_keyword_map.py` |

## Уже на проде (не этот PR)

- PR #157 / `seo-perf-20261127-v2`, OG image map, blog fonts, home answer-intro.
- Блог: noindex лишних страниц пагинации (#84).

## Требует согласования

- Массовые изменения title/description CMS-страниц вне репозитория (`data/lireate-seo-page-meta.inc.php` на хостинге).
- Изменение `robots.txt` (Disallow юридических URL — осознанное решение).
- Создание региональных лендингов «Москва/СПб/Казань» отдельными URL.

## Деплой `llms.txt`

```bash
deployment: hosting_cron
script: scripts/lireate_hosting_llms_txt_install.py --docroot "$DOCROOT"
```

Live URL после CRON: `https://${PUBLIC_SITE_HOST}/llms.txt` (проверить `curl -I`).
