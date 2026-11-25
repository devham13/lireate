# LIREATE Light V4 — отчёт подготовки (без публикации)

Ветка: `cursor/lireate-light-v4-prep-b68f`. PR: **#118**. На REG.RU **не выкладывалось**.

## Безопасность (коммит `6a94224`)

- Из репозитория убраны захардкоженные креды MySQL в portfolio/reviews CMS; подключение только через `template/page/lireate-cms-db.inc.php` и **`data/db.php` на хостинге** (в Git не коммитится).
- Удалены `packages/lireate-hosting-pf-20261125/portfolio.php` и `reviews.php` с дублем секретов.
- **Смену пароля БД на хостинге** выполняет владелец (история Git остаётся).

## Скриншоты (`docs/light-v4-screenshots/`)

| Файл | Страница | Ширина |
| --- | --- | --- |
| `after-home-cms-desktop.png` | Главная (fullpage) | 1440 |
| `after-home-cms-mobile.png` | Главная (fullpage) | **390** |
| `after-home-cms-viewport-desktop.png` | Главная **первый экран** (CMS hamburger + подменю) | 1440 |
| `after-home-cms-viewport-mobile.png` | Главная **первый экран** | **390** |
| `after-tm-desktop.png`, `after-tm-mobile.png`, `after-tm-viewport-mobile.png` | Регистрация ТЗ (sticky bar — viewport) | 1440 / **390** |
| `after-service-desktop.png`, `after-service-mobile.png`, `after-service-viewport-mobile.png` | Продление ТЗ (viewport 390) | 1440 / **390** |
| `after-patent-desktop.png`, `after-patent-mobile.png` | Патентная задача | 1440 / **390** |
| `after-blog-list-desktop.png`, `after-blog-list-mobile.png` | Блог (обложки live) | 1440 / **390** |
| `after-about-desktop.png`, `after-about-mobile.png` | О компании (светлая) | 1440 / **390** |
| `after-team-desktop.png`, `after-team-mobile.png` | Команда | 1440 / **390** |
| `after-portfolio-desktop.png`, `after-portfolio-mobile.png` | Портфолио Light V4 | 1440 / **390** |
| `after-reviews-desktop.png` | Отзывы Light V4 | 1440 |

**Круг 13 — только реальный CMS-рендер для after-* (не `_preview`, не cms-shell):**

1. `python3 scripts/lireate_cms_fixture_docroot.py` — DOCROOT с `index.php` + `template/main.php`, MySQL `lireate_ci` (`scripts/fixtures/cms-docroot/schema.sql`, нейтральные строки без маркеров владельца).
2. Установщики: `lireate_home_light_v4_install.py --apply`, overlay, PF, те же файлы что `blog --apply` (без PHP 5.6 gate — gate в CI).
3. Сервер: `LIREATE_FIXTURE_DOCROOT=… php -S 127.0.0.1:19080 -t $DOCROOT scripts/light_v4_main_render_router.php`
4. Захват: `bash scripts/run_light_v4_fixture_screenshots.sh` (before-* — живой `LIREATE_PUBLIC_SITE_URL`; after CMS — fixture; after tm/patent/service — `pages/services/…/index.php` через тот же router).

| Скриншот after-* | Рендер | Данные |
| --- | --- | --- |
| `after-home-cms-*` | fixture `index.php` + патченный `main.php` | fixture MySQL + home V4 |
| `after-portfolio-*`, `after-reviews-*`, `after-team-*`, `after-blog-*` | fixture `main.php` → `template/page/*.php` | fixture MySQL (нейтральные «Клиент А…») |
| `after-tm-*`, `after-service-*`, `after-patent-*` | репозиторий `pages/services/…/index.php` | статика услуги (без БД) |

Скриншоты на **fixture-данных** в отчёте владельцу как эталон live **не показываем** (другой объём портфолио/отзывов); в репозитории — для регрессии CI/агента.

## Круг 14 — `tests/fixtures/live-render` (коммит `2eb3c13`+)

**Источник правды для after (7 CMS-страниц):** замороженный HTML `tests/fixtures/live-render/after/*.html` (живой `index.php` + `main.php`) + CSS ветки через `asset-map.json`. Снимки и метрики:

```bash
LIREATE_FIXTURE_OFFLINE=1 scripts/run_live_render_fixture_screenshots.sh --sides after --fail-on-contrast --out docs/light-v4-screenshots
```

Проверка **14 шотов** (home, article, reviews, team, portfolio, poshliny, prodlenie × 1440/390): `low_contrast_count=0` на всех; итог — `docs/light-v4-screenshots/metrics.json` (копия: `docs/live-render-after-metrics.json`).

