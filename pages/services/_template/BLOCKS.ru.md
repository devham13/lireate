# Карта блоков страницы услуги LIREATE

Копируйте нужные секции из эталонов (`zashchita-avtorskih-prav`, `prodlenie-tovarnogo-znaka`, `registraciya-tovarnogo-znaka-v-kitae`). Общие файлы — `pages/services/_shared/`.

## Обязательно на каждой опубликованной услуге

| Блок | Разметка / include | Скрипты |
|------|-------------------|---------|
| Подключение CSS | `assets/lireate-shared/lireate-service-dark.css` | — |
| JSON-LD | `Service`, `Organization`, `BreadcrumbList`, `FAQPage` в `<head>` | — |
| Nav pill | `.lx-nav-shell` + якоря | — |
| Breadcrumbs | `blocks/lireate-breadcrumbs.inc.php` + `$lx_breadcrumb_current` | — |
| Hero + маскот | `.lx-hero-mascot-slot` `data-lx-mascot="…"` | `lireate-motion-init.js`, `lireate-mascot.js`, `lireate-hero-panel.js`, `lireate-service-extras.js` |
| Route card | `.lx-glass.lx-glass-anim` + `.lx-route` | `lireate-hero-panel.js` |
| Форма + согласие | `#lireate-lead-form`, `/private_data`, `/politika_konfidencialnosti` | `lireate-contact.js` |
| Футер | `lireate-service-footer.inc.php` | — |
| Sticky mobile | `blocks/lireate-sticky-mobile.inc.php` | `lireate-mobile-bar.js` |
| Reduced motion | — | `lireate-motion-init.js` |
| Reveal | `.lx-reveal` | `lireate-reveal.js` |

## Опциональные блоки (по смыслу услуги)

| Блок | Атрибут / id | Скрипт |
|------|----------------|--------|
| Карусель «когда нужна» | `data-lx-carousel` | `lireate-carousel.js` |
| Шаги + маскот | `#program` + `data-lx-steps-walk` | `lireate-steps-walk.js` |
| Калькулятор пошлин | `data-lx-calc="prodlenie-tz"` или `po-reg` | `lireate-calculator.js` |
| Таймлайн | `data-lx-timeline` | `lireate-timeline.js` |
| До/после | `data-lx-compare` | `lireate-compare.js` |
| Квиз | `data-lx-quiz` | `lireate-quiz.js` |
| Отзывы | `blocks/lireate-reviews.inc.php` + `$lx_review_filter_services` | `lireate-reviews.js` |
| Related cards | `.lx-related-grid` | — |
| Trust | `blocks/lireate-trust.inc.php` + `$lx_trust_verified` | — |

**Демо-отзыв** — только в `_template`: `$lx_reviews_demo_only = true` (бейдж «Пример оформления»).

## Распределение по текущим услугам

| Блок | Защита АП | Продление ТЗ | Китай |
|------|-----------|--------------|-------|
| Маскот | `copyright` | `renewal` | `china` |
| Карусель | да | да | да |
| Steps-walk | 6 шагов | 4 шага | 6 шагов |
| Калькулятор | `po-reg` | `prodlenie-tz` | нет (нет верифицированных пошлин CNIPA) |
| Таймлайн | да | нет | да |
| Compare | да | нет | да |
| Quiz | да | нет | да |
| Отзывы | нет (нет данных по slug) | да | да |
| Related grid | да | да | да |

После правки `_shared`: `python3 scripts/lireate_service_sync_shared.py` и `python3 scripts/lireate_service_lint.py --slug …`.
