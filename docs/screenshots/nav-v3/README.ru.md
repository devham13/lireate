# Nav V3 screenshots (PR #127)

- **Viewport-only** (`full_page=false`), `scrollY=0`, PNG width = viewport width.
- **AFTER**: `php -S … -t repo pages/_preview/nav-v3-router.php` (CSS с `?v=` отдаётся **200**).
- QA: `capture-qa.json` — `overflow_x`, `hasNavCss`, размеры.
- Пересъёмка: `python3 scripts/capture_nav_v3_screenshots.py`