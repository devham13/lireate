# SEO_REPORT

Пакет **seo-20261127** (site crawl + blog CSVs в каталоге).

UTC: 2026-11-27T14:17:54.639684+00:00

## Crawl

- before: `/opt/cursor/artifacts/seo-registry.csv` (429 URLs)
- after: `/opt/cursor/artifacts/seo-registry-after-20261127.csv` (442 URLs, discover-extra; fatal_error=0)
- crawl lib: `TimeoutError` retried in `fetch_url` (full run completes)

## Яндекс Webmaster (API, владелец)

См. `seo-yandex-webmaster.csv`, `seo-yandex-diagnostics-p0.csv`.

| Метрика | Значение |
| --- | --- |
| Хост | `https://lireate.ru` (verified) |
| В поиске / исключено | 374 / 4 |
| ИКС (SQI) | 50 |
| Запросы 30д | **нет данных** |

**P0 диагностики (5):**

| ID | Действие | Статус |
| --- | --- | --- |
| DOCUMENTS_MISSING_DESCRIPTION | overrides + crawl `missing_description` (12 URL на after) | в работе / PR #172 |
| ERROR_IN_ROBOTS_TXT | `shared/robots.txt.yandex-recommended`; убрать `Disallow: /?…` и `/*?…` | **не на проде** |
| TOO_MANY_DOMAINS_ON_SEARCH | `docs/lireate-yandex-domains-analysis.ru.md` | ждёт владельца |
| DUPLICATE_CONTENT_ATTRS | уникальные title/desc (cms-overrides, kontaktyi/contacts) | P1 data-layer |
| DUPLICATE_PAGES | canonical + Clean-param utm/yclid/mode/nc; без массовых 301 | P0 robots + канон |

Запросы/позиции 30д: **нет данных**. Частота Wordstat: **нет данных** (API).

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

## Live vs repo (curl 17:18 MSK)

| Элемент | Прод | В #172 / не выложено |
| --- | --- | --- |
| Home title 51 chars | да | в main (#169) |
| МКТУ `/1/` `/25/` `/45/` BreadcrumbList | да | в main (#169) |
| Service WebPage `@id` + расшир. slugs | **частично** (findtz: Service+BC, без WebPage в HTML) | data-layer в #172, baseline обновлён |
| `robots.txt` Яндекс-clean | **нет** (ERROR) | `shared/robots.txt.yandex-recommended` |
| `llms.txt` 141096 B | да (Last-Mod 13:42 UTC) | combined builder в main |
| Sitemap index split | нет | план + JSON в #172; код в lireate-news |

## Deployed P0 (prod, #169)

- Home title ≤58 chars (51), description без изменений («с 2015 года»).
- МКТУ 1–45: WebPage + BreadcrumbList JSON-LD.

## Artifacts

- `seo-audit-before.csv`, `seo-audit-after.csv`
- `seo-url-inventory.csv`, `seo-errors.csv`, `seo-remaining-issues.csv`
- `seo-blog-audit.csv`, `seo-legal-review.csv`, `seo-cannibalization.csv` (blog)
- `seo-yandex-webmaster.csv`, `seo-yandex-diagnostics-p0.csv`
- `before-after.md`

## Blocked

- GSC / Wordstat / IndexNow — нет credentials в Cloud.
- lireate-news #187 — merge только по approve владельца.
