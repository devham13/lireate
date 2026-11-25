# LIREATE Light V4 — финальный отчёт подготовки (без публикации)

Дата: **25.11.2026**. Ветка: `cursor/lireate-light-v4-prep-b68f`. PR: **#118**. На REG.RU **не выкладывалось**, main **не мержили**.

## Что сделано в этом круге

| Область | Изменение |
| --- | --- |
| **Главная** | Факты и тексты как на live (stats, bullets, lead, карточка «финансовая гарантия», URL `/registraciya_tovarnogo_znaka_s_oplatoy_za_rezultat/`). Слоган без `aria-hidden`. Поля формы `#lh4-lead-form` — стили Light V4. |
| **Footer** | Общий светлый оверрайд `.lx-site-footer` в `template/css/lireate-cms-pages-light-v4.css` + базовая вёрстка `lireate-service-dark.css` на превью CMS. |
| **Блог** | `LIREATE_BLOG_PUBLIC_ORIGIN` в preview-bootstrap; обложки с live при превью; контраст автор / «Читать» / время / Max-баннер. |
| **Portfolio / reviews / uslugi** | Пакет `packages/lireate-hosting-pf-20261125/` + зеркало в `template/page/`; install `scripts/lireate_hosting_pf_install.py` (dry-run, backup, `--apply`). Превью `/portfolio`, `/reviews` (БД на хостинге; локально — stub). |
| **Скрины** | Пересняты home 1440/390, blog, portfolio, reviews (см. ниже). |

**Не трогали:** `pages/services/registraciya-tovarnogo-znaka-s-oplatoy-za-rezultat/` (другой агент).

## Скриншоты (`docs/light-v4-screenshots/`)

| Файл | Назначение |
| --- | --- |
| `after-home-cms-desktop.png`, `after-home-cms-mobile.png` | Главная CMS Light V4 |
| `after-blog-list-desktop.png`, `after-blog-list-mobile.png` | Список блога |
| `after-portfolio-desktop.png`, `after-portfolio-mobile.png` | Портфолио (шаблон + light overlay) |
| `after-reviews-desktop.png` | Отзывы (шаблон + light overlay) |
| `after-about-desktop.png`, `after-about-mobile.png` | О компании |
| `before-home-*`, `before-tm-*`, … | Сверка с live (как раньше) |

Превью: `LIREATE_PUBLIC_SITE_URL=https://<prod-host> php -S 127.0.0.1:19080 -t . scripts/light_v4_screenshot_router.php`

## CI

После push на ветку — проверить [PR #118 checks](https://github.com/devham13/lireate/pull/118) (php56, performance patch). Локально: `python3 scripts/lireate_hosting_pf_install.py --docroot /tmp/docroot` (dry-run).

## План выкладки на хостинг (пошагово)

1. **Бэкап** DOCROOT и БД (portfolio/reviews завязаны на MySQL).
2. На сервере: `git pull` ветки после merge PR в `main` (или деплой артефакта владельца).
3. Убедиться, что **lireate-news** / unslop cron не перезапишут только что залитые CMS-файлы (см. guard в `docs/`).
4. `python3 scripts/lireate_home_light_v4_install.py --docroot … --apply`
5. `python3 scripts/lireate_cms_light_v4_overlay_install.py --docroot … --apply`
6. **`python3 scripts/lireate_hosting_pf_install.py --docroot … --apply`** — `portfolio.php`, `reviews.php`, `portfolio/`, `template/page/uslugi/`.
7. Опубликовать услуги/патенты по текущему cron (`lireate_hosting_services_cron.sh`), **кроме** slug s-oplatoy в `pages/services/`.
8. Smoke: главная форма → `/mail.php`, footer на CMS-страницах, `/news/`, `/portfolio/`, `/reviews/`, mobile 390, Метрика **один** init на главной.

---

Подробная история ранних прогонов — в комментариях PR #118 и предыдущих коммитах ветки.