| Файл | Страница |
| --- | --- |
| `after-home-{1440,390}.png` (+ `*-full.png`) | Главная Light V4 (`#lireate-home` скрыт в CSS/JS) |
| `after-article-*` | Статья блога (класс `lireate-legacy-article-lu`) |
| `after-reviews-*` | Отзывы |
| `after-team-*` | Команда |
| `after-portfolio-*` | Портфолио |
| `after-poshliny-*` | Пошлины (patent tools) |
| `after-prodlenie-*` | Продление ТЗ |

Прочие after в `docs/light-v4-screenshots/` (tm, service, blog-list…) — прежний захват `run_light_v4_fixture_screenshots.sh`, не live-render.

**Код:** патч `main.php` (без `lireate-cms-page-dark` / `lireate-home-dark` на home), `$n` в `lireate-portfolio-cms.inc.php`, убран `LIREATE_BLOG_SKIP_PREAPPLY_GATE`, CI fixture — шаблон статьи для gate blog. См. `tests/fixtures/live-render/README.md`.

### `git show --stat f61597f -- docs/light-v4-screenshots`

```
Круг 12: after-blog-article (боевые body-классы), after-portfolio/reviews из template/page,
after-team-mobile, after-home viewport; портфолио ~116 KB desktop (fixture + шаблон).
```

Полный вывод: `git show --stat f61597f -- docs/light-v4-screenshots`

### Размеры after-* (ширина×высота, байты)

| Файл | Размер | Байты |
| --- | --- | ---: |
| after-home-cms-viewport-desktop.png | 1440×900 | 492873 |
| after-home-cms-viewport-mobile.png | 390×844 | 118293 |
| after-home-cms-desktop.png | 1440×7764 | 1703319 |
| after-home-cms-mobile.png | 390×12877 | 1211157 |
| after-tm-desktop.png | 1440×21186 | 3474476 |
| after-tm-mobile.png | 390×36235 | 3114160 |
| after-service-desktop.png | 1440×5363 | 858689 |
| after-service-mobile.png | 390×7967 | 584675 |
| after-patent-desktop.png | 1440×1004 | 79295 |
| after-patent-mobile.png | 390×1227 | 68644 |
| after-blog-list-desktop.png | 1440×4058 | 863737 |
| after-blog-list-mobile.png | 390×8736 | 875569 |
| after-about-desktop.png | 1440×1963 | 380926 |
| after-about-mobile.png | 390×3222 | 332331 |
| after-team-desktop.png | 1440×2253 | 462022 |
| after-team-mobile.png | 390×4243 | 441172 |
| after-portfolio-desktop.png | 1440×34273 | 3178439 |
| after-portfolio-mobile.png | 390×40943 | 2980109 |
| after-reviews-desktop.png | 1440×3496 | 2327380 |

## Блокер consent (главная)

- Разметка согласий: `lireate_home_consent_fields_inc_path()` + fallback `lireate_home_render_consent_fields()` — **без fatal**, если файла нет.
- Установщик главной копирует: `pages/services/_shared/lireate-lead-consent-fields.inc.php`, `lireate-lead-consent-marketing.js`, дубль JS: `template/js/lireate-home-consent-marketing.js`.
- Hidden `consent_form`, первая галочка `name="consent_request"`; marketing — `consent_marketing_data` / `consent_ads`.

## Откат

### 1. `scripts/lireate_home_light_v4_install.py`

```bash
python3 scripts/lireate_home_light_v4_install.py --docroot "$DOCROOT" --apply
```

Бэкап по умолчанию: `~/lireate-autopub/backups/home-light-v4-install/<UTCSTAMP>/` (`manifest.txt`, `created.json`).

Откат:

```bash
python3 scripts/lireate_home_light_v4_install.py --docroot "$DOCROOT" --rollback <YYYYMMDDTHHMMSSZ>
```

Пример id бэкапа главной: `20261125T165611Z`. `created.json` на откате удаляет новые файлы и **пустые** родительские каталоги (`pages/_shared`, `pages/home`, `pages/home/_shared`).

### 2. `scripts/lireate_cms_light_v4_overlay_install.py`

```bash
python3 scripts/lireate_cms_light_v4_overlay_install.py --docroot "$DOCROOT"          # dry-run
python3 scripts/lireate_cms_light_v4_overlay_install.py --docroot "$DOCROOT" --apply
python3 scripts/lireate_cms_light_v4_overlay_install.py --docroot "$DOCROOT" --rollback <YYYYMMDD-HHMMSS>
```

