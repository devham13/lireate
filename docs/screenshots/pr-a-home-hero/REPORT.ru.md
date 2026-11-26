# PR A — hero главной (align + новое фото)

**Ветка:** `cursor/home-hero-align-top` (от `main` `1133b9b`)

## Изменения

| Файл | Суть |
|------|------|
| `template/css/lireate-home-light-v4.css` | `hero-grid` → `align-items:start`; портрет 683×1024, `object-fit:contain`, max-height в первый экран; **HOME_CSS r23** |
| `template/css/lireate-cms-pages-light-v4.css` | preview: `wrapper` с отступом под `#leftSideBar` ≥1151px |
| `pages/home/_shared/lireate-home-data.php` | портрет с диска DOCROOT (не live URL, если JPG есть) |
| `template/images/home/gabdrakhmanova-elvira-2026-11.jpg` | Оригинал 683×1024 (байт-в-байт с `design-proposals/incoming/…`) |
| `template/images/home/gabdrakhmanova-elvira-2026-11.webp` | Доп. WebP q92 для `<picture>` |
| `pages/home/_shared/lireate-home-data.php` | `lireate_home_hero_expert_portrait()` |
| `pages/home/_shared/lireate-home-page.inc.php` | `<picture>`, alt «Габдрахманова Эльвира — Лирейт», `fetchpriority="high"` |
| `template/page/home/*` | Зеркало shared |
| `scripts/lireate_light_v4_asset_version.py` | `HOME_CSS_VERSION=20261126r22` |
| `scripts/lireate_home_light_v4_install_lib.py` | preload LCP + копирование JPG/WebP на хостинг |
| `scripts/test_lireate_home_hero_photo.py` | Регрессия ассетов/разметки/CSS |
| `.github/workflows/php56-compat.yml` | Подключён тест hero photo |

Старый `template/images/team/person/1.jpg` **не удалялся** (карусель команды).

## Скриншоты (preview `pages/home/_preview/`, PHP built-in server)

- `before-hero-1440.png`, `before-hero-390.png`
- `after-hero-1440.png`, `after-hero-390.png`
- `before-full-1440.png`, `before-full-390.png`, `after-full-1440.png`, `after-full-390.png`

## Выкладка на REG.RU

1. `python3 scripts/lireate_home_light_v4_install.py --apply` (или обновить только файлы из `copy_plan` install lib: home CSS/JS, `pages/home/_shared/*`, `template/page/home/*`, hero JPG/WebP).
2. Убедиться, что в `template/main.php` есть блок `lireate-home-light-v4-install-head:v1` с **preload** и `lireate-home-light-v4.css?v=20261126r22`.
3. CRON/ручной FTP по списку install lib; сброс CDN/кэш браузера не требуется сверх `?v=`.

## Откат

1. Вернуть предыдущий `HOME_CSS_VERSION` (r21/r20) в `lireate_light_v4_asset_version.py` и в `template/main.php` head-snippet.
2. Откатить `lireate-home-page.inc.php` (img на `team/person/1.jpg`) и CSS `align-items:center` / `aspect-ratio:1.03/1`.
3. Удалить с хостинга только **новые** файлы `template/images/home/gabdrakhmanova-elvira-2026-11.*` (опционально; на работу карусели не влияют).
4. Backup из `lireate_home_light_v4_install.py --rollback <id>` при полном apply.

**Не мержить без ручной проверки Юры на live-стенде.**