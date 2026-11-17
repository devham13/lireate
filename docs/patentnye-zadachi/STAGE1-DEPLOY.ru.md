# Этап 1 — выкладка и откат

## Подтверждение контура

| Параметр | Значение |
|---|---|
| Репозиторий | `devham13/lireate` — исходники CMS (`pages/services/<slug>/`, `_shared/`, публикатор на хостинге в `~/lireate-autopub/office-repo`) |
| Ветка | `cursor/patentnye-zadachi-etap1-3ea6` от `main` |
| Staging | Отдельного staging нет; проверка — PHP preview (`scripts/lireate_patent_preview_server.sh`) + CI PHP 5.6 + скриншоты |

## Итоговые URL (канон, со слэшем)

- `/patentnye-zadachi/` — каталог
- `/patentnye-zadachi/poshliny-registraciya/` — калькулятор регистрации
- `/patentnye-zadachi/poshliny-prodlenie/` — калькулятор продления
- `/patentnye-zadachi/redaktor-mktu/` — редактор МКТУ

Сервисные лонгриды по-прежнему на подчёркиваниях (`/registraciya_tovarnogo_znaka/`). Раздел «Патентные задачи» — **дефисы**, вложенные пути; `url_path` в manifest поддерживает сегменты (`patentnye-zadachi/poshliny-registraciya`).

## Выкладка

Существующий CRON услуг публикует **по одному slug** из `pages/services/<slug>/`:

1. `patentnye-zadachi`
2. `patentnye-zadachi-poshliny-registraciya`
3. `patentnye-zadachi-poshliny-prodlenie`
4. `patentnye-zadachi-redaktor-mktu`

Скрипт `lireate_hosting_publish_service.py` копирует дерево в `DOCROOT/<url_path>/` и подкладывает `_shared` в `DOCROOT/_shared` и `DOCROOT/patentnye-zadachi/_shared` — **доп. скрипт не нужен**.

После merge: вручную или через state добавить slugs в очередь публикации / дождаться CRON (до 4 прогонов). Меню — `docs/patentnye-zadachi/menu-patch-leftSideNav.md` или `lireate_hosting_menu_add.py --slug patentnye-zadachi`.

## Откат

1. Удалить каталоги в DOCROOT: `patentnye-zadachi/` (или откат бэкапа autopub).
2. Убрать пункт меню (маркер `lireate-menu:patentnye-zadachi`).
3. В git — revert merge PR этапа 1.