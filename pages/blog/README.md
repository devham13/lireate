# Блог LIREATE (`/news`)

**Обязательная тема:** LIREATE Light V4 — белый фон, фирменные красный и синий акценты, без тёмных/серых подложек. См. `docs/lireate-design-system-v4.ru.md` и `.cursor/rules/lireate-light-v4.mdc`. Шаблон статьи и листинга подключает `lireate-blog-light-v4.css` после старого CSS; новые публикации наследуют оболочку, `full_story` в базе не переписывается.

Шаблон статей для автопубликации (lireate-news) и CMS на REG.RU.

- Исходники layout: `pages/blog/_shared/`
- Превью: `bash scripts/lireate_blog_preview_server.sh` → `/blog_preview/`
- Линт: `python3 scripts/lireate_blog_lint.py`
- Синк в `template/page/news/`: `python3 scripts/lireate_blog_sync_template.py`

Подробнее: `docs/lireate-blog-article.ru.md`.
