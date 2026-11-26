# Light V4 — скриншоты до/после (ветка PR #118)

Пути относительно корня репозитория: `docs/light-v4-screenshots/*.png`.

| Файл | Описание |
| --- | --- |
| `before-home-{desktop,mobile}.png` | Продакшен главная |
| `after-home-cms-{desktop,mobile}.png` | CMS shell + `template/page/home/*` (локальный PHP) |
| `before-tm-*.png` / `after-tm-*.png` | `/registraciya_tovarnogo_znaka/` |
| `after-service-desktop.png` | Услуга `prodlenie-tovarnogo-znaka` |
| `after-patent-*.png` | `patentnye-zadachi/poshliny-registraciya` |
| `before-news-desktop.png` | Продакшен `/news/` |
| `after-blog-list-*.png` | Превью `pages/blog/_preview/list.php` |
| `after-blog-article-*.png` | Превью статьи |
| `before-team-*.png` / `after-team-*.png` | `/team/` |
| `before-contacts-*.png` / `after-contacts-*.png` | `/contacts/` |

Генерация: `scripts/capture_light_v4_screenshots.mjs` + PHP `scripts/light_v4_screenshot_router.php` на `:19080`.