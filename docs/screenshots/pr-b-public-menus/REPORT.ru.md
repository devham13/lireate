# PR B — публичные меню и Light V4 (Yura)

Ветка: `cursor/light-v4-menus-shell-b68f` (база `main`). **Merge: не выполнять** — PR описание для родительского агента.

## Скриншоты (preview)

| Страница | 1440 | 390 |
|---|---|---|
| `/uslugi/` (hub) | `uslugi-after-1440.png` | `uslugi-after-390.png` |
| `/mktu` | `mktu-after-1440.png` | `mktu-after-390.png` |

Before: состояние до PR — тёмный hub /uslugi без `lh4-subnav` и без `lireate-theme-light-v4` (см. `template/page/uslugi/lireate-uslugi.css` v2026-11-11-dark3). Отдельные before-шоты не снимались: визуальный дельта — по after + legacy CSS в репозитории.

Источник after: `template/page/uslugi/_preview/index.php`, `pages/mktu/_preview/index.php`, Playwright + `php -S`.

## Deploy (REG.RU docroot)

1. `git pull` merge PR в `main` (владелец / cron).
2. `python3 scripts/lireate_cms_light_v4_overlay_install.py --docroot "$LIREATE_DOCROOT" --apply` — global subnav, partners/docs/contacts/inc, uslugi tree, mktu CSS.
3. `python3 scripts/lireate_mktu_light_v4_install.py --docroot "$LIREATE_DOCROOT" --apply` — патч `template/main.php` head для `/mktu` (маркер `lireate-mktu-light-v4-install-head:v1`).
4. `python3 scripts/lireate_menu_remove_oplata.py --docroot "$LIREATE_DOCROOT" --apply` — убрать «Оплата услуг» (Tochka) из sidebar и дублей; **data/db.php не трогается**.
5. Очистить CDN/браузерный кэш CSS (`20261126r21`).

## Rollback

1. Overlay: `python3 scripts/lireate_cms_light_v4_overlay_install.py --docroot ... --rollback <backup-id>` (каталог `~/lireate-autopub/backups/cms-overlay-…`).
2. MKTU: восстановить `template/main.php` из бэкапа `mktu-light-v4-…` в том же каталоге backups.
3. Menu oplata: восстановить затронутые PHP из бэкапа `menu-remove-oplata-…`.
4. При необходимости откатить коммит в `main` и повторить overlay с предыдущей ревизией ассетов.

## Блокеры

- Нет SSH/FTP с Cloud Agent на прод (ожидаемый CRON/ручной apply Юры).
- `lireate_mktu_light_v4_install` требует живого фрагмента `lireate_design_mktu_lu_head_v1` в `template/main.php` на хостинге.

## Инвентаризация

`docs/pr-b-public-menu-inventory.ru.md` — `python3 scripts/lireate_public_pages_menu_inventory.py`.