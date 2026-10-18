# Публикация страниц услуг LIREATE на REG.RU

Скрипт лежит в репозитории **devham13/lireate** (`scripts/lireate_hosting_publish_service.py`), рядом с исходниками страниц `pages/services/<slug>/`. Новости по-прежнему публикуются из **devham13/lireate-news** в MySQL; услуги — отдельным потоком (копирование файлов в DOCROOT).

Production: `u3650956`, `server324.hosting.reg.ru`, `DOCROOT=/var/www/u3650956/data/www/public_html`.

## Почему скрипт в office-repo, а не в lireate-news

Страница услуги собирается агентами в `devham13/lireate` и коммитится в `pages/services/`. Публикатор на хостинге тянет **тот же** репозиторий и копирует каталог в `public_html`, без WordPress и без админки с капчей.

## Первая установка office-repo на хостинге

```bash
export LIREATE_AUTOPUB_HOME="$HOME/lireate-autopub"
mkdir -p "$LIREATE_AUTOPUB_HOME"
git clone https://github.com/devham13/lireate.git "$LIREATE_AUTOPUB_HOME/office-repo"
```

Добавьте в `$LIREATE_AUTOPUB_HOME/.env` (рядом с настройками новостей):

```bash
LIREATE_DOCROOT=/var/www/u3650956/data/www/public_html
LIREATE_PUBLIC_SITE_URL=https://${PUBLIC_SITE_HOST}
LIREATE_OFFICE_REPO=$HOME/lireate-autopub/office-repo
LIREATE_ALLOW_PUBLISH=no
```

## Обновить код office-repo

```bash
export LIREATE_AUTOPUB_HOME="$HOME/lireate-autopub"
cd "$LIREATE_AUTOPUB_HOME/office-repo"
git fetch origin main
git checkout main
git pull --ff-only
```

## Общие CSS/JS услуг

Файлы из `pages/services/_shared/` копируются в `assets/lireate-shared/` при публикации. В git перед merge желательно:

```bash
python3 scripts/lireate_service_sync_shared.py
```

## Dry-run (по умолчанию, без записи)

```bash
export LIREATE_AUTOPUB_HOME="$HOME/lireate-autopub"
export LIREATE_ENV_FILE="$LIREATE_AUTOPUB_HOME/.env"
PY="$(cat "$LIREATE_AUTOPUB_HOME/.python-interpreter" 2>/dev/null || echo python3)"
"$PY" "$LIREATE_AUTOPUB_HOME/office-repo/scripts/lireate_hosting_publish_service.py" \
  --slug registraciya-tovarnogo-znaka
```

Скрипт внутри dry-run может сделать `git pull` только при `--apply`; для предпросмотра без pull используйте локально актуальный `office-repo`.

## Реальная публикация

В `.env` установите `LIREATE_ALLOW_PUBLISH=yes`, затем:

```bash
export LIREATE_AUTOPUB_HOME="$HOME/lireate-autopub"
export LIREATE_ENV_FILE="$LIREATE_AUTOPUB_HOME/.env"
PY="$(cat "$LIREATE_AUTOPUB_HOME/.python-interpreter" 2>/dev/null || echo python3)"
"$PY" "$LIREATE_AUTOPUB_HOME/office-repo/scripts/lireate_hosting_publish_service.py" \
  --slug registraciya-tovarnogo-znaka \
  --apply
```

Успех: `STATUS: PUBLISHED` и HTTP 200 на `https://${PUBLIC_SITE_HOST}/uslugi/<slug>/` (или `url_path` из `publish.manifest.json`).

## CRON (пример: раз в час проверять очередь slug из env)

Публикацию по CRON лучше запускать только когда slug известен (после merge PR). Пример ручного cron для одной услуги:

```cron
TZ=Europe/Moscow
15 * * * * LIREATE_SERVICE_SLUG=registraciya-tovarnogo-znaka $(cat $HOME/lireate-autopub/.python-interpreter) $HOME/lireate-autopub/office-repo/scripts/lireate_hosting_publish_service.py --apply >> $HOME/lireate-autopub/logs/service-cron.log 2>&1
```

Перед включением cron убедитесь, что `LIREATE_ALLOW_PUBLISH=yes` осознанно.

## Логи и бэкапы

- Лог: `$HOME/lireate-autopub/logs/service-publish.log`
- Состояние: `$HOME/lireate-autopub/state/services-published.json`
- Бэкап при обновлении: `$HOME/lireate-autopub/backups/services/<slug>/<timestamp>/`

## Ограничения

- Cursor Cloud **не** имеет SSH/FTP к хостингу (RCPC).
- Админка `/admin/` с капчей **не** используется для автопубликации услуг.
- Скрипт не перезаписывает файлы в целевом каталоге, если они не перечислены в `.lireate-managed.json` от прошлой публикации LIREATE.
