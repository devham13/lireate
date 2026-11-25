# LIREATE Light V4 — отчёт подготовки (без публикации)

Ветка: `cursor/lireate-light-v4-prep-b68f`. PR: **#118**. На REG.RU **не выкладывалось**.

## Скриншоты (`docs/light-v4-screenshots/`)

| Файл | Страница | Ширина |
| --- | --- | --- |
| `after-home-cms-desktop.png` | Главная (CMS-меню + подменю) | 1440 |
| `after-home-cms-mobile.png` | Главная, форма в карточке | **390** |
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