# Деплой redesign «О компании» и «Контакты»

Канон оформления — как у `/team`: шапка и левое меню CMS, тёмный контент, мессенджеры, `lx-site-footer`. Не используется тёмный стиль «Патентных задач».

## Что меняется

- `/o_kompanii` — CMS-страница из БД (`$temp['apsw']`). Инсталлятор вставляет хук в `template/main.php` (вместо вывода HTML из БД) и подключает `pages/o_kompanii/_shared/`.
- `/contacts/` — файл `template/page/contacts/index.php` подключает уже существующий redesign `pages/kontaktyi/_shared/`. Класс `lireate-contacts-body` и тёмные CSS-списки добавляются и для slug `contacts` (раньше хук был только у `/kontaktyi/`, который 301 на `/contacts/`).
- SEO title/description у `/contacts/` не трогаем (`data/lireate-seo-page-meta.inc.php`).
- Адрес и карта — как на текущей живой странице: Зеленодольск, ул. Карла Маркса, д. 24, оф. 1.
- Форма на «Контактах» постит на `https://lireate.ru/mail.php` с теми же скрытыми полями, что у страниц услуг (UTM через `lireate-utm.js`).

Не изменяет: `index.php`, `.htaccess`, `mail.php`, `consent-gate.php`, `inc/lireate-amo-lead.php`, `data/db.php`.

## Подготовка

```bash
python3 scripts/lireate_about_sync_template.py
python3 scripts/lireate_contacts_sync_template.py
python3 scripts/lireate_about_install.py --check --docroot "$LIREATE_DOCROOT"
python3 scripts/lireate_contacts_install.py --check --docroot "$LIREATE_DOCROOT"
```

## Деплой на REG.RU

```bash
export LIREATE_DOCROOT=/path/to/site/docroot
python3 scripts/lireate_about_sync_template.py
python3 scripts/lireate_contacts_sync_template.py
python3 scripts/lireate_about_install.py --apply --docroot "$LIREATE_DOCROOT" --backups-root ~/lireate-autopub/backups/redesign1118-about-contacts
python3 scripts/lireate_contacts_install.py --apply --docroot "$LIREATE_DOCROOT" --backups-root ~/lireate-autopub/backups/redesign1118-about-contacts
```

Откат — `--rollback <UTC-id>` соответствующего инсталлятора либо `RESTORE.sh` из каталога бэкапа.
