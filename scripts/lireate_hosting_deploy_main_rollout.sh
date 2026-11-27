#!/usr/bin/env bash
# Одноразовая выкладка PR #136 (форма/крошки/услуги) + PR #137 (hero мессенджеры).
# Запуск: ISPmanager Shell на REG.RU (не из Cloud — там SSH auth часто недоступен).
set -euo pipefail

export LIREATE_AUTOPUB_HOME="${LIREATE_AUTOPUB_HOME:-$HOME/lireate-autopub}"
export LIREATE_ENV_FILE="${LIREATE_ENV_FILE:-$LIREATE_AUTOPUB_HOME/.env}"
if [[ ! -f "$LIREATE_ENV_FILE" ]]; then
  echo "BLOCKER: missing $LIREATE_ENV_FILE"
  exit 2
fi
set -a
# shellcheck disable=SC1090
source <(grep -E '^[A-Za-z_][A-Za-z0-9_]*=' "$LIREATE_ENV_FILE" | sed 's/^/export /')
set +a
PY="$(cat "$LIREATE_AUTOPUB_HOME/.python-interpreter" 2>/dev/null || echo python3)"
OFFICE="${LIREATE_OFFICE_REPO:-$LIREATE_AUTOPUB_HOME/office-repo}"
DOCROOT="${LIREATE_DOCROOT:-}"
if [[ ! -d "$OFFICE/.git" || -z "$DOCROOT" || ! -d "$DOCROOT" ]]; then
  echo "BLOCKER: office-repo or docroot invalid"
  exit 2
fi
cd "$OFFICE"
git fetch origin main
git checkout -f -B main origin/main
echo "git_main=$(git rev-parse --short HEAD)"
export LIREATE_ALLOW_PUBLISH=yes

echo "=== services cron (sync + perf patch) ==="
bash "$OFFICE/scripts/lireate_hosting_services_cron.sh" || true

echo "=== overlay (home + cms css) ==="
"$PY" "$OFFICE/scripts/lireate_cms_light_v4_overlay_install.py" --docroot "$DOCROOT" --apply

echo "=== pf (contacts css) ==="
"$PY" "$OFFICE/scripts/lireate_hosting_pf_install.py" --docroot "$DOCROOT" --apply

echo "=== home light v4 (hero messengers) ==="
"$PY" "$OFFICE/scripts/lireate_home_light_v4_install.py" --docroot "$DOCROOT" --apply
"$PY" "$OFFICE/scripts/lireate_home_light_v4_install.py" --docroot "$DOCROOT" --check

echo "=== services --update-all ==="
"$PY" "$OFFICE/scripts/lireate_hosting_publish_service.py" \
  --apply --skip-git-pull --update-all

echo "=== rollout verify ==="
"$PY" "$OFFICE/scripts/lireate_light_v4_hosting_rollout.py" --docroot "$DOCROOT" --office-repo "$OFFICE"

echo "DEPLOY_MAIN_ROLLOUT_DONE"