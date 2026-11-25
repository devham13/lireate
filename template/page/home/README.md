# Шаблон главной LIREATE Light V4 (CMS home /)

Файлы копируются в `DOCROOT/template/page/home/` и `DOCROOT/pages/home/_shared/` (см. `scripts/lireate_home_light_v4_install.py`).

## Интеграция CMS

1. На хостинге: `python3 scripts/lireate_home_light_v4_install.py --check` (env: `LIREATE_DOCROOT`)
2. Применение: `python3 scripts/lireate_home_light_v4_install.py --apply`
3. Откат: `python3 scripts/lireate_home_light_v4_install.py --rollback <BACKUP_ID>`

Патч `template/main.php`:

- классы тела: `lireate-home-light-v4 lireate-theme-light-v4` для `$temp['page']=='home'` или корневого `REQUEST_URI`;
- в `<head>` — `template/css/lireate-home-light-v4.css`, `template/css/lireate-light-v4.css`, JS-аналоги **после** legacy;
- контент — `require` `lireate-home-cms.inc.php` в `page_container` **или** замена блока `.main-screen.lireate-hero-v4`, если маркеры найдены.

Форма заявки: `POST /mail.php`, класс `ajax_form`, согласие — `/private_data`, `/politika_konfidencialnosti`; UTM — через `/template/js/lireate-utm.js`.

Телефон по умолчанию: **+7 (960) 048-49-57**; Telegram / Max — как на `/kontaktyi/`.

Макет: `design-proposals/lireate-light-v4/home/index.html` (без `noindex`, без отдельного header/footer превью).