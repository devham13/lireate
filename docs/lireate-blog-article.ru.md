# Статьи блога LIREATE (шаблон 2026)

Референс по компоновке: mayai.ru (только стиль, без контента).

## Исходники

| Путь | Назначение |
|------|------------|
| `pages/blog/_shared/` | CSS, JS, layout, config, lib |
| `template/page/news/` | Копия для DOCROOT + `lireate-blog-cms.inc.php` |
| `data/blog-articles-index.json` | «Читайте также» (реальные URL с lireate.ru) |
| `pages/blog/_preview/` | Локальное превью |

## Новые vs старые статьи

- **Оболочка** (двухколоночный layout, TOC, MAX, related, футер) подключается из CMS через `lireate-blog-cms.inc.php` — после выкладки шаблона на хостинг действует для всех статей.
- **`full_story` в MySQL** для уже опубликованных записей **не переписывается** репозиторием lireate. Повторная сборка HTML — только `lireate_hosting_publish.py --update` в **lireate-news** (явный флаг).
- Новые статьи: `article.html` как фрагмент; `h2` получают `id` на рендере (PHP) для якорей и scroll-spy.

## MAX

`LIREATE_BLOG_MAX_CHANNEL_URL` в `lireate-blog-config.php`.

## Хостинг

```bash
python3 scripts/lireate_blog_sync_template.py
# Скопировать на сервер в DOCROOT:
#   template/page/news/*
#   template/lireate/lireate-service-footer.inc.php
#   data/blog-articles-index.json
```

Подключить include в шаблоне CMS одной новости (см. `template/page/news/README.md`).
