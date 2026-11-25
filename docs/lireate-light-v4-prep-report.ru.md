# LIREATE Light V4 — отчёт подготовки (без публикации)

Ветка: `cursor/lireate-light-v4-prep-b68f`. PR: **#118**. На REG.RU **не выкладывалось**.

## Скриншоты (`docs/light-v4-screenshots/`)

| Файл | Страница | Ширина |
| --- | --- | --- |
| `after-home-cms-desktop.png` | Главная CMS + меню | 1440 |
| `after-home-cms-mobile.png` | Главная | 390 |
| `after-tm-desktop.png`, `after-tm-mobile.png` | Регистрация ТЗ | 1440 / 390 |
| `after-service-desktop.png` | Продление ТЗ | 1440 |
| `after-patent-desktop.png`, `after-patent-mobile.png` | Патентная задача (пример) | 1440 / 390 |
| `after-blog-list-desktop.png`, `after-blog-list-mobile.png` | Блог | 1440 / 390 |
| `after-about-desktop.png`, `after-about-mobile.png` | О компании | 1440 / 390 |
| `after-team-desktop.png`, `after-team-mobile.png` | Команда | 1440 / 390 |
| `after-portfolio-desktop.png`, `after-portfolio-mobile.png` | Портфолио (live HTML + Light V4) | 1440 / 390 |
| `after-reviews-desktop.png` | Отзывы (live HTML + Light V4) | 1440 |

Превью: `LIREATE_PUBLIC_SITE_URL=https://<prod-host> php -S 127.0.0.1:19080 -t . scripts/light_v4_screenshot_router.php`

## Изменения (финальный круг)

- **Главная:** тексты только с live; вторая галочка согласия (`consent_marketing_data` / `consent_ads`); CMS-меню в превью; форма — `text-align:left`, `box-sizing` на 390.
- **Footer:** светлый оверрайд на CMS-превью (главная, блог, о компании, команда).
- **Блог:** обложки из live (`live-news-covers.json`), контраст Max-баннера.
- **Portfolio/reviews:** фрагменты live HTML в превью; на хостинге шаблоны с БД без изменения логики.
- **PHP 5.6:** `lireate-home-data.php`, `catch (Error)` убран из home cms inc.

## CI

См. checks на [PR #118](https://github.com/devham13/lireate/pull/118) после push.

## Откат (все три установщика)

Перед любым `--apply` делайте бэкап. Каждый установщик кладёт копии затронутых файлов **до** перезаписи.

### 1. `scripts/lireate_home_light_v4_install.py`

```bash
python3 scripts/lireate_home_light_v4_install.py --docroot "$DOCROOT"          # dry-run
python3 scripts/lireate_home_light_v4_install.py --docroot "$DOCROOT" --apply  # бэкап + install
```

- **Бэкап:** `$DOCROOT/.lireate-backup-home/<UTCSTAMP>/` — `manifest.txt`, `created.json`, копии `pages/home/_shared/*`, `template/page/home/*`, патч `template/main.php`.
- **Откат:** восстановить файлы из каталога бэкапа поверх docroot; откатить `template/main.php` из того же бэкапа.

### 2. `scripts/lireate_cms_light_v4_overlay_install.py`

```bash
python3 scripts/lireate_cms_light_v4_overlay_install.py --docroot "$DOCROOT"
python3 scripts/lireate_cms_light_v4_overlay_install.py --docroot "$DOCROOT" --apply
```

- **Файлы:** `template/css/lireate-light-v4.css`, `template/js/lireate-light-v4.js`.
- **Откат:** вернуть предыдущие версии из **своего** бэкапа docroot (скрипт overlay сам бэкап не пишет — сделайте копию пары файлов до `--apply`).

### 3. `scripts/lireate_hosting_pf_install.py`

```bash
python3 scripts/lireate_hosting_pf_install.py --docroot "$DOCROOT"
python3 scripts/lireate_hosting_pf_install.py --docroot "$DOCROOT" --apply
```

- **Бэкап:** `$DOCROOT/.lireate-backup-pf/<UTCSTAMP>/` — перед перезаписью каждого пути.
- **Пути:** `portfolio.php`, `reviews.php`, `portfolio/`, `template/page/uslugi/`.
- **Откат:** скопировать из `.lireate-backup-pf/<UTCSTAMP>/` обратно в docroot.

## Cron lireate-news / guard-diff

Guard-diff для защиты overlay уже есть в репозитории **lireate-news**; **применяем его в lireate-news сами до выкладки** office-ветки (чтобы cron не перетёр CMS/главную после merge).

## План выкладки

1. Применить guard в **lireate-news**.  
2. Бэкап docroot (+ БД portfolio/reviews).  
3. `git pull` после merge.  
4. `lireate_home_light_v4_install.py --apply`  
5. `lireate_cms_light_v4_overlay_install.py --apply`  
6. `lireate_hosting_pf_install.py --apply`  
7. Smoke: формы (2 галочки), footer, `/news/`, меню CMS, mobile 390.

**Не трогали:** `pages/services/registraciya-tovarnogo-znaka-s-oplatoy-za-rezultat/`.