# SEO_REPORT

Пакет **seo-20261127** (site crawl + blog CSVs + Яндекс Webmaster).

UTC: 2026-11-27T14:12:00+03:00 (данные Вебмастера от владельца); crawl CSV — 2026-11-27T14:15:50Z.

## Яндекс Webmaster (API, владелец)

| Метрика | Значение |
| --- | ---: |
| Хост | `https://lireate.ru` (verified) |
| В поиске | 374 |
| Исключено | 4 |
| ИКС (SQI) | 50 |
| Запросы 30д | **нет данных** (0 строк API) |

**Диагностика (PRESENT / POSSIBLE_PROBLEM)** — см. `seo-yandex-diagnostics-p0.csv`:

1. **DOCUMENTS_MISSING_DESCRIPTION** — P0: meta description через data-layer / overrides (crawl: `missing_description`).
2. **ERROR_IN_ROBOTS_TXT** — P0: убрать `Disallow: /?…` и `Disallow: /*?…`; добавить `Clean-param: mode&test&nc /` → `shared/robots.txt.yandex-recommended`; check: `lireate_seo_robots_yandex_check.py`.
3. **TOO_MANY_DOMAINS_ON_SEARCH** — P0: **только анализ** `lireate.online`, `sam.lireate.*` → `docs/lireate-yandex-domains-analysis.ru.md` (зеркала не менять без владельца).
4. **DUPLICATE_CONTENT_ATTRS** — P0: уникальные title/description (cms-overrides, услуги; пары contacts/kontaktyi).
5. **DUPLICATE_PAGES** — P0: canonical + Clean-param (utm, yclid, mode, nc); без массовых 301 до карты дублей.

**Recrawl 429 URL sitemap:** выполняет владелец (17:12+ МСК); Cloud **не** дублирует полный crawl.

## Crawl (репозиторий)

- before: `seo-audit-before.csv` (429 URLs, baseline)
- after: `seo-audit-after.csv` (частичный/обновляемый; полный recrawl — у владельца)

## Issue counts (problems column)

- **description_long**: before=203, after=0, Δ=-203
- **description_short**: before=3, after=0, Δ=-3
- **missing_canonical**: before=0, after=8, Δ=8
- **missing_description**: before=0, after=12, Δ=12
- **missing_h1**: before=0, after=6, Δ=6
- **missing_title**: before=0, after=6, Δ=6
- **no_breadcrumb_jsonld**: before=61, after=28, Δ=-33
- **no_jsonld**: before=52, after=19, Δ=-33
- **title_long**: before=9, after=1, Δ=-8
- **title_short**: before=4, after=1, Δ=-3

## Deployed P0 (prod)

- Home title ≤58 chars (51), description без изменений («с 2015 года» не трогали).
- МКТУ 1–45: WebPage + BreadcrumbList JSON-LD (после атомарной выкладки head+schema).
- Service slugs (расшир.): WebPage + BreadcrumbList `@id` + Service `@id` (P1 data-layer, merge pending deploy).

## Artifacts

- `seo-audit-before.csv`, `seo-audit-after.csv`
- `seo-url-inventory.csv`, `seo-errors.csv`, `seo-remaining-issues.csv`
- `seo-blog-audit.csv`, `seo-legal-review.csv`, `seo-cannibalization.csv` (blog)
- `seo-yandex-webmaster.csv`, `seo-yandex-diagnostics-p0.csv`
- `before-after.md`

## Blocked

- GSC / Wordstat / IndexNow — нет credentials в Cloud.
- lireate-news #187 — merge только по approve владельца.
