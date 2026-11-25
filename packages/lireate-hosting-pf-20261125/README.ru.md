# Пакет portfolio / reviews / uslugi (снимок с хостинга)

Источник: снимок с prod (`/_lxp-pf-851cb0915cc65250/pf.tgz`, 2026-11-25).

Содержимое:

- `template/page/portfolio.php`, `reviews.php`, `lireate-*-cms.inc.php`, `lireate-cms-db.inc.php` — CMS-вставки (без секретов; БД через `data/db.php` на хостинге)
- `template/page/portfolio/` — карточки кейсов
- `uslugi/` — каталог услуг (`lireate-uslugi-cms.inc.php`, CSS, lib)

Установка на DOCROOT: `scripts/lireate_hosting_pf_install.py` (dry-run по умолчанию, `--apply` + бэкап).

В репозитории зеркало для превью: `template/page/portfolio/`, `template/page/uslugi/`, `template/page/lireate-portfolio-cms.inc.php`, `template/page/lireate-reviews-cms.inc.php`.