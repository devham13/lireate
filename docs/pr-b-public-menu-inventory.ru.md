# PR B — публичное меню и шаблоны

Источник sidebar: `pages/_shared/lireate-public-cms-sidebar.inc.php` (без Tochka).
subnav: `lireate_lh4_include_subnav()` на публичных CMS и услугах.
БД (`data/db.php`) не используется.

| page / раздел | sidebar | subnav | template / URL |
|---|---|---|---|
| contacts | yes | yes | `template/page/kontaktyi/lireate-contacts-cms.inc.php` |
| docs | yes | yes | `template/page/docs/lireate-docs-cms.inc.php` |
| kontaktyi | yes | yes | `template/page/kontaktyi/lireate-contacts-cms.inc.php` |
| mktu | yes | yes | `pages/mktu/_preview/index.php` |
| news | yes | yes | `template/page/news/lireate-blog-list.inc.php` |
| o_kompanii | yes | yes | `template/page/o_kompanii/lireate-about-cms.inc.php` |
| partners | yes | yes | `template/page/partners/lireate-partners-cms.inc.php` |
| portfolio | yes | yes | `template/page/lireate-portfolio-cms.inc.php` |
| reviews | yes | yes | `template/page/lireate-reviews-cms.inc.php` |
| services | yes | yes | ``pages/services/*/index.php`` |
| team | yes | yes | `template/page/team/lireate-team-cms.inc.php` |
| uslugi | yes | yes | `template/page/uslugi/lireate-uslugi-cms.inc.php` |
| — | — | — | `—` |
| (public) | yes | yes | `/uslugi/ (Все услуги)` |
| (public) | yes | yes | `/portfolio (Портфолио)` |
| (public) | yes | yes | `/reviews (Видео отзывы)` |
| (public) | yes | yes | `/news (Блог)` |
| (public) | yes | yes | `/mktu (МКТУ)` |
| (public) | yes | yes | `/patentnye-zadachi/ (Патентные задачи)` |
| (public) | yes | yes | `/o_kompanii (О компании)` |
| (public) | yes | yes | `/team (Команда Лирейт)` |
| (public) | yes | yes | `/partners (Партнёры)` |
| (public) | yes | yes | `/docs (Документы)` |
| (public) | yes | yes | `/contacts/ (Контакты)` |
| (public) | yes | yes | `/findtz/` → `pages/services/proverka-tovarnogo-znaka/` |
| (public) | yes | yes | `/prodlenie_tovarnogo_znaka/` → `pages/services/prodlenie-tovarnogo-znaka/` |
