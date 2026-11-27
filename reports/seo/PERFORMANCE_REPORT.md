# PERFORMANCE_REPORT

## Лабораторный замер (Unlighthouse smoke, mobile, 27.11.2026)

| URL | Performance | SEO | LCP | CLS |
| --- | ---: | ---: | --- | --- |
| `/` | 55 | 92 | 14.2 s | 0 |

Это **лабораторные** показатели (эмуляция mobile), не CrUX. Цель LCP ≤ 2.5 s не достигнута на главной.

## До / после

| Метрика | До (v1, fixture) | После (live marker v2) |
| --- | --- | --- |
| Perf patch | `seo-perf-20261120-v1` | `seo-perf-20261127-v2` на проде |
| Blog fonts | render-blocking | non-blocking (PR #157) |
| OG image | `og-default.jpg` mismatch | remap на `template/images/OG_image` |

## Рекомендации P1

- Дальнейшая оптимизация LCP-героя главной (preload WebP уже в perf-patch).
- Прогон `.github/workflows/seo-live-audit.yml` scope=priorities после деплоя.
