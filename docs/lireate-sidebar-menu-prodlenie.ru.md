# Боковое меню CMS: пункт услуги после «Регистрация товарного знака»

На lireate.ru левое меню раздела «Услуги» рендерится из PHP-шаблона в `DOCROOT` (не из `data/db.php`). Подпункты блока «Товарный знак» используют класс `leftSideNaviInSection`.

Пример якоря (уже на проде):

```html
<a href="/registraciya_tovarnogo_znaka" class="leftSideNaviInSection">Регистрация товарного знака</a>
```

Для **продления** (`prodlenie-tovarnogo-znaka`) после успешной публикации CRON вызывает `scripts/lireate_hosting_menu_add.py`: вставляет ссылку **сразу после** этого якоря, идемпотентно (маркер `<!-- lireate-menu:<slug> -->`).

## Метаданные

В `publish.manifest.json` услуги:

| Поле | Назначение |
|------|------------|
| `title` | Текст пункта меню (если нет `menu_title`) |
| `menu_title` | Явный текст пункта меню |
| `url_path` | Путь URL (`prodlenie_tovarnogo_znaka` → `/prodlenie_tovarnogo_znaka/`) |
| `menu_after` | Видимый текст якоря, после которого вставлять пункт |

То же поле `menu_after` можно задать в `pages/services/queue.md` в записи услуги.

## Ручной запуск на хостинге

```bash
export LIREATE_AUTOPUB_HOME="$HOME/lireate-autopub"
export LIREATE_ENV_FILE="$LIREATE_AUTOPUB_HOME/.env"
PY="$(cat "$LIREATE_AUTOPUB_HOME/.python-interpreter")"
"$PY" "$LIREATE_AUTOPUB_HOME/office-repo/scripts/lireate_hosting_menu_add.py" \
  --slug prodlenie-tovarnogo-znaka
```

Запись в шаблон: `LIREATE_ALLOW_MENU_EDIT=yes` и флаг `--apply`. Без `--apply` — только путь к файлу и diff.

## Безопасность

- `data/db.php` **никогда** не редактируется.
- Перед записью создаётся бэкап `*.bak-<UTC>`.
- Повторный запуск не дублирует пункт.
