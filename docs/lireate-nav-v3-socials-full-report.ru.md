# Nav V3 — отчёт для Юры (визуальный QA)

| | |
|--|--|
| PR | https://github.com/devham13/lireate/pull/127 |
| SHA | `e2a7b08` |
| Ветка | `cursor/nav-v3-socials-full-b68f` |

## Исправлено

1. **Логотип:** mask + `contain`, collapse под wordmark; полный **LIREATE** на 1440 и в drawer.
2. **RTZ subnav (корень):** `wire_open` — subnav внутри `.lireate-public-cms-content` (раньше был прямым ребёнком `body` на всю 100vw → «пустая» ячейка и обрез CTA); fallback CSS для legacy-разметки.
3. **RTZ превью:** body-классы услуги, base href, hero chips (Telegram/WhatsApp/MAX) как на проде.
4. **Drawer:** `390-menu-open` (верх, Услуги) и `390-menu-open-bottom` (соцсети).

## Проверки

- `python3 scripts/test_lireate_nav_v3_full.py` — OK  
- `capture_nav-v3` AFTER — `overflow_x: false`  
- CI: patch + php56 — дождаться зелёного на push

## Скрины

`docs/screenshots/nav-v3/after/{home,service-rtz}/1440.png`, `home/390-menu-open*.png`

**Без merge и без выкладки.**