# CMS Light V4 — overlay на информационные разделы

Подключение без переписывания контента: `template/css/lireate-light-v4.css` + `template/js/lireate-light-v4.js` и класс `lireate-theme-light-v4` на body.

## Уже в overlay (template/page)

- `/team/` — `lireate-team-page.inc.php`
- `/contacts/` — `lireate-contacts-page.inc.php`
- `/o_kompanii/` — `lireate-about-page.inc.php`
- `/docs/` — `lireate-docs-page.inc.php`
- `/partners/` — `lireate-partners-page.inc.php`
- Блог list/article — `lireate-blog-list.inc.php`, `lireate-blog-layout.inc.php`

## Публикация ассетов

```bash
python3 scripts/lireate_cms_light_v4_overlay_install.py --docroot ~/www/public_html --apply
```

Затем install соответствующих страниц (`lireate_team_install.py`, `lireate_contacts_install.py`, …) по документации hosting.

## Портфолио / кейсы

Шаблоны живут на REG.RU (нет в office-repo). После merge главной — отдельный проход по `template/page/` на хостинге или новый install по аналогии с team.