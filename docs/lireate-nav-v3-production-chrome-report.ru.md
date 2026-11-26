# Nav V3 — боевой chrome (не только _preview)

| | |
|--|--|
| PR | https://github.com/devham13/lireate/pull/129 |
| SHA | `b8fe21d` |
| Ветка | `cursor/nav-v3-production-chrome-b68f` |
| База | `main` (после merge PR #127 / `7f4c19b`) |

## Суть

1. **Регистрация ТЗ** — `pages/services/registraciya-tovarnogo-znaka/index.php` подключает `lireate-service-cms-chrome.inc.php` (общий `_shared`, пока только этот slug): sidebar + subnav Путь/Стоимость/FAQ/Заявка + «Оставить заявку», скрыт `lx-nav-shell`.
2. **CMS main.php** — `lireate_hosting_navigation_v3.py` добавляет классы `lireate-public-cms-chrome lireate-theme-light-v4`, копирует `pages/_shared/*.php`, CSS/JS `20261126r6`; collapse/соцсети работают и при `#collapseSide` без класса (JS).
3. **Главная mobile 390** — светлая шапка `#topBar`, убран зазор, «Разделы» по центру (`lireate-navigation-dual-v3.css`).
4. **/mktu** — mask-логотип для `lireate-mktu-lu-body`.

CSS попадает в cron-бандл через `<link>` в `template/main.php` (performance patch).

## Файлы на сервер (после apply)

| Путь в DOCROOT | Источник |
|----------------|----------|
| `template/css/lireate-navigation-dual-v3.css` | repo |
| `template/css/lireate-public-cms-chrome.css` | repo |
| `template/css/lireate-global-subnav-light-v4.css` | repo |
| `template/js/lireate-navigation-dual-v3.js` | repo |
| `template/main.php` | патч head + body classes |
| `pages/_shared/lireate-*.php` | 7 файлов chrome/subnav |
| `registraciya_tovarnogo_znaka/` | publish услуги (+ `_shared/lireate-service-cms-chrome.inc.php`) |

## Установка (REG.RU, Python 3.6+)

```bash
cd ~/www/<office-repo>
git fetch && git checkout <SHA>  # этот PR

# Nav V3 (dry-run)
python3 scripts/lireate_hosting_navigation_v3.py \
  --docroot "$HOME/www/public_html" \
  --backup-root "$HOME/lireate-autopub/backups/navigation"

# apply (PHP 5.6 lint внутри; на 3.6 без capture_output)
/opt/python/python-3.8.8/bin/python3 scripts/lireate_hosting_navigation_v3.py \
  --docroot "$HOME/www/public_html" \
  --backup-root "$HOME/lireate-autopub/backups/navigation" \
  --apply --php-bin php

# Услуга РТЗ (если ещё не выкладывали index с chrome)
LIREATE_ALLOW_PUBLISH=yes python3 scripts/lireate_hosting_publish_service.py \
  --slug registraciya-tovarnogo-znaka --apply
```

Cron ~16:00 склеит CSS в `lireate-bundle-*.css` — отдельный шаг не нужен.

## Скрины (production-like)

`python3 scripts/capture_nav_v3_production_screenshots.py` →  
`docs/screenshots/nav-v3/production/{home,service-rtz,mktu}/`

**Без merge и без выкладки с агента.**