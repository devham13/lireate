# LIREATE Light V4 — отчёт подготовки (без публикации)

Ветка: `cursor/lireate-light-v4-prep-b68f`. PR: **#118**. На REG.RU **не выкладывалось**.

## Скриншоты (`docs/light-v4-screenshots/`)

| Файл | Страница | Ширина |
| --- | --- | --- |
| `after-home-cms-desktop.png` | Главная (fullpage) | 1440 |
| `after-home-cms-mobile.png` | Главная (fullpage) | **390** |
| `after-home-cms-viewport-desktop.png` | Главная **первый экран** (CMS hamburger + подменю) | 1440 |
| `after-home-cms-viewport-mobile.png` | Главная **первый экран** | **390** |
| `after-tm-desktop.png`, `after-tm-mobile.png` | Регистрация ТЗ | 1440 / **390** |
| `after-service-desktop.png`, `after-service-mobile.png` | Продление ТЗ | 1440 / **390** |
| `after-patent-desktop.png`, `after-patent-mobile.png` | Патентная задача | 1440 / **390** |
| `after-blog-list-desktop.png`, `after-blog-list-mobile.png` | Блог (обложки live) | 1440 / **390** |
| `after-about-desktop.png`, `after-about-mobile.png` | О компании (светлая) | 1440 / **390** |
| `after-team-desktop.png`, `after-team-mobile.png` | Команда | 1440 / **390** |
| `after-portfolio-desktop.png`, `after-portfolio-mobile.png` | Портфолио Light V4 | 1440 / **390** |
| `after-reviews-desktop.png` | Отзывы Light V4 | 1440 |

Превью: `LIREATE_PUBLIC_SITE_URL=https://<prod-host> php -S 127.0.0.1:19080 -t . scripts/light_v4_screenshot_router.php`  
Захват: `LIREATE_PREVIEW_BASE=http://127.0.0.1:19080 node scripts/capture_light_v4_screenshots.mjs`

Размеры PNG (последний прогон `capture_light_v4_screenshots.mjs` + блог): см. вывод `identify` в коммите (раздел ниже).

## Блокер consent (главная)

- Разметка согласий: `lireate_home_consent_fields_inc_path()` + fallback `lireate_home_render_consent_fields()` — **без fatal**, если файла нет.
- Установщик главной копирует: `pages/services/_shared/lireate-lead-consent-fields.inc.php`, `lireate-lead-consent-marketing.js`, дубль JS: `template/js/lireate-home-consent-marketing.js`.
- Hidden `consent_form`, первая галочка `name="consent_request"`; marketing — `consent_marketing_data` / `consent_ads`.

## Откат

### 1. `scripts/lireate_home_light_v4_install.py`

```bash
python3 scripts/lireate_home_light_v4_install.py --docroot "$DOCROOT" --apply
```

Бэкап: `$DOCROOT/.lireate-backup-home/<UTCSTAMP>/` (`manifest.txt`, копии затронутых файлов).

### 2. `scripts/lireate_cms_light_v4_overlay_install.py`

```bash
python3 scripts/lireate_cms_light_v4_overlay_install.py --docroot "$DOCROOT"          # dry-run
python3 scripts/lireate_cms_light_v4_overlay_install.py --docroot "$DOCROOT" --apply  # бэкап
python3 scripts/lireate_cms_light_v4_overlay_install.py --docroot "$DOCROOT" --rollback --backup-id <UTCSTAMP>
```

Бэкап: `$DOCROOT/.lireate-backup-cms-overlay/<UTCSTAMP>/manifest.txt`

### 3. `scripts/lireate_hosting_pf_install.py`

```bash
python3 scripts/lireate_hosting_pf_install.py --docroot "$DOCROOT" --apply
```

Бэкап: `$DOCROOT/.lireate-backup-pf/<UTCSTAMP>/`

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