Бэкап: `~/lireate-autopub/backups/cms-overlay/cms-overlay-<UTCSTAMP>/` (`manifest.txt`, `created.json`).

### 3. `scripts/lireate_hosting_pf_install.py`

```bash
python3 scripts/lireate_hosting_pf_install.py --docroot "$DOCROOT" --apply
```

Бэкап: `~/lireate-autopub/backups/hosting-pf/pf-install-<UTCSTAMP>/`

## Доставка на хостинг (файл → установщик → путь)

| Файл (репозиторий) | Установщик | Путь на хостинге (DOCROOT) |
| --- | --- | --- |
| `template/css/lireate-portfolio-light-v4.css` | `lireate_hosting_pf_install.py`, `lireate_cms_light_v4_overlay_install.py` | `template/css/lireate-portfolio-light-v4.css` |
| `template/css/lireate-reviews-light-v4.css` | PF, overlay | `template/css/lireate-reviews-light-v4.css` |
| `template/css/lireate-cms-pages-light-v4.css` | PF, overlay | `template/css/lireate-cms-pages-light-v4.css` |
| `template/page/portfolio.php`, `reviews.php`, `lireate-*-cms.inc.php`, `lireate-cms-db.inc.php` | PF, overlay | `template/page/…` (не корень сайта) |
| `pages/_shared/lireate-cms-light-v4-head.inc.php` | PF, overlay | `pages/_shared/lireate-cms-light-v4-head.inc.php` |
| `template/css/lireate-light-v4.css`, `template/js/lireate-light-v4.js` | overlay | `template/css/…`, `template/js/…` |
| `template/css/lireate-home-light-v4.css`, `lireate-home-preview-chrome.css` | overlay | `template/css/…` |
| `template/page/team/*`, `o_kompanii/*`, `partners/*`, `docs/*`, `kontaktyi/*` | overlay | те же пути под `template/page/<раздел>/` |
| `pages/<раздел>/_shared/*` (team, about, …) | overlay | `template/page/<раздел>/` **и** `pages/<раздел>/_shared/` |
| `template/page/news/*` (блог) | `lireate_blog_install.py` | `template/page/news/` + патч шаблонов списка/статьи |

PF: `--rollback <YYYYMMDD-HHMMSS>` → `~/lireate-autopub/backups/hosting-pf/pf-install-<UTCSTAMP>/`. **`template/page/uslugi/` PF не трогает**; каталог `packages/.../uslugi/` удалён из пакета.

### Порядок выкладки (overlay → PF → blog → home → rollout)

1. `lireate_cms_light_v4_overlay_install.py`
2. `lireate_hosting_pf_install.py`
3. `lireate_blog_install.py --check|--apply` — `/news`, без `main.php`; откат: `--rollback <YYYYMMDD-HHMMSS>` (`blog-install-…` в `~/lireate-autopub/backups/`).
4. `lireate_home_light_v4_install.py` — `main.php`; откат: `--rollback <YYYYMMDD-HHMMSS>`.
5. `lireate_light_v4_hosting_rollout.py` — опубликованные услуги (нужны `LIREATE_DOCROOT`, `LIREATE_PUBLIC_SITE_URL` для HTTP verify blog).

**Десктоп-меню главной в превью:** как на live — верхнее меню шаблона + боковая колонка (`lireate-cms-chrome.inc.php`). PR #117 задаёт тексты/CTA в блоке Light V4, отдельного решения «убрать сайдбар» в #117 нет.

### Круг 12 (PHP 5.6 gate блога)

- Убран `static` в `lireate_blog_render_service_footer()` — batch render gate больше не теряет `lx-site-footer` на 2–5 статьях.
- Статьи: `lireate-theme-light-v4` на `body` (как список), контраст MAX в боковой колонке.

### Круг 13 (реальный main.php + CI fixture)

- **CI:** MySQL 5.7 service, `php:5.6` + `mysqli`, `scripts/lireate_blog_ci_fixture_apply.py` — полный `blog --apply` на fixture DOCROOT + CGI gate (`LIREATE_PHP_CGI_BIN` / `php` из setup-php 5.6).
- **Главная:** Light V4 **вне** `#lireate-home`; legacy hero скрывается точечно; при `--apply` флаг `lireate-home-v2.on` → `*.lireate-light-v4-disabled`, body без `lireate-home-v2`.
- **Статьи / PF:** расширенные overrides Light V4 для `lireate-legacy-article-lu` + `lireate-cms-page-dark`; портфолио/отзывы — селекторы `body.lireate-theme-light-v4`, flex сетки.

### Услуги (~20 slug) — хостинг

