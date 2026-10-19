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

`git pull` в office-repo **не** кладёт файлы в `public_html` сам. После merge в `main`:

```bash
export LIREATE_AUTOPUB_HOME=~/lireate-autopub
export LIREATE_ENV_FILE=~/lireate-autopub/.env
cd ~/lireate-autopub/office-repo && git fetch origin main && git checkout -B main FETCH_HEAD
python3 scripts/lireate_blog_sync_template.py
# Скопировать в $LIREATE_DOCROOT из .env:
#   template/page/news/*
#   template/lireate/lireate-service-footer.inc.php
#   template/lireate/lireate-messenger-urls.php
#   data/blog-articles-index.json
```

`lireate_hosting_publish.py` (lireate-news) пишет только в MySQL; шаблон `template/page/news` выкладывается отдельно.

На странице новости в CMS у `<body>` — класс `lireate-blog-body`.

Подключить include в шаблоне CMS одной новости (см. `template/page/news/README.md`).
