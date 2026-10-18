# Публикация страниц услуг LIREATE на REG.RU

Скрипты в **devham13/lireate** (`scripts/lireate_hosting_*`). Новости — отдельно из **devham13/lireate-news** (MySQL). Услуги — копирование `pages/services/<slug>/` в `DOCROOT`.

Production: `u3650956`, `DOCROOT=$HOME/www/public_html`, Python 3.8 из `~/lireate-autopub/.python-interpreter`.

## Почему office-repo на хостинге

Страница услуги коммитится в `devham13/lireate`. CRON на REG.RU тянет тот же репозиторий и выкладывает **только новые** slug (без перезаписи уже опубликованных).

## Первая установка (один блок для ISPmanager Shell)

Скопируйте целиком. Строка CRON **добавляется** к существующей задаче новостей (`lireate_hosting_cron.sh`), не заменяет её.

```bash
set -euo pipefail
export LIREATE_AUTOPUB_HOME="$HOME/lireate-autopub"
export LIREATE_ENV_FILE="$LIREATE_AUTOPUB_HOME/.env"
PY="$(cat "$LIREATE_AUTOPUB_HOME/.python-interpreter")"
OFFICE="$LIREATE_AUTOPUB_HOME/office-repo"
mkdir -p "$LIREATE_AUTOPUB_HOME/logs" "$LIREATE_AUTOPUB_HOME/state" "$LIREATE_AUTOPUB_HOME/var"
if [[ ! -d "$OFFICE/.git" ]]; then
  git clone https://github.com/devham13/lireate.git "$OFFICE"
fi
grep -q '^LIREATE_ALLOW_PUBLISH=' "$LIREATE_ENV_FILE" 2>/dev/null || echo 'LIREATE_ALLOW_PUBLISH=yes' >> "$LIREATE_ENV_FILE"
grep -q '^LIREATE_ALLOW_MENU_EDIT=' "$LIREATE_ENV_FILE" 2>/dev/null || echo 'LIREATE_ALLOW_MENU_EDIT=yes' >> "$LIREATE_ENV_FILE"
grep -q '^LIREATE_OFFICE_REPO=' "$LIREATE_ENV_FILE" 2>/dev/null || echo "LIREATE_OFFICE_REPO=$OFFICE" >> "$LIREATE_ENV_FILE"
grep -q '^LIREATE_DOCROOT=' "$LIREATE_ENV_FILE" 2>/dev/null || echo 'LIREATE_DOCROOT=$HOME/www/public_html' >> "$LIREATE_ENV_FILE"
CRON_LINE='*/30 * * * * bash $HOME/lireate-autopub/office-repo/scripts/lireate_hosting_services_cron.sh >> $HOME/lireate-autopub/logs/services-cron.log 2>&1'
( crontab -l 2>/dev/null | grep -v 'lireate_hosting_services_cron.sh' ; echo "$CRON_LINE" ) | crontab -
bash "$OFFICE/scripts/lireate_hosting_services_cron.sh" --init
bash "$OFFICE/scripts/lireate_hosting_services_cron.sh"
echo "OK: services CRON installed; check $LIREATE_AUTOPUB_HOME/logs/services-cron.log"
```

Ожидание после `--init`: в логе `STATUS: INIT_DONE`, в state — slug уже лежащих в `DOCROOT` услуг (например `registraciya-tovarnogo-znaka-v-kitae`). Первый обычный прогон: `STATUS: NO_NEW_SERVICE` или публикация одной новой услуги.

## Синхронизация office-repo (single-branch клон)

```bash
cd "$HOME/lireate-autopub/office-repo"
git fetch origin main
git checkout -B main FETCH_HEAD
```

`scripts/lireate_hosting_services_cron.sh` делает это автоматически каждые 30 минут.

## Скрипты

| Скрипт | Назначение |
|--------|------------|
| `lireate_hosting_services_cron.sh` | CRON: git sync → одна новая услуга → опционально пункт меню |
| `lireate_hosting_publish_service.py` | Копирование каталога услуги в `DOCROOT` (`--apply`) |
| `lireate_hosting_menu_add.py` | Пункт `leftSideNaviInSection` в шаблоне CMS |

Публикация одной страницы вручную:

```bash
"$PY" "$OFFICE/scripts/lireate_hosting_publish_service.py" --slug <slug> --apply --skip-git-pull
```

## Состояние и политика «не трогать старое»

- Текстовый state CRON: `$HOME/lireate-autopub/state/services-published.txt` (по одному slug на строку).
- JSON от публикатора: `$HOME/lireate-autopub/state/services-published.json`.
- Уже опубликованные slug **не** обновляются и **не** перезаписываются.
- Если каталог URL уже есть в `DOCROOT`, CRON только дописывает slug в state.
- `data/db.php` CRON и меню **не** меняют.

## Переменные `.env`

```bash
LIREATE_DOCROOT=/var/www/u3650956/data/www/public_html
LIREATE_PUBLIC_SITE_URL=https://${PUBLIC_SITE_HOST}
LIREATE_OFFICE_REPO=$HOME/lireate-autopub/office-repo
LIREATE_ALLOW_PUBLISH=yes
LIREATE_ALLOW_MENU_EDIT=yes   # без yes меню только dry-run в лог CRON
```

## Очередь в git vs хостинг

1. Cloud automation: PR → merge в `main` после зелёного `lireate_service_lint.py`.
2. В `pages/services/queue.md` для услуги: `status: done` (страница в репозитории).
3. В течение ~30 минут CRON публикует slug и дописывает `services-published.txt`.
4. Статус `published` в очереди выставляет хостинг (через state), не Cloud Agent.

## Безопасное обновление уже опубликованной страницы

CRON и обычный `--apply` **не перезаписывают** уже опубликованные slug. Чтобы выкатить новый дизайн или правки из git **только** по файлам, которые публиковал LIREATE (`.lireate-managed.json`, state, `assets/lireate-shared/*`):

```bash
export LIREATE_AUTOPUB_HOME="$HOME/lireate-autopub"
export LIREATE_ENV_FILE="$LIREATE_AUTOPUB_HOME/.env"
PY="$(cat "$LIREATE_AUTOPUB_HOME/.python-interpreter")"
OFFICE="$LIREATE_AUTOPUB_HOME/office-repo"
"$PY" "$OFFICE/scripts/lireate_hosting_publish_service.py" \
  --slug prodlenie-tovarnogo-znaka \
  --apply \
  --skip-git-pull \
  --update
```

Обновить **все** slug из `state/services-published.json`:

```bash
"$PY" "$OFFICE/scripts/lireate_hosting_publish_service.py" \
  --apply \
  --skip-git-pull \
  --update-all
```

Режим `--update` делает бэкап затронутых файлов. Если маркера нет, но slug есть в JSON-state (старые публикации), допускается полное обновление плана. Чужие файлы в каталоге не трогаются.

## Логи и бэкапы

- CRON: `$HOME/lireate-autopub/logs/services-cron.log`
- Публикация: `$HOME/lireate-autopub/logs/service-publish.log`
- Бэкап файлов услуги: `$HOME/lireate-autopub/backups/services/<slug>/<timestamp>/`
- Бэкап шаблона меню: `*.bak-<UTC>` рядом с файлом

## Локальная проверка (разработка)

```bash
bash scripts/test_lireate_hosting_services_local.sh
```

## Ограничения

- Cursor Cloud **не** имеет SSH/FTP к хостингу.
- Админка `/admin/` с капчей для автопубликации услуг **не** используется.
- Публикатор отказывается перезаписывать «чужие» файлы без `.lireate-managed.json`.
