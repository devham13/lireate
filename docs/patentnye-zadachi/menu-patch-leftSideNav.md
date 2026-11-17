# Патч меню: «Патентные задачи» (применить на сервере вручную)

**Не коммитить в `template/parts/leftSideNav.php` в git** — файл живёт только на хостинге.

## Файлы для правки

По аналогии с `scripts/lireate_hosting_menu_add.py` затронуть все PHP в DOCROOT с классом `leftSideNaviInSection` (как минимум):

- `template/parts/leftSideNav.php`
- мобильные/главные варианты (`leftSideNavMain.php` и т.п., если есть)

## Вставка (после пункта «МКТУ»)

Якорь (уже на проде):

```html
<a href="/mktu" class="leftSideNaviButton ">МКТУ</a>
```

Добавить **сразу после** блока кнопки МКТУ (или в списке `leftSideNaviInSection` рядом с инструментами), идемпотентный маркер:

```html
<!-- lireate-menu:patentnye-zadachi -->
<a href="/patentnye-zadachi/" class="leftSideNaviInSection">Патентные задачи</a>
```

Альтернатива для верхнего ряда кнопок (если нужен уровень `leftSideNaviButton`):

```html
<!-- lireate-menu:patentnye-zadachi -->
<a href="/patentnye-zadachi/" class="leftSideNaviButton">Патентные задачи</a>
```

## Автоматизация после merge

На хостинге можно также выполнить (dry-run сначала):

```bash
"$PY" "$OFFICE_REPO/scripts/lireate_hosting_menu_add.py" \
  --slug patentnye-zadachi --office-repo "$OFFICE_REPO" --env-file "$ENV_FILE"
# LIREATE_ALLOW_MENU_EDIT=yes ... --apply
```

В `publish.manifest.json` каталога задано `menu_after`: `МКТУ`.

## Откат

Удалить блок между `<!-- lireate-menu:patentnye-zadachi -->` и закрывающим `</a>`, восстановить файл из бэкапа `~/lireate-autopub/backups/`.