```bash
export LIREATE_DOCROOT="$DOCROOT"
export LIREATE_OFFICE_REPO="$OFFICE_REPO"
python3 scripts/lireate_light_v4_hosting_rollout.py --docroot "$DOCROOT"          # dry-run: publish --update без --apply по каждому slug
python3 scripts/lireate_light_v4_hosting_rollout.py --docroot "$DOCROOT" --apply   # + LIREATE_ALLOW_PUBLISH=yes + perf patch
```

**Исключение slug:** `registraciya-tovarnogo-znaka-s-oplatoy-za-rezultat`.

Заголовок «как защитить…» в превью блога: поднята первая буква только в `pages/blog/_preview/`; на live текст из CMS/MySQL не меняется.

## Cron lireate-news / guard-diff

Guard-diff **не применён** в репозитории lireate-news: в git есть только описание/diff в `docs/`. **Применяем guard в lireate-news сами до выкладки** office-ветки.

## CI

См. checks на [PR #118](https://github.com/devham13/lireate/pull/118) после push.

**Не трогали:** `pages/services/registraciya-tovarnogo-znaka-s-oplatoy-za-rezultat/`.

## Тексты главной вне live (на согласование владельца)

| Текст (фрагмент) | Источник |
| --- | --- |
| Бесплатная консультация ↗ | дизайн PR #117 |
| Специалист Лирейт · консультации, сопровождение заявок и защита интеллектуальной собственности | дизайн PR #117 |
| Команда Лирейт ↗ | дизайн PR #117 |
| О компании ↗ | дизайн PR #117 |
| Оставьте заявку — специалисты свяжутся с вами, уточнят задачу и подберут следующий шаг: проверка знака, консультация по  | дизайн PR #117 |
| Патентование решений | дизайн PR #117 |
| Договоры и передача прав | дизайн PR #117 |
| Защита программ и интерфейсов | дизайн PR #117 |
| Команда LIREATE | только ветка / не live |
| Экспертный блог | дизайн PR #117 |
| Перейти в блог ↗ | дизайн PR #117 |
| Оставьте заявку — специалисты свяжутся с вами и подскажут, что делать дальше. | только ветка / не live |
| Получить консультацию ↗ | дизайн PR #117 |
| Подробнее об услуге ↗ | дизайн PR #117 |
| Разбираемся в ситуации, уточняем объект защиты и предлагаем дальнейший план. | дизайн PR #117 |
| Подробнее об этапе | дизайн PR #117 |
| Согласуем условия, готовим материалы и проверяем их перед подачей. | только ветка / не live |
| Ведём дело, отслеживаем статус и помогаем пройти этапы рассмотрения. | дизайн PR #117 |
| Подробно об этапе | дизайн PR #117 |
| Начинаем с понимания вашей ситуации — чтобы выбрать подходящий способ защиты интеллектуальной собственности. | дизайн PR #117 |
| Что делает Лирейт | дизайн PR #117 |
| Выясняем, что именно нужно защитить: бренд, разработку, программу или права по договору. | дизайн PR #117 |
| Уточняем цели, сроки, текущие документы и вопросы клиента. | дизайн PR #117 |
| Объясняем возможные пути работы и следующий шаг. | дизайн PR #117 |
| Что вы получаете | дизайн PR #117 |
| Понятный план действий | дизайн PR #117 |
| Вы знаете, какие действия предстоят, какие данные понадобятся и как начать работу. | дизайн PR #117 |
| Обсудить задачу | дизайн PR #117 |
| Выберите следующий этап или вернитесь к предыдущему | дизайн PR #117 |
| Следующий шаг | дизайн PR #117 |
| Да, Лирейт занимается патентованием технических решений. | дизайн PR #117 |
| Подробнее о патентах ↗ | дизайн PR #117 |
| Лирейт оказывает услуги по правовой защите программного кода и интерфейсов. | только ветка / не live |
| Посмотреть услугу ↗ | дизайн PR #117 |
| Свяжитесь с командой по телефону | дизайн PR #117 |
| Практические вопросы по защите товарного знака. | только ветка / не live |
| Разбор вопросов, связанных с компенсацией. | только ветка / не live |
| Материал для тех, кто столкнулся с отказом. | только ветка / не live |
| Оставьте заявку — специалисты Лирейт свяжутся с вами и помогут понять, какой способ защиты подойдёт в вашей ситуации. | только ветка / не live |
| честная оценка перспектив и понятный следующий шаг | дизайн PR #117 |
| быстрый контакт через Telegram, Max и телефон | дизайн PR #117 |
| Отправить заявку | только ветка / не live |

_Разрешены только: (а) тексты prod-сайта, (б) design-proposals PR #117 home index._